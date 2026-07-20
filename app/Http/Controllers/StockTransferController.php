<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Branch;
use App\Models\Stock;
use App\Models\StockBatch;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\StockAuditLog;
use App\Exports\StockTransferExport;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class StockTransferController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:admin,warehouse,inventory_manager,procurement', only: [
                'create', 'store', 'import', 'importPreview', 'importConfirm',
            ]),
            new Middleware('role:admin,warehouse,inventory_manager', only: [
                'approve', 'reject', 'cancel',
            ]),
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = StockTransfer::with(['fromBranch', 'toBranch', 'requestedBy'])
            ->latest();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($type = $request->get('type')) {
            $query->where('transfer_type', $type);
        }
        if ($branch = $request->get('branch')) {
            $query->where(fn($q) => $q->where('from_branch_id', $branch)->orWhere('to_branch_id', $branch));
        }
        if ($from = $request->get('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $transfers = $query->paginate(20)->withQueryString();
        $branches  = Branch::where('is_active', true)->orderBy('name')->get();

        $stats = Cache::remember('transfers:stats', 120, fn() => [
            'total'    => StockTransfer::count(),
            'pending'  => StockTransfer::where('status', StockTransfer::STATUS_PENDING)->count(),
            'approved' => StockTransfer::where('status', StockTransfer::STATUS_APPROVED)->count(),
            'this_month' => StockTransfer::whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)->count(),
        ]);

        return view('stock.transfers.index', compact('transfers', 'branches', 'stats'));
    }

    // ─────────────────────────────────────────────────────────────
    // CREATE FORM
    // ─────────────────────────────────────────────────────────────

    public function create()
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $home     = Branch::where('is_home', true)->first();
        return view('stock.transfers.create', compact('branches', 'home'));
    }

    // ─────────────────────────────────────────────────────────────
    // STORE (draft or submit)
    // ─────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'transfer_type'  => 'required|in:OUTGOING,INCOMING',
            'from_branch_id' => 'nullable|exists:branches,id',
            'to_branch_id'   => 'nullable|exists:branches,id',
            'notes'          => 'nullable|string|max:1000',
            'reference_doc'  => 'nullable|string|max:100',
            'items'          => 'required|json',
        ]);

        $items = json_decode($request->input('items'), true);

        if (empty($items)) {
            return back()->with('error', 'No items added to the transfer.')->withInput();
        }

        // Validate each item
        $errors = [];
        foreach ($items as $i => $item) {
            if (empty($item['product_code']))  $errors[] = "Row " . ($i + 1) . ": product code is required.";
            if (empty($item['qty_requested']) || $item['qty_requested'] < 1) $errors[] = "Row " . ($i + 1) . ": quantity must be at least 1.";
        }
        if ($errors) {
            return back()->with('error', implode(' ', $errors))->withInput();
        }

        $action = $request->input('action', 'draft'); // 'draft' or 'submit'

        DB::beginTransaction();
        try {
            $totalQty = array_sum(array_column($items, 'qty_requested'));

            $transfer = StockTransfer::create([
                'transfer_no'    => StockTransfer::generateTransferNo(),
                'transfer_type'  => $request->transfer_type,
                'from_branch_id' => $request->from_branch_id,
                'to_branch_id'   => $request->to_branch_id,
                'status'         => $action === 'submit' ? StockTransfer::STATUS_PENDING : StockTransfer::STATUS_DRAFT,
                'notes'          => $request->notes,
                'reference_doc'  => $request->reference_doc,
                'total_items'    => count($items),
                'total_qty'      => $totalQty,
                'requested_by'   => auth()->id(),
            ]);

            foreach ($items as $item) {
                $stock = Stock::where('product_code', $item['product_code'])->first();
                StockTransferItem::create([
                    'transfer_id'         => $transfer->id,
                    'product_code'        => $item['product_code'],
                    'product_description' => $item['product_description'] ?? ($stock?->product_description ?? $item['product_code']),
                    'qty_requested'       => (int) $item['qty_requested'],
                    'buying_price'        => $stock?->buying_price  ?? 0,
                    'selling_price'       => $stock?->selling_price ?? 0,
                    'tax_code'            => $stock?->tax_code ?? null,
                    'notes'               => $item['notes'] ?? null,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('StockTransfer store failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to create transfer: ' . $e->getMessage())->withInput();
        }

        Cache::forget('transfers:stats');

        $message = $action === 'submit'
            ? "Transfer {$transfer->transfer_no} submitted for approval."
            : "Transfer {$transfer->transfer_no} saved as draft.";

        return redirect()->route('stock.transfers.show', $transfer)->with('success', $message);
    }

    // ─────────────────────────────────────────────────────────────
    // SHOW
    // ─────────────────────────────────────────────────────────────

    public function show(StockTransfer $transfer)
    {
        $transfer->load(['items', 'fromBranch', 'toBranch', 'requestedBy', 'approvedBy']);

        $auditLogs = StockAuditLog::where('reference_type', 'StockTransfer')
            ->where('reference_id', $transfer->id)
            ->orderBy('created_at')
            ->get();

        // Enrich items with current stock level for comparison
        $codes        = $transfer->items->pluck('product_code');
        $currentStock = Stock::whereIn('product_code', $codes)->get()->keyBy('product_code');

        return view('stock.transfers.show', compact('transfer', 'auditLogs', 'currentStock'));
    }

    // ─────────────────────────────────────────────────────────────
    // APPROVE
    // ─────────────────────────────────────────────────────────────

    public function approve(Request $request, StockTransfer $transfer)
    {
        if (!in_array($transfer->status, [StockTransfer::STATUS_DRAFT, StockTransfer::STATUS_PENDING])) {
            return back()->with('error', 'Only DRAFT or PENDING transfers can be approved.');
        }

        $transfer->load('items');

        // Pre-validate stock availability for OUTGOING transfers
        if ($transfer->transfer_type === StockTransfer::TYPE_OUTGOING) {
            $insufficient = [];
            foreach ($transfer->items as $item) {
                $effectiveQty = $item->qty_approved ?? $item->qty_requested;
                $stock = Stock::where('product_code', $item->product_code)->first();
                if (!$stock || $stock->quantity < $effectiveQty) {
                    $insufficient[] = "{$item->product_code} (need {$effectiveQty}, have " . ($stock?->quantity ?? 0) . ")";
                }
            }
            if ($insufficient) {
                return back()->with('error', 'Insufficient stock for: ' . implode(', ', $insufficient));
            }
        }

        DB::beginTransaction();
        try {
            foreach ($transfer->items as $item) {
                $effectiveQty = $item->qty_approved ?? $item->qty_requested;
                $stock = Stock::where('product_code', $item->product_code)->first();

                if ($transfer->transfer_type === StockTransfer::TYPE_OUTGOING) {
                    if ($stock) {
                        $qtyBefore = $stock->quantity;
                        $stock->quantity = max(0, $stock->quantity - $effectiveQty);
                        $stock->save();

                        // Move the physical batches between locations (best-effort —
                        // only applies to whatever batch qty actually exists at the
                        // source branch; the aggregate quantity above is always correct
                        // regardless of batch-level location detail being available).
                        if ($transfer->from_branch_id) {
                            $this->moveBranchBatches(
                                $item->product_code,
                                $effectiveQty,
                                $transfer->from_branch_id,
                                $transfer->to_branch_id,
                                $transfer->id
                            );
                        }

                        StockAuditLog::record(
                            action:         StockAuditLog::TRANSFER_OUT,
                            productCode:    $item->product_code,
                            productDescription: $item->product_description,
                            qtyBefore:      $qtyBefore,
                            qtyAfter:       $stock->quantity,
                            notes:          "Transfer to: " . ($transfer->toBranch?->name ?? 'External'),
                            referenceType:  'StockTransfer',
                            referenceId:    $transfer->id,
                            referenceLabel: $transfer->transfer_no
                        );
                    }
                } else {
                    // INCOMING — add to stock
                    if ($stock) {
                        $qtyBefore = $stock->quantity;
                        $stock->quantity += $effectiveQty;
                        $stock->save();
                    } else {
                        // Create new stock record from transfer item data
                        $stock = Stock::create([
                            'product_code'        => $item->product_code,
                            'product_description' => $item->product_description,
                            'buying_price'        => $item->buying_price,
                            'selling_price'       => $item->selling_price,
                            'quantity'            => $effectiveQty,
                            'tax_code'            => $item->tax_code ?? 'EX',
                            'tax_id'              => 1,
                            'tax_percentage'      => 0,
                            'tax_amount'          => 0,
                            'sales_amount_with_tax' => $item->selling_price,
                        ]);
                        $qtyBefore = 0;
                    }

                    StockAuditLog::record(
                        action:         StockAuditLog::TRANSFER_IN,
                        productCode:    $item->product_code,
                        productDescription: $item->product_description,
                        qtyBefore:      $qtyBefore,
                        qtyAfter:       $stock->quantity,
                        notes:          "Transfer from: " . ($transfer->fromBranch?->name ?? 'External'),
                        referenceType:  'StockTransfer',
                        referenceId:    $transfer->id,
                        referenceLabel: $transfer->transfer_no
                    );
                }
            }

            $transfer->update([
                'status'      => StockTransfer::STATUS_APPROVED,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('StockTransfer approve failed: ' . $e->getMessage());
            return back()->with('error', 'Approval failed: ' . $e->getMessage());
        }

        $this->bustStockCache();
        Cache::forget('transfers:stats');

        return back()->with('success', "Transfer {$transfer->transfer_no} approved. Stock updated.");
    }

    // ─────────────────────────────────────────────────────────────
    // REJECT
    // ─────────────────────────────────────────────────────────────

    public function reject(Request $request, StockTransfer $transfer)
    {
        if (!in_array($transfer->status, [StockTransfer::STATUS_DRAFT, StockTransfer::STATUS_PENDING])) {
            return back()->with('error', 'This transfer cannot be rejected in its current status.');
        }

        $request->validate(['reject_reason' => 'nullable|string|max:500']);

        $transfer->update([
            'status'        => StockTransfer::STATUS_REJECTED,
            'reject_reason' => $request->reject_reason ?? 'No reason provided.',
            'approved_by'   => auth()->id(),
            'approved_at'   => now(),
        ]);

        Cache::forget('transfers:stats');

        return back()->with('success', "Transfer {$transfer->transfer_no} rejected.");
    }

    // ─────────────────────────────────────────────────────────────
    // CANCEL
    // ─────────────────────────────────────────────────────────────

    public function cancel(StockTransfer $transfer)
    {
        if (!in_array($transfer->status, [StockTransfer::STATUS_DRAFT, StockTransfer::STATUS_PENDING])) {
            return back()->with('error', 'Only DRAFT or PENDING transfers can be cancelled.');
        }

        $transfer->update(['status' => StockTransfer::STATUS_CANCELLED]);
        Cache::forget('transfers:stats');

        return back()->with('success', "Transfer {$transfer->transfer_no} cancelled.");
    }

    // ─────────────────────────────────────────────────────────────
    // EXPORT (download as Excel)
    // ─────────────────────────────────────────────────────────────

    public function export(StockTransfer $transfer)
    {
        $transfer->load('items', 'fromBranch', 'toBranch', 'requestedBy', 'approvedBy');
        $filename = $transfer->transfer_no . '-' . now()->format('Ymd') . '.xlsx';
        return Excel::download(new StockTransferExport($transfer), $filename);
    }

    // ─────────────────────────────────────────────────────────────
    // IMPORT TEMPLATE (blank Excel for filling in)
    // ─────────────────────────────────────────────────────────────

    public function template()
    {
        $stocks = Stock::orderBy('product_code')
            ->get(['product_code', 'product_description', 'buying_price', 'selling_price', 'tax_code', 'quantity']);

        $filename = 'stock-transfer-template-' . now()->format('Ymd') . '.xlsx';

        return Excel::download(new \App\Exports\StockImportTemplateExport($stocks), $filename);
    }

    // ─────────────────────────────────────────────────────────────
    // IMPORT (parse uploaded file → create INCOMING transfer)
    // ─────────────────────────────────────────────────────────────

    public function import(Request $request)
    {
        $request->validate([
            'file'           => 'required|file|mimes:xlsx,csv,xls|max:5120',
            'from_branch_id' => 'nullable|exists:branches,id',
            'to_branch_id'   => 'nullable|exists:branches,id',
            'reference_doc'  => 'nullable|string|max:100',
            'notes'          => 'nullable|string|max:1000',
        ]);

        try {
            $rows = Excel::toCollection(null, $request->file('file'))->first();
        } catch (\Exception $e) {
            return back()->with('error', 'Could not read file: ' . $e->getMessage())->withInput();
        }

        if (!$rows || $rows->count() < 2) {
            return back()->with('error', 'File is empty or has no data rows.')->withInput();
        }

        // Detect header row and build column index map
        $headers  = $rows->first()->map(fn($h) => strtolower(trim((string) $h)))->toArray();
        $codeIdx  = array_search('product_code', $headers)  ?? array_search('product code', $headers)  ?? array_search('code', $headers);
        $qtyIdx   = array_search('qty_requested', $headers) ?? array_search('quantity', $headers)       ?? array_search('qty', $headers);
        $notesIdx = array_search('notes', $headers)         ?? null;

        if ($codeIdx === false || $qtyIdx === false) {
            return back()->with('error', 'File must have "product_code" and "quantity" (or "qty") columns.')->withInput();
        }

        $parsedItems = [];
        $skipped     = 0;

        foreach ($rows->slice(1) as $row) {
            $arr  = $row->values()->toArray();
            $code = trim((string) ($arr[$codeIdx] ?? ''));
            $qty  = (int) ($arr[$qtyIdx]  ?? 0);

            if (!$code || $qty < 1) { $skipped++; continue; }

            $stock = Stock::where('product_code', $code)->first();
            $parsedItems[] = [
                'product_code'        => $code,
                'product_description' => $stock?->product_description ?? $code,
                'qty_requested'       => $qty,
                'buying_price'        => $stock?->buying_price  ?? 0,
                'selling_price'       => $stock?->selling_price ?? 0,
                'tax_code'            => $stock?->tax_code ?? null,
                'notes'               => $notesIdx !== null ? trim((string) ($arr[$notesIdx] ?? '')) : null,
                'stock_exists'        => $stock !== null,
            ];
        }

        if (empty($parsedItems)) {
            return back()->with('error', "No valid rows found in the file. Rows skipped: {$skipped}.")->withInput();
        }

        // Store parsed items in session for confirmation step
        session(['import_preview' => [
            'items'          => $parsedItems,
            'from_branch_id' => $request->from_branch_id,
            'to_branch_id'   => $request->to_branch_id,
            'reference_doc'  => $request->reference_doc,
            'notes'          => $request->notes,
            'skipped'        => $skipped,
        ]]);

        return redirect()->route('stock.transfers.import.preview');
    }

    public function importPreview(Request $request)
    {
        $preview = session('import_preview');
        if (!$preview) {
            return redirect()->route('stock.transfers.create')->with('error', 'No import data found. Please upload a file first.');
        }

        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $home     = Branch::where('is_home', true)->first();

        return view('stock.transfers.import-preview', compact('preview', 'branches', 'home'));
    }

    public function importConfirm(Request $request)
    {
        $preview = session('import_preview');
        if (!$preview) {
            return redirect()->route('stock.transfers.create')->with('error', 'Session expired. Please upload the file again.');
        }

        $items = $preview['items'];

        DB::beginTransaction();
        try {
            $totalQty = array_sum(array_column($items, 'qty_requested'));

            $transfer = StockTransfer::create([
                'transfer_no'    => StockTransfer::generateTransferNo(),
                'transfer_type'  => StockTransfer::TYPE_INCOMING,
                'from_branch_id' => $preview['from_branch_id'],
                'to_branch_id'   => $preview['to_branch_id'],
                'status'         => StockTransfer::STATUS_PENDING,
                'notes'          => $preview['notes'],
                'reference_doc'  => $preview['reference_doc'],
                'total_items'    => count($items),
                'total_qty'      => $totalQty,
                'requested_by'   => auth()->id(),
            ]);

            foreach ($items as $item) {
                StockTransferItem::create([
                    'transfer_id'         => $transfer->id,
                    'product_code'        => $item['product_code'],
                    'product_description' => $item['product_description'],
                    'qty_requested'       => $item['qty_requested'],
                    'buying_price'        => $item['buying_price'],
                    'selling_price'       => $item['selling_price'],
                    'tax_code'            => $item['tax_code'],
                    'notes'               => $item['notes'],
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }

        session()->forget('import_preview');
        Cache::forget('transfers:stats');

        return redirect()->route('stock.transfers.show', $transfer)
            ->with('success', "Import transfer {$transfer->transfer_no} created ({$preview['skipped']} rows skipped). Review and approve to update stock.");
    }

    // ─────────────────────────────────────────────────────────────
    // AUDIT LOG
    // ─────────────────────────────────────────────────────────────

    public function audit(Request $request)
    {
        $query = StockAuditLog::latest('created_at');

        if ($code = $request->get('product_code')) {
            $query->where('product_code', 'LIKE', "%{$code}%");
        }
        if ($action = $request->get('action')) {
            $query->where('action', $action);
        }
        if ($user = $request->get('user')) {
            $query->where('performed_by_name', 'LIKE', "%{$user}%");
        }
        if ($from = $request->get('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $logs    = $query->paginate(50)->withQueryString();
        $actions = StockAuditLog::select('action')->distinct()->pluck('action');

        return view('stock.transfers.audit', compact('logs', 'actions'));
    }

    // ─────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────

    /**
     * Move on-hand qty for a product between two branches at the batch
     * level, preserving batch_number/expiry_date/unit_cost so FEFO and
     * expiry-traceability follow the stock to its new location. Only moves
     * as much as is actually on hand at the source branch — any shortfall
     * against $qty is a pre-existing data gap (e.g. untracked legacy stock)
     * and is left to the aggregate `stocks.quantity` figure.
     */
    private function moveBranchBatches(string $productCode, int $qty, int $fromBranchId, ?int $toBranchId, int $transferId): void
    {
        $remaining = $qty;

        $batches = StockBatch::where('product_code', $productCode)
            ->where('branch_id', $fromBranchId)
            ->orderedForFefo()
            ->get();

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $take = min($batch->qty_on_hand, $remaining);
            if ($take <= 0) {
                continue;
            }

            $batch->qty_on_hand -= $take;
            if ($batch->qty_on_hand === 0) {
                $batch->status = StockBatch::STATUS_DEPLETED;
            }
            $batch->save();

            if ($toBranchId) {
                $destination = StockBatch::where('product_code', $productCode)
                    ->where('branch_id', $toBranchId)
                    ->where('batch_number', $batch->batch_number)
                    ->active()
                    ->first();

                if ($destination) {
                    $destination->increment('qty_on_hand', $take);
                } else {
                    StockBatch::create([
                        'product_code' => $productCode,
                        'branch_id'    => $toBranchId,
                        'batch_number' => $batch->batch_number,
                        'expiry_date'  => $batch->expiry_date,
                        'qty_on_hand'  => $take,
                        'unit_cost'    => $batch->unit_cost,
                        'status'       => StockBatch::STATUS_ACTIVE,
                        'source_type'  => 'StockTransfer',
                        'source_id'    => $transferId,
                    ]);
                }
            }

            $remaining -= $take;
        }
    }

    private function bustStockCache(): void
    {
        Cache::deleteMultiple([
            'stock:total_products', 'stock:total_units',
            'stock:low_stock_count', 'stock:out_of_stock',
            'stock:low_stock_items', 'stock:top_profit', 'stock:fast_moving',
        ]);
    }
}
