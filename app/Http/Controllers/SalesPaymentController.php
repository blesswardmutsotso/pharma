<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\SalesInvoice;
use App\Models\SalesPayment;
use App\Models\SalesPaymentAllocation;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesPaymentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('role:admin,finance')];
    }

    public function store(Request $request, Client $client)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'in:eft,cash,cheque,mobile_money'],
            'reference' => ['nullable', 'string', 'max:255'],
            'allocations' => ['required', 'array', 'min:1'],
            'allocations.*.sales_invoice_id' => ['required', 'exists:sales_invoices,id'],
            'allocations.*.amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $allocationTotal = round(array_sum(array_column($validated['allocations'], 'amount')), 2);
        if ($allocationTotal > (float) $validated['amount'] + 0.005) {
            throw ValidationException::withMessages([
                'amount' => 'Allocated total cannot exceed the payment amount.',
            ]);
        }

        DB::transaction(function () use ($validated, $client) {
            $payment = SalesPayment::create([
                'client_id' => $client->id,
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'reference' => $validated['reference'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['allocations'] as $allocation) {
                $invoice = SalesInvoice::findOrFail($allocation['sales_invoice_id']);

                if ((float) $allocation['amount'] > $invoice->balance() + 0.005) {
                    throw ValidationException::withMessages([
                        'amount' => "Allocation to invoice {$invoice->invoice_number} exceeds its outstanding balance.",
                    ]);
                }

                SalesPaymentAllocation::create([
                    'sales_payment_id' => $payment->id,
                    'sales_invoice_id' => $invoice->id,
                    'amount' => $allocation['amount'],
                ]);

                $invoice->refreshStatus();
                $invoice->salesOrder?->markCompletedIfSettled();
            }
        });

        return back()->with('success', 'Payment recorded and allocated.');
    }
}
