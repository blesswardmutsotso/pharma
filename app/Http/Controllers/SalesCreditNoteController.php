<?php

namespace App\Http\Controllers;

use App\Models\SalesCreditNote;
use App\Models\SalesInvoice;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class SalesCreditNoteController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('role:admin,finance')];
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
                'created_by' => auth()->id(),
            ]);

            $salesInvoice->refreshStatus();
            $salesInvoice->salesOrder?->markCompletedIfSettled();
        });

        return back()->with('success', 'Credit note recorded.');
    }
}
