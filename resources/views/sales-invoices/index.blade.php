@extends('layouts.app')

@section('title', 'Sales Invoices')

@section('content')
<div class="page-wrap">

    <div class="page-header">
        <div>
            <h4><i class="bi bi-receipt-cutoff me-2 text-success"></i>Sales Invoices</h4>
            <div class="sub">Auto-generated on dispatch, with batch &amp; expiry per line</div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Invoice Number</th>
                        <th>Client</th>
                        <th>Invoice Date</th>
                        <th>Due Date</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Balance</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td><span class="inv-no">{{ $invoice->invoice_number }}</span></td>
                            <td>{{ $invoice->client?->name }}</td>
                            <td>{{ $invoice->invoice_date?->format('Y-m-d') }}</td>
                            <td>{{ $invoice->due_date?->format('Y-m-d') }}</td>
                            <td class="text-end">{{ number_format($invoice->total, 2) }}</td>
                            <td class="text-end fw-semibold {{ $invoice->balance() > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($invoice->balance(), 2) }}</td>
                            <td><span class="badge-status badge-{{ $invoice->status }}">{{ ucfirst(str_replace('_', ' ', $invoice->status)) }}</span></td>
                            <td class="text-center">
                                <a class="btn-action" href="{{ route('sales-invoices.show', $invoice) }}" title="View"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="bi bi-receipt-cutoff"></i>
                                    <p>No invoices yet — invoices are generated automatically when a sales order is dispatched.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top bg-light" style="font-size:.8rem;">
            <span class="text-muted">Showing {{ $invoices->firstItem() }}–{{ $invoices->lastItem() }} of {{ $invoices->total() }} invoices</span>
            {{ $invoices->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>

</div>
@endsection
