<?php

namespace App\Services;

use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use App\Models\SalesOrder;

class DeliveryNoteGenerationService
{
    /**
     * Auto-generate the delivery note for a just-dispatched sales order,
     * alongside the tax invoice. One line per batch allocation, so batch
     * number and expiry date travel onto the delivery note the same way
     * they do onto the invoice.
     */
    public function generateFor(SalesOrder $salesOrder): DeliveryNote
    {
        $deliveryNote = DeliveryNote::create([
            'delivery_note_no' => DeliveryNote::generateNumber(),
            'sales_order_id' => $salesOrder->id,
            'client_id' => $salesOrder->client_id,
            'branch_id' => $salesOrder->branch_id,
            'delivered_by' => auth()->id(),
            'delivery_date' => now()->toDateString(),
        ]);

        foreach ($salesOrder->items as $item) {
            foreach ($item->batchAllocations as $allocation) {
                DeliveryNoteItem::create([
                    'delivery_note_id' => $deliveryNote->id,
                    'product_code' => $item->product_code,
                    'product_description' => $item->product_description,
                    'batch_number' => $allocation->stockBatch->batch_number,
                    'expiry_date' => $allocation->stockBatch->expiry_date,
                    'qty' => $allocation->qty_allocated,
                ]);
            }
        }

        return $deliveryNote;
    }
}
