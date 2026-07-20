@extends('layouts.app')

@section('title', 'Products')

@section('content')
<div class="page-wrap">

    <div class="page-header">
        <div>
            <h4><i class="bi bi-capsule me-2 text-success"></i>Product Catalogue</h4>
            <div class="sub">Manage SKUs, pricing, and reorder rules</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('products.template') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-download me-1"></i>Import Template
            </a>
            <a href="{{ route('products.create') }}" class="btn btn-success btn-sm">
                <i class="bi bi-plus-lg me-1"></i>New Product
            </a>
        </div>
    </div>

    <div class="stat-cards">
        <div class="stat-card">
            <div class="icon green"><i class="bi bi-capsule"></i></div>
            <div><div class="label">Total Products</div><div class="value">{{ number_format($stats['total']) }}</div></div>
        </div>
        <div class="stat-card">
            <div class="icon yellow"><i class="bi bi-exclamation-triangle"></i></div>
            <div><div class="label">Low Stock</div><div class="value">{{ number_format($stats['low_stock']) }}</div></div>
        </div>
        <div class="stat-card">
            <div class="icon red"><i class="bi bi-x-circle"></i></div>
            <div><div class="label">Out of Stock</div><div class="value">{{ number_format($stats['out_of_stock']) }}</div></div>
        </div>
        <div class="stat-card">
            <div class="icon blue"><i class="bi bi-tags"></i></div>
            <div><div class="label">Categories</div><div class="value">{{ number_format($stats['categories']) }}</div></div>
        </div>
    </div>

    <form method="GET" class="filter-bar">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" style="width:220px;" placeholder="Search code, name, generic name…">
        <select name="category" class="form-select" style="width:170px;">
            <option value="">All Categories</option>
            @foreach ($categories as $category)
                <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
            @endforeach
        </select>
        <label class="d-flex align-items-center gap-2" style="font-size:.82rem;">
            <input type="checkbox" name="low_stock" value="1" class="form-check-input" @checked(request('low_stock'))>
            Low stock only
        </label>
        <button type="submit" class="btn btn-success"><i class="bi bi-funnel-fill me-1"></i>Filter</button>
        @if(request()->hasAny(['search','category','low_stock']))
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle me-1"></i>Clear</a>
        @endif
    </form>

    <form action="{{ route('products.bulk-import') }}" method="POST" enctype="multipart/form-data" class="filter-bar">
        @csrf
        <span class="text-muted" style="font-size:.82rem;"><i class="bi bi-upload me-1"></i>Bulk import products:</span>
        <input type="file" name="file" accept=".csv,.xls,.xlsx" class="form-control" style="width:260px;" required>
        <button type="submit" class="btn btn-outline-success"><i class="bi bi-cloud-upload me-1"></i>Import</button>
    </form>
    @if (session('import_errors') && count(session('import_errors')))
        <div class="alert alert-warning py-2 px-3 mb-3" style="font-size:.82rem;border-radius:10px;">
            <strong>Some rows were skipped:</strong>
            <ul class="mb-0 mt-1 ps-3">
                @foreach (session('import_errors') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th class="text-center">Qty on Hand</th>
                        <th class="text-center">Reorder Point</th>
                        <th class="text-end">Selling Price</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td><span class="inv-no">{{ $product->product_code }}</span></td>
                            <td>{{ $product->product_description }}</td>
                            <td>{{ $product->category ?? '—' }}</td>
                            <td class="text-center">
                                <span class="badge-status {{ $product->quantity == 0 ? 'badge-rejected' : ($product->isLowStock() ? 'badge-pending' : 'badge-approved') }}">
                                    {{ $product->quantity }}
                                </span>
                            </td>
                            <td class="text-center">{{ $product->reorder_point }}</td>
                            <td class="text-end">${{ number_format($product->selling_price, 2) }}</td>
                            <td class="text-center">
                                <a class="btn-action" href="{{ route('products.show', $product) }}" title="View"><i class="bi bi-eye"></i></a>
                                <a class="btn-action" href="{{ route('products.edit', $product) }}" title="Edit"><i class="bi bi-pencil"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="bi bi-capsule"></i>
                                    <p>No products found{{ request()->hasAny(['search','category','low_stock']) ? ' matching your filters' : '' }}.<br>
                                    <a href="{{ route('products.create') }}" class="text-success fw-semibold">Add the first product</a></p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top bg-light" style="font-size:.8rem;">
            <span class="text-muted">Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ $products->total() }} products</span>
            {{ $products->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>

</div>
@endsection
