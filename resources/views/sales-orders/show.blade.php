@extends('layouts.app')

@section('title', 'SO ' . $salesOrder->so_number)

@section('content')
<div class="page-wrap">

    <div class="page-header">
        <div>
            <h4><i class="bi bi-cart-plus me-2 text-success"></i>Sales Order {{ $salesOrder->so_number }}</h4>
            <div class="sub"><span class="badge-status badge-{{ $salesOrder->status }}">{{ ucfirst($salesOrder->status) }}</span></div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('sales-orders.pdf', $salesOrder) }}" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="bi bi-file-earmark-pdf me-1"></i>Print Invoice</a>
            @if ($salesOrder->deliveryNote)
                <a href="{{ route('delivery-notes.pdf', $salesOrder->deliveryNote) }}" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="bi bi-truck me-1"></i>Print Delivery Note</a>
            @endif
            <a href="{{ route('sales-orders.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    <div class="detail-card">
        <div class="detail-grid">
            <div><div class="label">Client</div><div class="value">{{ $salesOrder->client?->name }}</div></div>
            <div><div class="label">Currency</div><div class="value">{{ $salesOrder->currency }}</div></div>
            <div><div class="label">Client PO Number</div><div class="value">{{ $salesOrder->client_po_number ?? '—' }}</div></div>
            <div><div class="label">Order Date</div><div class="value">{{ $salesOrder->order_date?->format('Y-m-d') }}</div></div>
            <div><div class="label">Required Date</div><div class="value">{{ $salesOrder->required_date?->format('Y-m-d') ?? '—' }}</div></div>
            <div><div class="label">Dispatched At</div><div class="value">{{ $salesOrder->dispatched_at?->format('Y-m-d H:i') ?? '—' }}</div></div>
            <div><div class="label">Fulfilling Branch</div><div class="value">{{ $salesOrder->branch?->name ?? '—' }}</div></div>
            <div><div class="label">Created By</div><div class="value">{{ $salesOrder->createdBy?->name ?? '—' }}</div></div>
            <div><div class="label">Confirmed By</div><div class="value">{{ $salesOrder->confirmedBy?->name ?? '—' }}</div></div>
        </div>
    </div>

    <div class="d-flex gap-2 mb-4 flex-wrap">
        @if ($salesOrder->canBeConfirmed())
            <form action="{{ route('sales-orders.confirm', $salesOrder) }}" method="POST"
                  data-confirm="Confirm {{ $salesOrder->so_number }} and allocate stock using FEFO?" data-confirm-icon="question">
                @csrf
                <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-check2-circle me-1"></i>Confirm &amp; Allocate Stock (FEFO)</button>
            </form>
        @endif
        @if (in_array($salesOrder->status, ['confirmed', 'picking']) && $salesOrder->items->contains(fn ($i) => $i->isBackordered()))
            <form action="{{ route('sales-orders.allocate-remaining', $salesOrder) }}" method="POST"
                  data-confirm="Re-check stock and allocate anything now available for backordered lines?" data-confirm-icon="question">
                @csrf
                <button type="submit" class="btn btn-outline-success btn-sm"><i class="bi bi-arrow-repeat me-1"></i>Allocate Remaining Stock</button>
            </form>
        @endif
        @if ($salesOrder->canStartPicking())
            <form action="{{ route('sales-orders.start-picking', $salesOrder) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-box-arrow-up me-1"></i>Start Picking</button>
            </form>
        @endif
        @if ($salesOrder->status === 'picking')
            <a href="{{ route('sales-orders.picking-list', $salesOrder) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-clipboard-check me-1"></i>View Picking List</a>
            <form action="{{ route('sales-orders.dispatch', $salesOrder) }}" method="POST"
                  data-confirm="Dispatch {{ $salesOrder->so_number }}? This deducts stock and generates the invoice." data-confirm-icon="question">
                @csrf
                <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-truck me-1"></i>Dispatch</button>
            </form>
        @endif
        @if ($salesOrder->canBeCancelled())
            <form action="{{ route('sales-orders.cancel', $salesOrder) }}" method="POST"
                  data-confirm="Cancel {{ $salesOrder->so_number }}? Any reserved stock will be released." data-confirm-danger="true">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-x-circle me-1"></i>Cancel</button>
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
                        <th class="text-center">Qty Allocated</th>
                        <th class="text-center">Qty Dispatched</th>
                        <th class="text-end">Unit Price</th>
                        <th class="text-end">Discount</th>
                        <th class="text-end">Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($salesOrder->items as $item)
                        <tr>
                            <td>{{ $item->product_code }} — {{ $item->product_description }}</td>
                            <td class="text-center">{{ $item->qty_ordered }}</td>
                            <td class="text-center">
                                {{ $item->qty_allocated }}
                                @if ($item->isBackordered())
                                    <span class="badge-status badge-pending" title="Backordered">{{ $item->backorderedQty() }} short</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $item->qty_dispatched }}</td>
                            <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-end">{{ number_format($item->discount, 2) }}</td>
                            <td class="text-end">{{ number_format($item->line_total, 2) }}</td>
                        </tr>
                        @foreach ($item->batchAllocations as $allocation)
                            <tr class="text-muted" style="font-size:.78rem;">
                                <td colspan="7">
                                    <i class="bi bi-arrow-return-right me-1"></i>Batch <span class="inv-no">{{ $allocation->stockBatch->batch_number }}</span>
                                    (exp {{ $allocation->stockBatch->expiry_date->format('Y-m-d') }}): {{ $allocation->qty_allocated }} units
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if ($salesOrder->invoice)
        <div class="alert alert-light border small">
            <i class="bi bi-info-circle me-1"></i>To record a return against this order, open its
            <a href="{{ route('sales-invoices.show', $salesOrder->invoice) }}">invoice ({{ $salesOrder->invoice->invoice_number }})</a>
            — returns are recorded per invoice line so the credit note matches exactly what's being sent back.
        </div>
    @endif

</div>
@endsection
