@extends('layouts.app')

@section('title', 'Delivery Note ' . $deliveryNote->delivery_note_no)

@section('content')
<div class="page-wrap">

    <div class="page-header">
        <div>
            <h4><i class="bi bi-truck me-2 text-success"></i>Delivery Note {{ $deliveryNote->delivery_note_no }}</h4>
            <div class="sub">Sales Order <a href="{{ route('sales-orders.show', $deliveryNote->salesOrder) }}">{{ $deliveryNote->salesOrder?->so_number }}</a></div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('delivery-notes.pdf', $deliveryNote) }}" target="_blank" class="btn btn-success btn-sm"><i class="bi bi-file-earmark-pdf me-1"></i>Print PDF</a>
            <a href="{{ route('delivery-notes.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    <div class="detail-card">
        <div class="detail-grid">
            <div><div class="label">Client</div><div class="value">{{ $deliveryNote->client?->name }}</div></div>
            <div><div class="label">Delivery Date</div><div class="value">{{ $deliveryNote->delivery_date?->format('Y-m-d') }}</div></div>
            <div><div class="label">Delivering Branch</div><div class="value">{{ $deliveryNote->branch?->name ?? '—' }}</div></div>
            <div><div class="label">Delivered By</div><div class="value">{{ $deliveryNote->deliveredBy?->name ?? '—' }}</div></div>
        </div>
    </div>

    <div class="table-card mb-4">
        <div class="p-3 border-bottom fw-semibold" style="font-size:.85rem;">
            <i class="bi bi-list-ul me-1 text-success"></i>Items Delivered
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Batch Number</th>
                        <th>Expiry Date</th>
                        <th class="text-center">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($deliveryNote->items as $item)
                        <tr>
                            <td>{{ $item->product_code }} — {{ $item->product_description }}</td>
                            <td><span class="inv-no">{{ $item->batch_number ?? '—' }}</span></td>
                            <td>{{ $item->expiry_date?->format('Y-m-d') ?? '—' }}</td>
                            <td class="text-center">{{ $item->qty }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
