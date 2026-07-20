@extends('layouts.app')

@section('title', 'Invoice ' . $salesInvoice->invoice_number)

@section('content')
<div class="page-wrap">

    <div class="page-header">
        <div>
            <h4><i class="bi bi-receipt-cutoff me-2 text-success"></i>Invoice {{ $salesInvoice->invoice_number }}</h4>
            <div class="sub"><span class="badge-status badge-{{ $salesInvoice->status }}">{{ ucfirst(str_replace('_', ' ', $salesInvoice->status)) }}</span></div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('sales-invoices.pdf', $salesInvoice) }}" target="_blank" class="btn btn-success btn-sm"><i class="bi bi-file-earmark-pdf me-1"></i>Print PDF</a>
            @if ($salesInvoice->client)
                <a href="{{ route('clients.statement', $salesInvoice->client) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-file-earmark-text me-1"></i>Client Statement</a>
            @endif
            <a href="{{ route('sales-invoices.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    <div class="detail-card">
        <div class="detail-grid">
            <div><div class="label">Client</div><div class="value">{{ $salesInvoice->client?->name }}</div></div>
            <div><div class="label">Invoice Date</div><div class="value">{{ $salesInvoice->invoice_date?->format('Y-m-d') }}</div></div>
            <div><div class="label">Due Date</div><div class="value">{{ $salesInvoice->due_date?->format('Y-m-d') }}</div></div>
            <div><div class="label">Balance Due</div><div class="value {{ $salesInvoice->balance() > 0 ? 'text-danger' : 'text-success' }}">${{ number_format($salesInvoice->balance(), 2) }}</div></div>
        </div>
    </div>

    <div class="table-card mb-4">
        <div class="p-3 border-bottom fw-semibold" style="font-size:.85rem;">
            <i class="bi bi-list-ul me-1 text-success"></i>Line Items
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Batch Number</th>
                        <th>Expiry Date</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Unit Price</th>
                        <th class="text-end">Tax</th>
                        <th class="text-end">Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($salesInvoice->items as $item)
                        <tr>
                            <td>{{ $item->product_code }} — {{ $item->product_description }}</td>
                            <td><span class="inv-no">{{ $item->batch_number }}</span></td>
                            <td>{{ $item->expiry_date?->format('Y-m-d') }}</td>
                            <td class="text-center">{{ $item->qty }}</td>
                            <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-end">{{ number_format($item->tax_amount, 2) }}</td>
                            <td class="text-end">{{ number_format($item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6" class="text-end fw-semibold">Subtotal</td>
                        <td class="text-end">{{ number_format($salesInvoice->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="6" class="text-end fw-semibold">Tax</td>
                        <td class="text-end">{{ number_format($salesInvoice->tax_total, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="6" class="text-end fw-bold">Total</td>
                        <td class="text-end fw-bold">{{ number_format($salesInvoice->total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="detail-card">
        <div class="card-title">Credit Notes</div>
        <div class="table-responsive mb-3">
            <table class="table">
                <thead><tr><th>Credit Note Number</th><th class="text-end">Amount</th><th>Reason</th></tr></thead>
                <tbody>
                    @forelse ($salesInvoice->creditNotes as $creditNote)
                        <tr>
                            <td><span class="inv-no">{{ $creditNote->credit_note_number }}</span></td>
                            <td class="text-end">{{ number_format($creditNote->amount, 2) }}</td>
                            <td>{{ $creditNote->reason }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">No credit notes issued.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if (!$salesInvoice->isSettled())
            <form action="{{ route('sales-invoices.credit-notes.store', $salesInvoice) }}" method="POST" class="row g-2 align-items-end"
                  data-confirm="Issue this credit note against {{ $salesInvoice->invoice_number }}?" data-confirm-icon="question">
                @csrf
                <div class="col-md-3">
                    <label class="form-label">Amount</label>
                    <input type="number" step="0.01" name="amount" class="form-control" max="{{ $salesInvoice->balance() }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Reason</label>
                    <input type="text" name="reason" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-success w-100"><i class="bi bi-file-earmark-minus me-1"></i>Issue Credit Note</button>
                </div>
            </form>
        @endif
    </div>

    <div class="table-card">
        <div class="p-3 border-bottom fw-semibold" style="font-size:.85rem;">
            <i class="bi bi-cash-coin me-1 text-success"></i>Payments Applied
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th class="text-end">Amount Applied</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($salesInvoice->paymentAllocations as $allocation)
                        <tr>
                            <td>{{ $allocation->payment?->payment_date?->format('Y-m-d') }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $allocation->payment?->payment_method)) }}</td>
                            <td>{{ $allocation->payment?->reference ?? '—' }}</td>
                            <td class="text-end">{{ number_format($allocation->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state py-3">
                                    <p class="mb-0">No payments applied yet.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
