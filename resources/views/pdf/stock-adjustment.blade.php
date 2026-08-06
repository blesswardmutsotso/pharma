<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #212529; }
    .header { display: flex; justify-content: space-between; border-bottom: 2px solid #b80330; padding-bottom: 10px; margin-bottom: 15px; }
    .company { font-size: 18px; font-weight: bold; color: #b80330; }
    .doc-title { font-size: 20px; font-weight: bold; text-align: right; color: #212529; }
    .doc-number { font-size: 13px; text-align: right; color: #6c757d; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th { background: #f8f9fa; border-bottom: 2px solid #dee2e6; text-align: left; padding: 6px 8px; font-size: 10px; text-transform: uppercase; }
    td { padding: 6px 8px; border-bottom: 1px solid #f1f3f5; }
    .text-end { text-align: right; }
    .totals td { border: none; font-weight: bold; }
    .meta-grid { display: flex; justify-content: space-between; margin-bottom: 15px; }
    .meta-box { font-size: 11px; }
    .meta-box .label { color: #6c757d; font-size: 9px; text-transform: uppercase; }
    .signatures { margin-top: 40px; display: flex; justify-content: space-between; font-size: 10px; }
    .company-logo { width: 150px; height: 62px; object-fit: contain; }
</style>
</head>
<body>

<div class="header">
    <div style="display:flex;align-items:center;gap:12px;">
        @if (file_exists(public_path('logo.png')))
            <img src="{{ public_path('logo.png') }}" class="company-logo">
        @endif
        <div>
            <div class="company">{{ config('company.name') }}</div>
            <div style="font-size:9px;color:#6c757d;">
                {{ config('company.address') }}
                <br>
                @if (config('company.phone_sales') ?: config('company.phone')) Tel: {{ config('company.phone_sales') ?: config('company.phone') }} @endif
                @if (config('company.phone_mobile')) &nbsp;·&nbsp; Mobile: {{ config('company.phone_mobile') }} @endif
                @if (config('company.email_sales') ?: config('company.email')) &nbsp;·&nbsp; {{ config('company.email_sales') ?: config('company.email') }} @endif
            </div>
        </div>
    </div>
    <div>
        <div class="doc-title">STOCK ADJUSTMENT</div>
        <div class="doc-number">{{ $adjustment->adjustment_no }}</div>
    </div>
</div>

<div class="meta-grid">
    <div class="meta-box">
        <div class="label">Type</div>
        <div><strong>{{ $adjustment->typeLabel() }}</strong></div>
        <div>{{ $adjustment->reason ?? '—' }}</div>
    </div>
    <div class="meta-box" style="text-align:right;">
        <div><span class="label">Branch:</span> {{ $adjustment->branch?->name ?? 'Not location-specific' }}</div>
        <div><span class="label">Status:</span> {{ ucfirst($adjustment->status) }}</div>
        <div><span class="label">Approved By:</span> {{ $adjustment->approvedBy?->name ?? '—' }}</div>
        <div><span class="label">Approved At:</span> {{ $adjustment->approved_at?->format('Y-m-d H:i') ?? '—' }}</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Product</th>
            <th>Batch</th>
            <th>Expiry</th>
            <th class="text-end">System Qty</th>
            <th class="text-end">Counted Qty</th>
            <th class="text-end">Variance</th>
            <th class="text-end">Unit Cost</th>
            <th class="text-end">Value Impact</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($adjustment->items as $item)
            <tr>
                <td>{{ $item->product_code }} — {{ $item->product_description }}</td>
                <td>{{ $item->batch_number ?? '—' }}</td>
                <td>{{ $item->stockBatch?->expiry_date?->format('Y-m-d') ?? '—' }}</td>
                <td class="text-end">{{ $item->qty_system }}</td>
                <td class="text-end">{{ $item->qty_counted }}</td>
                <td class="text-end">{{ $item->qty_variance > 0 ? '+' : '' }}{{ $item->qty_variance }}</td>
                <td class="text-end">{{ number_format($item->unit_cost, 2) }}</td>
                <td class="text-end">{{ number_format($item->valueImpact(), 2) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot class="totals">
        <tr>
            <td colspan="7" style="text-align:right;">Net Value Impact</td>
            <td class="text-end">{{ number_format($adjustment->netValueImpact(), 2) }}</td>
        </tr>
    </tfoot>
</table>

@include('pdf.partials.banking-details')

<div class="signatures">
    <div>Counted By: ______________________</div>
    <div>Approved By: ______________________</div>
    <div>Date: ______________________</div>
</div>

</body>
</html>
