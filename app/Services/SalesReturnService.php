<?php

namespace App\Services;

use App\Models\SalesCreditNote;
use App\Models\SalesCreditNoteItem;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\Stock;
use App\Models\StockAuditLog;
use App\Models\StockBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Reverses a sale the way a pharma wholesaler actually needs to: one action
 * that both quarantines the physical goods (pending QA inspection) and
 * credits the client's invoice balance for exactly what was returned — the
 * two were previously separate, disconnected flows (see
 * SalesOrderController::returnItem(), now removed, and the old money-only
 * SalesCreditNoteController::store()).
 */
class SalesReturnService
{
    /**
     * @param array<int, array{sales_invoice_item_id: int, qty: int}> $lines
     */
    public function processReturn(SalesInvoice $invoice, array $lines, string $reason): SalesCreditNote
    {
        if ($invoice->status === SalesInvoice::STATUS_CANCELLED) {
            throw ValidationException::withMessages([
                'lines' => 'This invoice is cancelled — nothing was ever collected against it, so there is nothing to return.',
            ]);
        }

        $items = SalesInvoiceItem::where('sales_invoice_id', $invoice->id)
            ->whereIn('id', collect($lines)->pluck('sales_invoice_item_id'))
            ->get()
            ->keyBy('id');

        $errors = [];
        $validatedLines = [];

        foreach ($lines as $index => $line) {
            $qty = (int) ($line['qty'] ?? 0);
            if ($qty <= 0) {
                continue;
            }

            $item = $items->get((int) ($line['sales_invoice_item_id'] ?? 0));
            if ($item === null) {
                $errors["lines.{$index}"] = 'One of the selected lines does not belong to this invoice.';
                continue;
            }

            $returnable = $item->returnableQty();
            if ($qty > $returnable) {
                $errors["lines.{$index}"] = "{$item->product_code}: can't return {$qty} — only {$returnable} of the invoiced {$item->qty} is still returnable.";
                continue;
            }

            $validatedLines[] = ['item' => $item, 'qty' => $qty];
        }

        if (empty($validatedLines) && empty($errors)) {
            $errors['lines'] = 'Enter a quantity to return for at least one line.';
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        return DB::transaction(function () use ($invoice, $validatedLines, $reason) {
            $creditNote = SalesCreditNote::create([
                'credit_note_number' => SalesCreditNote::generateCreditNoteNumber(),
                'sales_invoice_id' => $invoice->id,
                'sales_order_id' => $invoice->sales_order_id,
                'amount' => 0,
                'reason' => $reason,
                'type' => SalesCreditNote::TYPE_RETURN,
                'created_by' => auth()->id(),
            ]);

            $totalAmount = 0;
            $buyingPriceByProduct = [];

            foreach ($validatedLines as $n => $line) {
                /** @var SalesInvoiceItem $item */
                $item = $line['item'];
                $qty = $line['qty'];

                // Mirrors SalesInvoiceGenerationService's own math exactly:
                // unit_price stays tax-exclusive, line_total is tax-inclusive.
                $lineBeforeTax = round($qty * $item->unit_price, 2);
                $taxAmount = round($lineBeforeTax * $item->tax_percentage / 100, 2);
                $lineTotal = round($lineBeforeTax + $taxAmount, 2);

                if (!array_key_exists($item->product_code, $buyingPriceByProduct)) {
                    $buyingPriceByProduct[$item->product_code] = (float) (Stock::where('product_code', $item->product_code)->value('buying_price') ?? 0);
                }

                $batch = StockBatch::create([
                    'product_code' => $item->product_code,
                    'batch_number' => "RET-{$creditNote->credit_note_number}-" . ($n + 1),
                    'expiry_date' => $item->expiry_date,
                    'qty_on_hand' => $qty,
                    'unit_cost' => $buyingPriceByProduct[$item->product_code],
                    'status' => StockBatch::STATUS_QUARANTINE,
                    'source_type' => 'SalesCreditNote',
                    'source_id' => $creditNote->id,
                ]);

                SalesCreditNoteItem::create([
                    'sales_credit_note_id' => $creditNote->id,
                    'sales_invoice_item_id' => $item->id,
                    'product_code' => $item->product_code,
                    'product_description' => $item->product_description,
                    'batch_number' => $item->batch_number,
                    'expiry_date' => $item->expiry_date,
                    'qty' => $qty,
                    'unit_price' => $item->unit_price,
                    'tax_percentage' => $item->tax_percentage,
                    'tax_amount' => $taxAmount,
                    'line_total' => $lineTotal,
                    'stock_batch_id' => $batch->id,
                ]);

                $stock = Stock::where('product_code', $item->product_code)->first();
                $qtyBefore = $stock?->quantity ?? 0;
                $stock?->syncQuantityFromBatches();

                StockAuditLog::record(
                    action: StockAuditLog::RETURN_GOODS,
                    productCode: $item->product_code,
                    productDescription: $item->product_description,
                    qtyBefore: $qtyBefore,
                    qtyAfter: $stock?->quantity ?? $qtyBefore,
                    notes: "Return against invoice {$invoice->invoice_number} ({$creditNote->credit_note_number}): {$reason}",
                    referenceType: 'SalesCreditNote',
                    referenceId: $creditNote->id,
                    referenceLabel: $creditNote->credit_note_number,
                );

                $totalAmount += $lineTotal;
            }

            $creditNote->update(['amount' => round($totalAmount, 2)]);

            $invoice->refreshStatus();
            $invoice->salesOrder?->markCompletedIfSettled();

            return $creditNote->fresh('items');
        });
    }

    /**
     * Permanently writes off a quarantined batch (damaged, expired, failed
     * inspection) — it can never be resold. The credit already given to the
     * client for it stands regardless; inspection outcome is a warehouse/QA
     * concern, not a billing one.
     */
    public function scrapBatch(StockBatch $batch, string $reason): void
    {
        if ($batch->status !== StockBatch::STATUS_QUARANTINE) {
            throw ValidationException::withMessages([
                'batch' => 'Only quarantined batches can be scrapped.',
            ]);
        }

        $qtyBefore = $batch->stock?->quantity ?? 0;

        $batch->update(['status' => StockBatch::STATUS_SCRAPPED]);
        $batch->stock?->syncQuantityFromBatches();

        StockAuditLog::record(
            action: StockAuditLog::SCRAP,
            productCode: $batch->product_code,
            productDescription: $batch->stock?->product_description ?? $batch->product_code,
            qtyBefore: $qtyBefore,
            qtyAfter: $batch->stock?->quantity ?? $qtyBefore,
            notes: "Batch {$batch->batch_number} scrapped/destroyed: {$reason}",
            referenceType: 'StockBatch',
            referenceId: $batch->id,
            referenceLabel: $batch->batch_number,
        );
    }

    /**
     * Releases a quarantined batch back into sellable stock once it has
     * passed inspection — same transition StockBatchController::release()
     * already performed, now with an audit trail to match scrapBatch().
     */
    public function releaseFromQuarantine(StockBatch $batch): void
    {
        if ($batch->status !== StockBatch::STATUS_QUARANTINE) {
            throw ValidationException::withMessages([
                'batch' => 'Only quarantined batches can be released.',
            ]);
        }

        $qtyBefore = $batch->stock?->quantity ?? 0;

        $batch->update(['status' => StockBatch::STATUS_ACTIVE]);
        $batch->stock?->syncQuantityFromBatches();

        StockAuditLog::record(
            action: StockAuditLog::RETURN_GOODS,
            productCode: $batch->product_code,
            productDescription: $batch->stock?->product_description ?? $batch->product_code,
            qtyBefore: $qtyBefore,
            qtyAfter: $batch->stock?->quantity ?? $qtyBefore,
            notes: "Batch {$batch->batch_number} released from quarantine into sellable stock.",
            referenceType: 'StockBatch',
            referenceId: $batch->id,
            referenceLabel: $batch->batch_number,
        );
    }
}
