@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
<style>
:root { --green: #198754; --green-dk: #145c2d; --green-lt: #d1e7dd; }
.an-wrap  { padding: 1.5rem 2rem 3rem; }
.an-title { font-weight: 800; color: var(--green-dk); font-size: 1.45rem; margin: 0; }
.an-sub   { font-size: .8rem; color: #6c757d; margin-top: .15rem; }
.section-label { font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .1em; color: #adb5bd; margin: 1.8rem 0 .75rem; display: flex; align-items: center; gap: .5rem; }
.section-label::after { content: ''; flex: 1; height: 1px; background: #e9ecef; }
.chart-row { display: grid; gap: 1rem; margin-bottom: 1rem; }
.chart-row.cols-2 { grid-template-columns: 1fr 1fr; }
@media(max-width:900px) { .chart-row.cols-2 { grid-template-columns: 1fr; } }
.card { background: #fff; border: 1px solid #e9ecef; border-radius: 12px; padding: 1.25rem 1.4rem; box-shadow: 0 1px 4px rgba(0,0,0,.05); }
.card-title { font-size: .75rem; font-weight: 800; text-transform: uppercase; letter-spacing: .07em; color: #495057; margin-bottom: 1rem; display: flex; align-items: center; justify-content: space-between; }
.tbl { width: 100%; border-collapse: collapse; font-size: .81rem; }
.tbl thead th { background: #f8f9fa; border-bottom: 2px solid #e9ecef; color: #495057; font-weight: 700; font-size: .69rem; text-transform: uppercase; letter-spacing: .06em; padding: .55rem .85rem; white-space: nowrap; }
.tbl tbody td { padding: .55rem .85rem; border-bottom: 1px solid #f1f3f5; vertical-align: middle; }
.tbl tbody tr:last-child td { border-bottom: none; }
.b { font-size: .68rem; font-weight: 700; padding: .22em .58em; border-radius: 5px; }
.b-green  { background: #d1e7dd; color: #145c2d; }
.b-red    { background: #fee2e2; color: #b91c1c; }
.b-yellow { background: #fff3cd; color: #856404; }
.b-blue   { background: #dbeafe; color: #1d4ed8; }
.mono { font-family: 'Courier New', monospace; font-size: .78rem; }
</style>
@endpush

@section('content')
<div class="an-wrap">

    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.5rem">
        <div>
            <div class="an-title">Welcome back, {{ auth()->user()->name }}</div>
            <div class="an-sub">Pharmaceutical Wholesale Overview &nbsp;·&nbsp; {{ now()->format('d M Y, H:i') }}</div>
        </div>
    </div>

    <div class="section-label">At a glance</div>
    <div class="stat-cards">
        <a href="{{ route('products.index', ['low_stock' => 1]) }}" class="stat-card text-decoration-none">
            <div class="icon {{ $lowStockCount > 0 ? 'yellow' : 'green' }}"><i class="bi bi-exclamation-triangle"></i></div>
            <div><div class="label">Low Stock Products</div><div class="value">{{ $lowStockCount }}</div></div>
        </a>
        <div class="stat-card">
            <div class="icon {{ $expiringBatchesCount > 0 ? 'yellow' : 'green' }}"><i class="bi bi-hourglass-split"></i></div>
            <div><div class="label">Batches Expiring (90 days)</div><div class="value">{{ $expiringBatchesCount }}</div></div>
        </div>
        <a href="{{ route('purchase-orders.index') }}" class="stat-card text-decoration-none">
            <div class="icon blue"><i class="bi bi-file-earmark-text"></i></div>
            <div><div class="label">Open Purchase Orders</div><div class="value">{{ $openPurchaseOrders }}</div></div>
        </a>
        <a href="{{ route('sales-orders.index') }}" class="stat-card text-decoration-none">
            <div class="icon blue"><i class="bi bi-cart-plus"></i></div>
            <div><div class="label">Open Sales Orders</div><div class="value">{{ $openSalesOrders }}</div></div>
        </a>
        <a href="{{ route('sales-invoices.index') }}" class="stat-card text-decoration-none">
            <div class="icon {{ $unpaidInvoicesTotal > 0 ? 'red' : 'green' }}"><i class="bi bi-cash-stack"></i></div>
            <div><div class="label">Outstanding Receivables</div><div class="value">${{ number_format($unpaidInvoicesTotal, 2) }}</div></div>
        </a>
    </div>

    <div class="section-label">Recent activity</div>
    <div class="chart-row cols-2">
        <div class="card">
            <div class="card-title">Recent Sales Orders</div>
            <table class="tbl">
                <thead><tr><th>SO Number</th><th>Client</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse ($recentSalesOrders as $so)
                        <tr>
                            <td><a href="{{ route('sales-orders.show', $so) }}">{{ $so->so_number }}</a></td>
                            <td>{{ $so->client?->name }}</td>
                            <td><span class="b b-blue">{{ ucfirst($so->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="3" style="text-align:center;color:#adb5bd;padding:1rem">No sales orders yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card">
            <div class="card-title">Recent Purchase Orders</div>
            <table class="tbl">
                <thead><tr><th>PO Number</th><th>Supplier</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse ($recentPurchaseOrders as $po)
                        <tr>
                            <td><a href="{{ route('purchase-orders.show', $po) }}">{{ $po->po_number }}</a></td>
                            <td>{{ $po->supplier?->name }}</td>
                            <td><span class="b b-blue">{{ ucfirst($po->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="3" style="text-align:center;color:#adb5bd;padding:1rem">No purchase orders yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="section-label">Stock control</div>
    <div class="chart-row cols-2">
        <div class="card">
            <div class="card-title">Low Stock Products <span class="badge {{ $lowStockItems->count() > 0 ? 'b-yellow' : 'b-green' }}">{{ $lowStockItems->count() }}</span></div>
            <table class="tbl">
                <thead><tr><th>Code</th><th>Product</th><th>Qty</th><th>Reorder Point</th></tr></thead>
                <tbody>
                    @forelse ($lowStockItems as $item)
                        <tr>
                            <td class="mono">{{ $item->product_code }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($item->product_description, 28) }}</td>
                            <td><span class="b {{ $item->quantity == 0 ? 'b-red' : 'b-yellow' }}">{{ $item->quantity }}</span></td>
                            <td>{{ $item->reorder_point }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="text-align:center;color:#198754;padding:1rem">All stock levels healthy</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card">
            <div class="card-title">Expiry Watch <span class="badge {{ $expiringBatches->count() > 0 ? 'b-yellow' : 'b-green' }}">{{ $expiringBatches->count() }}</span></div>
            <table class="tbl">
                <thead><tr><th>Batch</th><th>Product</th><th>Expiry</th><th>Qty</th></tr></thead>
                <tbody>
                    @forelse ($expiringBatches as $batch)
                        <tr>
                            <td class="mono">{{ $batch->batch_number }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($batch->stock?->product_description, 28) }}</td>
                            <td>{{ $batch->expiry_date->format('Y-m-d') }}</td>
                            <td>{{ $batch->qty_on_hand }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="text-align:center;color:#198754;padding:1rem">No batches due to expire in the next 90 days</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
