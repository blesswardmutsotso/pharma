@extends('layouts.app')

@section('title', 'Stock Audit Log')

@push('styles')
<style>
    .transfers-wrapper { padding: 1.5rem 2rem; }

    .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: .75rem; }
    .page-header h4 { font-weight: 700; color: var(--pos-green-dk); margin: 0; font-size: 1.35rem; letter-spacing: -.01em; }
    .page-header .sub { font-size: .8rem; color: #6c757d; margin-top: .1rem; }

    .filter-bar { background: #fff; border: 1px solid #e9ecef; border-radius: 12px; padding: .9rem 1.25rem; margin-bottom: 1.25rem; display: flex; flex-wrap: wrap; gap: .6rem; align-items: center; box-shadow: 0 1px 4px rgba(0,0,0,.04); }
    .filter-bar .form-control, .filter-bar .form-select { border-radius: 8px; font-size: .82rem; border-color: #dee2e6; height: 36px; }
    .filter-bar .form-control:focus, .filter-bar .form-select:focus { border-color: var(--pos-green); box-shadow: 0 0 0 .2rem rgba(25,135,84,.15); }
    .filter-bar .btn { height: 36px; font-size: .82rem; border-radius: 8px; padding: 0 1rem; }

    .table-card { background: #fff; border: 1px solid #e9ecef; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.05); }
    .table-card .table { margin: 0; font-size: .83rem; }
    .table-card .table thead th { background: #f8f9fa; border-bottom: 2px solid #e9ecef; color: #495057; font-weight: 700; font-size: .72rem; text-transform: uppercase; letter-spacing: .06em; padding: .75rem 1rem; white-space: nowrap; }
    .table-card .table tbody td { padding: .6rem 1rem; vertical-align: middle; border-color: #f1f3f5; color: #343a40; }
    .table-card .table tbody tr:hover { background: #f8fffe; }

    .badge-action { font-size: .7rem; font-weight: 600; padding: .28em .65em; border-radius: 6px; }
    .badge-green { background: #d1e7dd; color: #145c2d; }
    .badge-red   { background: #fee2e2; color: #b91c1c; }
    .badge-amber { background: #fff3cd; color: #a16207; }

    .inv-no { font-family: 'Courier New', monospace; font-size: .78rem; color: #1d4ed8; font-weight: 600; }
    .qty-change-pos { font-weight: 700; color: #145c2d; font-variant-numeric: tabular-nums; }
    .qty-change-neg { font-weight: 700; color: #b91c1c; font-variant-numeric: tabular-nums; }

    .empty-state { text-align: center; padding: 4rem 2rem; color: #adb5bd; }
    .empty-state i { font-size: 3rem; margin-bottom: 1rem; display: block; }
    .pagination .page-link { font-size: .8rem; border-radius: 7px !important; margin: 0 2px; color: var(--pos-green); border-color: #dee2e6; }
    .pagination .page-item.active .page-link { background: var(--pos-green); border-color: var(--pos-green); color: #fff; }
</style>
@endpush

@section('content')
<div class="transfers-wrapper">

    {{-- Header --}}
    <div class="page-header">
        <div>
            <a href="{{ route('stock.transfers.index') }}" class="text-muted text-decoration-none" style="font-size:.82rem;">
                <i class="bi bi-arrow-left me-1"></i>Transfers
            </a>
            <h4 class="mt-1"><i class="bi bi-journal-text me-2 text-success"></i>Stock Audit Log</h4>
            <div class="sub">Immutable record of all stock movements</div>
        </div>
    </div>

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('stock.transfers.audit') }}" class="filter-bar">
        <input type="text" name="product_code" class="form-control" style="width:165px;"
               placeholder="Product code…" value="{{ request('product_code') }}">
        <select name="action" class="form-select" style="width:165px;">
            <option value="">All Actions</option>
            @foreach($actions as $a)
                <option value="{{ $a }}" @selected(request('action') === $a)>
                    {{ ucwords(strtolower(str_replace('_', ' ', $a))) }}
                </option>
            @endforeach
        </select>
        <input type="text" name="user" class="form-control" style="width:155px;"
               placeholder="User name…" value="{{ request('user') }}">
        <input type="date" name="from" class="form-control" style="width:145px;" value="{{ request('from') }}">
        <input type="date" name="to"   class="form-control" style="width:145px;" value="{{ request('to') }}">
        <button type="submit" class="btn btn-success"><i class="bi bi-funnel-fill me-1"></i>Filter</button>
        @if(request()->hasAny(['product_code','action','user','from','to']))
            <a href="{{ route('stock.transfers.audit') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle me-1"></i>Clear
            </a>
        @endif
    </form>

    {{-- Table --}}
    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Action</th>
                        <th>Product</th>
                        <th class="text-end">Before</th>
                        <th class="text-end">After</th>
                        <th class="text-end">Change</th>
                        <th>Reference</th>
                        <th>User</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    @php
                        $badgeClass = match($log->actionColor()) {
                            'green' => 'badge-green',
                            'red'   => 'badge-red',
                            default => 'badge-amber',
                        };
                    @endphp
                    <tr>
                        <td style="white-space:nowrap;font-size:.8rem;">
                            {{ $log->created_at->format('d M Y') }}<br>
                            <span class="text-muted" style="font-size:.72rem;">{{ $log->created_at->format('H:i:s') }}</span>
                        </td>
                        <td>
                            <span class="badge-action {{ $badgeClass }}">{{ $log->actionLabel() }}</span>
                        </td>
                        <td>
                            <span class="inv-no">{{ $log->product_code }}</span>
                            <span class="text-muted ms-1" style="font-size:.78rem;">{{ Str::limit($log->product_description, 32) }}</span>
                        </td>
                        <td class="text-end" style="font-variant-numeric:tabular-nums;font-size:.82rem;">{{ number_format($log->qty_before) }}</td>
                        <td class="text-end" style="font-variant-numeric:tabular-nums;font-size:.82rem;">{{ number_format($log->qty_after) }}</td>
                        <td class="text-end">
                            <span class="{{ $log->qty_change >= 0 ? 'qty-change-pos' : 'qty-change-neg' }}">
                                {{ $log->qty_change >= 0 ? '+' : '' }}{{ number_format($log->qty_change) }}
                            </span>
                        </td>
                        <td style="font-size:.78rem;">
                            @if($log->reference_label)
                                @if($log->reference_type === 'StockTransfer')
                                    <a href="{{ route('stock.transfers.show', $log->reference_id) }}"
                                       class="inv-no text-decoration-none">{{ $log->reference_label }}</a>
                                @else
                                    <span class="inv-no">{{ $log->reference_label }}</span>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                            @if($log->notes)
                                <div class="text-muted mt-1" style="font-size:.72rem;">{{ Str::limit($log->notes, 45) }}</div>
                            @endif
                        </td>
                        <td style="font-size:.8rem;color:#495057;">{{ $log->performed_by_name }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="bi bi-journal-text"></i>
                                <p>No audit entries found{{ request()->hasAny(['product_code','action','user','from','to']) ? ' matching your filters' : '' }}.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top bg-light" style="font-size:.8rem;">
            <span class="text-muted">Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }} entries</span>
            {{ $logs->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>

</div>
@endsection
