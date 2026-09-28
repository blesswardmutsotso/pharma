<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ExportsCsv;
use App\Http\Controllers\Concerns\Sortable;
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
    use ExportsCsv, Sortable;

    private const SORTABLE_COLUMNS = ['adjustment_no', 'type', 'status', 'created_at'];

    public static function middleware(): array
    {
        return [
            new Middleware('role:admin,manager,inventory_manager,warehouse', only: ['create', 'store']),
            new Middleware('role:admin,manager,supervisor,inventory_manager', only: ['approve', 'reject']),
        ];
    }

    private function filteredAdjustmentsQuery(Request $request)
    {
        $query = StockAdjustment::with(['branch', 'requestedBy', 'items']);

        if ($search = $request->get('search')) {
            $query->where('adjustment_no', 'like', "%{$search}%");
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $query = $this->filteredAdjustmentsQuery($request);
        $adjustments = $this->applySort($query, $request, self::SORTABLE_COLUMNS, 'created_at', 'desc')
            ->paginate(20)->withQueryString();

        return view('stock-adjustments.index', compact('adjustments'));
    }

    public function export(Request $request)
    {
        $query = $request->filled('ids')
            ? StockAdjustment::with(['branch', 'requestedBy', 'items'])->whereIn('id', $request->input('ids'))
            : $this->filteredAdjustmentsQuery($request);

        $rows = $this->applySort($query, $request, self::SORTABLE_COLUMNS, 'created_at', 'desc')
            ->get()
            ->map(fn (StockAdjustment $a) => [
                'number' => $a->adjustment_no,
                'type' => $a->typeLabel(),
                'branch' => $a->branch?->name,
                'requested_by' => $a->requestedBy?->name,
                'status' => ucfirst($a->status),
                'net_value_impact' => number_format($a->netValueImpact(), 2),
            ]);

        return $this->streamCsvExport('stock-adjustments-' . now()->format('Ymd_His') . '.csv', [
            'number' => 'Adjustment No.', 'type' => 'Type', 'branch' => 'Branch',
            'requested_by' => 'Requested By', 'status' => 'Status', 'net_value_impact' => 'Net Value Impact',
        ], $rows);
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
            'items.*.expiry_date' => ['nullable', 'date'],
            'items.*.qty_counted' => ['required', 'integer', 'min:0'],
        ]);

        // Found-stock lines (counted > what's actually in the batch system) that
        // aren't tied to an existing batch need a batch number + expiry date to
        // become a real, FEFO-allocatable batch on approval — otherwise the
        // "extra" stock would only ever exist as an aggregate number that Sales
        // Orders/invoicing can never actually allocate against.
        $lineErrors = [];
        foreach ($validated['items'] as $index => $item) {
            $batch = null;
            if (!empty($item['batch_number'])) {
                $batchQuery = StockBatch::where('product_code', $item['product_code'])
                    ->where('batch_number', $item['batch_number']);
                if (!empty($validated['branch_id'])) {
                    $batchQuery->where('branch_id', $validated['branch_id']);
                }
                $batch = $batchQuery->first();
            }

            if ($batch) {
                continue;
            }

            $batchesQuery = StockBatch::where('product_code', $item['product_code'])->active();
            if (!empty($validated['branch_id'])) {
                $batchesQuery->atBranch($validated['branch_id']);
            }
            $qtySystem = (int) $batchesQuery->sum('qty_on_hand');
            $variance = (int) $item['qty_counted'] - $qtySystem;

            if ($variance > 0 && (empty($item['batch_number']) || empty($item['expiry_date']))) {
                $lineErrors["items.{$index}.expiry_date"] = "Row " . ($index + 1) . " ({$item['product_code']}): counted more than the system has on record ({$qtySystem}) without matching an existing batch — provide a batch number and expiry date for the extra stock found.";
            }
        }

        if (!empty($lineErrors)) {
            throw \Illuminate\Validation\ValidationException::withMessages($lineErrors);
        }

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

                if ($batch) {
                    $qtySystem = $batch->qty_on_hand;
                } else {
                    $batchesQuery = StockBatch::where('product_code', $item['product_code'])->active();
                    if (!empty($validated['branch_id'])) {
                        $batchesQuery->atBranch($validated['branch_id']);
                    }
                    $qtySystem = (int) $batchesQuery->sum('qty_on_hand');
                }

                $qtyCounted = (int) $item['qty_counted'];

                StockAdjustmentItem::create([
                    'stock_adjustment_id' => $adjustment->id,
                    'product_code' => $item['product_code'],
                    'product_description' => $item['product_description'],
                    'stock_batch_id' => $batch?->id,
                    'batch_number' => $item['batch_number'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
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
        $stockAdjustment->load(['items.stockBatch', 'branch', 'requestedBy', 'approvedBy']);

        return view('stock-adjustments.show', ['adjustment' => $stockAdjustment]);
    }

    public function pdf(Request $request, StockAdjustment $stockAdjustment)
    {
        $stockAdjustment->load(['items.stockBatch', 'branch', 'approvedBy']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.stock-adjustment', [
            'adjustment' => $stockAdjustment,
        ])->setPaper('a4', 'landscape');

        $filename = "{$stockAdjustment->adjustment_no}.pdf";

        return $request->boolean('download') ? $pdf->download($filename) : $pdf->stream($filename);
    }

    public function approve(StockAdjustment $stockAdjustment)
    {
        if (!$stockAdjustment->canBeApproved()) {
            return back()->with('error', 'Only draft or submitted adjustments can be approved.');
        }

        // Adjustments submitted before this fix (or via a stale cached page)
        // could still be missing batch/expiry info for a found-stock line —
        // refuse to approve rather than fabricate an expiry date or silently
        // fall back to the old aggregate-only write.
        $incomplete = $stockAdjustment->items->first(
            fn (StockAdjustmentItem $item) => !$item->stock_batch_id && $item->qty_variance > 0 && (!$item->batch_number || !$item->expiry_date)
        );

        if ($incomplete) {
            return back()->with('error', sprintf(
                'Cannot approve — %s counts more than the system has on record but has no batch number/expiry date to create a real batch for the extra stock. Reject this adjustment and resubmit it with that information.',
                $incomplete->product_code
            ));
        }

        DB::transaction(function () use ($stockAdjustment) {
            foreach ($stockAdjustment->items as $item) {
                if ($item->qty_variance === 0) {
                    continue;
                }

                if ($item->stock_batch_id) {
                    // Matched an existing batch — adjust it directly (e.g. a
                    // recount of a specific batch, or damage found within it).
                    $batch = StockBatch::find($item->stock_batch_id);
                    if ($batch) {
                        $batch->qty_on_hand = max(0, $batch->qty_on_hand + $item->qty_variance);
                        if ($batch->qty_on_hand === 0) {
                            $batch->status = StockBatch::STATUS_DEPLETED;
                        }
                        $batch->save();
                    }
                } elseif ($item->qty_variance > 0) {
                    // Found stock with no matching batch — create a real,
                    // FEFO-allocatable batch instead of only bumping the
                    // aggregate quantity (which Sales Orders never read from).
                    StockBatch::create([
                        'product_code' => $item->product_code,
                        'branch_id' => $stockAdjustment->branch_id,
                        'batch_number' => $item->batch_number,
                        'expiry_date' => $item->expiry_date,
                        'qty_on_hand' => $item->qty_variance,
                        'unit_cost' => $item->unit_cost,
                        'status' => StockBatch::STATUS_ACTIVE,
                        'source_type' => 'StockAdjustment',
                        'source_id' => $stockAdjustment->id,
                    ]);
                } else {
                    // Shrinkage (damage/theft/miscount) not tied to one batch —
                    // absorb it from real batches, oldest-expiry first, so the
                    // reduction actually removes allocatable stock rather than
                    // just hiding it behind the aggregate quantity.
                    $toRemove = abs($item->qty_variance);
                    $batchesQuery = StockBatch::where('product_code', $item->product_code)->orderedForFefo();
                    if ($stockAdjustment->branch_id) {
                        $batchesQuery->atBranch($stockAdjustment->branch_id);
                    }
                    foreach ($batchesQuery->get() as $batch) {
                        if ($toRemove <= 0) {
                            break;
                        }
                        $take = min($batch->qty_on_hand, $toRemove);
                        if ($take <= 0) {
                            continue;
                        }
                        $batch->qty_on_hand -= $take;
                        if ($batch->qty_on_hand === 0) {
                            $batch->status = StockBatch::STATUS_DEPLETED;
                        }
                        $batch->save();
                        $toRemove -= $take;
                    }
                }

                $stock = Stock::where('product_code', $item->product_code)->first();
                if ($stock) {
                    $qtyBefore = $stock->quantity;
                    $stock->syncQuantityFromBatches();

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
