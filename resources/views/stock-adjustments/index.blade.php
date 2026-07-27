@extends('layouts.app')

@section('title', 'Stock Adjustments')

@section('content')
<div class="page-wrap">

    <div class="page-header">
        <div>
            <h4><i class="bi bi-clipboard-data me-2 text-success"></i>Stock Adjustments</h4>
            <div class="sub">Stock-takes, damage, theft and breakage write-offs</div>
        </div>
        <a href="{{ route('stock-adjustments.create') }}" class="btn btn-success btn-sm">
            <i class="bi bi-plus-lg me-1"></i>New Adjustment
        </a>
    </div>

    <div class="filter-bar mb-3">
        <form method="GET" class="d-flex flex-wrap gap-2 align-items-center w-100">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" style="max-width:180px;" placeholder="Adjustment no…">
            <select name="status" class="form-select form-select-sm" style="max-width:160px;" onchange="this.form.submit()">
                <option value="">All statuses</option>
                @foreach (['draft', 'submitted', 'approved', 'rejected'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <select name="type" class="form-select form-select-sm" style="max-width:200px;" onchange="this.form.submit()">
                <option value="">All types</option>
                @foreach (\App\Models\StockAdjustment::types() as $key => $label)
                    <option value="{{ $key }}" @selected(request('type') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-funnel-fill me-1"></i>Filter</button>
        </form>
    </div>

    <form method="POST" action="{{ route('stock-adjustments.export') }}">
        @csrf
        <x-forward-filters />

        <div class="table-card">
            <div class="d-flex justify-content-end p-2 border-bottom">
                <button type="submit" class="btn btn-outline-success btn-sm"><i class="bi bi-download me-1"></i>Export CSV</button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width:30px;"><input type="checkbox" class="select-all-checkbox form-check-input"></th>
                            <x-sortable-th field="adjustment_no">Adjustment No.</x-sortable-th>
                            <x-sortable-th field="type">Type</x-sortable-th>
                            <th>Branch</th>
                            <th>Requested By</th>
                            <x-sortable-th field="status">Status</x-sortable-th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($adjustments as $adjustment)
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="{{ $adjustment->id }}" class="row-checkbox form-check-input"></td>
                                <td><span class="inv-no">{{ $adjustment->adjustment_no }}</span></td>
                                <td>{{ $adjustment->typeLabel() }}</td>
                                <td>{{ $adjustment->branch?->name ?? '—' }}</td>
                                <td>{{ $adjustment->requestedBy?->name }}</td>
                                <td><span class="badge-status {{ $adjustment->statusBadgeClass() }}">{{ ucfirst($adjustment->status) }}</span></td>
                                <td class="text-center">
                                    <a class="btn-action" href="{{ route('stock-adjustments.show', $adjustment) }}" title="View"><i class="bi bi-eye"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="bi bi-clipboard-data"></i>
                                        <p>No stock adjustments found{{ request()->hasAny(['search', 'status', 'type']) ? ' matching your filters' : '' }}.<br>
                                        <a href="{{ route('stock-adjustments.create') }}" class="text-success fw-semibold">Record the first stock-take or write-off</a></p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($adjustments->hasPages())
            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top bg-light" style="font-size:.8rem;">
                <span class="text-muted">Showing {{ $adjustments->firstItem() }}–{{ $adjustments->lastItem() }} of {{ $adjustments->total() }} adjustments</span>
                {{ $adjustments->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </form>

</div>
@endsection
