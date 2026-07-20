@extends('layouts.app')

@section('title', 'Picking List — ' . $salesOrder->so_number)

@push('styles')
<style>
    @media print {
        .app-header, .app-sidebar, .flash-alerts, .no-print { display: none !important; }
        .app-main { margin: 0 !important; }
    }
</style>
@endpush

@section('content')
<div class="page-wrap">

    <div class="page-header no-print">
        <div>
            <h4><i class="bi bi-clipboard-check me-2 text-success"></i>Picking List — {{ $salesOrder->so_number }}</h4>
            <div class="sub">Batch/expiry allocated via FEFO — pick in the order shown</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('sales-orders.show', $salesOrder) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
            <button onclick="window.print()" class="btn btn-success btn-sm"><i class="bi bi-printer me-1"></i>Print</button>
        </div>
    </div>

    <div class="detail-card">
        <div class="detail-grid">
            <div><div class="label">Customer</div><div class="value">{{ $salesOrder->client?->name }}</div></div>
            <div><div class="label">Order Date</div><div class="value">{{ $salesOrder->order_date?->format('Y-m-d') }}</div></div>
            <div><div class="label">Required Date</div><div class="value">{{ $salesOrder->required_date?->format('Y-m-d') ?? '—' }}</div></div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Batch Number</th>
                        <th>Expiry Date</th>
                        <th class="text-center">Qty to Pick</th>
                        <th class="text-center">Picked</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($salesOrder->items as $item)
                        @foreach ($item->batchAllocations as $allocation)
                            <tr>
                                <td>{{ $item->product_code }} — {{ $item->product_description }}</td>
                                <td><span class="inv-no">{{ $allocation->stockBatch->batch_number }}</span></td>
                                <td>{{ $allocation->stockBatch->expiry_date->format('Y-m-d') }}</td>
                                <td class="text-center">{{ $allocation->qty_allocated }}</td>
                                <td class="text-center" style="font-size:1.1rem;">&#9633;</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row mt-5" style="font-size:.9rem;">
        <div class="col-md-6">Picked By: ______________________</div>
        <div class="col-md-6">Checked By: ______________________</div>
    </div>
</div>
@endsection
