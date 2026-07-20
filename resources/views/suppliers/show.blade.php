@extends('layouts.app')

@section('title', $supplier->name)

@section('content')
<div class="page-wrap">

    <div class="page-header">
        <div>
            <h4><i class="bi bi-truck me-2 text-success"></i>{{ $supplier->name }}</h4>
            <div class="sub"><span class="badge-status badge-{{ $supplier->status }}">{{ ucfirst($supplier->status) }}</span></div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
            <form action="{{ route('suppliers.toggle-status', $supplier) }}" method="POST"
                  data-confirm="{{ $supplier->status === 'active' ? 'Deactivate' : 'Activate' }} {{ $supplier->name }}?" data-confirm-icon="question">
                @csrf
                <button type="submit" class="btn btn-outline-{{ $supplier->status === 'active' ? 'danger' : 'success' }} btn-sm">
                    <i class="bi bi-power me-1"></i>{{ $supplier->status === 'active' ? 'Deactivate' : 'Activate' }}
                </button>
            </form>
            <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    <div class="detail-card">
        <div class="detail-grid">
            <div><div class="label">Contact Person</div><div class="value">{{ $supplier->contact_person ?? '—' }}</div></div>
            <div><div class="label">Phone</div><div class="value">{{ $supplier->phone ?? '—' }}</div></div>
            <div><div class="label">Email</div><div class="value">{{ $supplier->email ?? '—' }}</div></div>
            <div><div class="label">TIN</div><div class="value">{{ $supplier->tin ?? '—' }}</div></div>
            <div><div class="label">License Number</div><div class="value">{{ $supplier->license_number ?? '—' }}</div></div>
            <div>
                <div class="label">License Expiry</div>
                <div class="value {{ $supplier->isLicenseExpired() ? 'text-danger' : '' }}">
                    {{ $supplier->license_expiry_date?->format('Y-m-d') ?? '—' }}
                    @if ($supplier->isLicenseExpired()) (Expired) @endif
                </div>
            </div>
            <div><div class="label">Accreditation Body</div><div class="value">{{ $supplier->accreditation_body ?? '—' }}</div></div>
            <div><div class="label">Address</div><div class="value">{{ $supplier->address ?? '—' }}</div></div>
            <div><div class="label">Payment Terms</div><div class="value">{{ $supplier->payment_terms ?? '—' }}</div></div>
        </div>
    </div>

    <div class="table-card">
        <div class="p-3 border-bottom fw-semibold" style="font-size:.85rem;">
            <i class="bi bi-file-earmark-text me-1 text-success"></i>Purchase Orders
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>PO Number</th>
                        <th>Order Date</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($supplier->purchaseOrders()->latest()->limit(20)->get() as $po)
                        <tr>
                            <td><span class="inv-no">{{ $po->po_number }}</span></td>
                            <td>{{ $po->order_date?->format('Y-m-d') }}</td>
                            <td><span class="badge-status badge-{{ $po->status }}">{{ ucfirst($po->status) }}</span></td>
                            <td class="text-center">
                                <a class="btn-action" href="{{ route('purchase-orders.show', $po) }}" title="View"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <p>No purchase orders for this supplier yet.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
