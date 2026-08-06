@extends('layouts.app')

@section('title', 'Sales Orders')

@section('content')
<div class="page-wrap">

    <div class="page-header">
        <div>
            <h4><i class="bi bi-cart-plus me-2 text-success"></i>Sales Orders</h4>
            <div class="sub">Draft → Confirmed → Picking → Dispatched → Invoiced → Completed</div>
        </div>
        <a href="{{ route('sales-orders.create') }}" class="btn btn-success btn-sm"><i class="bi bi-plus-lg me-1"></i>New Sales Order</a>
    </div>

    <form method="GET" class="filter-bar">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" style="width:240px;" placeholder="Search SO number, client…">
        <select name="status" class="form-select" style="width:160px;">
            <option value="">All Statuses</option>
            @foreach (['draft', 'confirmed', 'picking', 'dispatched', 'invoiced', 'completed', 'cancelled'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-success"><i class="bi bi-funnel-fill me-1"></i>Filter</button>
        @if(request()->hasAny(['search', 'status']))
            <a href="{{ route('sales-orders.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle me-1"></i>Clear</a>
        @endif
    </form>

    <form method="POST" action="{{ route('sales-orders.export') }}">
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
                            <x-sortable-th field="so_number">SO Number</x-sortable-th>
                            <th>Client</th>
                            <x-sortable-th field="order_date">Order Date</x-sortable-th>
                            <x-sortable-th field="status">Status</x-sortable-th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($salesOrders as $salesOrder)
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="{{ $salesOrder->id }}" class="row-checkbox form-check-input"></td>
                                <td><span class="inv-no">{{ $salesOrder->so_number }}</span></td>
                                <td>{{ $salesOrder->client?->name }}</td>
                                <td>{{ $salesOrder->order_date?->format('Y-m-d') }}</td>
                                <td><span class="badge-status badge-{{ $salesOrder->status }}">{{ ucfirst($salesOrder->status) }}</span></td>
                                <td class="text-end">{{ number_format($salesOrder->items->sum('line_total'), 2) }}</td>
                                <td class="text-center">
                                    <a class="btn-action" href="{{ route('sales-orders.show', $salesOrder) }}" title="View"><i class="bi bi-eye"></i> View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="bi bi-cart-plus"></i>
                                        <p>No sales orders found{{ request()->hasAny(['search', 'status']) ? ' matching your filters' : '' }}.<br>
                                        <a href="{{ route('sales-orders.create') }}" class="text-success fw-semibold">Create the first sales order</a></p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($salesOrders->hasPages())
            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top bg-light" style="font-size:.8rem;">
                <span class="text-muted">Showing {{ $salesOrders->firstItem() }}–{{ $salesOrders->lastItem() }} of {{ $salesOrders->total() }} sales orders</span>
                {{ $salesOrders->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </form>

</div>
@endsection
