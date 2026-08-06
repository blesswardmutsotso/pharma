@extends('layouts.app')

@section('title', 'Delivery Notes')

@section('content')
<div class="page-wrap">

    <div class="page-header">
        <div>
            <h4><i class="bi bi-truck me-2 text-success"></i>Delivery Notes</h4>
            <div class="sub">Generated automatically when a sales order is dispatched</div>
        </div>
    </div>

    <form method="GET" class="filter-bar">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" style="width:260px;" placeholder="Search delivery note, SO number, client…">
        <button type="submit" class="btn btn-success"><i class="bi bi-funnel-fill me-1"></i>Filter</button>
        @if(request()->hasAny(['search']))
            <a href="{{ route('delivery-notes.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle me-1"></i>Clear</a>
        @endif
    </form>

    <form method="POST" action="{{ route('delivery-notes.export') }}">
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
                            <x-sortable-th field="delivery_note_no">Delivery Note No.</x-sortable-th>
                            <th>Sales Order</th>
                            <th>Client</th>
                            <x-sortable-th field="delivery_date">Delivery Date</x-sortable-th>
                            <th>Delivered By</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($deliveryNotes as $note)
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="{{ $note->id }}" class="row-checkbox form-check-input"></td>
                                <td><span class="inv-no">{{ $note->delivery_note_no }}</span></td>
                                <td>{{ $note->salesOrder?->so_number }}</td>
                                <td>{{ $note->client?->name }}</td>
                                <td>{{ $note->delivery_date?->format('Y-m-d') }}</td>
                                <td>{{ $note->deliveredBy?->name ?? '—' }}</td>
                                <td class="text-center">
                                    <a class="btn-action" href="{{ route('delivery-notes.show', $note) }}" title="View"><i class="bi bi-eye"></i> View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="bi bi-truck"></i>
                                        <p>No delivery notes found{{ request()->hasAny(['search']) ? ' matching your filters' : '' }}.<br>
                                        Delivery notes are created automatically when a sales order is dispatched.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($deliveryNotes->hasPages())
            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top bg-light" style="font-size:.8rem;">
                <span class="text-muted">Showing {{ $deliveryNotes->firstItem() }}–{{ $deliveryNotes->lastItem() }} of {{ $deliveryNotes->total() }} delivery notes</span>
                {{ $deliveryNotes->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </form>

</div>
@endsection
