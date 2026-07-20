@extends('layouts.app')

@section('title', 'Branches')

@push('styles')
<style>
    .branch-wrapper { padding: 1.5rem 2rem; }
    .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: .75rem; }
    .page-header h4 { font-weight: 700; color: var(--pos-green-dk); margin: 0; font-size: 1.35rem; }
    .page-header .sub { font-size: .8rem; color: #6c757d; margin-top: .1rem; }
    .table-card { background: #fff; border: 1px solid #e9ecef; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.05); }
    .table-card .table { margin: 0; font-size: .83rem; }
    .table-card .table thead th { background: #f8f9fa; border-bottom: 2px solid #e9ecef; color: #495057; font-weight: 700; font-size: .72rem; text-transform: uppercase; letter-spacing: .06em; padding: .75rem 1rem; }
    .table-card .table tbody td { padding: .65rem 1rem; vertical-align: middle; border-color: #f1f3f5; }
    .table-card .table tbody tr:hover { background: #f8fffe; }
    .badge-active   { background: #d1e7dd; color: #145c2d; font-size: .7rem; font-weight: 600; padding: .25em .65em; border-radius: 6px; }
    .badge-inactive { background: #f1f3f5; color: #6c757d; font-size: .7rem; font-weight: 600; padding: .25em .65em; border-radius: 6px; }
    .badge-home     { background: #dbeafe; color: #1d4ed8; font-size: .7rem; font-weight: 600; padding: .25em .65em; border-radius: 6px; }
    .mono { font-family: 'Courier New', monospace; font-size: .8rem; }
</style>
@endpush

@section('content')
<div class="branch-wrapper">
    <div class="page-header">
        <div>
            <h4>Branches</h4>
            <div class="sub">Manage your business locations</div>
        </div>
        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
            + Add Branch
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="table-card">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Address</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($branches as $branch)
                <tr>
                    <td>
                        <strong>{{ $branch->name }}</strong>
                        @if($branch->is_home)
                            <span class="badge-home ms-1">Home</span>
                        @endif
                    </td>
                    <td class="mono">{{ $branch->code }}</td>
                    <td style="color:#6c757d">{{ $branch->address ?? '—' }}</td>
                    <td style="color:#6c757d">{{ $branch->phone ?? '—' }}</td>
                    <td>
                        <span class="badge-{{ $branch->is_active ? 'active' : 'inactive' }}">
                            {{ $branch->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td style="white-space:nowrap">
                        <button class="btn btn-sm btn-outline-secondary me-1"
                            onclick="openEdit({{ $branch->id }}, '{{ addslashes($branch->name) }}', '{{ $branch->code }}', '{{ addslashes($branch->address ?? '') }}', '{{ $branch->phone ?? '' }}', {{ $branch->is_active ? 1 : 0 }})">
                            Edit
                        </button>
                        @if(!$branch->is_home)
                        <form method="POST" action="{{ route('admin.branches.destroy', $branch) }}" class="d-inline"
                              onsubmit="return confirm('Delete branch {{ addslashes($branch->name) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:2rem;color:#adb5bd">No branches yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Add Modal --}}
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.branches.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add Branch</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Branch Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. CBD Branch" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Branch Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control mono" placeholder="e.g. CBD" required>
                        <div class="form-text">Short unique identifier (max 20 chars)</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Address</label>
                        <input type="text" name="address" class="form-control" placeholder="Optional">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="text" name="phone" class="form-control" placeholder="Optional">
                    </div>
                    @if($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save Branch</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="editForm">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Branch</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Branch Name</label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Branch Code</label>
                        <input type="text" name="code" id="editCode" class="form-control mono" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Address</label>
                        <input type="text" name="address" id="editAddress" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="text" name="phone" id="editPhone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="is_active" id="editActive" class="form-select">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openEdit(id, name, code, address, phone, isActive) {
    document.getElementById('editForm').action = `/admin/branches/${id}`;
    document.getElementById('editName').value    = name;
    document.getElementById('editCode').value    = code;
    document.getElementById('editAddress').value = address;
    document.getElementById('editPhone').value   = phone;
    document.getElementById('editActive').value  = isActive;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>
@endpush
