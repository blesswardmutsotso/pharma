<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ExportsCsv;
use App\Http\Controllers\Concerns\Sortable;
use App\Models\Branch;
use App\Models\Client;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Stock;
use App\Models\StockAuditLog;
use App\Models\StockBatch;
use App\Services\FefoAllocationService;
use App\Services\SalesInvoiceGenerationService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class SalesOrderController extends Controller implements HasMiddleware
{
    use ExportsCsv, Sortable;

    private const SORTABLE_COLUMNS = ['so_number', 'order_date', 'status'];

    public static function middleware(): array
    {
        return [
            new Middleware('role:admin,manager,sales', only: ['create', 'store']),
            new Middleware('role:admin,manager,supervisor,sales', only: ['confirm', 'cancel', 'allocateRemaining']),
            new Middleware('role:admin,manager,supervisor,sales,warehouse', only: ['startPicking', 'dispatch', 'returnItem']),
        ];
    }

    private function filteredSalesOrdersQuery(Request $request)
    {
        $query = SalesOrder::with('client');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('so_number', 'like', "%{$search}%")
                  ->orWhereHas('client', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $query = $this->filteredSalesOrdersQuery($request);
        $salesOrders = $this->applySort($query, $request, self::SORTABLE_COLUMNS, 'order_date', 'desc')
            ->paginate(20)->withQueryString();

        return view('sales-orders.index', compact('salesOrders'));
    }

    public function export(Request $request)
    {
        $query = $request->filled('ids')
            ? SalesOrder::with('client')->whereIn('id', $request->input('ids'))
            : $this->filteredSalesOrdersQuery($request);

        $rows = $this->applySort($query, $request, self::SORTABLE_COLUMNS, 'order_date', 'desc')
            ->get()
            ->map(fn (SalesOrder $so) => [
                'number' => $so->so_number,
                'client' => $so->client?->name,
                'date' => $so->order_date?->format('Y-m-d'),
                'status' => ucfirst($so->status),
            ]);

        return $this->streamCsvExport('sales-orders-' . now()->format('Ymd_His') . '.csv', [
            'number' => 'SO Number', 'client' => 'Client', 'date' => 'Order Date', 'status' => 'Status',
        ], $rows);
    }

    public function create()
    {
        $clients = Client::orderBy('name')->limit(200)->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $home = Branch::homeOrNull();

        return view('sales-orders.create', compact('clients', 'branches', 'home'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'order_date' => ['required', 'date'],
            'required_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_code' => ['required', 'string'],
            'items.*.product_description' => ['required', 'string'],
            'items.*.qty_ordered' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated) {
            $salesOrder = SalesOrder::create([
                'so_number' => SalesOrder::generateSoNumber(),
                'client_id' => $validated['client_id'],
                'branch_id' => $validated['branch_id'] ?? null,
                'order_date' => $validated['order_date'],
                'required_date' => $validated['required_date'] ?? null,
                'status' => SalesOrder::STATUS_DRAFT,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                $lineTotal = round((float) $item['qty_ordered'] * (float) $item['unit_price'], 2);

                SalesOrderItem::create([
                    'sales_order_id' => $salesOrder->id,
                    'product_code' => $item['product_code'],
                    'product_description' => $item['product_description'],
                    'qty_ordered' => $item['qty_ordered'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $lineTotal,
                ]);
            }
        });

        return redirect()->route('sales-orders.index')->with('success', 'Sales order created successfully.');
    }

    public function show(SalesOrder $salesOrder)
    {
        $salesOrder->load(['client', 'branch', 'createdBy', 'confirmedBy', 'items.batchAllocations.stockBatch']);

        return view('sales-orders.show', compact('salesOrder'));
    }

    public function pdf(Request $request, SalesOrder $salesOrder)
    {
        $salesOrder->load(['client', 'branch', 'createdBy', 'confirmedBy', 'items']);

        $isDuplicate = $salesOrder->print_count > 0;
        $salesOrder->increment('print_count');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.sales-order', [
            'salesOrder' => $salesOrder,
            'isDuplicate' => $isDuplicate,
        ])->setPaper('a4', 'portrait');

        $filename = "{$salesOrder->so_number}.pdf";

        return $request->boolean('download') ? $pdf->download($filename) : $pdf->stream($filename);
    }

    public function confirm(SalesOrder $salesOrder, FefoAllocationService $fefo)
    {
        if (!$salesOrder->canBeConfirmed()) {
            return back()->with('error', 'Only draft sales orders can be confirmed.');
        }

        $client = $salesOrder->client;
        $orderValue = (float) $salesOrder->items->sum('line_total');

        if ($client && $client->wouldExceedCreditLimit($orderValue)) {
            return back()->with('error', sprintf(
                'Cannot confirm: %s would exceed their credit limit (outstanding %s + this order %s > limit %s).',
                $client->name,
                number_format($client->outstandingBalance(), 2),
                number_format($orderValue, 2),
                number_format((float) $client->credit_limit, 2),
            ));
        }

        DB::transaction(function () use ($salesOrder, $fefo) {
            foreach ($salesOrder->items as $item) {
                $fefo->allocate($item);
            }

            $salesOrder->update([
                'status' => SalesOrder::STATUS_CONFIRMED,
                'confirmed_by' => auth()->id(),
            ]);
        });

        $backordered = $salesOrder->items->filter->isBackordered();

        if ($backordered->isNotEmpty()) {
            $lines = $backordered->map(fn (SalesOrderItem $i) => "{$i->product_code} ({$i->backorderedQty()} short)")->implode(', ');

            return back()->with('success', "Sales order confirmed. Stock allocated where available — backordered: {$lines}. Use \"Allocate Remaining Stock\" once more stock arrives.");
        }

        return back()->with('success', 'Sales order confirmed and stock fully allocated (FEFO).');
    }

    /**
     * Re-attempt FEFO allocation for any items still short on stock —
     * lets a backordered order be topped up once new stock arrives (e.g.
     * after a follow-up GRN) without needing to raise a new order.
     */
    public function allocateRemaining(SalesOrder $salesOrder, FefoAllocationService $fefo)
    {
        if (!in_array($salesOrder->status, [SalesOrder::STATUS_CONFIRMED, SalesOrder::STATUS_PICKING], true)) {
            return back()->with('error', 'Only confirmed or picking orders can have backorders allocated.');
        }

        DB::transaction(function () use ($salesOrder, $fefo) {
            foreach ($salesOrder->items as $item) {
                $fefo->allocate($item);
            }
        });

        $stillBackordered = $salesOrder->items->filter->isBackordered();

        if ($stillBackordered->isNotEmpty()) {
            return back()->with('success', 'Allocated whatever new stock was available — some items are still backordered.');
        }

        return back()->with('success', 'All previously backordered items are now fully allocated.');
    }

    public function startPicking(SalesOrder $salesOrder)
    {
        if (!$salesOrder->canStartPicking()) {
            return back()->with('error', 'Only confirmed sales orders can move to picking.');
        }

        $salesOrder->update(['status' => SalesOrder::STATUS_PICKING]);

        return redirect()->route('sales-orders.picking-list', $salesOrder)
            ->with('success', 'Sales order moved to picking. Picking list is ready.');
    }

    public function pickingList(SalesOrder $salesOrder)
    {
        $salesOrder->load(['client', 'branch', 'items.batchAllocations.stockBatch']);

        return view('sales-orders.picking-list', compact('salesOrder'));
    }

    public function dispatch(SalesOrder $salesOrder, FefoAllocationService $fefo, SalesInvoiceGenerationService $invoicer)
    {
        if (!$salesOrder->canBeDispatched()) {
            return back()->with('error', 'Only sales orders in picking can be dispatched.');
        }

        DB::transaction(function () use ($salesOrder, $fefo, $invoicer) {
            foreach ($salesOrder->items as $item) {
                $fefo->dispatchAllocations($item);

                $stock = Stock::where('product_code', $item->product_code)->first();
                if ($stock) {
                    $qtyBefore = $stock->quantity;
                    $stock->syncQuantityFromBatches();

                    StockAuditLog::record(
                        action: StockAuditLog::SALE,
                        productCode: $item->product_code,
                        productDescription: $item->product_description,
                        qtyBefore: $qtyBefore,
                        qtyAfter: $stock->quantity,
                        notes: 'Sales order ' . $salesOrder->so_number . ' dispatched',
                        referenceType: 'SalesOrder',
                        referenceId: $salesOrder->id,
                        referenceLabel: $salesOrder->so_number,
                    );
                }
            }

            $salesOrder->update([
                'status' => SalesOrder::STATUS_DISPATCHED,
                'dispatched_at' => now(),
            ]);

            // BRD FR-INV-001: tax invoice generated automatically upon dispatch.
            $invoicer->generateFor($salesOrder->fresh(['items.batchAllocations.stockBatch']));
            $salesOrder->update(['status' => SalesOrder::STATUS_INVOICED]);
        });

        return back()->with('success', 'Sales order dispatched and invoice generated.');
    }

    public function cancel(SalesOrder $salesOrder, FefoAllocationService $fefo)
    {
        if (!$salesOrder->canBeCancelled()) {
            return back()->with('error', 'This sales order can no longer be cancelled.');
        }

        DB::transaction(function () use ($salesOrder, $fefo) {
            foreach ($salesOrder->items as $item) {
                $fefo->releaseAllocations($item);
            }

            $salesOrder->update(['status' => SalesOrder::STATUS_CANCELLED]);
        });

        return back()->with('success', 'Sales order cancelled and reserved stock released.');
    }

    /**
     * Customer return — goods go to quarantine pending inspection rather
     * than straight back into sellable stock.
     */
    public function returnItem(Request $request, SalesOrder $salesOrder)
    {
        $validated = $request->validate([
            'sales_order_item_id' => ['required', 'exists:sales_order_items,id'],
            'qty' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $item = SalesOrderItem::where('sales_order_id', $salesOrder->id)
            ->findOrFail($validated['sales_order_item_id']);

        DB::transaction(function () use ($item, $validated, $salesOrder) {
            $stock = Stock::where('product_code', $item->product_code)->first();
            $qtyBefore = $stock?->quantity ?? 0;

            StockBatch::create([
                'product_code' => $item->product_code,
                'batch_number' => 'RETURN-' . $salesOrder->so_number,
                'expiry_date' => now()->addYear(),
                'qty_on_hand' => $validated['qty'],
                'unit_cost' => $item->unit_price,
                'status' => StockBatch::STATUS_QUARANTINE,
                'source_type' => 'SalesOrderReturn',
                'source_id' => $salesOrder->id,
            ]);

            if ($stock) {
                $stock->syncQuantityFromBatches();

                StockAuditLog::record(
                    action: StockAuditLog::RETURN_GOODS,
                    productCode: $item->product_code,
                    productDescription: $item->product_description,
                    qtyBefore: $qtyBefore,
                    qtyAfter: $stock->quantity,
                    notes: 'Return from sales order ' . $salesOrder->so_number . ': ' . ($validated['reason'] ?? ''),
                    referenceType: 'SalesOrder',
                    referenceId: $salesOrder->id,
                    referenceLabel: $salesOrder->so_number,
                );
            }
        });

        return back()->with('success', 'Return recorded — goods quarantined pending inspection.');
    }
}
