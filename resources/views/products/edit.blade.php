@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="page-wrap">
    <div class="page-header">
        <div>
            <h4><i class="bi bi-pencil-square me-2 text-success"></i>Edit Product</h4>
            <div class="sub">{{ $product->product_code }} — {{ $product->product_description }}</div>
        </div>
        <a href="{{ route('products.show', $product) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Product</a>
    </div>

    <form action="{{ route('products.update', $product) }}" method="POST" class="form-card">
        @csrf
        @method('PUT')
        @include('products._form', ['product' => $product])

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
            <a href="{{ route('products.show', $product) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
