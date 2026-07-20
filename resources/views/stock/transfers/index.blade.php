@extends('layouts.app')

@section('title', 'Stock Transfers')

@push('styles')
<style>
    .transfers-wrapper { padding: 1.5rem 2rem; }

    .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: .75rem; }
    .page-header h4 { font-weight: 700; color: var(--pos-green-dk); margin: 0; font-size: 1.35rem; letter-spacing: -.01em; }
    .page-header .sub { font-size: .8rem; color: #6c757d; margin-top: .1rem; }

    .stat-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 1rem; margin-bottom: 1.75rem; }
    .stat-card { background: #fff; border: 1px solid #e9ecef; border-radius: 12px; padding: 1rem 1.25rem; display: flex; align-items: center; gap: .9rem; box-shadow: 0 1px 4px rgba(0,0,0,.05); transition: box-shadow .15s, transform .15s; }
    .stat-card:hover { box-shadow: 0 4px 14px rgba(25,135,84,.12); transform: translateY(-2px); }
    .stat-card .icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; }
    .stat-card .icon.green  { background: var(--pos-green-light); color: var(--pos-green); }
    .stat-card .icon.blue   { background: #dbeafe; color: #1d4ed8; }
    .stat-card .icon.yellow { background: #fef9c3; color: #a16207; }
    .stat-card .icon.red    { background: #fee2e2; color: #b91c1c; }
    .stat-card .label { font-size: .72rem; text-transform: uppercase; letter-spacing: .06em; color: #6c757d; font-weight: 600; }
    .stat-card .value { font-size: 1.3rem; font-weight: 700; color: #212529; line-height: 1.1; margin-top: .15rem; }

    .filter-bar { background: #fff; border: 1px solid #e9ecef; border-radius: 12px; padding: .9rem 1.25rem; margin-bottom: 1.25rem; display: flex; flex-wrap: wrap; gap: .6rem; align-items: center; box-shadow: 0 1px 4px rgba(0,0,0,.04); }
    .filter-bar .form-control, .filter-bar .form-select { border-radius: 8px; font-size: .82rem; border-color: #dee2e6; height: 36px; }
    .filter-bar .form-control:focus, .filter-bar .form-select:focus { border-color: var(--pos-green); box-shadow: 0 0 0 .2rem rgba(25,135,84,.15); }
    .filter-bar .btn { height: 36px; font-size: .82rem; border-radius: 8px; padding: 0 1rem; }

    .table-card { background: #fff; border: 1px solid #e9ecef; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.05); }
    .table-card .table { margin: 0; font-size: .83rem; }
    .table-card .table thead th { background: #f8f9fa; border-bottom: 2px solid #e9ecef; color: #495057; font-weight: 700; font-size: .72rem; text-transform: uppercase; letter-spacing: .06em; padding: .75rem 1rem; white-space: nowrap; }
    .table-card .table tbody td { padding: .65rem 1rem; vertical-align: middle; border-color: #f1f3f5; color: #343a40; }
    .table-card .table tbody tr:hover { background: #f8fffe; }

    .badge-type { font-size: .7rem; font-weight: 600; padding: .28em .65em; border-radius: 6px; }
    .badge-out  { background: #fff3cd; color: #a16207; }
    .badge-in   { background: #d1e7dd; color: #145c2d; }

    .badge-status { font-size: .7rem; font-weight: 600; padding: .28em .65em; border-radius: 6px; }
    .badge-draft     { background: #e9ecef; color: #495057; }
    .badge-pending   { background: #fff3cd; color: #a16207; }
    .badge-approved  { background: #d1e7dd; color: #145c2d; }
    .badge-rejected  { background: #fee2e2; color: #b91c1c; }
    .badge-cancelled { background: #e9ecef; color: #6c757d; }

    .inv-no { font-family: 'Courier New', monospace; font-size: .8rem; color: #495057; font-weight: 600; }
    .user-pill { display: inline-flex; align-items: center; gap: .35rem; font-size: .78rem; color: #495057; }
    .user-pill .avatar { width: 22px; height: 22px; border-radius: 50%; background: var(--pos-green-light); color: var(--pos-green-dk); font-size: .65rem; font-weight: 700; display: flex; align-items: center; justify-content: center; text-transform: uppercase; flex-shrink: 0; }

    .btn-action { width: 30px; height: 30px; border-radius: 7px; border: 1px solid #dee2e6; background: #fff; color: #495057; display: inline-flex; align-items: center; justify-content: center; font-size: .85rem; transition: background .12s, color .12s, border-color .12s; text-decoration: none; cursor: pointer; }
    .btn-action:hover { background: var(--pos-green-light); color: var(--pos-green-dk); border-color: var(--pos-green); }

    .empty-state { text-align: center; padding: 4rem 2rem; color: #adb5bd; }
    .empty-state i { font-size: 3rem; margin-bottom: 1rem; display: block; }
    .empty-state p { font-size: .9rem; margin: 0; }

    .pagination .page-link { font-size: .8rem; border-radius: 7px !important; margin: 0 2px; color: var(--pos-green); border-color: #dee2e6; }
    .pagination .page-item.active .page-link { background: var(--pos-green); border-color: var(--pos-green); color: #fff; }
</style>
@endpush

@section('content')
<div class="transfers-wrapper">

    {{-- Header --}}
    <div class="page-header">
        <div>
            <h4><i class="bi bi-arrow-left-right me-2 text-success"></i>Stock Transfers</h4>
            <div class="sub">Manage stock movements between branches</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('stock.transfers.audit') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-journal-text me-1"></i>Audit Log
            </a>
            <a href="{{ route('stock.transfers.create') }}" class="btn btn-success btn-sm">
                <i class="bi bi-plus-lg me-1"></i>New Transfer
            </a>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show py-2 px-3 mb-3" style="font-size:.83rem;border-radius:10px;" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show py-2 px-3 mb-3" style="font-size:.83rem;border-radius:10px;" role="alert">
        <i class="bi bi-exclamation-circle-fill me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Stat cards --}}
    <div class="stat-cards">
        <div class="stat-card">
            <div class="icon green"><i class="bi bi-arrow-left-right"></i></div>
            <div><div class="label">Total</div><div class="value">{{ number_format($stats['total']) }}</div></div>
        </div>
        <div class="stat-card">
            <div class="icon yellow"><i class="bi bi-hourglass-split"></i></div>
            <div><div class="label">Pending</div><div class="value">{{ number_format($stats['pending']) }}</div></div>
        </div>
        <div class="stat-card">
            <div class="icon green"><i class="bi bi-check-circle"></i></div>
            <div><div class="label">Approved</div><div class="value">{{ number_format($stats['approved']) }}</div></div>
        </div>
        <div class="stat-card">
            <div class="icon blue"><i class="bi bi-calendar-month"></i></div>
            <div><div class="label">This Month</div><div class="value">{{ number_format($stats['this_month']) }}</div></div>
        </div>
    </div>

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('stock.transfers.index') }}" class="filter-bar">
        <select name="status" class="form-select" style="width:145px;">
            <option value="">All Statuses</option>
            @foreach(['DRAFT','PENDING','APPROVED','REJECTED','CANCELLED'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(strtolower($s)) }}</option>
            @endforeach
        </select>
        <select name="type" class="form-select" style="width:130px;">
            <option value="">All Types</option>
            <option value="OUTGOING" @selected(request('type') === 'OUTGOING')>Outgoing</option>
            <option value="INCOMING" @selected(request('type') === 'INCOMING')>Incoming</option>
        </select>
        <select name="branch" class="form-select" style="width:155px;">
            <option value="">All Branches</option>
            @foreach($branches as $b)
                <option value="{{ $b->id }}" @selected(request('branch') == $b->id)>{{ $b->name }}</option>
            @endforeach
        </select>
        <input type="date" name="from" class="form-control" style="width:145px;" value="{{ request('from') }}">
        <input type="date" name="to"   class="form-control" style="width:145px;" value="{{ request('to') }}">
        <button type="submit" class="btn btn-success"><i class="bi bi-funnel-fill me-1"></i>Filter</button>
        @if(request()->hasAny(['status','type','branch','from','to']))
            <a href="{{ route('stock.transfers.index') }}" class="btn btn-outline-secondary">
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
                        <th>Transfer No</th>
                        <th>Type</th>
                        <th>From</th>
                        <th>To</th>
                        <th class="text-center">Items</th>
                        <th class="text-center">Qty</th>
                        <th>Status</th>
                        <th>Requested By</th>
                        <th>Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transfers as $t)
                    @php
                        $statusClass = match($t->status) {
                            'APPROVED'  => 'badge-approved',
                            'PENDING'   => 'badge-pending',
                            'REJECTED'  => 'badge-rejected',
                            'CANCELLED' => 'badge-cancelled',
                            default     => 'badge-draft',
                        };
                        $initials = strtoupper(substr($t->requestedBy?->name ?? 'U', 0, 1));
                    @endphp
                    <tr>
                        <td><span class="inv-no">{{ $t->transfer_no }}</span></td>
                        <td>
                            @if($t->transfer_type === 'OUTGOING')
                                <span class="badge-type badge-out"><i class="bi bi-arrow-right me-1"></i>Out</span>
                            @else
                                <span class="badge-type badge-in"><i class="bi bi-arrow-left me-1"></i>In</span>
                            @endif
                        </td>
                        <td style="font-size:.8rem;">{{ $t->fromBranch?->name ?? '—' }}</td>
                        <td style="font-size:.8rem;">{{ $t->toBranch?->name ?? '—' }}</td>
                        <td class="text-center" style="font-size:.8rem;">{{ $t->total_items }}</td>
                        <td class="text-center" style="font-size:.8rem;">{{ number_format($t->total_qty) }}</td>
                        <td><span class="badge-status {{ $statusClass }}">{{ ucfirst(strtolower($t->status)) }}</span></td>
                        <td>
                            <span class="user-pill">
                                <span class="avatar">{{ $initials }}</span>
                                {{ $t->requestedBy?->name ?? '—' }}
                            </span>
                        </td>
                        <td style="white-space:nowrap;font-size:.8rem;">
                            {{ $t->created_at->format('d M Y') }}<br>
                            <span class="text-muted" style="font-size:.72rem;">{{ $t->created_at->format('H:i') }}</span>
                        </td>
                        <td class="text-center">
                            <a class="btn-action" href="{{ route('stock.transfers.show', $t) }}" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10">
                            <div class="empty-state">
                                <i class="bi bi-arrow-left-right"></i>
                                <p>No transfers found{{ request()->hasAny(['status','type','branch','from','to']) ? ' matching your filters' : '' }}.<br>
                                <a href="{{ route('stock.transfers.create') }}" class="text-success fw-semibold">Create the first transfer</a></p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transfers->hasPages())
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top bg-light" style="font-size:.8rem;">
            <span class="text-muted">Showing {{ $transfers->firstItem() }}–{{ $transfers->lastItem() }} of {{ $transfers->total() }} transfers</span>
            {{ $transfers->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>

</div>
@endsection
