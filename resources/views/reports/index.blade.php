@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="page-wrap">

    <div class="page-header">
        <div>
            <h4><i class="bi bi-file-earmark-bar-graph me-2 text-success"></i>Reports</h4>
            <div class="sub">Named, exportable operational reports</div>
        </div>
    </div>

    <div class="stat-cards">
        @foreach ($reports as $report)
            <a href="{{ route('reports.show', $report['slug']) }}" class="stat-card text-decoration-none" style="align-items:flex-start;">
                <div class="icon green"><i class="bi {{ $report['icon'] }}"></i></div>
                <div>
                    <div class="label" style="text-transform:none;font-size:.85rem;color:#212529;font-weight:700;">{{ $report['name'] }}</div>
                    <div class="trend" style="margin-top:.25rem;">{{ $report['desc'] }}</div>
                </div>
            </a>
        @endforeach
    </div>

</div>
@endsection
