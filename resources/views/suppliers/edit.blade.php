@extends('layouts.app')

@section('title', 'Edit Supplier')

@section('content')
<div class="page-wrap">
    <div class="page-header">
        <div>
            <h4><i class="bi bi-pencil-square me-2 text-success"></i>Edit Supplier</h4>
            <div class="sub">{{ $supplier->name }}</div>
        </div>
        <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Supplier</a>
    </div>

    <form action="{{ route('suppliers.update', $supplier) }}" method="POST" class="form-card">
        @csrf
        @method('PUT')
        @include('suppliers._form', ['supplier' => $supplier])

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
            <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
