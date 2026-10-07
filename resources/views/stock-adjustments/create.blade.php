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
        <div class="form-text mb-2">Products must be picked from the catalogue below — typing a code that doesn't exist will be rejected on submit instead of silently doing nothing.</div>
        <div class="alert alert-light border small mb-3">
            Leave <strong>Batch Number</strong> blank to count against a product's total across all its batches.
            If what you counted is <strong>lower</strong> than the system total, the shortfall is removed from
            existing batches automatically (oldest-expiry first). If it's <strong>higher</strong> — you found stock
            the system doesn't know about — you must give it a batch number and expiry date so it becomes real,
            sellable stock rather than just a number on this page.
        </div>
        <div class="table-responsive">
            <table class="table table-sm" id="itemsTable">
                <thead>
                    <tr>
                        <th style="width:18%">Product Code</th>
                        <th>Product Description</th>
                        <th style="width:14%">Current System Qty</th>
                        <th style="width:12%">Batch Number</th>
                        <th style="width:14%">Expiry Date (required if found stock is extra)</th>
                        <th style="width:10%">Qty Counted</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                    <tr>
                        <td>
                            <div class="product-search-wrap" style="position:relative;">
                                <input type="text" name="items[0][product_code]" class="form-control product-search-input" autocomplete="off" placeholder="Search product…" required>
                                <div class="product-search-results" style="display:none;position:absolute;top:100%;left:0;right:0;z-index:20;background:#fff;border:1px solid #dee2e6;border-radius:6px;max-height:220px;overflow-y:auto;box-shadow:0 4px 12px rgba(0,0,0,.1);"></div>
                            </div>
                        </td>
                        <td><input type="text" name="items[0][product_description]" class="form-control" readonly required></td>
                        <td><input type="text" class="form-control system-qty-display" disabled placeholder="—"></td>
                        <td><input type="text" name="items[0][batch_number]" class="form-control"></td>
                        <td><input type="date" name="items[0][expiry_date]" class="form-control"></td>
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
        row.querySelector('.product-search-results').style.display = 'none';
        row.querySelector('.product-search-results').innerHTML = '';
        tbody.appendChild(row);
        itemIndex++;
        wireRemoveButtons();
    });

    wireRemoveButtons();

    // ── Product search-as-you-type — same picker as GRN/Sales Orders, so a
    // typo can never create a no-op adjustment against a nonexistent product.
    let searchTimer = null;

    function renderResultItem(p) {
        return `
            <div class="product-search-item" style="padding:.5rem .75rem;cursor:pointer;font-size:.82rem;border-bottom:1px solid #f1f3f5;"
                 data-code="${p.product_code}" data-desc="${p.product_description}" data-qty="${p.quantity}">
                <div class="fw-semibold">${p.product_code} — ${p.product_description}</div>
                <div class="text-muted">System qty on hand: ${p.quantity}${p.quantity == 0 ? ' (depleted)' : ''}${p.batch_number ? ` &nbsp;·&nbsp; Next batch: ${p.batch_number} (exp ${p.expiry_date})` : ''}</div>
            </div>
        `;
    }

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
                        resultsBox.innerHTML = '<div class="p-2 text-muted" style="font-size:.82rem;">No matching products — add it to the catalogue first</div>';
                        resultsBox.style.display = 'block';
                        return;
                    }

                    resultsBox.innerHTML = products.map(renderResultItem).join('');
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
        row.querySelector('.system-qty-display').value = item.dataset.qty;

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
