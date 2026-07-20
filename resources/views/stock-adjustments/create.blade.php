@extends('layouts.app')

@section('title', 'New Stock Adjustment')

@section('content')
<div class="page-wrap">
    <div class="page-header">
        <div>
            <h4><i class="bi bi-clipboard-data me-2 text-success"></i>New Stock Adjustment</h4>
            <div class="sub">Record a physical count variance, damage, theft or breakage write-off</div>
        </div>
        <a href="{{ route('stock-adjustments.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Adjustments</a>
    </div>

    <form action="{{ route('stock-adjustments.store') }}" method="POST" class="form-card">
        @csrf

        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Type</label>
                <select name="type" class="form-select" required>
                    @foreach ($types as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Branch / Warehouse</label>
                <select name="branch_id" class="form-select">
                    <option value="">Not location-specific</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected($home && $branch->id === $home->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Reason</label>
                <input type="text" name="reason" class="form-control" placeholder="e.g. Q3 cycle count, warehouse water damage" value="{{ old('reason') }}">
            </div>
        </div>

        <div class="form-section-title">Lines</div>
        <div class="alert alert-light border small mb-3">
            Leave <strong>Batch Number</strong> blank to adjust the product's overall quantity instead of a specific batch.
            The system quantity is captured automatically when you submit — enter what you physically counted.
        </div>
        <div class="table-responsive">
            <table class="table table-sm" id="itemsTable">
                <thead>
                    <tr>
                        <th>Product Code</th>
                        <th>Product Description</th>
                        <th>Batch Number (optional)</th>
                        <th>Qty Counted</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                    <tr>
                        <td><input type="text" name="items[0][product_code]" class="form-control" required></td>
                        <td><input type="text" name="items[0][product_description]" class="form-control" required></td>
                        <td><input type="text" name="items[0][batch_number]" class="form-control"></td>
                        <td><input type="number" name="items[0][qty_counted]" class="form-control" min="0" required></td>
                        <td><button type="button" class="btn-action remove-row" title="Remove"><i class="bi bi-trash"></i></button></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <button type="button" id="addItemBtn" class="btn btn-outline-success btn-sm mb-3"><i class="bi bi-plus-lg me-1"></i>Add Line</button>

        <div class="form-section-title">Notes</div>
        <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Submit for Approval</button>
            <a href="{{ route('stock-adjustments.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
