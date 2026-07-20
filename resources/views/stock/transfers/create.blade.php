@extends('layouts.app')

@section('title', 'New Stock Transfer')

@push('styles')
<style>
    .transfers-wrapper { padding: 1.5rem 2rem; }

    .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: .75rem; }
    .page-header h4 { font-weight: 700; color: var(--pos-green-dk); margin: 0; font-size: 1.35rem; letter-spacing: -.01em; }

    .form-card { background: #fff; border: 1px solid #e9ecef; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.05); margin-bottom: 1.25rem; overflow: hidden; }
    .form-card .card-head { padding: .85rem 1.25rem; border-bottom: 1px solid #f1f3f5; background: #fafafa; }
    .form-card .card-head h6 { margin: 0; font-weight: 700; font-size: .88rem; color: #343a40; }
    .form-card .card-body-p { padding: 1.25rem; }

    /* Tab nav */
    .tab-nav { display: flex; border-bottom: 2px solid #e9ecef; margin-bottom: 1.5rem; gap: 0; }
    .tab-btn { padding: .6rem 1.25rem; font-size: .85rem; font-weight: 600; color: #6c757d; background: none; border: none; border-bottom: 2px solid transparent; margin-bottom: -2px; cursor: pointer; transition: color .15s, border-color .15s; }
    .tab-btn.active { color: var(--pos-green-dk); border-bottom-color: var(--pos-green); }
    .tab-btn:hover:not(.active) { color: #343a40; }

    /* Form controls */
    .form-control, .form-select { border-radius: 8px; font-size: .83rem; border-color: #dee2e6; }
    .form-control:focus, .form-select:focus { border-color: var(--pos-green); box-shadow: 0 0 0 .2rem rgba(25,135,84,.15); }
    .form-label { font-size: .8rem; font-weight: 600; color: #495057; margin-bottom: .3rem; }

    /* Product search dropdown */
    .search-wrap { position: relative; }
    .search-results { position: absolute; z-index: 200; left: 0; right: 0; top: calc(100% + 4px); background: #fff; border: 1px solid #dee2e6; border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,.1); max-height: 280px; overflow-y: auto; display: none; }
    .search-results.show { display: block; }
    .search-item { padding: .6rem 1rem; display: flex; justify-content: space-between; align-items: center; cursor: pointer; border-bottom: 1px solid #f1f3f5; font-size: .82rem; transition: background .1s; }
    .search-item:last-child { border-bottom: none; }
    .search-item:hover { background: var(--pos-green-light); }
    .search-item .prod-code { font-family: 'Courier New', monospace; font-size: .75rem; color: #1d4ed8; font-weight: 600; }
    .search-item .prod-name { color: #343a40; margin-left: .5rem; }
    .search-item .prod-qty  { font-size: .75rem; color: #6c757d; white-space: nowrap; }

    /* Items table */
    .table-card { background: #fff; border: 1px solid #e9ecef; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.05); margin-bottom: 1.25rem; }
    .table-card .table { margin: 0; font-size: .83rem; }
    .table-card .table thead th { background: #f8f9fa; border-bottom: 2px solid #e9ecef; color: #495057; font-weight: 700; font-size: .72rem; text-transform: uppercase; letter-spacing: .06em; padding: .7rem 1rem; white-space: nowrap; }
    .table-card .table tbody td { padding: .55rem .9rem; vertical-align: middle; border-color: #f1f3f5; }
    .table-card .table tbody tr:hover { background: #f8fffe; }

    .btn-remove { width: 28px; height: 28px; border-radius: 7px; border: 1px solid #dee2e6; background: #fff; color: #adb5bd; display: inline-flex; align-items: center; justify-content: center; font-size: .8rem; transition: all .12s; cursor: pointer; }
    .btn-remove:hover { background: #fee2e2; color: #b91c1c; border-color: #f5c2c7; }

    /* Upload drop zone */
    .drop-zone { border: 2px dashed #dee2e6; border-radius: 12px; padding: 2.5rem 1.5rem; text-align: center; cursor: pointer; transition: border-color .15s, background .15s; }
    .drop-zone:hover { border-color: var(--pos-green); background: var(--pos-green-light); }
    .drop-zone i { font-size: 2.5rem; color: #adb5bd; display: block; margin-bottom: .75rem; }
    .drop-zone p { margin: 0; font-size: .83rem; color: #6c757d; }
    .drop-zone .file-chosen { font-size: .8rem; color: var(--pos-green-dk); margin-top: .5rem; font-weight: 600; display: none; }
</style>
@endpush

@section('content')
<div class="transfers-wrapper">

    {{-- Header --}}
    <div class="page-header">
        <div>
            <a href="{{ route('stock.transfers.index') }}" class="text-muted text-decoration-none" style="font-size:.82rem;">
                <i class="bi bi-arrow-left me-1"></i>All Transfers
            </a>
            <h4 class="mt-1"><i class="bi bi-plus-circle me-2 text-success"></i>New Stock Transfer</h4>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show py-2 px-3 mb-3" style="font-size:.83rem;border-radius:10px;" role="alert">
        <i class="bi bi-exclamation-circle-fill me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Tab nav --}}
    <div class="tab-nav">
        <button class="tab-btn active" id="tab-manual" onclick="switchTab('manual')">
            <i class="bi bi-pencil-square me-1"></i>Manual Entry
        </button>
        <button class="tab-btn" id="tab-import" onclick="switchTab('import')">
            <i class="bi bi-upload me-1"></i>Import from File
        </button>
    </div>

    {{-- ──── MANUAL ENTRY ─────────────────────────────────── --}}
    <div id="panel-manual">
        <form id="transfer-form" method="POST" action="{{ route('stock.transfers.store') }}">
            @csrf

            {{-- Meta --}}
            <div class="form-card">
                <div class="card-head"><h6><i class="bi bi-info-circle me-1"></i>Transfer Details</h6></div>
                <div class="card-body-p">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Transfer Type <span class="text-danger">*</span></label>
                            <select name="transfer_type" required class="form-select">
                                <option value="OUTGOING">Outgoing — send stock out</option>
                                <option value="INCOMING">Incoming — receive stock in</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">From Branch</label>
                            <select name="from_branch_id" class="form-select">
                                <option value="">— External / N/A —</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ $home?->id === $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">To Branch</label>
                            <select name="to_branch_id" class="form-select">
                                <option value="">— External / N/A —</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Reference Doc</label>
                            <input type="text" name="reference_doc" value="{{ old('reference_doc') }}"
                                   class="form-control" placeholder="e.g. PO-2026-001">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2"
                                      placeholder="Optional notes about this transfer"
                                      style="resize:none;">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Product search + table --}}
            <div class="form-card">
                <div class="card-head">
                    <h6><i class="bi bi-box-seam me-1"></i>Add Products</h6>
                </div>
                <div class="card-body-p pb-2">
                    <div class="search-wrap">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0" style="border-radius:8px 0 0 8px;border-color:#dee2e6;">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" id="product-search"
                                   class="form-control border-start-0"
                                   style="border-radius:0 8px 8px 0;"
                                   placeholder="Search by product code or description…">
                        </div>
                        <div class="search-results" id="search-results"></div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="items-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Description</th>
                                <th class="text-center" style="width:110px;">Qty</th>
                                <th style="width:200px;">Notes</th>
                                <th style="width:40px;"></th>
                            </tr>
                        </thead>
                        <tbody id="items-body">
                            <tr id="empty-row">
                                <td colspan="5" class="text-center py-4 text-muted" style="font-size:.85rem;">
                                    <i class="bi bi-search me-1"></i>Search and select products above
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <input type="hidden" name="items" id="items-json" value="[]">
            </div>

            {{-- Submit --}}
            <div class="d-flex justify-content-end gap-2">
                <button type="submit" name="action" value="draft"
                        class="btn btn-outline-secondary">
                    <i class="bi bi-save me-1"></i>Save as Draft
                </button>
                <button type="submit" name="action" value="submit"
                        class="btn btn-success">
                    <i class="bi bi-send me-1"></i>Submit for Approval
                </button>
            </div>
        </form>
    </div>

    {{-- ──── IMPORT FROM FILE ────────────────────────────────── --}}
    <div id="panel-import" class="d-none">
        <form method="POST" action="{{ route('stock.transfers.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-card">
                <div class="card-head"><h6><i class="bi bi-file-earmark-arrow-up me-1"></i>Import Details</h6></div>
                <div class="card-body-p">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">From Branch</label>
                            <select name="from_branch_id" class="form-select">
                                <option value="">— External —</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">To Branch</label>
                            <select name="to_branch_id" class="form-select">
                                <option value="">— External —</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ $home?->id === $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Reference Doc</label>
                            <input type="text" name="reference_doc" class="form-control" placeholder="e.g. PO-2026-001">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Notes</label>
                            <input type="text" name="notes" class="form-control" placeholder="Optional">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Upload File <span class="text-danger">*</span></label>
                            <div class="drop-zone" onclick="document.getElementById('file-input').click()">
                                <i class="bi bi-cloud-upload"></i>
                                <p><strong class="text-success">Click to upload</strong> or drag and drop</p>
                                <p class="mt-1" style="font-size:.75rem;">XLSX, XLS, CSV — max 5 MB</p>
                                <p class="file-chosen" id="file-chosen-name"></p>
                            </div>
                            <input id="file-input" type="file" name="file" accept=".xlsx,.xls,.csv" class="d-none"
                                   onchange="
                                     const n = document.getElementById('file-chosen-name');
                                     n.textContent = '✓ ' + this.files[0].name;
                                     n.style.display = 'block';
                                   ">
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between">
                <a href="{{ route('stock.transfers.template') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-download me-1"></i>Download Template
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-upload me-1"></i>Upload &amp; Preview
                </button>
            </div>
        </form>
    </div>

</div>

@push('scripts')
<script>
// ── Tab switching ─────────────────────────────────────────────────────────────
function switchTab(tab) {
    document.getElementById('panel-manual').classList.toggle('d-none', tab !== 'manual');
    document.getElementById('panel-import').classList.toggle('d-none', tab !== 'import');
    document.getElementById('tab-manual').classList.toggle('active', tab === 'manual');
    document.getElementById('tab-import').classList.toggle('active', tab === 'import');
}

// ── Live product search ───────────────────────────────────────────────────────
let searchTimeout;
const searchInput   = document.getElementById('product-search');
const searchResults = document.getElementById('search-results');

searchInput.addEventListener('input', () => {
    clearTimeout(searchTimeout);
    const q = searchInput.value.trim();
    if (q.length < 2) { searchResults.classList.remove('show'); return; }
    searchTimeout = setTimeout(() => fetchProducts(q), 250);
});

searchInput.addEventListener('keydown', e => {
    if (e.key === 'Escape') searchResults.classList.remove('show');
});

document.addEventListener('click', e => {
    if (!searchInput.contains(e.target) && !searchResults.contains(e.target))
        searchResults.classList.remove('show');
});

async function fetchProducts(q) {
    try {
        const res  = await fetch(`/live-search?search=${encodeURIComponent(q)}`);
        const data = await res.json();
        renderResults(Array.isArray(data) ? data : (data.stocks ?? []));
    } catch { searchResults.classList.remove('show'); }
}

function renderResults(products) {
    if (!products.length) {
        searchResults.innerHTML = '<div class="search-item text-muted">No products found</div>';
        searchResults.classList.add('show');
        return;
    }
    searchResults.innerHTML = products.slice(0, 20).map(p => `
        <div class="search-item" onclick='addProduct(${JSON.stringify(p)})'>
            <div>
                <span class="prod-code">${p.product_code}</span>
                <span class="prod-name">${p.product_description}</span>
            </div>
            <span class="prod-qty">Qty: ${p.quantity ?? '—'}</span>
        </div>
    `).join('');
    searchResults.classList.add('show');
}

// ── Item management ───────────────────────────────────────────────────────────
let items = [];

function addProduct(p) {
    searchResults.classList.remove('show');
    searchInput.value = '';
    if (items.find(i => i.product_code === p.product_code)) {
        const row = document.querySelector(`[data-code="${CSS.escape(p.product_code)}"] input.qty-input`);
        if (row) { row.focus(); row.select(); }
        return;
    }
    items.push({ product_code: p.product_code, product_description: p.product_description, qty_requested: 1, notes: '' });
    renderTable();
}

function removeItem(code) {
    items = items.filter(i => i.product_code !== code);
    renderTable();
}

function renderTable() {
    const tbody = document.getElementById('items-body');
    if (!items.length) {
        tbody.innerHTML = `<tr id="empty-row"><td colspan="5" class="text-center py-4 text-muted" style="font-size:.85rem;"><i class="bi bi-search me-1"></i>Search and select products above</td></tr>`;
        document.getElementById('items-json').value = '[]';
        return;
    }
    tbody.innerHTML = items.map((item, idx) => `
        <tr data-code="${item.product_code}">
            <td><span style="font-family:'Courier New',monospace;font-size:.78rem;color:#1d4ed8;font-weight:600;">${item.product_code}</span></td>
            <td style="font-size:.81rem;color:#343a40;">${item.product_description}</td>
            <td class="text-center">
                <input type="number" min="1" value="${item.qty_requested}"
                       class="form-control form-control-sm qty-input text-center"
                       style="width:80px;margin:auto;"
                       onchange="updateItem(${idx}, 'qty_requested', parseInt(this.value)||1)">
            </td>
            <td>
                <input type="text" value="${item.notes||''}" placeholder="Optional"
                       class="form-control form-control-sm"
                       onchange="updateItem(${idx}, 'notes', this.value)">
            </td>
            <td class="text-center">
                <button type="button" class="btn-remove" onclick="removeItem('${item.product_code}')">
                    <i class="bi bi-trash3"></i>
                </button>
            </td>
        </tr>
    `).join('');
    document.getElementById('items-json').value = JSON.stringify(items);
}

function updateItem(idx, field, value) {
    items[idx][field] = value;
    document.getElementById('items-json').value = JSON.stringify(items);
}

document.getElementById('transfer-form').addEventListener('submit', e => {
    if (!items.length) {
        e.preventDefault();
        alert('Please add at least one product before submitting.');
    }
});
</script>
@endpush
@endsection
