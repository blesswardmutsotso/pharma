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

    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>GRN Number</th>
                        <th>Supplier</th>
                        <th>Purchase Order</th>
                        <th>Received Date</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($goodsReceivedNotes as $note)
                        <tr>
                            <td><span class="inv-no">{{ $note->grn_number }}</span></td>
                            <td>{{ $note->supplier?->name }}</td>
                            <td>{{ $note->purchaseOrder?->po_number ?? '—' }}</td>
                            <td>{{ $note->received_date?->format('Y-m-d') }}</td>
                            <td><span class="badge-status badge-{{ $note->status }}">{{ ucfirst($note->status) }}</span></td>
                            <td class="text-center">
                                <a class="btn-action" href="{{ route('goods-received-notes.show', $note) }}" title="View"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="bi bi-clipboard-check"></i>
                                    <p>No goods received notes yet.<br>
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
            {{ $goodsReceivedNotes->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>

</div>
@endsection
