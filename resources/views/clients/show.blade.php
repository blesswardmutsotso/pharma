@extends('layouts.app')

@section('title', $client->name)

@section('content')
<div class="page-wrap">

    <div class="page-header">
        <div>
            <h4><i class="bi bi-people me-2 text-success"></i>{{ $client->name }}</h4>
            <div class="sub">{{ $client->contact_person ?? 'No contact person on file' }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('clients.statement', $client) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-file-earmark-text me-1"></i>Statement</a>
            <a href="{{ route('clients.edit', $client) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
        </div>
    </div>

    <div class="detail-card">
        <div class="detail-grid">
            <div><div class="label">Contact Person</div><div class="value">{{ $client->contact_person ?? '—' }}</div></div>
            <div><div class="label">Phone</div><div class="value">{{ $client->phone ?? '—' }}</div></div>
            <div><div class="label">Email</div><div class="value">{{ $client->email ?? '—' }}</div></div>
            <div><div class="label">VAT Number</div><div class="value">{{ $client->vat_number ?? '—' }}</div></div>
            <div><div class="label">TIN Number</div><div class="value">{{ $client->tin ?? '—' }}</div></div>
            <div><div class="label">Address</div><div class="value">{{ $client->fullAddress() ?: '—' }}</div></div>
            <div>
                <div class="label">Credit Limit</div>
                <div class="value">
                    @if ($client->credit_limit > 0)
                        {{ number_format($client->credit_limit, 2) }}
                        <span class="text-muted" style="font-size:.75rem;">(outstanding: {{ number_format($client->outstandingBalance(), 2) }})</span>
                    @else
                        No limit
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="table-card mb-4">
        <div class="p-3 border-bottom fw-semibold" style="font-size:.85rem;">
            <i class="bi bi-cart-plus me-1 text-success"></i>Recent Sales Orders
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead><tr><th>SO Number</th><th>Order Date</th><th>Status</th><th class="text-center">Actions</th></tr></thead>
                <tbody>
                    @forelse ($client->salesOrders as $so)
                        <tr>
                            <td><span class="inv-no">{{ $so->so_number }}</span></td>
                            <td>{{ $so->order_date?->format('Y-m-d') }}</td>
                            <td><span class="badge-status badge-{{ $so->status }}">{{ ucfirst($so->status) }}</span></td>
                            <td class="text-center"><a class="btn-action" href="{{ route('sales-orders.show', $so) }}" title="View"><i class="bi bi-eye"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">No sales orders yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="table-card">
        <div class="p-3 border-bottom fw-semibold" style="font-size:.85rem;">
            <i class="bi bi-receipt-cutoff me-1 text-success"></i>Recent Invoices
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead><tr><th>Invoice</th><th>Date</th><th class="text-end">Total</th><th class="text-end">Balance</th><th>Status</th><th class="text-center">Actions</th></tr></thead>
                <tbody>
                    @forelse ($client->salesInvoices as $invoice)
                        <tr>
                            <td><span class="inv-no">{{ $invoice->invoice_number }}</span></td>
                            <td>{{ $invoice->invoice_date?->format('Y-m-d') }}</td>
                            <td class="text-end">{{ number_format($invoice->total, 2) }}</td>
                            <td class="text-end">{{ number_format($invoice->balance(), 2) }}</td>
                            <td><span class="badge-status badge-{{ $invoice->status }}">{{ ucfirst(str_replace('_', ' ', $invoice->status)) }}</span></td>
                            <td class="text-center"><a class="btn-action" href="{{ route('sales-invoices.show', $invoice) }}" title="View"><i class="bi bi-eye"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">No invoices yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
