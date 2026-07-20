@php
    $val = fn ($field, $default = '') => old($field, $client?->{$field} ?? $default);
@endphp

<div class="form-section-title">Client Details</div>
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Client / Business Name</label>
        <input type="text" name="name" class="form-control" value="{{ $val('name') }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Contact Person</label>
        <input type="text" name="contact_person" class="form-control" value="{{ $val('contact_person') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Phone Number</label>
        <input type="text" name="phone" class="form-control" value="{{ $val('phone') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" value="{{ $val('email') }}">
    </div>
</div>

<div class="form-section-title">Tax Registration</div>
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">VAT Number</label>
        <input type="text" name="vat_number" class="form-control" value="{{ $val('vat_number') }}" maxlength="9">
    </div>
    <div class="col-md-4">
        <label class="form-label">TIN Number</label>
        <input type="text" name="tin" class="form-control" value="{{ $val('tin') }}" maxlength="10">
    </div>
</div>

<div class="form-section-title">Address</div>
<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">Province</label>
        <input type="text" name="province" class="form-control" value="{{ $val('province') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">City</label>
        <input type="text" name="city" class="form-control" value="{{ $val('city') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">District</label>
        <input type="text" name="district" class="form-control" value="{{ $val('district') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">House No.</label>
        <input type="text" name="house_no" class="form-control" value="{{ $val('house_no') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Street</label>
        <input type="text" name="street" class="form-control" value="{{ $val('street') }}">
    </div>
</div>
