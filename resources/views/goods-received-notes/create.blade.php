@extends('layouts.app')

@section('title', 'New Goods Received Note')

@section('content')
<div class="page-wrap">
    <div class="page-header">
        <div>
            <h4><i class="bi bi-clipboard-check me-2 text-success"></i>New Goods Received Note</h4>
            <div class="sub">Capture batch number and expiry date for every line received</div>
        </div>
        <a href="{{ route('goods-received-notes.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to GRNs</a>
    </div>

    <form action="{{ route('goods-received-notes.store') }}" method="POST" class="form-card">
        @csrf

        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">GRN Number</label>
                <input type="text" name="grn_number" class="form-control" value="{{ old('grn_number') }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Supplier</label>
                <select name="supplier_id" class="form-select" required>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Purchase Order</label>
                <select name="purchase_order_id" class="form-select">
                    <option value="">None</option>
                    @foreach ($purchaseOrders as $purchaseOrder)
                        <option value="{{ $purchaseOrder->id }}">{{ $purchaseOrder->po_number }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Received Date</label>
                <input type="date" name="received_date" class="form-control" value="{{ now()->toDateString() }}" required>
            </div>
            <div class="col-md-1">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="received">Received</option>
                    <option value="partial">Partial</option>
                    <option value="returned">Returned</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Receiving Branch / Warehouse</label>
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
                        <th style="width:12%">Product Code</th>
                        <th style="width:18%">Description</th>
                        <th style="width:9%">Qty</th>
                        <th style="width:10%">Unit Cost</th>
                        <th style="width:13%">Batch Number</th>
                        <th style="width:13%">Expiry Date</th>
                        <th style="width:11%">Condition</th>
                        <th style="width:40px"></th>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                    <tr>
                        <td><input type="text" name="items[0][product_code]" class="form-control" required></td>
                        <td><input type="text" name="items[0][product_description]" class="form-control" required></td>
                        <td><input type="number" name="items[0][qty_received]" class="form-control" min="1" required></td>
                        <td><input type="number" step="0.01" name="items[0][unit_cost]" class="form-control" min="0" required></td>
                        <td><input type="text" name="items[0][batch_number]" class="form-control" required></td>
                        <td><input type="date" name="items[0][expiry_date]" class="form-control" required></td>
                        <td>
                            <select name="items[0][status]" class="form-select" required>
                                <option value="accepted">Accepted</option>
                                <option value="quarantine">Quarantine</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </td>
                        <td><button type="button" class="btn-action remove-row" title="Remove"><i class="bi bi-trash"></i></button></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <button type="button" id="addItemBtn" class="btn btn-outline-success btn-sm mb-3"><i class="bi bi-plus-lg me-1"></i>Add Item</button>
        <div class="form-text mb-3">Quarantined or rejected lines are captured for audit but do not add to sellable stock.</div>

        <div class="form-section-title">Notes</div>
        <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Save GRN</button>
            <a href="{{ route('goods-received-notes.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
        row.querySelectorAll('input, select').forEach(field => {
            if (field.tagName === 'SELECT') field.selectedIndex = 0; else field.value = '';
            field.name = field.name.replace(/items\[\d+\]/, `items[${itemIndex}]`);
        });
        tbody.appendChild(row);
        itemIndex++;
        wireRemoveButtons();
    });

    wireRemoveButtons();
})();
</script>
@endpush
