@extends('layouts.app')

@section('title', 'User Activity Logs')

@section('content')
<div class="page-wrap">

    <div class="page-header">
        <div>
            <h4><i class="bi bi-clock-history me-2 text-success"></i>User Activity Logs</h4>
            <div class="sub">Login, logout and failed login attempts across all users</div>
        </div>
    </div>

    <form method="GET" class="filter-bar mb-3">
        <select name="action" class="form-select form-select-sm" style="max-width:180px;">
            <option value="">All actions</option>
            <option value="login" @selected(request('action') === 'login')>Login</option>
            <option value="logout" @selected(request('action') === 'logout')>Logout</option>
            <option value="failed_login" @selected(request('action') === 'failed_login')>Failed Login</option>
        </select>
        <input type="text" name="user" class="form-control form-control-sm" style="max-width:180px;" placeholder="User name…" value="{{ request('user') }}">
        <input type="date" name="from" class="form-control form-control-sm" style="max-width:160px;" value="{{ request('from') }}">
        <input type="date" name="to" class="form-control form-control-sm" style="max-width:160px;" value="{{ request('to') }}">
        <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-funnel-fill me-1"></i>Filter</button>
        @if (request()->hasAny(['action', 'user', 'from', 'to']))
            <a href="{{ route('user-activity-logs.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle me-1"></i>Clear</a>
        @endif
    </form>

    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>IP Address</th>
                        <th>User Agent</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td style="white-space:nowrap;">
                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                            </td>
                            <td>{{ $log->user_name ?? '—' }}</td>
                            <td>
                                <span class="badge-status badge-{{ $log->action === 'failed_login' ? 'rejected' : ($log->action === 'login' ? 'approved' : 'pending') }}">
                                    {{ $log->actionLabel() }}
                                </span>
                            </td>
                            <td>{{ $log->ip_address ?? '—' }}</td>
                            <td class="text-muted" style="font-size:.78rem;">{{ Str::limit($log->user_agent, 60) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="bi bi-clock-history"></i>
                                    <p>No activity logged yet{{ request()->hasAny(['action', 'user', 'from', 'to']) ? ' matching your filters' : '' }}.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top bg-light" style="font-size:.8rem;">
            <span class="text-muted">Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }} entries</span>
            {{ $logs->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>

</div>
@endsection
