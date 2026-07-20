@extends('layouts.app')

@section('title', 'Transfer ' . $transfer->transfer_no)

@push('styles')
<style>
    .transfers-wrapper { padding: 1.5rem 2rem; }

    .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: .75rem; }
    .page-header h4 { font-weight: 700; color: var(--pos-green-dk); margin: 0; font-size: 1.35rem; letter-spacing: -.01em; }
    .page-header .sub { font-size: .8rem; color: #6c757d; margin-top: .1rem; }

    .detail-card { background: #fff; border: 1px solid #e9ecef; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.05); margin-bottom: 1.25rem; overflow: hidden; }
    .detail-card .card-head { padding: .85rem 1.25rem; border-bottom: 1px solid #f1f3f5; display: flex; align-items: center; justify-content: space-between; background: #fafafa; }
    .detail-card .card-head h6 { margin: 0; font-weight: 700; font-size: .88rem; color: #343a40; }
    .detail-card .card-body-p { padding: 1.25rem; }

    .table-card { background: #fff; border: 1px solid #e9ecef; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.05); margin-bottom: 1.25rem; }
    .table-card .table { margin: 0; font-size: .83rem; }
    .table-card .table thead th { background: #f8f9fa; border-bottom: 2px solid #e9ecef; color: #495057; font-weight: 700; font-size: .72rem; text-transform: uppercase; letter-spacing: .06em; padding: .75rem 1rem; white-space: nowrap; }
    .table-card .table tbody td { padding: .6rem 1rem; vertical-align: middle; border-color: #f1f3f5; color: #343a40; }
    .table-card .table tbody tr:hover { background: #f8fffe; }
    .table-card .table tfoot td { background: #f8f9fa; font-weight: 700; font-size: .83rem; border-top: 2px solid #e9ecef; padding: .7rem 1rem; }

    .badge-status { font-size: .72rem; font-weight: 600; padding: .3em .7em; border-radius: 20px; }
    .badge-draft     { background: #e9ecef; color: #495057; }
    .badge-pending   { background: #fff3cd; color: #a16207; }
    .badge-approved  { background: #d1e7dd; color: #145c2d; }
    .badge-rejected  { background: #fee2e2; color: #b91c1c; }
    .badge-cancelled { background: #e9ecef; color: #6c757d; }

    .inv-no { font-family: 'Courier New', monospace; font-size: .8rem; font-weight: 600; }

    .meta-row { display: flex; justify-content: space-between; padding: .45rem 0; font-size: .83rem; border-bottom: 1px solid #f1f3f5; }
    .meta-row:last-child { border-bottom: none; }
    .meta-row .meta-label { color: #6c757d; }
    .meta-row .meta-value { font-weight: 600; color: #212529; text-align: right; }

    /* Audit timeline */
    .timeline { padding: 0 1.25rem 1.25rem; }
    .tl-item { display: flex; gap: .85rem; padding: .6rem 0; border-bottom: 1px solid #f1f3f5; }
    .tl-item:last-child { border-bottom: none; }
    .tl-dot { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .75rem; flex-shrink: 0; margin-top: .15rem; }
    .tl-dot.green { background: #d1e7dd; color: #145c2d; }
    .tl-dot.red   { background: #fee2e2; color: #b91c1c; }
    .tl-dot.amber { background: #fff3cd; color: #a16207; }
    .tl-body { flex: 1; min-width: 0; }
    .tl-title { font-size: .82rem; font-weight: 700; color: #212529; }
    .tl-meta  { font-size: .75rem; color: #6c757d; margin-top: .1rem; }
    .tl-qty   { font-size: .75rem; margin-top: .2rem; }
    .tl-time  { font-size: .72rem; color: #adb5bd; white-space: nowrap; margin-top: .15rem; }

    .amount { font-weight: 700; font-variant-numeric: tabular-nums; color: var(--pos-green-dk); }
    .low-stock { color: #b91c1c; font-weight: 700; }
</style>
@endpush

@section('content')
<div class="transfers-wrapper">

    {{-- Header --}}
    <div class="page-header">
        <div>
            <a href="{{ route('stock.transfers.index') }}" class="text-muted text-decoration-none" style="font-size:.82rem;">
                <i class="bi bi-arrow-left me-1"></i>All Transfers
            </a>
            <h4 class="mt-1">
                <i class="bi bi-arrow-left-right me-2 text-success"></i>
                <span class="inv-no fs-5">{{ $transfer->transfer_no }}</span>
                @php
                    $statusClass = match($transfer->status) {
                        'APPROVED'  => 'badge-approved',
                        'PENDING'   => 'badge-pending',
                        'REJECTED'  => 'badge-rejected',
                        'CANCELLED' => 'badge-cancelled',
                        default     => 'badge-draft',
                    };
                @endphp
                <span class="badge-status {{ $statusClass }} ms-2" style="font-size:.8rem;vertical-align:middle;">
                    {{ ucfirst(strtolower($transfer->status)) }}
                </span>
            </h4>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('stock.transfers.export', $transfer) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
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

    <div class="row g-4">

        {{-- Left: items + audit --}}
        <div class="col-lg-8">

            {{-- Items table --}}
            <div class="table-card">
                <div style="padding:.85rem 1.25rem; border-bottom:1px solid #f1f3f5; background:#fafafa; display:flex; align-items:center; justify-content:space-between;">
                    <h6 class="mb-0 fw-bold" style="font-size:.88rem;">Transfer Items</h6>
                    <span class="text-muted" style="font-size:.78rem;">{{ $transfer->total_items }} products · {{ number_format($transfer->total_qty) }} units</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Description</th>
                                <th class="text-center">Requested</th>
                                <th class="text-center">Approved</th>
                                <th class="text-end">Stock Now</th>
                                <th class="text-end">Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transfer->items as $item)
                            @php $currentQty = $currentStock[$item->product_code]?->quantity ?? null; @endphp
                            <tr>
                                <td><span class="inv-no">{{ $item->product_code }}</span></td>
                                <td style="font-size:.81rem;">{{ $item->product_description }}</td>
                                <td class="text-center">{{ number_format($item->qty_requested) }}</td>
                                <td class="text-center">
                                    @if($item->qty_approved !== null)
                                        <span class="text-success fw-bold">{{ number_format($item->qty_approved) }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($currentQty !== null)
                                        <span class="{{ $currentQty < 5 ? 'low-stock' : '' }}">{{ number_format($currentQty) }}</span>
                                    @else
                                        <span class="text-muted" style="font-size:.75rem;">not in system</span>
                                    @endif
                                </td>
                                <td class="text-end amount">{{ number_format($item->transferValue(), 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-end text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;">Total Value</td>
                                <td class="text-end amount">${{ number_format($transfer->items->sum(fn($i) => $i->transferValue()), 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Audit timeline --}}
            @if($auditLogs->isNotEmpty())
            <div class="detail-card">
                <div class="card-head"><h6>Stock Movement Log</h6></div>
                <div class="timeline">
                    @foreach($auditLogs as $log)
                    @php
                        $dotClass = match($log->actionColor()) { 'green' => 'green', 'red' => 'red', default => 'amber' };
                        $icon     = match($log->action) {
                            'TRANSFER_OUT' => 'bi-arrow-up-right',
                            'TRANSFER_IN'  => 'bi-arrow-down-left',
                            'SALE'         => 'bi-cart-dash',
                            'RETURN'       => 'bi-arrow-counterclockwise',
                            default        => 'bi-pencil',
                        };
                    @endphp
                    <div class="tl-item">
                        <div class="tl-dot {{ $dotClass }}"><i class="bi {{ $icon }}"></i></div>
                        <div class="tl-body">
                            <div class="tl-title">{{ $log->actionLabel() }} — <span class="inv-no">{{ $log->product_code }}</span></div>
                            <div class="tl-meta">{{ Str::limit($log->product_description, 45) }}</div>
                            <div class="tl-qty">
                                Qty: <span class="text-danger fw-semibold">{{ number_format($log->qty_before) }}</span>
                                → <span class="text-success fw-semibold">{{ number_format($log->qty_after) }}</span>
                                <span class="text-muted ms-1">({{ $log->qty_change >= 0 ? '+' : '' }}{{ number_format($log->qty_change) }})</span>
                            </div>
                            @if($log->notes)
                                <div class="tl-meta">{{ $log->notes }}</div>
                            @endif
                            <div class="tl-meta">by {{ $log->performed_by_name }}</div>
                        </div>
                        <div class="tl-time">{{ $log->created_at->format('d M H:i') }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

        {{-- Right: meta + actions --}}
        <div class="col-lg-4">

            {{-- Transfer details --}}
            <div class="detail-card">
                <div class="card-head"><h6>Details</h6></div>
                <div class="card-body-p">
                    <div class="meta-row">
                        <span class="meta-label">Type</span>
                        <span class="meta-value">{{ ucfirst(strtolower($transfer->transfer_type)) }}</span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label">From Branch</span>
                        <span class="meta-value">{{ $transfer->fromBranch?->name ?? '—' }}</span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label">To Branch</span>
                        <span class="meta-value">{{ $transfer->toBranch?->name ?? '—' }}</span>
                    </div>
                    @if($transfer->reference_doc)
                    <div class="meta-row">
                        <span class="meta-label">Reference</span>
                        <span class="meta-value inv-no">{{ $transfer->reference_doc }}</span>
                    </div>
                    @endif
                    <div class="meta-row">
                        <span class="meta-label">Requested By</span>
                        <span class="meta-value">{{ $transfer->requestedBy?->name ?? '—' }}</span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label">Created</span>
                        <span class="meta-value" style="font-weight:400;font-size:.8rem;">{{ $transfer->created_at->format('d M Y H:i') }}</span>
                    </div>
                    @if($transfer->approved_at)
                    <div class="meta-row">
                        <span class="meta-label">{{ $transfer->status === 'APPROVED' ? 'Approved' : 'Actioned' }} By</span>
                        <span class="meta-value">{{ $transfer->approvedBy?->name ?? '—' }}</span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label">{{ $transfer->status === 'APPROVED' ? 'Approved' : 'Actioned' }} At</span>
                        <span class="meta-value" style="font-weight:400;font-size:.8rem;">{{ $transfer->approved_at->format('d M Y H:i') }}</span>
                    </div>
                    @endif
                    @if($transfer->notes)
                    <div class="meta-row" style="flex-direction:column;gap:.35rem;">
                        <span class="meta-label">Notes</span>
                        <span style="font-size:.8rem;color:#495057;background:#f8f9fa;padding:.5rem .75rem;border-radius:8px;display:block;">{{ $transfer->notes }}</span>
                    </div>
                    @endif
                    @if($transfer->reject_reason)
                    <div class="meta-row" style="flex-direction:column;gap:.35rem;">
                        <span class="meta-label text-danger">Rejection Reason</span>
                        <span style="font-size:.8rem;color:#b91c1c;background:#fee2e2;padding:.5rem .75rem;border-radius:8px;display:block;">{{ $transfer->reject_reason }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            @if(in_array($transfer->status, ['DRAFT', 'PENDING']))
            <div class="detail-card">
                <div class="card-head"><h6>Actions</h6></div>
                <div class="card-body-p d-flex flex-column gap-2">

                    {{-- Approve --}}
                    <form method="POST" action="{{ route('stock.transfers.approve', $transfer) }}"
                          onsubmit="return confirm('Approve this transfer? Stock levels will be updated immediately.')">
                        @csrf
                        <button type="submit" class="btn btn-success w-100 btn-sm">
                            <i class="bi bi-check-circle me-1"></i>Approve Transfer
                        </button>
                    </form>

                    {{-- Reject toggle --}}
                    <button class="btn btn-outline-danger w-100 btn-sm" type="button"
                            data-bs-toggle="collapse" data-bs-target="#rejectForm">
                        <i class="bi bi-x-circle me-1"></i>Reject Transfer
                    </button>
                    <div class="collapse" id="rejectForm">
                        <form method="POST" action="{{ route('stock.transfers.reject', $transfer) }}" class="mt-1">
                            @csrf
                            <textarea name="reject_reason" class="form-control form-control-sm mb-2" rows="2"
                                      placeholder="Reason for rejection (optional)"
                                      style="border-radius:8px;font-size:.82rem;"></textarea>
                            <button type="submit" class="btn btn-danger btn-sm w-100">Confirm Rejection</button>
                        </form>
                    </div>

                    {{-- Cancel --}}
                    <form method="POST" action="{{ route('stock.transfers.cancel', $transfer) }}"
                          onsubmit="return confirm('Cancel this transfer?')">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary w-100 btn-sm">
                            <i class="bi bi-slash-circle me-1"></i>Cancel
                        </button>
                    </form>

                </div>
            </div>
            @endif

        </div>
    </div>

</div>
@endsection
