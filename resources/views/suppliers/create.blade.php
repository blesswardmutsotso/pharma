@extends('layouts.app')

@section('title', 'New Supplier')

@section('content')
<div class="page-wrap">
    <div class="page-header">
        <div>
            <h4><i class="bi bi-truck me-2 text-success"></i>New Supplier</h4>
            <div class="sub">Add a supplier to the master list</div>
        </div>
        <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Suppliers</a>
    </div>

    <form action="{{ route('suppliers.store') }}" method="POST" class="form-card">
        @csrf
        @include('suppliers._form', ['supplier' => null])

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Save Supplier</button>
            <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
