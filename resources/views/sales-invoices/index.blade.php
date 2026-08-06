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

    <form method="GET" class="filter-bar">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" style="width:240px;" placeholder="Search invoice number, client…">
        <select name="status" class="form-select" style="width:170px;">
            <option value="">All Statuses</option>
            @foreach (['unpaid' => 'Unpaid', 'partially_paid' => 'Partially Paid', 'paid' => 'Paid', 'cancelled' => 'Cancelled'] as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-success"><i class="bi bi-funnel-fill me-1"></i>Filter</button>
        @if(request()->hasAny(['search', 'status']))
            <a href="{{ route('sales-invoices.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle me-1"></i>Clear</a>
        @endif
    </form>

    <form method="POST" action="{{ route('sales-invoices.export') }}">
        @csrf
        <x-forward-filters />

        <div class="table-card">
            <div class="d-flex justify-content-end p-2 border-bottom">
                <button type="submit" class="btn btn-outline-success btn-sm"><i class="bi bi-download me-1"></i>Export CSV</button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width:30px;"><input type="checkbox" class="select-all-checkbox form-check-input"></th>
                            <x-sortable-th field="invoice_number">Invoice Number</x-sortable-th>
                            <th>Client</th>
                            <x-sortable-th field="invoice_date">Invoice Date</x-sortable-th>
                            <x-sortable-th field="due_date">Due Date</x-sortable-th>
                            <x-sortable-th field="total" align="end">Total</x-sortable-th>
                            <th class="text-end">Balance</th>
                            <x-sortable-th field="status">Status</x-sortable-th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoices as $invoice)
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="{{ $invoice->id }}" class="row-checkbox form-check-input"></td>
                                <td><span class="inv-no">{{ $invoice->invoice_number }}</span></td>
                                <td>{{ $invoice->client?->name }}</td>
                                <td>{{ $invoice->invoice_date?->format('Y-m-d') }}</td>
                                <td>{{ $invoice->due_date?->format('Y-m-d') }}</td>
                                <td class="text-end">{{ number_format($invoice->total, 2) }}</td>
                                <td class="text-end fw-semibold {{ $invoice->balance() > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($invoice->balance(), 2) }}</td>
                                <td><span class="badge-status badge-{{ $invoice->status }}">{{ ucfirst(str_replace('_', ' ', $invoice->status)) }}</span></td>
                                <td class="text-center">
                                    <a class="btn-action" href="{{ route('sales-invoices.show', $invoice) }}" title="View"><i class="bi bi-eye"></i> View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <div class="empty-state">
                                        <i class="bi bi-receipt-cutoff"></i>
                                        <p>No invoices found{{ request()->hasAny(['search', 'status']) ? ' matching your filters' : ' — invoices are generated automatically when a sales order is dispatched' }}.</p>
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
                {{ $invoices->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </form>

</div>
@endsection
