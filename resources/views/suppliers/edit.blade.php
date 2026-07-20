@extends('layouts.app')

@section('title', 'Edit Supplier')

@section('content')
<div class="page-wrap">
    <div class="page-header">
        <div>
            <h4><i class="bi bi-pencil-square me-2 text-success"></i>Edit Supplier</h4>
            <div class="sub">{{ $supplier->name }}</div>
        </div>
        <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Supplier</a>
    </div>

    <form action="{{ route('suppliers.update', $supplier) }}" method="POST" class="form-card">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $supplier->name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Contact Person</label>
                <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person', $supplier->contact_person) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $supplier->phone) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $supplier->email) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">TIN</label>
                <input type="text" name="tin" class="form-control" value="{{ old('tin', $supplier->tin) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">License Number</label>
                <input type="text" name="license_number" class="form-control" value="{{ old('license_number', $supplier->license_number) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">License Expiry Date</label>
                <input type="date" name="license_expiry_date" class="form-control" value="{{ old('license_expiry_date', $supplier->license_expiry_date?->toDateString()) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Accreditation Body</label>
                <input type="text" name="accreditation_body" class="form-control" value="{{ old('accreditation_body', $supplier->accreditation_body) }}" placeholder="e.g. MCAZ">
            </div>
            <div class="col-md-6">
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-control" value="{{ old('address', $supplier->address) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Payment Terms</label>
                <input type="text" name="payment_terms" class="form-control" value="{{ old('payment_terms', $supplier->payment_terms) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="active" @selected(old('status', $supplier->status) === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $supplier->status) === 'inactive')>Inactive</option>
                </select>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
            <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
