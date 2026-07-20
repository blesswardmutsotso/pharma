@extends('layouts.app')

@section('title', 'SO ' . $salesOrder->so_number)

@section('content')
<div class="page-wrap">

    <div class="page-header">
        <div>
            <h4><i class="bi bi-cart-plus me-2 text-success"></i>Sales Order {{ $salesOrder->so_number }}</h4>
            <div class="sub"><span class="badge-status badge-{{ $salesOrder->status }}">{{ ucfirst($salesOrder->status) }}</span></div>
        </div>
        <a href="{{ route('sales-orders.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>

    <div class="detail-card">
        <div class="detail-grid">
            <div><div class="label">Client</div><div class="value">{{ $salesOrder->client?->name }}</div></div>
            <div><div class="label">Order Date</div><div class="value">{{ $salesOrder->order_date?->format('Y-m-d') }}</div></div>
            <div><div class="label">Required Date</div><div class="value">{{ $salesOrder->required_date?->format('Y-m-d') ?? '—' }}</div></div>
            <div><div class="label">Dispatched At</div><div class="value">{{ $salesOrder->dispatched_at?->format('Y-m-d H:i') ?? '—' }}</div></div>
            <div><div class="label">Fulfilling Branch</div><div class="value">{{ $salesOrder->branch?->name ?? '—' }}</div></div>
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
                        <th class="text-end">Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($salesOrder->items as $item)
                        <tr>
                            <td>{{ $item->product_code }} — {{ $item->product_description }}</td>
                            <td class="text-center">{{ $item->qty_ordered }}</td>
                            <td class="text-center">{{ $item->qty_allocated }}</td>
                            <td class="text-center">{{ $item->qty_dispatched }}</td>
                            <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-end">{{ number_format($item->line_total, 2) }}</td>
                        </tr>
                        @foreach ($item->batchAllocations as $allocation)
                            <tr class="text-muted" style="font-size:.78rem;">
                                <td colspan="6">
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

    @if ($salesOrder->status === 'dispatched')
        <div class="detail-card">
            <div class="card-title"><i class="bi bi-arrow-counterclockwise me-1"></i>Record a Return</div>
            <form action="{{ route('sales-orders.return', $salesOrder) }}" method="POST" class="row g-3 align-items-end"
                  data-confirm="Record this return? The quantity will be quarantined pending inspection." data-confirm-icon="question">
                @csrf
                <div class="col-md-4">
                    <label class="form-label">Item</label>
                    <select name="sales_order_item_id" class="form-select" required>
                        @foreach ($salesOrder->items as $item)
                            <option value="{{ $item->id }}">{{ $item->product_code }} — {{ $item->product_description }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Qty</label>
                    <input type="number" name="qty" class="form-control" min="1" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Reason</label>
                    <input type="text" name="reason" class="form-control">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-counterclockwise me-1"></i>Record</button>
                </div>
            </form>
        </div>
    @endif

</div>
@endsection
