@extends('layouts.app')

@section('title', 'Review Import')

@push('styles')
<style>
    .transfers-wrapper { padding: 1.5rem 2rem; }
    .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: .75rem; }
    .page-header h4 { font-weight: 700; color: var(--pos-green-dk); margin: 0; font-size: 1.35rem; letter-spacing: -.01em; }
    .table-card { background: #fff; border: 1px solid #e9ecef; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.05); margin-bottom: 1.25rem; }
    .table-card .table { margin: 0; font-size: .83rem; }
    .table-card .table thead th { background: #f8f9fa; border-bottom: 2px solid #e9ecef; color: #495057; font-weight: 700; font-size: .72rem; text-transform: uppercase; letter-spacing: .06em; padding: .75rem 1rem; white-space: nowrap; }
    .table-card .table tbody td { padding: .6rem 1rem; vertical-align: middle; border-color: #f1f3f5; }
    .table-card .table tbody tr:hover { background: #f8fffe; }
    .inv-no { font-family: 'Courier New', monospace; font-size: .78rem; color: #1d4ed8; font-weight: 600; }
</style>
@endpush

@section('content')
<div class="transfers-wrapper">

    <div class="page-header">
        <div>
            <a href="{{ route('stock.transfers.create') }}" class="text-muted text-decoration-none" style="font-size:.82rem;">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
            <h4 class="mt-1"><i class="bi bi-eye me-2 text-success"></i>Review Import</h4>
        </div>
    </div>

    <div class="alert alert-info py-2 px-3 mb-3" style="font-size:.83rem;border-radius:10px;">
        <i class="bi bi-info-circle-fill me-2"></i>
        <strong>{{ count($preview['items']) }}</strong> products parsed.
        @if($preview['skipped'] > 0)
            <span class="text-warning fw-semibold ms-2">{{ $preview['skipped'] }} rows skipped</span> (missing code or zero qty).
        @endif
        Review the items below then confirm to create the transfer.
    </div>

    <div class="table-card">
        <div style="padding:.85rem 1.25rem;border-bottom:1px solid #f1f3f5;background:#fafafa;">
            <h6 class="mb-0 fw-bold" style="font-size:.88rem;">Parsed Items</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Description</th>
                        <th class="text-center">Qty</th>
                        <th>Notes</th>
                        <th class="text-center">In System</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($preview['items'] as $item)
                    <tr>
                        <td><span class="inv-no">{{ $item['product_code'] }}</span></td>
                        <td style="font-size:.81rem;">{{ $item['product_description'] }}</td>
                        <td class="text-center fw-bold">{{ number_format($item['qty_requested']) }}</td>
                        <td style="font-size:.78rem;color:#6c757d;">{{ $item['notes'] ?: '—' }}</td>
                        <td class="text-center">
                            @if($item['stock_exists'])
                                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle" style="font-size:.7rem;">
                                    <i class="bi bi-check-circle me-1"></i>Found
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size:.7rem;">
                                    <i class="bi bi-plus-circle me-1"></i>New
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('stock.transfers.create') }}" class="btn btn-outline-secondary">
            <i class="bi bi-x-circle me-1"></i>Cancel
        </a>
        <form method="POST" action="{{ route('stock.transfers.import.confirm') }}">
            @csrf
            <button type="submit" class="btn btn-success">
                <i class="bi bi-check-circle me-1"></i>Confirm &amp; Create Transfer
            </button>
        </form>
    </div>

</div>
@endsection
