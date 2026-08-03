@extends('layouts.app')

@section('title', 'Purchase Orders')

@section('content')
<div class="page-wrap">

    <div class="page-header">
        <div>
            <h4><i class="bi bi-file-earmark-text me-2 text-success"></i>Purchase Orders</h4>
            <div class="sub">Draft → Submitted → Approved → Received → Closed</div>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('purchase-orders.generate-drafts') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-magic me-1"></i>Generate Drafts for Low Stock
                </button>
            </form>
            <a href="{{ route('purchase-orders.create') }}" class="btn btn-success btn-sm">
                <i class="bi bi-plus-lg me-1"></i>New Purchase Order
            </a>
        </div>
    </div>

    <form method="GET" class="filter-bar">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" style="width:240px;" placeholder="Search PO number, supplier…">
        <select name="status" class="form-select" style="width:160px;">
            <option value="">All Statuses</option>
            @foreach (['draft', 'submitted', 'approved', 'received', 'closed'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-success"><i class="bi bi-funnel-fill me-1"></i>Filter</button>
        @if(request()->hasAny(['search', 'status']))
            <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle me-1"></i>Clear</a>
        @endif
    </form>

    <form method="POST" action="{{ route('purchase-orders.export') }}">
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
                            <x-sortable-th field="po_number">PO Number</x-sortable-th>
                            <th>Supplier</th>
                            <x-sortable-th field="order_date">Order Date</x-sortable-th>
                            <x-sortable-th field="status">Status</x-sortable-th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($purchaseOrders as $purchaseOrder)
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="{{ $purchaseOrder->id }}" class="row-checkbox form-check-input"></td>
                                <td><span class="inv-no">{{ $purchaseOrder->po_number }}</span></td>
                                <td>{{ $purchaseOrder->supplier?->name }}</td>
                                <td>{{ $purchaseOrder->order_date?->format('Y-m-d') }}</td>
                                <td><span class="badge-status badge-{{ $purchaseOrder->status }}">{{ ucfirst($purchaseOrder->status) }}</span></td>
                                <td class="text-end">{{ number_format($purchaseOrder->items->sum('line_total'), 2) }}</td>
                                <td class="text-center">
                                    <a class="btn-action" href="{{ route('purchase-orders.show', $purchaseOrder) }}" title="View"><i class="bi bi-eye"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="bi bi-file-earmark-text"></i>
                                        <p>No purchase orders found{{ request()->hasAny(['search', 'status']) ? ' matching your filters' : '' }}.<br>
                                        <a href="{{ route('purchase-orders.create') }}" class="text-success fw-semibold">Create the first purchase order</a></p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($purchaseOrders->hasPages())
            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top bg-light" style="font-size:.8rem;">
                <span class="text-muted">Showing {{ $purchaseOrders->firstItem() }}–{{ $purchaseOrders->lastItem() }} of {{ $purchaseOrders->total() }} purchase orders</span>
                {{ $purchaseOrders->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </form>

</div>
@endsection
