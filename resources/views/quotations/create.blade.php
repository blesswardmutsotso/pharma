@extends('layouts.app')

@section('title', 'New Quotation')

@section('content')
<div class="page-wrap">
    <div class="page-header">
        <div>
            <h4><i class="bi bi-file-earmark-ruled me-2 text-success"></i>New Quotation</h4>
            <div class="sub">Prepare a quote for a client</div>
        </div>
        <a href="{{ route('quotations.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Quotations</a>
    </div>

    <form action="{{ route('quotations.store') }}" method="POST" class="form-card">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
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
            <div class="col-md-3">
                <label class="form-label">Quote Date</label>
                <input type="date" name="quote_date" class="form-control" value="{{ now()->toDateString() }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Valid Until</label>
                <input type="date" name="valid_until" class="form-control">
            </div>
        </div>
        <div class="form-text mt-1">Quote number is generated automatically on save (e.g. Qu0001).</div>

        <div class="form-section-title">Line Items</div>

        <div class="mb-3" style="position:relative;max-width:420px;">
            <label class="form-label">Search Products to Add</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" id="quickSearchInput" class="form-control" autocomplete="off" placeholder="Search by product name or code…">
            </div>
            <div id="quickSearchResults" style="display:none;position:absolute;top:100%;left:0;right:0;z-index:20;background:#fff;border:1px solid #dee2e6;border-radius:6px;max-height:260px;overflow-y:auto;box-shadow:0 4px 12px rgba(0,0,0,.1);"></div>
        </div>

        <div class="table-responsive">
            <table class="table" id="itemsTable">
                <thead>
                    <tr>
                        <th style="width:14%">Product Code</th>
                        <th>Description</th>
                        <th style="width:9%">Qty</th>
                        <th style="width:12%">Unit Price</th>
                        <th style="width:12%">Discount</th>
                        <th style="width:12%">Total</th>
                        <th style="width:40px"></th>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                    <tr>
                        <td>
                            <div class="product-search-wrap" style="position:relative;">
                                <input type="text" name="items[0][product_code]" class="form-control product-search-input" autocomplete="off" required>
                                <div class="product-search-results" style="display:none;position:absolute;top:100%;left:0;right:0;z-index:20;background:#fff;border:1px solid #dee2e6;border-radius:6px;max-height:220px;overflow-y:auto;box-shadow:0 4px 12px rgba(0,0,0,.1);"></div>
                            </div>
                        </td>
                        <td><input type="text" name="items[0][product_description]" class="form-control" required></td>
                        <td><input type="number" name="items[0][qty]" class="form-control qty-input" min="1" required></td>
                        <td><input type="number" step="0.01" name="items[0][unit_price]" class="form-control unit-price-input" min="0" required></td>
                        <td><input type="number" step="0.01" name="items[0][discount]" class="form-control discount-input" min="0" value="0"></td>
                        <td><input type="text" class="form-control total-display" disabled value="0.00"></td>
                        <td><button type="button" class="btn-action remove-row" title="Remove"><i class="bi bi-trash"></i></button></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <button type="button" id="addItemBtn" class="btn btn-outline-success btn-sm mb-3"><i class="bi bi-plus-lg me-1"></i>Add Item</button>

        <div class="d-flex justify-content-end mb-3">
            <div style="min-width:280px;font-size:.85rem;">
                <div class="d-flex justify-content-between py-1"><span class="text-muted">Subtotal</span><span id="summarySubtotal">0.00</span></div>
                <div class="d-flex justify-content-between py-1"><span class="text-muted">Discount</span><span id="summaryDiscount">0.00</span></div>
                <div class="d-flex justify-content-between py-2 border-top fw-bold"><span>Grand Total</span><span id="summaryGrandTotal">0.00</span></div>
            </div>
        </div>

        <div class="form-section-title">Notes</div>
        <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Save Quotation</button>
            <a href="{{ route('quotations.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
                if (tbody.querySelectorAll('tr').length > 1) {
                    btn.closest('tr').remove();
                    recalcSummary();
                }
            };
        });
    }

    function recalcRowTotal(row) {
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const price = parseFloat(row.querySelector('.unit-price-input').value) || 0;
        const discount = parseFloat(row.querySelector('.discount-input').value) || 0;
        const total = Math.max((qty * price) - discount, 0);
        row.querySelector('.total-display').value = total.toFixed(2);
        return { qty, price, discount, total };
    }

    function recalcSummary() {
        let subtotal = 0;
        let discountTotal = 0;
        let grandTotal = 0;

        tbody.querySelectorAll('tr').forEach(row => {
            const { qty, price, discount, total } = recalcRowTotal(row);
            subtotal += qty * price;
            discountTotal += discount;
            grandTotal += total;
        });

        document.getElementById('summarySubtotal').textContent = subtotal.toFixed(2);
        document.getElementById('summaryDiscount').textContent = discountTotal.toFixed(2);
        document.getElementById('summaryGrandTotal').textContent = grandTotal.toFixed(2);
    }

    function fillRow(row, prefill) {
        row.querySelector('.product-search-input').value = prefill.code;
        row.querySelector('[name$="[product_description]"]').value = prefill.desc;
        row.querySelector('.qty-input').value = 1;
        row.querySelector('.unit-price-input').value = prefill.price;
        recalcSummary();
    }

    function addRow(prefill) {
        // If the only row on the page is still completely blank, fill it in
        // directly instead of leaving an empty row above the new one.
        const firstRow = tbody.querySelector('tr');
        const isFirstRowEmpty = tbody.querySelectorAll('tr').length === 1
            && !firstRow.querySelector('[name$="[product_code]"]').value;

        if (prefill && isFirstRowEmpty) {
            fillRow(firstRow, prefill);
            return;
        }

        const row = firstRow.cloneNode(true);
        row.querySelectorAll('input').forEach(input => {
            if (input.classList.contains('discount-input')) {
                input.value = '0';
            } else if (input.classList.contains('total-display')) {
                input.value = '0.00';
            } else {
                input.value = '';
            }
            if (input.name) input.name = input.name.replace(/items\[\d+\]/, `items[${itemIndex}]`);
        });
        row.querySelector('.product-search-results').style.display = 'none';
        row.querySelector('.product-search-results').innerHTML = '';

        if (prefill) {
            fillRow(row, prefill);
        }

        tbody.appendChild(row);
        itemIndex++;
        wireRemoveButtons();
        recalcSummary();
    }

    document.getElementById('addItemBtn').addEventListener('click', () => addRow(null));

    wireRemoveButtons();
    recalcSummary();

    tbody.addEventListener('input', (e) => {
        if (e.target.classList.contains('qty-input') || e.target.classList.contains('unit-price-input') || e.target.classList.contains('discount-input')) {
            recalcSummary();
        }
    });

    // ── Product search-as-you-type (includes depleted/zero-stock items) ──
    let searchTimer = null;

    function renderResultItem(p) {
        return `
            <div class="product-search-item" style="padding:.5rem .75rem;cursor:pointer;font-size:.82rem;border-bottom:1px solid #f1f3f5;"
                 data-code="${p.product_code}" data-desc="${p.product_description}" data-price="${p.selling_price}">
                <div class="fw-semibold">${p.product_code} — ${p.product_description}</div>
                <div class="text-muted">Price: ${Number(p.selling_price).toFixed(2)} &nbsp;·&nbsp; Qty on hand: ${p.quantity}${p.quantity == 0 ? ' (depleted)' : ''}${p.batch_number ? ` &nbsp;·&nbsp; Next batch: ${p.batch_number} (exp ${p.expiry_date})` : ''}</div>
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
                        resultsBox.innerHTML = '<div class="p-2 text-muted" style="font-size:.82rem;">No products found</div>';
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
        row.querySelector('.unit-price-input').value = item.dataset.price;
        recalcSummary();

        const resultsBox = item.closest('.product-search-results');
        resultsBox.style.display = 'none';
        resultsBox.innerHTML = '';
    });

    document.addEventListener('click', (e) => {
        if (e.target.closest('.product-search-wrap') || e.target.closest('#quickSearchInput')) return;
        tbody.querySelectorAll('.product-search-results').forEach(box => box.style.display = 'none');
        quickSearchResults.style.display = 'none';
    });

    // ── Standalone "search then add a new row" box above the table ──
    const quickSearchInput = document.getElementById('quickSearchInput');
    const quickSearchResults = document.getElementById('quickSearchResults');
    let quickSearchTimer = null;

    quickSearchInput.addEventListener('input', () => {
        const query = quickSearchInput.value.trim();
        clearTimeout(quickSearchTimer);

        if (query.length < 2) {
            quickSearchResults.style.display = 'none';
            quickSearchResults.innerHTML = '';
            return;
        }

        quickSearchTimer = setTimeout(() => {
            fetch(`{{ route('products.search') }}?q=${encodeURIComponent(query)}`)
                .then(r => r.json())
                .then(products => {
                    if (!products.length) {
                        quickSearchResults.innerHTML = '<div class="p-2 text-muted" style="font-size:.82rem;">No products found</div>';
                        quickSearchResults.style.display = 'block';
                        return;
                    }

                    quickSearchResults.innerHTML = products.map(p => `
                        <div class="quick-search-item" style="padding:.5rem .75rem;cursor:pointer;font-size:.82rem;border-bottom:1px solid #f1f3f5;"
                             data-code="${p.product_code}" data-desc="${p.product_description}" data-price="${p.selling_price}">
                            <div class="fw-semibold">${p.product_code} — ${p.product_description}</div>
                            <div class="text-muted">Price: ${Number(p.selling_price).toFixed(2)} &nbsp;·&nbsp; Qty on hand: ${p.quantity}${p.quantity == 0 ? ' (depleted)' : ''}${p.batch_number ? ` &nbsp;·&nbsp; Next batch: ${p.batch_number} (exp ${p.expiry_date})` : ''}</div>
                        </div>
                    `).join('');
                    quickSearchResults.style.display = 'block';
                });
        }, 250);
    });

    quickSearchResults.addEventListener('click', (e) => {
        const item = e.target.closest('.quick-search-item');
        if (!item) return;

        addRow({ code: item.dataset.code, desc: item.dataset.desc, price: item.dataset.price });

        quickSearchInput.value = '';
        quickSearchResults.style.display = 'none';
        quickSearchResults.innerHTML = '';
    });
})();
</script>
@endpush
