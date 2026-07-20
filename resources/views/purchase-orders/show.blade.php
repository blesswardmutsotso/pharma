@extends('layouts.app')

@section('title', 'PO ' . $purchaseOrder->po_number)

@section('content')
<div class="page-wrap">

    <div class="page-header">
        <div>
            <h4><i class="bi bi-file-earmark-text me-2 text-success"></i>Purchase Order {{ $purchaseOrder->po_number }}</h4>
            <div class="sub"><span class="badge-status badge-{{ $purchaseOrder->status }}">{{ ucfirst($purchaseOrder->status) }}</span></div>
        </div>
        <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>

    <div class="detail-card">
        <div class="detail-grid">
            <div><div class="label">Supplier</div><div class="value">{{ $purchaseOrder->supplier?->name }}</div></div>
            <div><div class="label">Order Date</div><div class="value">{{ $purchaseOrder->order_date?->format('Y-m-d') }}</div></div>
            <div><div class="label">Expected Delivery</div><div class="value">{{ $purchaseOrder->expected_delivery_date?->format('Y-m-d') ?? '—' }}</div></div>
            <div><div class="label">Requested By</div><div class="value">{{ $purchaseOrder->requestedBy?->name ?? '—' }}</div></div>
            <div><div class="label">Approved By</div><div class="value">{{ $purchaseOrder->approvedBy?->name ?? '—' }}</div></div>
        </div>
    </div>

    <div class="d-flex gap-2 mb-4">
        @if ($purchaseOrder->canBeSubmitted())
            <form action="{{ route('purchase-orders.submit', $purchaseOrder) }}" method="POST"
                  data-confirm="Submit {{ $purchaseOrder->po_number }} for approval?" data-confirm-icon="question">
                @csrf
                <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-send-check me-1"></i>Submit for Approval</button>
            </form>
        @endif
        @if ($purchaseOrder->canBeApproved())
            <form action="{{ route('purchase-orders.approve', $purchaseOrder) }}" method="POST"
                  data-confirm="Approve {{ $purchaseOrder->po_number }}? This authorizes it for receiving." data-confirm-icon="question">
                @csrf
                <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-check-circle me-1"></i>Approve</button>
            </form>
        @endif
        @if ($purchaseOrder->canBeClosed())
            <form action="{{ route('purchase-orders.close', $purchaseOrder) }}" method="POST"
                  data-confirm="Close {{ $purchaseOrder->po_number }}? No further receiving will be possible." data-confirm-danger="true">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="bi bi-lock me-1"></i>Close</button>
            </form>
        @endif
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
                        <th class="text-center">Qty Ordered</th>
                        <th class="text-center">Qty Received</th>
                        <th class="text-center">Discrepancy</th>
                        <th class="text-end">Unit Cost</th>
                        <th class="text-end">Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchaseOrder->items as $item)
                        <tr class="{{ $item->hasDiscrepancy() ? 'table-warning' : '' }}">
                            <td>{{ $item->product_code }} — {{ $item->product_description }}</td>
                            <td class="text-center">{{ $item->qty_ordered }}</td>
                            <td class="text-center">{{ $item->qty_received }}</td>
                            <td class="text-center">
                                @if($item->hasDiscrepancy())
                                    <span class="badge-status badge-{{ $item->discrepancy() > 0 ? 'approved' : 'rejected' }}">{{ $item->discrepancy() > 0 ? '+' : '' }}{{ $item->discrepancy() }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-end">{{ number_format($item->unit_cost, 2) }}</td>
                            <td class="text-end">{{ number_format($item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if ($purchaseOrder->notes)
        <div class="detail-card">
            <div class="card-title">Notes</div>
            <p class="mb-0">{{ $purchaseOrder->notes }}</p>
        </div>
    @endif

</div>
@endsection
