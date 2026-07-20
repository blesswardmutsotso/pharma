@extends('layouts.app')

@section('title', 'Adjustment ' . $adjustment->adjustment_no)

@section('content')
<div class="page-wrap">

    <div class="page-header">
        <div>
            <h4><i class="bi bi-clipboard-data me-2 text-success"></i>Adjustment {{ $adjustment->adjustment_no }}</h4>
            <div class="sub"><span class="badge-status {{ $adjustment->statusBadgeClass() }}">{{ ucfirst($adjustment->status) }}</span> · {{ $adjustment->typeLabel() }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('stock-adjustments.pdf', $adjustment) }}" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="bi bi-file-earmark-pdf me-1"></i>Print PDF</a>
            <a href="{{ route('stock-adjustments.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    <div class="detail-card">
        <div class="detail-grid">
            <div><div class="label">Branch / Warehouse</div><div class="value">{{ $adjustment->branch?->name ?? 'Not location-specific' }}</div></div>
            <div><div class="label">Reason</div><div class="value">{{ $adjustment->reason ?? '—' }}</div></div>
            <div><div class="label">Requested By</div><div class="value">{{ $adjustment->requestedBy?->name ?? '—' }}</div></div>
            <div><div class="label">Approved By</div><div class="value">{{ $adjustment->approvedBy?->name ?? '—' }}</div></div>
            <div><div class="label">Approved At</div><div class="value">{{ $adjustment->approved_at?->format('Y-m-d H:i') ?? '—' }}</div></div>
        </div>
    </div>

    <div class="d-flex gap-2 mb-4">
        @if ($adjustment->canBeApproved())
            <form action="{{ route('stock-adjustments.approve', $adjustment) }}" method="POST"
                  data-confirm="Approve {{ $adjustment->adjustment_no }}? Stock quantities will be updated to match the counted figures." data-confirm-icon="question">
                @csrf
                <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-check-circle me-1"></i>Approve &amp; Apply</button>
            </form>
            <form action="{{ route('stock-adjustments.reject', $adjustment) }}" method="POST"
                  data-confirm="Reject {{ $adjustment->adjustment_no }}? No stock changes will be made." data-confirm-danger="true">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-x-circle me-1"></i>Reject</button>
            </form>
        @endif
    </div>

    <div class="table-card mb-4">
        <div class="p-3 border-bottom fw-semibold" style="font-size:.85rem;">
            <i class="bi bi-list-ul me-1 text-success"></i>Lines
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Batch</th>
                        <th class="text-center">System Qty</th>
                        <th class="text-center">Counted Qty</th>
                        <th class="text-center">Variance</th>
                        <th class="text-end">Unit Cost</th>
                        <th class="text-end">Value Impact</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($adjustment->items as $item)
                        <tr class="{{ $item->qty_variance !== 0 ? ($item->qty_variance > 0 ? 'table-success' : 'table-warning') : '' }}">
                            <td>{{ $item->product_code }} — {{ $item->product_description }}</td>
                            <td>{{ $item->batch_number ?? '—' }}</td>
                            <td class="text-center">{{ $item->qty_system }}</td>
                            <td class="text-center">{{ $item->qty_counted }}</td>
                            <td class="text-center">{{ $item->qty_variance > 0 ? '+' : '' }}{{ $item->qty_variance }}</td>
                            <td class="text-end">{{ number_format($item->unit_cost, 2) }}</td>
                            <td class="text-end">{{ number_format($item->qty_variance * $item->unit_cost, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if ($adjustment->notes)
        <div class="detail-card">
            <div class="card-title">Notes</div>
            <p class="mb-0">{{ $adjustment->notes }}</p>
        </div>
    @endif

</div>
@endsection
