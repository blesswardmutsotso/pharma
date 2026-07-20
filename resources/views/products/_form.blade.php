@php
    $val = fn ($field, $default = '') => old($field, $product?->{$field} ?? $default);
@endphp

<div class="form-section-title">Identification</div>
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Product Code (SKU)</label>
        <input type="text" name="product_code" class="form-control" value="{{ $val('product_code') }}" required>
    </div>
    <div class="col-md-8">
        <label class="form-label">Product Name</label>
        <input type="text" name="product_description" class="form-control" value="{{ $val('product_description') }}" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Category</label>
        <input type="text" name="category" class="form-control" value="{{ $val('category') }}" placeholder="e.g. Antibiotics">
    </div>
    <div class="col-md-4">
        <label class="form-label">Generic Name</label>
        <input type="text" name="generic_name" class="form-control" value="{{ $val('generic_name') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Dosage Form</label>
        <input type="text" name="dosage_form" class="form-control" value="{{ $val('dosage_form') }}" placeholder="Tablet, Capsule...">
    </div>
</div>

<div class="form-section-title">Regulatory</div>
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Manufacturer</label>
        <input type="text" name="manufacturer" class="form-control" value="{{ $val('manufacturer') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Registration Number</label>
        <input type="text" name="registration_number" class="form-control" value="{{ $val('registration_number') }}" placeholder="MCAZ registration no.">
    </div>
    <div class="col-md-4">
        <label class="form-label">Controlled Substance Schedule</label>
        <input type="text" name="controlled_substance_schedule" class="form-control" value="{{ $val('controlled_substance_schedule') }}" placeholder="e.g. Schedule III (leave blank if none)">
    </div>
</div>

<div class="form-section-title">Packaging &amp; Storage</div>
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Strength</label>
        <input type="text" name="strength" class="form-control" value="{{ $val('strength') }}" placeholder="500mg">
    </div>
    <div class="col-md-4">
        <label class="form-label">Pack Size</label>
        <input type="text" name="pack_size" class="form-control" value="{{ $val('pack_size') }}" placeholder="10x10">
    </div>
    <div class="col-md-4">
        <label class="form-label">Unit of Measure</label>
        <input type="text" name="unit_of_measure" class="form-control" value="{{ $val('unit_of_measure') }}" placeholder="Box, Strip, Each">
    </div>
    <div class="col-md-6">
        <label class="form-label">Storage Condition</label>
        <select name="storage_condition" class="form-select">
            <option value="">—</option>
            @foreach (['Ambient', 'Refrigerated', 'Frozen', 'Controlled Room Temperature'] as $option)
                <option value="{{ $option }}" @selected($val('storage_condition') === $option)>{{ $option }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 d-flex align-items-end">
        <label class="d-flex align-items-center gap-2">
            <input type="checkbox" name="requires_batch_tracking" value="1" class="form-check-input" @checked($val('requires_batch_tracking', true))>
            Requires batch / expiry tracking
        </label>
    </div>
</div>

<div class="form-section-title">Pricing &amp; Stock Control</div>
<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">Buying Price</label>
        <input type="number" step="0.01" name="buying_price" class="form-control" value="{{ $val('buying_price', 0) }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Selling Price</label>
        <input type="number" step="0.01" name="selling_price" class="form-control" value="{{ $val('selling_price', 0) }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Reorder Point</label>
        <input type="number" name="reorder_point" class="form-control" value="{{ $val('reorder_point', 0) }}" min="0">
    </div>
    <div class="col-md-3">
        <label class="form-label">Reorder Quantity</label>
        <input type="number" name="reorder_qty" class="form-control" value="{{ $val('reorder_qty', 0) }}" min="0">
    </div>
    <div class="col-md-6">
        <label class="form-label">Default Supplier</label>
        <select name="default_supplier_id" class="form-select">
            <option value="">— None —</option>
            @foreach ($suppliers ?? [] as $supplier)
                <option value="{{ $supplier->id }}" @selected((string) $val('default_supplier_id') === (string) $supplier->id)>{{ $supplier->name }}</option>
            @endforeach
        </select>
        <div class="form-text">Used by "Generate Draft POs for Low Stock" on the Purchase Orders page.</div>
    </div>
</div>
