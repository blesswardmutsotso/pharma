@extends('layouts.app')

@section('title', 'Clients')

@section('content')
<div class="page-wrap">

    <div class="page-header">
        <div>
            <h4><i class="bi bi-people me-2 text-success"></i>Clients</h4>
            <div class="sub">Customer master data used across quotations, sales orders, and invoicing</div>
        </div>
        <a href="{{ route('clients.create') }}" class="btn btn-success btn-sm"><i class="bi bi-plus-lg me-1"></i>New Client</a>
    </div>

    <div class="stat-cards">
        <div class="stat-card">
            <div class="icon green"><i class="bi bi-people"></i></div>
            <div><div class="label">Total Clients</div><div class="value">{{ number_format($stats['total']) }}</div></div>
        </div>
    </div>

    <form method="GET" class="filter-bar">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" style="width:280px;" placeholder="Search name, contact, phone, email, VAT, TIN…">
        <button type="submit" class="btn btn-success"><i class="bi bi-funnel-fill me-1"></i>Filter</button>
        @if(request()->hasAny(['search']))
            <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle me-1"></i>Clear</a>
        @endif
    </form>

    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Contact Person</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>VAT / TIN</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clients as $client)
                        <tr>
                            <td class="fw-semibold">{{ $client->name }}</td>
                            <td>{{ $client->contact_person ?? '—' }}</td>
                            <td>{{ $client->phone ?? '—' }}</td>
                            <td>{{ $client->email ?? '—' }}</td>
                            <td>{{ $client->vat_number ?? '—' }} / {{ $client->tin ?? '—' }}</td>
                            <td class="text-center">
                                <a class="btn-action" href="{{ route('clients.show', $client) }}" title="View"><i class="bi bi-eye"></i></a>
                                <a class="btn-action" href="{{ route('clients.edit', $client) }}" title="Edit"><i class="bi bi-pencil"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="bi bi-people"></i>
                                    <p>No clients found{{ request()->hasAny(['search']) ? ' matching your search' : '' }}.<br>
                                    <a href="{{ route('clients.create') }}" class="text-success fw-semibold">Add the first client</a></p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($clients->hasPages())
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top bg-light" style="font-size:.8rem;">
            <span class="text-muted">Showing {{ $clients->firstItem() }}–{{ $clients->lastItem() }} of {{ $clients->total() }} clients</span>
            {{ $clients->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>

</div>
@endsection
