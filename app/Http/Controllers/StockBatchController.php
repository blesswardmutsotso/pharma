<?php

namespace App\Http\Controllers;

use App\Models\StockBatch;
use App\Services\SalesReturnService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\ValidationException;

class StockBatchController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('role:admin,manager,supervisor,inventory_manager,warehouse')];
    }

    /**
     * Release a quarantined batch back into sellable stock once it has
     * passed inspection (BRD FR-STK-005 quarantine workflow).
     */
    public function release(StockBatch $batch, SalesReturnService $returns)
    {
        try {
            $returns->releaseFromQuarantine($batch);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', "Batch {$batch->batch_number} released from quarantine.");
    }

    /**
     * Permanently write off a quarantined batch that failed inspection
     * (damaged, broken cold chain, tampered) — it can never re-enter
     * sellable stock. Does not affect any credit note already issued for it.
     */
    public function scrap(Request $request, StockBatch $batch, SalesReturnService $returns)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $returns->scrapBatch($batch, $validated['reason']);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', "Batch {$batch->batch_number} scrapped — permanently removed from sellable stock.");
    }
}
