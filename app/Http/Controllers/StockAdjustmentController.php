<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Stock;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\StockAuditLog;
use App\Models\StockBatch;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:admin,inventory_manager,warehouse', only: ['create', 'store']),
            new Middleware('role:admin,inventory_manager', only: ['approve', 'reject']),
        ];
    }

    public function index(Request $request)
    {
        $query = StockAdjustment::with(['branch', 'requestedBy'])->latest();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        $adjustments = $query->paginate(20)->withQueryString();

        return view('stock-adjustments.index', compact('adjustments'));
    }

    public function create()
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $home = Branch::homeOrNull();

        return view('stock-adjustments.create', [
            'branches' => $branches,
            'home' => $home,
            'types' => StockAdjustment::types(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'exists:branches,id'],
            'type' => ['required', 'in:' . implode(',', array_keys(StockAdjustment::types()))],
            'reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_code' => ['required', 'string', 'max:100'],
            'items.*.product_description' => ['required', 'string', 'max:255'],
            'items.*.batch_number' => ['nullable', 'string', 'max:100'],
            'items.*.qty_counted' => ['required', 'integer', 'min:0'],
        ]);

        $adjustment = DB::transaction(function () use ($validated) {
            $adjustment = StockAdjustment::create([
                'adjustment_no' => StockAdjustment::generateAdjustmentNo(),
                'branch_id' => $validated['branch_id'] ?? null,
                'type' => $validated['type'],
                'status' => StockAdjustment::STATUS_SUBMITTED,
                'reason' => $validated['reason'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'requested_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                $batch = null;
                if (!empty($item['batch_number'])) {
                    $batchQuery = StockBatch::where('product_code', $item['product_code'])
                        ->where('batch_number', $item['batch_number']);
                    if (!empty($validated['branch_id'])) {
                        $batchQuery->where('branch_id', $validated['branch_id']);
                    }
                    $batch = $batchQuery->first();
                }

                $stock = Stock::where('product_code', $item['product_code'])->first();
                $qtySystem = $batch ? $batch->qty_on_hand : (int) ($stock?->quantity ?? 0);
                $qtyCounted = (int) $item['qty_counted'];

                StockAdjustmentItem::create([
                    'stock_adjustment_id' => $adjustment->id,
                    'product_code' => $item['product_code'],
                    'product_description' => $item['product_description'],
                    'stock_batch_id' => $batch?->id,
                    'batch_number' => $item['batch_number'] ?? null,
                    'qty_system' => $qtySystem,
                    'qty_counted' => $qtyCounted,
                    'qty_variance' => $qtyCounted - $qtySystem,
                    'unit_cost' => $batch?->unit_cost ?? $stock?->buying_price ?? 0,
                ]);
            }

            return $adjustment;
        });

        return redirect()->route('stock-adjustments.show', $adjustment)
            ->with('success', "Adjustment {$adjustment->adjustment_no} submitted for approval.");
    }

    public function show(StockAdjustment $stockAdjustment)
    {
        $stockAdjustment->load(['items', 'branch', 'requestedBy', 'approvedBy']);

        return view('stock-adjustments.show', ['adjustment' => $stockAdjustment]);
    }

    public function pdf(Request $request, StockAdjustment $stockAdjustment)
    {
        $stockAdjustment->load(['items', 'branch', 'approvedBy']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.stock-adjustment', [
            'adjustment' => $stockAdjustment,
        ])->setPaper('a4', 'portrait');

        $filename = "{$stockAdjustment->adjustment_no}.pdf";

        return $request->boolean('download') ? $pdf->download($filename) : $pdf->stream($filename);
    }

    public function approve(StockAdjustment $stockAdjustment)
    {
        if (!$stockAdjustment->canBeApproved()) {
            return back()->with('error', 'Only draft or submitted adjustments can be approved.');
        }

        DB::transaction(function () use ($stockAdjustment) {
            foreach ($stockAdjustment->items as $item) {
                if ($item->qty_variance === 0) {
                    continue;
                }

                if ($item->stock_batch_id) {
                    $batch = StockBatch::find($item->stock_batch_id);
                    if ($batch) {
                        $batch->qty_on_hand = max(0, $batch->qty_on_hand + $item->qty_variance);
                        if ($batch->qty_on_hand === 0) {
                            $batch->status = StockBatch::STATUS_DEPLETED;
                        }
                        $batch->save();
                    }
                }

                $stock = Stock::where('product_code', $item->product_code)->first();
                if ($stock) {
                    $qtyBefore = $stock->quantity;

                    if ($item->stock_batch_id) {
                        $stock->syncQuantityFromBatches();
                    } else {
                        $stock->quantity = max(0, $stock->quantity + $item->qty_variance);
                        $stock->save();
                    }

                    StockAuditLog::record(
                        action: StockAuditLog::ADJUSTMENT,
                        productCode: $item->product_code,
                        productDescription: $item->product_description,
                        qtyBefore: $qtyBefore,
                        qtyAfter: $stock->quantity,
                        notes: $stockAdjustment->typeLabel() . ($stockAdjustment->reason ? ': ' . $stockAdjustment->reason : ''),
                        referenceType: 'StockAdjustment',
                        referenceId: $stockAdjustment->id,
                        referenceLabel: $stockAdjustment->adjustment_no,
                    );
                }
            }

            $stockAdjustment->update([
                'status' => StockAdjustment::STATUS_APPROVED,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        });

        return back()->with('success', "Adjustment {$stockAdjustment->adjustment_no} approved and stock updated.");
    }

    public function reject(StockAdjustment $stockAdjustment)
    {
        if (!$stockAdjustment->canBeApproved()) {
            return back()->with('error', 'This adjustment cannot be rejected in its current status.');
        }

        $stockAdjustment->update([
            'status' => StockAdjustment::STATUS_REJECTED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', "Adjustment {$stockAdjustment->adjustment_no} rejected. No stock changes made.");
    }
}
