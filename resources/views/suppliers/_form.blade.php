@php
    $val = fn ($field, $default = '') => old($field, $supplier?->{$field} ?? $default);
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="{{ $val('name') }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Contact Person</label>
        <input type="text" name="contact_person" class="form-control" value="{{ $val('contact_person') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control" value="{{ $val('phone') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="{{ $val('email') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">TIN</label>
        <input type="text" name="tin" class="form-control" value="{{ $val('tin') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">License Number</label>
        <input type="text" name="license_number" class="form-control" value="{{ $val('license_number') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">License Expiry Date</label>
        <input type="date" name="license_expiry_date" class="form-control" value="{{ old('license_expiry_date', $supplier?->license_expiry_date?->toDateString()) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Accreditation Body</label>
        <input type="text" name="accreditation_body" class="form-control" value="{{ $val('accreditation_body') }}" placeholder="e.g. MCAZ">
    </div>
    <div class="col-md-6">
        <label class="form-label">MCAZ Licensed Person</label>
        <input type="text" name="mcaz_licensed_person" class="form-control" value="{{ $val('mcaz_licensed_person') }}" placeholder="Name of the MCAZ-licensed responsible person">
    </div>
    <div class="col-md-6">
        <label class="form-label">Wholesale License Number</label>
        <input type="text" name="wholesale_license_number" class="form-control" value="{{ $val('wholesale_license_number') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Address</label>
        <input type="text" name="address" class="form-control" value="{{ $val('address') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Payment Terms</label>
        <input type="text" name="payment_terms" class="form-control" value="{{ $val('payment_terms', 'Net 30') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="active" @selected($val('status', 'active') === 'active')>Active</option>
            <option value="inactive" @selected($val('status') === 'inactive')>Inactive</option>
        </select>
    </div>
</div>

<div class="form-section-title">Banking Details</div>
<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">Bank Name</label>
        <input type="text" name="bank_name" class="form-control" value="{{ $val('bank_name') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Account Name</label>
        <input type="text" name="bank_account_name" class="form-control" value="{{ $val('bank_account_name') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">USD Account Number</label>
        <input type="text" name="bank_account_number" class="form-control" value="{{ $val('bank_account_number') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">ZWG Account Number</label>
        <input type="text" name="zig_bank_account_number" class="form-control" value="{{ $val('zig_bank_account_number') }}">
    </div>
</div>
