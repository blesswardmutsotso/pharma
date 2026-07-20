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

    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>PO Number</th>
                        <th>Supplier</th>
                        <th>Order Date</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($purchaseOrders as $purchaseOrder)
                        <tr>
                            <td><span class="inv-no">{{ $purchaseOrder->po_number }}</span></td>
                            <td>{{ $purchaseOrder->supplier?->name }}</td>
                            <td>{{ $purchaseOrder->order_date?->format('Y-m-d') }}</td>
                            <td><span class="badge-status badge-{{ $purchaseOrder->status }}">{{ ucfirst($purchaseOrder->status) }}</span></td>
                            <td class="text-center">
                                <a class="btn-action" href="{{ route('purchase-orders.show', $purchaseOrder) }}" title="View"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <p>No purchase orders yet.<br>
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
            {{ $purchaseOrders->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>

</div>
@endsection
