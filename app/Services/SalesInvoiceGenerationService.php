<?php

namespace App\Services;

use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\SalesOrder;
use App\Models\Stock;

class SalesInvoiceGenerationService
{
    /**
     * Auto-generate the tax invoice for a just-dispatched sales order.
     * One invoice line per batch allocation, so batch number and expiry
     * date travel onto the invoice as required by BRD FR-INV-002.
     */
    public function generateFor(SalesOrder $salesOrder): SalesInvoice
    {
        $invoice = SalesInvoice::create([
            'invoice_number' => SalesInvoice::generateInvoiceNumber(),
            'sales_order_id' => $salesOrder->id,
            'client_id' => $salesOrder->client_id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => SalesInvoice::STATUS_UNPAID,
            'created_by' => auth()->id(),
        ]);

        $subtotal = 0;
        $taxTotal = 0;

        foreach ($salesOrder->items as $item) {
            $taxPercentage = (float) (Stock::where('product_code', $item->product_code)->value('tax_percentage') ?? 0);

            foreach ($item->batchAllocations as $allocation) {
                $qty = $allocation->qty_allocated;
                $lineBeforeTax = round($qty * $item->unit_price, 2);
                $taxAmount = round($lineBeforeTax * $taxPercentage / 100, 2);
                $lineTotal = round($lineBeforeTax + $taxAmount, 2);

                SalesInvoiceItem::create([
                    'sales_invoice_id' => $invoice->id,
                    'product_code' => $item->product_code,
                    'product_description' => $item->product_description,
                    'batch_number' => $allocation->stockBatch->batch_number,
                    'expiry_date' => $allocation->stockBatch->expiry_date,
                    'qty' => $qty,
                    'unit_price' => $item->unit_price,
                    'tax_percentage' => $taxPercentage,
                    'tax_amount' => $taxAmount,
                    'line_total' => $lineTotal,
                ]);

                $subtotal += $lineBeforeTax;
                $taxTotal += $taxAmount;
            }
        }

        $invoice->update([
            'subtotal' => round($subtotal, 2),
            'tax_total' => round($taxTotal, 2),
            'total' => round($subtotal + $taxTotal, 2),
        ]);

        return $invoice;
    }
}
