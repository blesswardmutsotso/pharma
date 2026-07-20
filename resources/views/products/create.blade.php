@extends('layouts.app')

@section('title', 'New Product')

@section('content')
<div class="page-wrap">
    <div class="page-header">
        <div>
            <h4><i class="bi bi-capsule me-2 text-success"></i>New Product</h4>
            <div class="sub">Add a product to the catalogue</div>
        </div>
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Products</a>
    </div>

    <form action="{{ route('products.store') }}" method="POST" class="form-card">
        @csrf
        @include('products._form', ['product' => null])

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Save Product</button>
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
