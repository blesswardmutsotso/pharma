<?php

namespace App\Http\Controllers;

use App\Models\StockBatch;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class StockBatchController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('role:admin,inventory_manager,warehouse')];
    }

    /**
     * Release a quarantined batch back into sellable stock once it has
     * passed inspection (BRD FR-STK-005 quarantine workflow).
     */
    public function release(StockBatch $batch)
    {
        if ($batch->status !== StockBatch::STATUS_QUARANTINE) {
            return back()->with('error', 'Only quarantined batches can be released.');
        }

        $batch->update(['status' => StockBatch::STATUS_ACTIVE]);
        $batch->stock?->syncQuantityFromBatches();

        return back()->with('success', "Batch {$batch->batch_number} released from quarantine.");
    }
}
