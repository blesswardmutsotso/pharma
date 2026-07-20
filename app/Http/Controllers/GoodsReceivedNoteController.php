<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\GoodsReceivedNote;
use App\Models\GoodsReceivedNoteItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Stock;
use App\Models\StockAuditLog;
use App\Models\StockBatch;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class GoodsReceivedNoteController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:admin,procurement,warehouse,inventory_manager', only: ['create', 'store']),
        ];
    }
    public function index()
    {
        $goodsReceivedNotes = GoodsReceivedNote::with(['supplier', 'purchaseOrder'])->latest()->paginate(20);

        return view('goods-received-notes.index', compact('goodsReceivedNotes'));
    }

    public function create()
    {
        $suppliers = Supplier::where('status', 'active')->get();
        $purchaseOrders = PurchaseOrder::with('supplier')->latest()->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $home = Branch::homeOrNull();

        return view('goods-received-notes.create', compact('suppliers', 'purchaseOrders', 'branches', 'home'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'grn_number' => ['required', 'string', 'max:100', 'unique:goods_received_notes,grn_number'],
            'purchase_order_id' => ['nullable', 'exists:purchase_orders,id'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'received_date' => ['required', 'date'],
            'status' => ['required', 'in:received,partial,returned'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array'],
            'items.*.product_code' => ['required', 'string', 'max:100'],
            'items.*.product_description' => ['required', 'string', 'max:255'],
            'items.*.qty_received' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.batch_number' => ['required', 'string', 'max:100'],
            'items.*.expiry_date' => ['required', 'date'],
            'items.*.status' => ['required', 'in:accepted,quarantine,rejected'],
        ]);

        DB::transaction(function () use ($validated) {
            $branchId = $validated['branch_id'] ?? null;

            $note = GoodsReceivedNote::create([
                'grn_number' => $validated['grn_number'],
                'purchase_order_id' => $validated['purchase_order_id'] ?? null,
                'supplier_id' => $validated['supplier_id'],
                'branch_id' => $branchId,
                'received_date' => $validated['received_date'],
                'received_by' => auth()->id(),
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $purchaseOrder = $note->purchase_order_id
                ? PurchaseOrder::find($note->purchase_order_id)
                : null;

            foreach ($validated['items'] as $item) {
                GoodsReceivedNoteItem::query()->insert([
                    'goods_received_note_id' => $note->id,
                    'product_code' => $item['product_code'],
                    'product_description' => $item['product_description'],
                    'qty_received' => (int) $item['qty_received'],
                    'unit_cost' => (float) $item['unit_cost'],
                    'batch_number' => $item['batch_number'],
                    'expiry_date' => Carbon::parse($item['expiry_date'])->toDateString(),
                    'status' => $item['status'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $stock = Stock::where('product_code', $item['product_code'])->first();
                $qtyReceived = (int) $item['qty_received'];

                if ($stock) {
                    $qtyBefore = (int) $stock->quantity;
                    $stock->buying_price = (float) $item['unit_cost'];
                    $stock->save();

                    // 'rejected' lines are not taken into inventory at all — no batch created.
                    if ($item['status'] !== 'rejected') {
                        StockBatch::create([
                            'product_code' => $item['product_code'],
                            'branch_id' => $branchId,
                            'batch_number' => $item['batch_number'],
                            'expiry_date'  => Carbon::parse($item['expiry_date'])->toDateString(),
                            'qty_on_hand'  => $qtyReceived,
                            'unit_cost'    => (float) $item['unit_cost'],
                            'status'       => $item['status'] === 'quarantine'
                                ? StockBatch::STATUS_QUARANTINE
                                : StockBatch::STATUS_ACTIVE,
                            'source_type'  => 'GoodsReceivedNote',
                            'source_id'    => $note->id,
                        ]);
                    }

                    $stock->syncQuantityFromBatches();

                    StockAuditLog::record(
                        action: StockAuditLog::STOCK_IN,
                        productCode: $item['product_code'],
                        productDescription: $item['product_description'],
                        qtyBefore: $qtyBefore,
                        qtyAfter: $stock->quantity,
                        notes: 'Goods received note '.$note->grn_number,
                        referenceType: 'GoodsReceivedNote',
                        referenceId: $note->id,
                        referenceLabel: $note->grn_number,
                    );
                }

                if ($purchaseOrder) {
                    $poItem = PurchaseOrderItem::where('purchase_order_id', $purchaseOrder->id)
                        ->where('product_code', $item['product_code'])
                        ->first();

                    if ($poItem) {
                        $poItem->increment('qty_received', $qtyReceived);
                        $poItem->refresh();

                        if ($poItem->hasDiscrepancy()) {
                            StockAuditLog::record(
                                action: StockAuditLog::GRN_DISCREPANCY,
                                productCode: $item['product_code'],
                                productDescription: $item['product_description'],
                                qtyBefore: $poItem->qty_ordered,
                                qtyAfter: $poItem->qty_received,
                                notes: sprintf(
                                    'PO %s: ordered %d, received %d (%s%d)',
                                    $purchaseOrder->po_number,
                                    $poItem->qty_ordered,
                                    $poItem->qty_received,
                                    $poItem->discrepancy() > 0 ? '+' : '',
                                    $poItem->discrepancy()
                                ),
                                referenceType: 'PurchaseOrder',
                                referenceId: $purchaseOrder->id,
                                referenceLabel: $purchaseOrder->po_number,
                            );
                        }
                    }
                }
            }

            if ($purchaseOrder) {
                $purchaseOrder->refreshReceivedStatus();
            }
        });

        return redirect()->route('goods-received-notes.index')->with('success', 'Goods received note created successfully.');
    }

    public function show(GoodsReceivedNote $goodsReceivedNote)
    {
        $goodsReceivedNote->load(['supplier', 'purchaseOrder', 'items']);

        return view('goods-received-notes.show', compact('goodsReceivedNote'));
    }

    public function pdf(Request $request, GoodsReceivedNote $goodsReceivedNote)
    {
        $goodsReceivedNote->load(['supplier', 'purchaseOrder', 'items']);

        $isDuplicate = $goodsReceivedNote->print_count > 0;
        $goodsReceivedNote->increment('print_count');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.grn', [
            'grn' => $goodsReceivedNote,
            'isDuplicate' => $isDuplicate,
        ])->setPaper('a4', 'portrait');

        $filename = "{$goodsReceivedNote->grn_number}.pdf";

        return $request->boolean('download') ? $pdf->download($filename) : $pdf->stream($filename);
    }
}
