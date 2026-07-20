@extends('layouts.app')

@section('title', 'Quotations')

@section('content')
<div class="page-wrap">

    <div class="page-header">
        <div>
            <h4><i class="bi bi-file-earmark-ruled me-2 text-success"></i>Quotations</h4>
            <div class="sub">Draft quotes and convert accepted ones to sales orders</div>
        </div>
        <a href="{{ route('quotations.create') }}" class="btn btn-success btn-sm"><i class="bi bi-plus-lg me-1"></i>New Quotation</a>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Quote Number</th>
                        <th>Client</th>
                        <th>Quote Date</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($quotations as $quotation)
                        <tr>
                            <td><span class="inv-no">{{ $quotation->quote_number }}</span></td>
                            <td>{{ $quotation->client?->name }}</td>
                            <td>{{ $quotation->quote_date?->format('Y-m-d') }}</td>
                            <td><span class="badge-status badge-{{ $quotation->status }}">{{ ucfirst($quotation->status) }}</span></td>
                            <td class="text-center">
                                <a class="btn-action" href="{{ route('quotations.show', $quotation) }}" title="View"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="bi bi-file-earmark-ruled"></i>
                                    <p>No quotations yet.<br>
                                    <a href="{{ route('quotations.create') }}" class="text-success fw-semibold">Create the first quotation</a></p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($quotations->hasPages())
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top bg-light" style="font-size:.8rem;">
            <span class="text-muted">Showing {{ $quotations->firstItem() }}–{{ $quotations->lastItem() }} of {{ $quotations->total() }} quotations</span>
            {{ $quotations->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>

</div>
@endsection
