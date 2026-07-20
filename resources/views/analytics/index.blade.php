@extends('layouts.app')

@section('title', 'Analytics')

@push('styles')
<style>
:root { --green: #198754; --green-dk: #145c2d; --green-lt: #d1e7dd; }

.an-wrap  { padding: 1.5rem 2rem 3rem; }
.an-title { font-weight: 800; color: var(--green-dk); font-size: 1.45rem; margin: 0; }
.an-sub   { font-size: .8rem; color: #6c757d; margin-top: .15rem; }

/* ── Section headers ── */
.section-label { font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .1em; color: #adb5bd; margin: 1.8rem 0 .75rem; display: flex; align-items: center; gap: .5rem; }
.section-label::after { content: ''; flex: 1; height: 1px; background: #e9ecef; }

/* ── KPI grid ── */
.kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: .85rem; }
.kpi { background: #fff; border: 1px solid #e9ecef; border-radius: 12px; padding: 1rem 1.2rem; box-shadow: 0 1px 3px rgba(0,0,0,.05); position: relative; overflow: hidden; }
.kpi::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: var(--green); border-radius: 12px 12px 0 0; }
.kpi.warn::before { background: #ffc107; }
.kpi.danger::before { background: #dc3545; }
.kpi .label { font-size: .67rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: #6c757d; }
.kpi .value { font-size: 1.6rem; font-weight: 800; color: #212529; margin: .15rem 0 .1rem; font-variant-numeric: tabular-nums; line-height: 1; }
.kpi .value.green  { color: var(--green); }
.kpi .value.red    { color: #dc3545; }
.kpi .value.orange { color: #fd7e14; }
.kpi .trend { font-size: .72rem; color: #6c757d; }
.kpi .trend .up   { color: #198754; font-weight: 600; }
.kpi .trend .dn   { color: #dc3545; font-weight: 600; }

/* ── Charts ── */
.chart-row   { display: grid; gap: 1rem; margin-bottom: 1rem; }
.chart-row.cols-2 { grid-template-columns: 1fr 1fr; }
.chart-row.cols-3 { grid-template-columns: 2fr 1fr 1fr; }
.chart-row.cols-1 { grid-template-columns: 1fr; }
@media(max-width:900px) { .chart-row.cols-2, .chart-row.cols-3 { grid-template-columns: 1fr; } }

.card { background: #fff; border: 1px solid #e9ecef; border-radius: 12px; padding: 1.25rem 1.4rem; box-shadow: 0 1px 4px rgba(0,0,0,.05); }
.card-title { font-size: .75rem; font-weight: 800; text-transform: uppercase; letter-spacing: .07em; color: #495057; margin-bottom: 1rem; display: flex; align-items: center; justify-content: space-between; }
.card-title .badge { font-size: .62rem; font-weight: 700; padding: .2em .55em; border-radius: 5px; }

/* ── Tables ── */
.tbl { width: 100%; border-collapse: collapse; font-size: .81rem; }
.tbl thead th { background: #f8f9fa; border-bottom: 2px solid #e9ecef; color: #495057; font-weight: 700; font-size: .69rem; text-transform: uppercase; letter-spacing: .06em; padding: .55rem .85rem; white-space: nowrap; }
.tbl tbody td { padding: .55rem .85rem; border-bottom: 1px solid #f1f3f5; vertical-align: middle; }
.tbl tbody tr:last-child td { border-bottom: none; }
.tbl tbody tr:hover td { background: #f8fffe; }

/* ── Badges ── */
.b { font-size: .68rem; font-weight: 700; padding: .22em .58em; border-radius: 5px; }
.b-green  { background: #d1e7dd; color: #145c2d; }
.b-red    { background: #fee2e2; color: #b91c1c; }
.b-yellow { background: #fff3cd; color: #856404; }
.b-blue   { background: #dbeafe; color: #1d4ed8; }
.b-purple { background: #ede9fe; color: #5b21b6; }
.b-grey   { background: #f1f3f5; color: #6c757d; }

.rank { display: inline-flex; align-items: center; justify-content: center; width: 20px; height: 20px; border-radius: 50%; background: #f1f3f5; font-size: .68rem; font-weight: 700; color: #495057; }
.rank.gold   { background: #fef3c7; color: #92400e; }
.rank.silver { background: #f1f3f5; color: #6b7280; }
.rank.bronze { background: #fde8d8; color: #9a3412; }

.mono { font-family: 'Courier New', monospace; font-size: .78rem; }
.num  { font-variant-numeric: tabular-nums; }

/* ── User avatar ── */
.avatar { width: 28px; height: 28px; border-radius: 50%; background: var(--green-lt); color: var(--green-dk); font-size: .72rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }

/* ── Progress bar ── */
.progress-bar-wrap { background: #f1f3f5; border-radius: 4px; height: 6px; overflow: hidden; margin-top: 4px; }
.progress-bar-fill { height: 100%; border-radius: 4px; background: var(--green); }

/* ── Alert ── */
.analyst-note { background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: .75rem 1rem; font-size: .8rem; color: #78350f; display: flex; gap: .6rem; align-items: flex-start; margin-bottom: 1rem; }
.analyst-note.red { background: #fff1f2; border-color: #fecdd3; color: #9f1239; }
.analyst-note.green { background: #f0fdf4; border-color: #bbf7d0; color: #14532d; }
</style>
@endpush

@section('content')
<div class="an-wrap">

{{-- Header --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.5rem">
    <div>
        <div class="an-title">Business Analytics</div>
        <div class="an-sub">Pharmaceutical wholesale performance &nbsp;·&nbsp; Updated live</div>
    </div>
    <div style="font-size:.75rem;color:#adb5bd">{{ now()->format('d M Y, H:i') }}</div>
</div>

{{-- Analyst alerts --}}
@if($revDiff !== null && $revDiff < -20)
<div class="analyst-note red">
    <i class="bi bi-graph-down-arrow" style="margin-top:1px;flex-shrink:0"></i>
    <span><strong>Revenue down {{ abs($revDiff) }}% vs last month.</strong> Investigate whether this is seasonal, a data gap, or a real decline in sales volume.</span>
</div>
@elseif($revDiff !== null && $revDiff > 20)
<div class="analyst-note green">
    <i class="bi bi-graph-up-arrow" style="margin-top:1px;flex-shrink:0"></i>
    <span><strong>Revenue up {{ $revDiff }}% vs last month.</strong> Strong growth — identify which products and clients are driving it.</span>
</div>
@endif
@if($totalOutstanding > 0 && $ageing['90+'] > 0)
<div class="analyst-note red">
    <i class="bi bi-exclamation-triangle-fill" style="margin-top:1px;flex-shrink:0"></i>
    <span><strong>${{ number_format($ageing['90+'], 2) }} is 90+ days overdue.</strong> Follow up with these customers before extending further credit.</span>
</div>
@endif

{{-- ──────────────────────────────────────────────── --}}
<div class="section-label">Overview — this month</div>

<div class="kpi-grid">
    <div class="kpi">
        <div class="label">Revenue this month</div>
        <div class="value green">${{ number_format($revenueThisMonth, 2) }}</div>
        <div class="trend">
            @if($revDiff !== null)
                <span class="{{ $revDiff >= 0 ? 'up' : 'dn' }}">{{ $revDiff >= 0 ? '▲' : '▼' }} {{ abs($revDiff) }}%</span> vs last month
            @else
                First month of data
            @endif
        </div>
    </div>
    <div class="kpi">
        <div class="label">Invoices this month</div>
        <div class="value">{{ number_format($invoiceCountThis) }}</div>
        <div class="trend">{{ number_format($invoiceCountAll) }} all time</div>
    </div>
    <div class="kpi">
        <div class="label">Avg invoice value</div>
        <div class="value">${{ number_format($avgInvoiceValue, 2) }}</div>
        <div class="trend">Across all invoices</div>
    </div>
    <div class="kpi {{ $totalOutstanding > 0 ? 'danger' : '' }}">
        <div class="label">Outstanding receivables</div>
        <div class="value {{ $totalOutstanding > 0 ? 'red' : 'green' }}">${{ number_format($totalOutstanding, 2) }}</div>
        <div class="trend">Current: ${{ number_format($ageing['current'], 2) }} · 90+: ${{ number_format($ageing['90+'], 2) }}</div>
    </div>
    <div class="kpi">
        <div class="label">Stock retail value</div>
        <div class="value">${{ number_format($stockValue->retail ?? 0, 2) }}</div>
        <div class="trend">Cost: ${{ number_format($stockValue->cost ?? 0, 2) }}</div>
    </div>
    <div class="kpi {{ count($lowStock) > 0 ? 'warn' : '' }}">
        <div class="label">Low stock items</div>
        <div class="value {{ count($lowStock) > 0 ? 'orange' : 'green' }}">{{ count($lowStock) }}</div>
        <div class="trend">Below reorder point</div>
    </div>
</div>

{{-- ──────────────────────────────────────────────── --}}
<div class="section-label">Revenue trend</div>

<div class="chart-row cols-1">
    <div class="card">
        <div class="card-title">Daily Invoiced Revenue — Last 30 Days</div>
        <canvas id="revenueChart" height="65"></canvas>
    </div>
</div>

<div class="chart-row cols-2">
    <div class="card">
        <div class="card-title">6-Month Invoiced vs Credited</div>
        <canvas id="monthlyChart" height="160"></canvas>
    </div>
    <div class="card">
        <div class="card-title">Receivables Ageing</div>
        <canvas id="ageingChart" height="160"></canvas>
    </div>
</div>

{{-- ──────────────────────────────────────────────── --}}
<div class="section-label">Order pipelines</div>

<div class="chart-row cols-2">
    <div class="card">
        <div class="card-title">Sales Order Pipeline</div>
        <table class="tbl">
            <thead><tr><th>Status</th><th class="num">Count</th></tr></thead>
            <tbody>
                @foreach($soStatuses as $status)
                <tr>
                    <td><span class="b b-blue">{{ ucfirst($status) }}</span></td>
                    <td class="num">{{ $soPipeline[$status] ?? 0 }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card">
        <div class="card-title">Purchase Order Status</div>
        <table class="tbl">
            <thead><tr><th>Status</th><th class="num">Count</th></tr></thead>
            <tbody>
                @forelse($poStatuses as $status => $cnt)
                <tr>
                    <td><span class="b b-blue">{{ ucfirst($status) }}</span></td>
                    <td class="num">{{ $cnt }}</td>
                </tr>
                @empty
                <tr><td colspan="2" style="text-align:center;color:#adb5bd;padding:1rem">No purchase orders yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ──────────────────────────────────────────────── --}}
<div class="section-label">Product &amp; client intelligence</div>

<div class="chart-row cols-2">
    <div class="card">
        <div class="card-title">Top 10 Products by Revenue <span style="font-size:.65rem;color:#adb5bd;font-weight:400">(invoiced)</span></div>
        <div style="overflow-x:auto">
        <table class="tbl">
            <thead><tr><th>#</th><th>Product</th><th class="num">Qty Sold</th><th class="num">Revenue</th></tr></thead>
            <tbody>
                @foreach($topProducts as $i => $p)
                <tr>
                    <td><span class="rank {{ $i == 0 ? 'gold' : ($i == 1 ? 'silver' : ($i == 2 ? 'bronze' : '')) }}">{{ $i+1 }}</span></td>
                    <td style="max-width:200px">{{ Str::limit($p->product_description, 35) }}</td>
                    <td class="num">{{ number_format($p->qty_sold) }}</td>
                    <td class="num" style="font-weight:700;color:var(--green)">${{ number_format($p->revenue, 2) }}</td>
                </tr>
                @endforeach
                @if(collect($topProducts)->isEmpty())
                <tr><td colspan="4" style="text-align:center;color:#adb5bd;padding:1.5rem">No invoiced products yet</td></tr>
                @endif
            </tbody>
        </table>
        </div>
    </div>
    <div class="card">
        <div class="card-title">Top 10 Clients by Revenue</div>
        <div style="overflow-x:auto">
        <table class="tbl">
            <thead><tr><th>Client</th><th class="num">Invoices</th><th class="num">Revenue</th></tr></thead>
            <tbody>
                @forelse($topClients as $c)
                <tr>
                    <td>{{ $c->name }}</td>
                    <td class="num">{{ $c->cnt }}</td>
                    <td class="num" style="font-weight:700;color:var(--green)">${{ number_format($c->total, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="3" style="text-align:center;color:#adb5bd;padding:1.5rem">No invoiced clients yet</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>

{{-- ──────────────────────────────────────────────── --}}
<div class="section-label">Stock control</div>

<div class="chart-row cols-2">
    <div class="card">
        <div class="card-title">Restock Recommendations <span class="badge {{ count($lowStock) > 0 ? 'b-yellow' : 'b-green' }}">{{ count($lowStock) }} items</span></div>
        <div style="overflow-x:auto">
        <table class="tbl">
            <thead><tr><th>Code</th><th>Product</th><th class="num">Qty</th><th class="num">Reorder Qty</th><th class="num">Unit Price</th></tr></thead>
            <tbody>
                @forelse($lowStock as $s)
                <tr>
                    <td class="mono">{{ $s->product_code }}</td>
                    <td>{{ Str::limit($s->product_description, 28) }}</td>
                    <td><span class="b {{ $s->quantity == 0 ? 'b-red' : 'b-yellow' }}">{{ $s->quantity == 0 ? 'OUT' : $s->quantity }}</span></td>
                    <td class="num"><span class="b b-blue">{{ $s->reorder_qty }}</span></td>
                    <td class="num">${{ number_format($s->selling_price, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center;color:#198754;padding:1.5rem">✓ All stock levels healthy</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
    <div class="card">
        <div class="card-title">Expiry Watch <span class="badge {{ count($expiringSoon) > 0 ? 'b-yellow' : 'b-green' }}">{{ count($expiringSoon) }} batches</span></div>
        <div style="overflow-x:auto">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Batch</th>
                    <th>Product</th>
                    <th class="num">Expiry</th>
                    <th class="num">Days Left</th>
                    <th class="num">Qty</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expiringSoon as $item)
                    @php $daysLeft = \Carbon\Carbon::parse($item->expiry_date)->diffInDays(now(), false); @endphp
                    <tr>
                        <td class="mono">{{ $item->product_code }}</td>
                        <td class="mono">{{ $item->batch_number }}</td>
                        <td>{{ Str::limit($item->product_description, 28) }}</td>
                        <td class="num">{{ \Carbon\Carbon::parse($item->expiry_date)->toDateString() }}</td>
                        <td class="num"><span class="b {{ $daysLeft <= 30 ? 'b-red' : 'b-yellow' }}">{{ $daysLeft }}</span></td>
                        <td class="num">{{ $item->qty_on_hand }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;color:#198754;padding:1.5rem">✓ No batches due to expire in the next 90 days</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Inter','Segoe UI',sans-serif";
Chart.defaults.font.size   = 11;
Chart.defaults.color       = '#6c757d';

const GREEN = '#198754', GREEN2 = '#20c997', RED = '#dc3545', ORANGE = '#fd7e14', BLUE = '#0d6efd', PURPLE = '#6f42c1';

// ── Revenue 30 days ──
new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
        labels: @json($revenueLabels),
        datasets: [{
            label: 'Revenue',
            data: @json($revenueData),
            borderColor: GREEN, backgroundColor: 'rgba(25,135,84,.07)',
            borderWidth: 2.5, pointRadius: 2.5, pointBackgroundColor: GREEN,
            fill: true, tension: .4
        }]
    },
    options: {
        responsive: true, plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { maxTicksLimit: 10 } },
            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.04)' }, ticks: { callback: v => '$'+v.toLocaleString() } }
        }
    }
});

// ── 6-month invoiced vs credited ──
new Chart(document.getElementById('monthlyChart'), {
    type: 'bar',
    data: {
        labels: @json($monthly6Labels),
        datasets: [
            { label: 'Invoiced',  data: @json($invoicedByMonth), backgroundColor: GREEN, borderRadius: 4 },
            { label: 'Credited',  data: @json($creditedByMonth), backgroundColor: RED,   borderRadius: 4 },
        ]
    },
    options: {
        responsive: true, interaction: { mode: 'index' },
        plugins: { legend: { position: 'bottom', labels: { padding: 12, boxWidth: 10 } } },
        scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.04)' }, ticks: { callback: v => '$'+v.toLocaleString() } }
        }
    }
});

// ── Receivables ageing doughnut ──
new Chart(document.getElementById('ageingChart'), {
    type: 'doughnut',
    data: {
        labels: ['Current', '30 Days', '60 Days', '90+ Days'],
        datasets: [{
            data: [{{ $ageing['current'] }}, {{ $ageing['30'] }}, {{ $ageing['60'] }}, {{ $ageing['90+'] }}],
            backgroundColor: [GREEN, ORANGE, '#fd7e14', RED], borderWidth: 2, borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom', labels: { padding: 12, boxWidth: 10 } } }
    }
});
</script>
@endpush
