<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Stock;

class ReorderService
{
    /**
     * For every low-stock product with a default supplier configured,
     * ensure there is an open draft PO line for it. Skips products that
     * already have a pending (draft/submitted/approved) PO line so repeated
     * runs don't create duplicate orders.
     *
     * @return int number of draft purchase orders created
     */
    public function generateDraftPurchaseOrders(): int
    {
        $lowStockProducts = Stock::whereNotNull('default_supplier_id')
            ->where('reorder_point', '>', 0)
            ->whereColumn('quantity', '<=', 'reorder_point')
            ->get();

        $created = 0;
        $draftsBySupplier = [];

        foreach ($lowStockProducts as $product) {
            if ($this->hasOpenPurchaseOrderLine($product->product_code)) {
                continue;
            }

            $supplierId = $product->default_supplier_id;

            if (!isset($draftsBySupplier[$supplierId])) {
                $draftsBySupplier[$supplierId] = PurchaseOrder::create([
                    'po_number' => PurchaseOrder::generatePoNumber(),
                    'supplier_id' => $supplierId,
                    'order_date' => now()->toDateString(),
                    'status' => PurchaseOrder::STATUS_DRAFT,
                    'notes' => 'Auto-generated from low-stock reorder trigger.',
                    'requested_by' => auth()->id(),
                ]);
                $created++;
            }

            $purchaseOrder = $draftsBySupplier[$supplierId];
            $qty = $product->reorder_qty > 0 ? $product->reorder_qty : max($product->reorder_point, 1);

            PurchaseOrderItem::create([
                'purchase_order_id' => $purchaseOrder->id,
                'product_code' => $product->product_code,
                'product_description' => $product->product_description,
                'qty_ordered' => $qty,
                'unit_cost' => $product->buying_price,
                'line_total' => round($qty * $product->buying_price, 2),
            ]);
        }

        return $created;
    }

    private function hasOpenPurchaseOrderLine(string $productCode): bool
    {
        return PurchaseOrderItem::where('product_code', $productCode)
            ->whereHas('purchaseOrder', fn ($q) => $q->whereIn('status', [
                PurchaseOrder::STATUS_DRAFT,
                PurchaseOrder::STATUS_SUBMITTED,
                PurchaseOrder::STATUS_APPROVED,
            ]))
            ->exists();
    }
}
