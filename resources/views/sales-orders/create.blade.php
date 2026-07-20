@extends('layouts.app')

@section('title', 'New Sales Order')

@section('content')
<div class="page-wrap">
    <div class="page-header">
        <div>
            <h4><i class="bi bi-cart-plus me-2 text-success"></i>New Sales Order</h4>
            <div class="sub">Stock is allocated (FEFO) once the order is confirmed</div>
        </div>
        <a href="{{ route('sales-orders.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Sales Orders</a>
    </div>

    <form action="{{ route('sales-orders.store') }}" method="POST" class="form-card">
        @csrf

        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">SO Number</label>
                <input type="text" name="so_number" class="form-control" value="{{ old('so_number') }}" required>
            </div>
            <div class="col-md-5">
                <label class="form-label d-flex justify-content-between">
                    <span>Client</span>
                    <a href="{{ route('clients.create') }}" target="_blank" class="text-success" style="font-size:.75rem;"><i class="bi bi-plus-circle me-1"></i>New Client</a>
                </label>
                <select name="client_id" class="form-select" required>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Order Date</label>
                <input type="date" name="order_date" class="form-control" value="{{ now()->toDateString() }}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Required Date</label>
                <input type="date" name="required_date" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Fulfilling Branch / Warehouse</label>
                <select name="branch_id" class="form-select">
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected($home && $branch->id === $home->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-section-title">Line Items</div>
        <div class="table-responsive">
            <table class="table" id="itemsTable">
                <thead>
                    <tr>
                        <th style="width:16%">Product Code</th>
                        <th>Description</th>
                        <th style="width:14%">Qty</th>
                        <th style="width:16%">Unit Price</th>
                        <th style="width:40px"></th>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                    <tr>
                        <td><input type="text" name="items[0][product_code]" class="form-control" required></td>
                        <td><input type="text" name="items[0][product_description]" class="form-control" required></td>
                        <td><input type="number" name="items[0][qty_ordered]" class="form-control" min="1" required></td>
                        <td><input type="number" step="0.01" name="items[0][unit_price]" class="form-control" min="0" required></td>
                        <td><button type="button" class="btn-action remove-row" title="Remove"><i class="bi bi-trash"></i></button></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <button type="button" id="addItemBtn" class="btn btn-outline-success btn-sm mb-3"><i class="bi bi-plus-lg me-1"></i>Add Item</button>

        <div class="form-section-title">Notes</div>
        <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Save Sales Order</button>
            <a href="{{ route('sales-orders.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    let itemIndex = 1;
    const tbody = document.getElementById('itemsBody');

    function wireRemoveButtons() {
        tbody.querySelectorAll('.remove-row').forEach(btn => {
            btn.onclick = () => {
                if (tbody.querySelectorAll('tr').length > 1) btn.closest('tr').remove();
            };
        });
    }

    document.getElementById('addItemBtn').addEventListener('click', () => {
        const row = tbody.querySelector('tr').cloneNode(true);
        row.querySelectorAll('input').forEach(input => {
            input.value = '';
            input.name = input.name.replace(/items\[\d+\]/, `items[${itemIndex}]`);
        });
        tbody.appendChild(row);
        itemIndex++;
        wireRemoveButtons();
    });

    wireRemoveButtons();
})();
</script>
@endpush
