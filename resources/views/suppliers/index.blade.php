@extends('layouts.app')

@section('title', 'Suppliers')

@section('content')
<div class="page-wrap">

    <div class="page-header">
        <div>
            <h4><i class="bi bi-truck me-2 text-success"></i>Suppliers</h4>
            <div class="sub">Manage supplier master data</div>
        </div>
        <a href="{{ route('suppliers.create') }}" class="btn btn-success btn-sm"><i class="bi bi-plus-lg me-1"></i>New Supplier</a>
    </div>

    <form method="GET" class="filter-bar">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" style="width:280px;" placeholder="Search name, contact, phone, email…">
        <button type="submit" class="btn btn-success"><i class="bi bi-funnel-fill me-1"></i>Filter</button>
        @if(request()->hasAny(['search']))
            <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle me-1"></i>Clear</a>
        @endif
    </form>

    <form method="POST" action="{{ route('suppliers.export') }}">
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
                            <x-sortable-th field="name">Name</x-sortable-th>
                            <x-sortable-th field="contact_person">Contact Person</x-sortable-th>
                            <x-sortable-th field="phone">Phone</x-sortable-th>
                            <x-sortable-th field="email">Email</x-sortable-th>
                            <x-sortable-th field="status">Status</x-sortable-th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($suppliers as $supplier)
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="{{ $supplier->id }}" class="row-checkbox form-check-input"></td>
                                <td class="fw-semibold">{{ $supplier->name }}</td>
                                <td>{{ $supplier->contact_person ?? '—' }}</td>
                                <td>{{ $supplier->phone ?? '—' }}</td>
                                <td>{{ $supplier->email ?? '—' }}</td>
                                <td><span class="badge-status badge-{{ $supplier->status }}">{{ ucfirst($supplier->status) }}</span></td>
                                <td class="text-center">
                                    <a class="btn-action" href="{{ route('suppliers.show', $supplier) }}" title="View"><i class="bi bi-eye"></i> View</a>
                                    <a class="btn-action" href="{{ route('suppliers.edit', $supplier) }}" title="Edit"><i class="bi bi-pencil"></i> Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="bi bi-truck"></i>
                                        <p>No suppliers yet.<br>
                                        <a href="{{ route('suppliers.create') }}" class="text-success fw-semibold">Add the first supplier</a></p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($suppliers->hasPages())
            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top bg-light" style="font-size:.8rem;">
                <span class="text-muted">Showing {{ $suppliers->firstItem() }}–{{ $suppliers->lastItem() }} of {{ $suppliers->total() }} suppliers</span>
                {{ $suppliers->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </form>

</div>
@endsection
