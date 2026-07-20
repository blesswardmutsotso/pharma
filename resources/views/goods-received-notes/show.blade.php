@extends('layouts.app')

@section('title', 'GRN ' . $goodsReceivedNote->grn_number)

@section('content')
<div class="page-wrap">

    <div class="page-header">
        <div>
            <h4><i class="bi bi-clipboard-check me-2 text-success"></i>GRN {{ $goodsReceivedNote->grn_number }}</h4>
            <div class="sub"><span class="badge-status badge-{{ $goodsReceivedNote->status }}">{{ ucfirst($goodsReceivedNote->status) }}</span></div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('goods-received-notes.pdf', $goodsReceivedNote) }}" target="_blank" class="btn btn-success btn-sm"><i class="bi bi-file-earmark-pdf me-1"></i>Print PDF</a>
            <a href="{{ route('goods-received-notes.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    <div class="detail-card">
        <div class="detail-grid">
            <div><div class="label">Supplier</div><div class="value">{{ $goodsReceivedNote->supplier?->name }}</div></div>
            <div><div class="label">Received Date</div><div class="value">{{ $goodsReceivedNote->received_date?->format('Y-m-d') }}</div></div>
            <div>
                <div class="label">Purchase Order</div>
                <div class="value">
                    @if ($goodsReceivedNote->purchaseOrder)
                        <a href="{{ route('purchase-orders.show', $goodsReceivedNote->purchaseOrder) }}">{{ $goodsReceivedNote->purchaseOrder->po_number }}</a>
                    @else
                        —
                    @endif
                </div>
            </div>
            <div><div class="label">Receiving Branch</div><div class="value">{{ $goodsReceivedNote->branch?->name ?? '—' }}</div></div>
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
                        <th class="text-center">Qty Received</th>
                        <th class="text-end">Unit Cost</th>
                        <th>Condition</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($goodsReceivedNote->items as $item)
                        <tr>
                            <td>{{ $item->product_code }} — {{ $item->product_description }}</td>
                            <td><span class="inv-no">{{ $item->batch_number }}</span></td>
                            <td>{{ $item->expiry_date?->format('Y-m-d') }}</td>
                            <td class="text-center">{{ $item->qty_received }}</td>
                            <td class="text-end">{{ number_format($item->unit_cost, 2) }}</td>
                            <td><span class="badge-status badge-{{ $item->status }}">{{ ucfirst($item->status) }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if ($goodsReceivedNote->notes)
        <div class="detail-card">
            <div class="card-title">Notes</div>
            <p class="mb-0">{{ $goodsReceivedNote->notes }}</p>
        </div>
    @endif

</div>
@endsection
