<?php

namespace App\Http\Controllers;

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
use RuntimeException;

class SalesOrderController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:admin,sales', only: ['create', 'store', 'confirm', 'cancel']),
            new Middleware('role:admin,sales,warehouse', only: ['startPicking', 'dispatch', 'returnItem']),
        ];
    }
    public function index()
    {
        $salesOrders = SalesOrder::with('client')->latest()->paginate(20);

        return view('sales-orders.index', compact('salesOrders'));
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
            'so_number' => ['required', 'string', 'max:100', 'unique:sales_orders,so_number'],
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
                'so_number' => $validated['so_number'],
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
        $salesOrder->load(['client', 'items.batchAllocations.stockBatch']);

        return view('sales-orders.show', compact('salesOrder'));
    }

    public function confirm(SalesOrder $salesOrder, FefoAllocationService $fefo)
    {
        if (!$salesOrder->canBeConfirmed()) {
            return back()->with('error', 'Only draft sales orders can be confirmed.');
        }

        try {
            DB::transaction(function () use ($salesOrder, $fefo) {
                foreach ($salesOrder->items as $item) {
                    $fefo->allocate($item);
                }

                $salesOrder->update([
                    'status' => SalesOrder::STATUS_CONFIRMED,
                    'confirmed_by' => auth()->id(),
                ]);
            });
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Sales order confirmed and stock allocated (FEFO).');
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
        $salesOrder->load(['client', 'items.batchAllocations.stockBatch']);

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
