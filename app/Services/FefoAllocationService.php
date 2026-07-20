<?php

namespace App\Services;

use App\Models\SalesOrderItem;
use App\Models\SalesOrderItemBatch;
use App\Models\StockBatch;
use RuntimeException;

class FefoAllocationService
{
    /**
     * Reserve stock for a sales order line using First-Expiry-First-Out
     * ordering, splitting across batches if one alone doesn't cover the qty.
     * Throws if there isn't enough available stock — callers should check
     * availableQtyFor() first if they want to fail without a transaction.
     *
     * @throws RuntimeException
     */
    public function allocate(SalesOrderItem $item): void
    {
        $needed = $item->qty_ordered - $item->qty_allocated;

        if ($needed <= 0) {
            return;
        }

        $branchId = $item->salesOrder?->branch_id;

        if ($this->availableQtyFor($item->product_code, $branchId) < $needed) {
            throw new RuntimeException("Insufficient stock for {$item->product_code} to fulfil this order.");
        }

        $remaining = $needed;
        $batches = StockBatch::where('product_code', $item->product_code)->atBranch($branchId)->orderedForFefo()->get();

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $take = min($batch->availableQty(), $remaining);
            if ($take <= 0) {
                continue;
            }

            $batch->reserve($take);

            SalesOrderItemBatch::create([
                'sales_order_item_id' => $item->id,
                'stock_batch_id' => $batch->id,
                'qty_allocated' => $take,
            ]);

            $remaining -= $take;
        }

        $item->increment('qty_allocated', $needed - $remaining);
    }

    public function availableQtyFor(string $productCode, ?int $branchId = null): int
    {
        return (int) StockBatch::where('product_code', $productCode)
            ->atBranch($branchId)
            ->active()
            ->get()
            ->sum(fn (StockBatch $batch) => $batch->availableQty());
    }

    /**
     * Release all batch reservations for an item (e.g. on order cancellation)
     * without physically dispatching stock.
     */
    public function releaseAllocations(SalesOrderItem $item): void
    {
        foreach ($item->batchAllocations as $allocation) {
            $allocation->stockBatch->release($allocation->qty_allocated);
            $allocation->delete();
        }

        $item->update(['qty_allocated' => 0]);
    }

    /**
     * Physically ship all allocated batches for an item — reduces on-hand
     * stock and clears the reservation.
     */
    public function dispatchAllocations(SalesOrderItem $item): void
    {
        foreach ($item->batchAllocations as $allocation) {
            $allocation->stockBatch->dispatch($allocation->qty_allocated);
        }

        $item->update(['qty_dispatched' => $item->qty_allocated]);
    }
}
