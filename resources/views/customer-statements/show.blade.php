@extends('layouts.app')

@section('title', 'Statement — ' . $client->name)

@push('styles')
<style>
    @media print {
        .app-header, .app-sidebar, .flash-alerts, .no-print { display: none !important; }
        .app-main { margin: 0 !important; }
    }
</style>
@endpush

@section('content')
<div class="page-wrap">

    <div class="page-header no-print">
        <div>
            <h4><i class="bi bi-file-earmark-person me-2 text-success"></i>Statement — {{ $client->name }}</h4>
            <div class="sub">Invoices, payments, and ageing summary</div>
        </div>
        <button onclick="window.print()" class="btn btn-success btn-sm"><i class="bi bi-printer me-1"></i>Print</button>
    </div>

    <div class="stat-cards">
        <div class="stat-card">
            <div class="icon {{ $balanceDue > 0 ? 'red' : 'green' }}"><i class="bi bi-cash-stack"></i></div>
            <div><div class="label">Balance Due</div><div class="value">${{ number_format($balanceDue, 2) }}</div></div>
        </div>
        <div class="stat-card">
            <div class="icon green"><i class="bi bi-calendar-check"></i></div>
            <div><div class="label">Current</div><div class="value" style="font-size:1.1rem;">${{ number_format($ageing['current'], 2) }}</div></div>
        </div>
        <div class="stat-card">
            <div class="icon yellow"><i class="bi bi-calendar-week"></i></div>
            <div><div class="label">30 Days</div><div class="value" style="font-size:1.1rem;">${{ number_format($ageing['30'], 2) }}</div></div>
        </div>
        <div class="stat-card">
            <div class="icon yellow"><i class="bi bi-calendar-week"></i></div>
            <div><div class="label">60 Days</div><div class="value" style="font-size:1.1rem;">${{ number_format($ageing['60'], 2) }}</div></div>
        </div>
        <div class="stat-card">
            <div class="icon red"><i class="bi bi-exclamation-octagon"></i></div>
            <div><div class="label">90+ Days</div><div class="value" style="font-size:1.1rem;">${{ number_format($ageing['90+'], 2) }}</div></div>
        </div>
    </div>

    <div class="table-card mb-4">
        <div class="p-3 border-bottom fw-semibold" style="font-size:.85rem;">
            <i class="bi bi-receipt-cutoff me-1 text-success"></i>Invoices
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Date</th>
                        <th>Due Date</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Balance</th>
                        <th>Ageing</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td><a href="{{ route('sales-invoices.show', $invoice) }}" class="inv-no">{{ $invoice->invoice_number }}</a></td>
                            <td>{{ $invoice->invoice_date?->format('Y-m-d') }}</td>
                            <td>{{ $invoice->due_date?->format('Y-m-d') }}</td>
                            <td class="text-end">{{ number_format($invoice->total, 2) }}</td>
                            <td class="text-end">{{ number_format($invoice->balance(), 2) }}</td>
                            <td>
                                @if ($invoice->isSettled())
                                    <span class="badge-status badge-paid">Paid</span>
                                @else
                                    <span class="badge-status badge-{{ $invoice->ageingBucket() === 'current' ? 'approved' : ($invoice->ageingBucket() === '90+' ? 'rejected' : 'pending') }}">{{ ucfirst($invoice->ageingBucket()) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">No invoices for this client yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="table-card mb-4">
        <div class="p-3 border-bottom fw-semibold" style="font-size:.85rem;">
            <i class="bi bi-cash-coin me-1 text-success"></i>Payments
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_date?->format('Y-m-d') }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                            <td>{{ $payment->reference ?? '—' }}</td>
                            <td class="text-end">{{ number_format($payment->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">No payments recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="detail-card no-print">
        <div class="card-title"><i class="bi bi-cash-coin me-1"></i>Record a Payment</div>
        <form action="{{ route('clients.payments.store', $client) }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Amount</label>
                    <input type="number" step="0.01" name="amount" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Payment Date</label>
                    <input type="date" name="payment_date" class="form-control" value="{{ now()->toDateString() }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Method</label>
                    <select name="payment_method" class="form-select" required>
                        <option value="eft">EFT</option>
                        <option value="cash">Cash</option>
                        <option value="cheque">Cheque</option>
                        <option value="mobile_money">Mobile Money</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Reference</label>
                    <input type="text" name="reference" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Apply To Invoice</label>
                    <select name="allocations[0][sales_invoice_id]" class="form-select">
                        @foreach ($invoices->filter(fn ($i) => !$i->isSettled()) as $invoice)
                            <option value="{{ $invoice->id }}">{{ $invoice->invoice_number }} (balance {{ number_format($invoice->balance(), 2) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Amount to Allocate</label>
                    <input type="number" step="0.01" name="allocations[0][amount]" class="form-control" required>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Record Payment</button>
            </div>
        </form>
    </div>

</div>
@endsection
