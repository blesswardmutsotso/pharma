@extends('layouts.app')

@section('title', 'New Purchase Order')

@section('content')
<div class="page-wrap">
    <div class="page-header">
        <div>
            <h4><i class="bi bi-file-earmark-text me-2 text-success"></i>New Purchase Order</h4>
            <div class="sub">New purchase orders always start as Draft</div>
        </div>
        <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Purchase Orders</a>
    </div>

    <form action="{{ route('purchase-orders.store') }}" method="POST" class="form-card">
        @csrf
        <input type="hidden" name="status" value="draft">

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">PO Number</label>
                <input type="text" name="po_number" class="form-control" value="{{ old('po_number') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Supplier</label>
                <select name="supplier_id" class="form-select" required>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Currency</label>
                <select name="currency" class="form-select" required>
                    <option value="USD">USD</option>
                    <option value="ZWG">ZWG (ZiG)</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Order Date</label>
                <input type="date" name="order_date" class="form-control" value="{{ now()->toDateString() }}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Expected Delivery</label>
                <input type="date" name="expected_delivery_date" class="form-control">
            </div>
        </div>

        <div class="form-section-title">Line Items</div>
        <div class="form-text mb-2">Search to pick an existing product (auto-fills description &amp; last buying price), or just type a new one if you're ordering something not in the catalogue yet.</div>
        <div class="table-responsive">
            <table class="table" id="itemsTable">
                <thead>
                    <tr>
                        <th style="width:18%">Product Code</th>
                        <th>Description</th>
                        <th style="width:12%">Qty</th>
                        <th style="width:14%">Unit Cost</th>
                        <th style="width:40px"></th>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                    <tr>
                        <td>
                            <div class="product-search-wrap" style="position:relative;">
                                <input type="text" name="items[0][product_code]" class="form-control product-search-input" autocomplete="off" placeholder="Search or type new code…" required>
                                <div class="product-search-results" style="display:none;position:absolute;top:100%;left:0;right:0;z-index:20;background:#fff;border:1px solid #dee2e6;border-radius:6px;max-height:220px;overflow-y:auto;box-shadow:0 4px 12px rgba(0,0,0,.1);"></div>
                            </div>
                        </td>
                        <td><input type="text" name="items[0][product_description]" class="form-control" required></td>
                        <td><input type="number" name="items[0][qty_ordered]" class="form-control" min="1" required></td>
                        <td><input type="number" step="0.01" name="items[0][unit_cost]" class="form-control" min="0" required></td>
                        <td><button type="button" class="btn-action remove-row" title="Remove"><i class="bi bi-trash"></i></button></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <button type="button" id="addItemBtn" class="btn btn-outline-success btn-sm mb-3"><i class="bi bi-plus-lg me-1"></i>Add Item</button>

        <div class="form-section-title">Notes</div>
        <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Save Purchase Order</button>
            <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
        row.querySelector('.product-search-results').style.display = 'none';
        row.querySelector('.product-search-results').innerHTML = '';
        tbody.appendChild(row);
        itemIndex++;
        wireRemoveButtons();
    });

    wireRemoveButtons();

    // ── Product search-as-you-type — optional assist only. Unlike GRN/Stock
    // Adjustments, a PO can legitimately order a product that isn't in the
    // catalogue yet, so the field stays free-text and nothing is enforced.
    let searchTimer = null;

    tbody.addEventListener('input', (e) => {
        if (!e.target.classList.contains('product-search-input')) return;

        const input = e.target;
        const resultsBox = input.closest('.product-search-wrap').querySelector('.product-search-results');
        const query = input.value.trim();

        clearTimeout(searchTimer);

        if (query.length < 2) {
            resultsBox.style.display = 'none';
            resultsBox.innerHTML = '';
            return;
        }

        searchTimer = setTimeout(() => {
            fetch(`{{ route('products.search') }}?q=${encodeURIComponent(query)}`)
                .then(r => r.json())
                .then(products => {
                    if (!products.length) {
                        resultsBox.innerHTML = '<div class="p-2 text-muted" style="font-size:.82rem;">No matching products — ordering something new is fine, just type its details</div>';
                        resultsBox.style.display = 'block';
                        return;
                    }

                    resultsBox.innerHTML = products.map(p => `
                        <div class="product-search-item" style="padding:.5rem .75rem;cursor:pointer;font-size:.82rem;border-bottom:1px solid #f1f3f5;"
                             data-code="${p.product_code}" data-desc="${p.product_description}" data-cost="${p.buying_price}">
                            <div class="fw-semibold">${p.product_code} — ${p.product_description}</div>
                            <div class="text-muted">Last buying price: ${Number(p.buying_price).toFixed(2)} &nbsp;·&nbsp; Qty on hand: ${p.quantity}</div>
                        </div>
                    `).join('');
                    resultsBox.style.display = 'block';
                });
        }, 250);
    });

    tbody.addEventListener('click', (e) => {
        const item = e.target.closest('.product-search-item');
        if (!item) return;

        const row = item.closest('tr');
        row.querySelector('.product-search-input').value = item.dataset.code;
        row.querySelector('[name$="[product_description]"]').value = item.dataset.desc;
        row.querySelector('[name$="[unit_cost]"]').value = item.dataset.cost;

        const resultsBox = item.closest('.product-search-results');
        resultsBox.style.display = 'none';
        resultsBox.innerHTML = '';
    });

    document.addEventListener('click', (e) => {
        if (e.target.closest('.product-search-wrap')) return;
        tbody.querySelectorAll('.product-search-results').forEach(box => box.style.display = 'none');
    });
})();
</script>
@endpush
