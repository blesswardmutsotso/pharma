@extends('layouts.app')

@section('title', $product->product_description)

@section('content')
<div class="page-wrap">

    <div class="page-header">
        <div>
            <h4><i class="bi bi-capsule me-2 text-success"></i>{{ $product->product_description }}</h4>
            <div class="sub"><span class="inv-no">{{ $product->product_code }}</span></div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
            @if(auth()->user()->isAdmin())
            <form action="{{ route('products.destroy', $product) }}" method="POST"
                  data-confirm="Delete {{ $product->product_code }}? This cannot be undone." data-confirm-danger="true">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i>Delete</button>
            </form>
            @endif
        </div>
    </div>

    <div class="detail-card">
        <div class="detail-grid">
            <div><div class="label">Category</div><div class="value">{{ $product->category ?? '—' }}</div></div>
            <div><div class="label">Generic Name</div><div class="value">{{ $product->generic_name ?? '—' }}</div></div>
            <div><div class="label">Dosage Form / Strength</div><div class="value">{{ $product->dosage_form }} {{ $product->strength }}</div></div>
            <div><div class="label">Pack Size / UoM</div><div class="value">{{ $product->pack_size }} / {{ $product->unit_of_measure }}</div></div>
            <div><div class="label">Storage</div><div class="value">{{ $product->storage_condition ?? '—' }}</div></div>
            <div><div class="label">Manufacturer</div><div class="value">{{ $product->manufacturer ?? '—' }}</div></div>
            <div><div class="label">Registration Number</div><div class="value">{{ $product->registration_number ?? '—' }}</div></div>
            <div><div class="label">Controlled Substance</div><div class="value">{{ $product->controlled_substance_schedule ?? 'Not controlled' }}</div></div>
            <div><div class="label">Qty on Hand</div><div class="value {{ $product->isLowStock() ? 'text-danger' : '' }}">{{ $product->quantity }}</div></div>
            <div><div class="label">Reorder Point / Qty</div><div class="value">{{ $product->reorder_point }} / {{ $product->reorder_qty }}</div></div>
            <div><div class="label">Selling Price</div><div class="value">${{ number_format($product->selling_price, 2) }}</div></div>
        </div>
    </div>

    <div class="table-card">
        <div class="p-3 border-bottom fw-semibold" style="font-size:.85rem;">
            <i class="bi bi-box-seam me-1 text-success"></i>Batches
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Batch Number</th>
                        <th>Location</th>
                        <th>Expiry Date</th>
                        <th class="text-center">Qty on Hand</th>
                        <th class="text-center">Qty Reserved</th>
                        <th class="text-center">Available</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($product->batches as $batch)
                        <tr>
                            <td><span class="inv-no">{{ $batch->batch_number }}</span></td>
                            <td>{{ $batch->branch?->name ?? '—' }}</td>
                            <td>{{ $batch->expiry_date->format('Y-m-d') }}</td>
                            <td class="text-center">{{ $batch->qty_on_hand }}</td>
                            <td class="text-center">{{ $batch->qty_reserved }}</td>
                            <td class="text-center">{{ $batch->availableQty() }}</td>
                            <td><span class="badge-status badge-{{ $batch->status }}">{{ ucfirst($batch->status) }}</span></td>
                            <td class="text-center">
                                @if ($batch->status === 'quarantine')
                                    <form action="{{ route('stock-batches.release', $batch) }}" method="POST"
                                          data-confirm="Release batch {{ $batch->batch_number }} from quarantine into sellable stock?" data-confirm-icon="question">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success btn-sm"><i class="bi bi-unlock me-1"></i>Release</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="bi bi-box-seam"></i>
                                    <p>No batches received yet.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
