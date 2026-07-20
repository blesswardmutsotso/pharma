<?php

namespace App\Http\Controllers;

use App\Models\Client;

class CustomerStatementController extends Controller
{
    public function show(Client $client)
    {
        $invoices = $client->salesInvoices()->with(['creditNotes', 'paymentAllocations'])->latest('invoice_date')->get();
        $payments = $client->salesPayments()->with('allocations.salesInvoice')->latest('payment_date')->get();

        $ageing = ['current' => 0.0, '30' => 0.0, '60' => 0.0, '90+' => 0.0];
        foreach ($invoices as $invoice) {
            if ($invoice->isSettled()) {
                continue;
            }
            $ageing[$invoice->ageingBucket()] += $invoice->balance();
        }

        $balanceDue = round(array_sum($ageing), 2);

        return view('customer-statements.show', compact('client', 'invoices', 'payments', 'ageing', 'balanceDue'));
    }
}
