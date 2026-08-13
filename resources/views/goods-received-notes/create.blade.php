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
                        <option value="{{ $purchaseOrder->id }}">{{ $purchaseOrder->po_number }} ({{ $purchaseOrder->currency }})</option>
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
        <div class="form-text mt-1">The GRN's currency follows whichever Purchase Order it's linked to (defaults to USD if none).</div>

        <div class="form-section-title">Line Items</div>
        <div class="form-text mb-2">Products must be picked from the catalogue below — this is what keeps received stock showing up correctly on the Products list. If a product isn't listed yet, <a href="{{ route('products.create') }}" target="_blank" class="text-success">add it to the catalogue first</a>.</div>
        <div class="form-text mb-2">Batch Number defaults to today's date (DDMMYYYY) — overwrite it with the manufacturer's actual lot number whenever you have it, for accurate recall traceability.</div>
        <div class="table-responsive">
            <table class="table" id="itemsTable">
                <thead>
                    <tr>
                        <th style="width:16%">Product Code</th>
                        <th style="width:16%">Description</th>
                        <th style="width:8%">Qty</th>
                        <th style="width:10%">Unit Cost</th>
                        <th style="width:12%">Batch Number</th>
                        <th style="width:12%">Expiry Date</th>
                        <th style="width:10%">Condition</th>
                        <th style="width:40px"></th>
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
                        <td><input type="number" name="items[0][qty_received]" class="form-control" min="1" required></td>
                        <td><input type="number" step="0.01" name="items[0][unit_cost]" class="form-control" min="0" required></td>
                        <td><input type="text" name="items[0][batch_number]" class="form-control batch-number-input" value="{{ now()->format('dmY') }}" required></td>
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
    const todayBatchNumber = @json(now()->format('dmY'));

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
            if (field.tagName === 'SELECT') {
                field.selectedIndex = 0;
            } else if (field.classList.contains('batch-number-input')) {
                field.value = todayBatchNumber;
            } else {
                field.value = '';
            }
            field.name = field.name.replace(/items\[\d+\]/, `items[${itemIndex}]`);
        });
        row.querySelector('.product-search-results').style.display = 'none';
        row.querySelector('.product-search-results').innerHTML = '';
        tbody.appendChild(row);
        itemIndex++;
        wireRemoveButtons();
    });

    wireRemoveButtons();

    // ── Product search-as-you-type — forces every line to reference a real
    // catalogue product, so received stock always lands on the Products list.
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
                        resultsBox.innerHTML = '<div class="p-2 text-muted" style="font-size:.82rem;">No matching products — add it to the catalogue first</div>';
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
