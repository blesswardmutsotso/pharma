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

    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>SO Number</th>
                        <th>Client</th>
                        <th>Order Date</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($salesOrders as $salesOrder)
                        <tr>
                            <td><span class="inv-no">{{ $salesOrder->so_number }}</span></td>
                            <td>{{ $salesOrder->client?->name }}</td>
                            <td>{{ $salesOrder->order_date?->format('Y-m-d') }}</td>
                            <td><span class="badge-status badge-{{ $salesOrder->status }}">{{ ucfirst($salesOrder->status) }}</span></td>
                            <td class="text-center">
                                <a class="btn-action" href="{{ route('sales-orders.show', $salesOrder) }}" title="View"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="bi bi-cart-plus"></i>
                                    <p>No sales orders yet.<br>
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
            {{ $salesOrders->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>

</div>
@endsection
