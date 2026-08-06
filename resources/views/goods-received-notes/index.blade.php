@extends('layouts.app')

@section('title', 'Goods Received Notes')

@section('content')
<div class="page-wrap">

    <div class="page-header">
        <div>
            <h4><i class="bi bi-clipboard-check me-2 text-success"></i>Goods Received Notes</h4>
            <div class="sub">Batch &amp; expiry-tracked receiving against purchase orders</div>
        </div>
        <a href="{{ route('goods-received-notes.create') }}" class="btn btn-success btn-sm"><i class="bi bi-plus-lg me-1"></i>New GRN</a>
    </div>

    <form method="GET" class="filter-bar">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" style="width:240px;" placeholder="Search GRN number, supplier…">
        <select name="status" class="form-select" style="width:160px;">
            <option value="">All Statuses</option>
            @foreach (['received', 'partial', 'returned'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-success"><i class="bi bi-funnel-fill me-1"></i>Filter</button>
        @if(request()->hasAny(['search', 'status']))
            <a href="{{ route('goods-received-notes.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle me-1"></i>Clear</a>
        @endif
    </form>

    <form method="POST" action="{{ route('goods-received-notes.export') }}">
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
                            <x-sortable-th field="grn_number">GRN Number</x-sortable-th>
                            <th>Supplier</th>
                            <th>Purchase Order</th>
                            <x-sortable-th field="received_date">Received Date</x-sortable-th>
                            <x-sortable-th field="status">Status</x-sortable-th>
                            <th>Currency</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($goodsReceivedNotes as $note)
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="{{ $note->id }}" class="row-checkbox form-check-input"></td>
                                <td><span class="inv-no">{{ $note->grn_number }}</span></td>
                                <td>{{ $note->supplier?->name }}</td>
                                <td>{{ $note->purchaseOrder?->po_number ?? '—' }}</td>
                                <td>{{ $note->received_date?->format('Y-m-d') }}</td>
                                <td><span class="badge-status badge-{{ $note->status }}">{{ ucfirst($note->status) }}</span></td>
                                <td>{{ $note->currency() }}</td>
                                <td class="text-end">{{ number_format($note->grandTotal(), 2) }}</td>
                                <td class="text-center">
                                    <a class="btn-action" href="{{ route('goods-received-notes.show', $note) }}" title="View"><i class="bi bi-eye"></i> View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <div class="empty-state">
                                        <i class="bi bi-clipboard-check"></i>
                                        <p>No goods received notes found{{ request()->hasAny(['search', 'status']) ? ' matching your filters' : '' }}.<br>
                                        <a href="{{ route('goods-received-notes.create') }}" class="text-success fw-semibold">Record the first GRN</a></p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($goodsReceivedNotes->hasPages())
            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top bg-light" style="font-size:.8rem;">
                <span class="text-muted">Showing {{ $goodsReceivedNotes->firstItem() }}–{{ $goodsReceivedNotes->lastItem() }} of {{ $goodsReceivedNotes->total() }} GRNs</span>
                {{ $goodsReceivedNotes->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </form>

</div>
@endsection
