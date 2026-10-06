<?php

namespace App\Http\Controllers;

use App\Models\SalesCreditNote;
use App\Models\SalesInvoice;
use App\Services\SalesReturnService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesCreditNoteController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            // Manual money-only adjustments (pricing corrections, goodwill) stay finance-gated.
            new Middleware('role:admin,manager,finance', only: ['store']),
            // Recording a goods return is as much a warehouse/sales action as a
            // financial one — union of who could previously record a return
            // (sales, warehouse) and who can touch an invoice's balance (finance).
            new Middleware('role:admin,manager,supervisor,sales,warehouse,finance', only: ['storeReturn']),
        ];
    }

    public function store(Request $request, SalesInvoice $salesInvoice)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:' . max($salesInvoice->balance(), 0.01)],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($validated, $salesInvoice) {
            SalesCreditNote::create([
                'credit_note_number' => SalesCreditNote::generateCreditNoteNumber(),
                'sales_invoice_id' => $salesInvoice->id,
                'amount' => $validated['amount'],
                'reason' => $validated['reason'],
                'type' => SalesCreditNote::TYPE_ADJUSTMENT,
                'created_by' => auth()->id(),
            ]);

            $salesInvoice->refreshStatus();
            $salesInvoice->salesOrder?->markCompletedIfSettled();
        });

        return back()->with('success', 'Credit note recorded.');
    }

    /**
     * Unified goods return: quarantines the stock AND credits the invoice
     * in one action, instead of the old disconnected flows.
     */
    public function storeReturn(Request $request, SalesInvoice $salesInvoice, SalesReturnService $returns)
    {
        $validated = $request->validate([
            'lines' => ['required', 'array'],
            'lines.*.sales_invoice_item_id' => ['required', 'integer'],
            'lines.*.qty' => ['required', 'integer', 'min:0'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $creditNote = $returns->processReturn($salesInvoice, $validated['lines'], $validated['reason']);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', "Return recorded — {$creditNote->credit_note_number} issued for " . number_format($creditNote->amount, 2) . " {$salesInvoice->currency()}. Goods are quarantined pending inspection.");
    }
}
