<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ExportsCsv;
use App\Http\Controllers\Concerns\Sortable;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Services\ReorderService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller implements HasMiddleware
{
    use ExportsCsv, Sortable;

    private const SORTABLE_COLUMNS = ['po_number', 'order_date', 'status'];

    public static function middleware(): array
    {
        return [
            new Middleware('role:admin,manager,procurement', only: ['create', 'store', 'submit', 'generateDrafts']),
            new Middleware('role:admin,manager,supervisor,procurement,inventory_manager', only: ['approve', 'close']),
        ];
    }

    private function filteredPurchaseOrdersQuery(Request $request)
    {
        $query = PurchaseOrder::with('supplier');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $query = $this->filteredPurchaseOrdersQuery($request);
        $purchaseOrders = $this->applySort($query, $request, self::SORTABLE_COLUMNS, 'order_date', 'desc')
            ->paginate(20)->withQueryString();

        return view('purchase-orders.index', compact('purchaseOrders'));
    }

    public function export(Request $request)
    {
        $query = $request->filled('ids')
            ? PurchaseOrder::with('supplier')->whereIn('id', $request->input('ids'))
            : $this->filteredPurchaseOrdersQuery($request);

        $rows = $this->applySort($query, $request, self::SORTABLE_COLUMNS, 'order_date', 'desc')
            ->get()
            ->map(fn (PurchaseOrder $po) => [
                'number' => $po->po_number,
                'supplier' => $po->supplier?->name,
                'date' => $po->order_date?->format('Y-m-d'),
                'status' => ucfirst($po->status),
            ]);

        return $this->streamCsvExport('purchase-orders-' . now()->format('Ymd_His') . '.csv', [
            'number' => 'PO Number', 'supplier' => 'Supplier', 'date' => 'Order Date', 'status' => 'Status',
        ], $rows);
    }

    public function create()
    {
        $suppliers = Supplier::where('status', 'active')->get();

        return view('purchase-orders.create', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'po_number' => ['required', 'string', 'max:100', 'unique:purchase_orders,po_number'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date'],
            'status' => ['required', 'in:draft'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array'],
            'items.*.product_code' => ['required', 'string'],
            'items.*.product_description' => ['required', 'string'],
            'items.*.qty_ordered' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated) {
            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $validated['po_number'],
                'supplier_id' => $validated['supplier_id'],
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
                'requested_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                $lineTotal = round(((float) $item['qty_ordered'] * (float) $item['unit_cost']), 2);

                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_code' => $item['product_code'],
                    'product_description' => $item['product_description'],
                    'qty_ordered' => $item['qty_ordered'],
                    'unit_cost' => $item['unit_cost'],
                    'line_total' => $lineTotal,
                ]);
            }
        });

        return redirect()->route('purchase-orders.index')->with('success', 'Purchase order created successfully.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'items', 'requestedBy', 'approvedBy']);

        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    public function submit(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canBeSubmitted()) {
            return back()->with('error', 'Only draft purchase orders can be submitted.');
        }

        $purchaseOrder->submit();

        return back()->with('success', 'Purchase order submitted for approval.');
    }

    public function approve(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canBeApproved()) {
            return back()->with('error', 'Only submitted purchase orders can be approved.');
        }

        $purchaseOrder->approve(auth()->user());

        return back()->with('success', 'Purchase order approved.');
    }

    public function close(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canBeClosed()) {
            return back()->with('error', 'Only approved or received purchase orders can be closed.');
        }

        $purchaseOrder->close();

        return back()->with('success', 'Purchase order closed.');
    }

    /**
     * Create draft purchase orders for every low-stock product that has a
     * default supplier configured. Requires review/approval before submission.
     */
    public function generateDrafts(ReorderService $reorderService)
    {
        $created = $reorderService->generateDraftPurchaseOrders();

        return redirect()->route('purchase-orders.index')->with(
            'success',
            $created > 0
                ? "Generated {$created} draft purchase order(s) for low-stock products."
                : 'No new draft purchase orders were needed.'
        );
    }
}
