@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="page-wrap">

    <div class="page-header">
        <div>
            <h4><i class="bi bi-file-earmark-bar-graph me-2 text-success"></i>{{ $title }}</h4>
            <div class="sub">{{ count($rows) }} row{{ count($rows) === 1 ? '' : 's' }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reports.show', [$slug, ...request()->except('export'), 'export' => 1]) }}" class="btn btn-outline-success btn-sm">
                <i class="bi bi-download me-1"></i>Export CSV
            </a>
            <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>All Reports</a>
        </div>
    </div>

    @if ($slug === 'sales-summary')
        <form method="GET" class="filter-bar">
            <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control" style="width:160px;">
            <span>to</span>
            <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control" style="width:160px;">
            <button type="submit" class="btn btn-success"><i class="bi bi-funnel-fill me-1"></i>Apply</button>
        </form>
    @elseif ($slug === 'expiry-alert')
        <form method="GET" class="filter-bar">
            <label style="font-size:.82rem;">Days ahead:</label>
            <input type="number" name="days" value="{{ request('days', 90) }}" class="form-control" style="width:120px;" min="1">
            <button type="submit" class="btn btn-success"><i class="bi bi-funnel-fill me-1"></i>Apply</button>
        </form>
    @elseif ($slug === 'batch-recall')
        <form method="GET" class="filter-bar">
            <label style="font-size:.82rem;">Batch Number:</label>
            <input type="text" name="batch_number" value="{{ $filters['batch_number'] ?? '' }}" class="form-control" style="width:220px;" placeholder="e.g. BATCH-2026-001" required>
            <button type="submit" class="btn btn-success"><i class="bi bi-search me-1"></i>Find Recipients</button>
        </form>
    @endif

    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        @foreach ($columns as $label)
                            <th>{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            @foreach (array_keys($columns) as $key)
                                <td>{{ data_get($row, $key) }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($columns) }}">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <p>No data for this report yet.</p>
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
