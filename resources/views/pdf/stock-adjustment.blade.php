<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #212529; }
    .header { display: flex; justify-content: space-between; border-bottom: 2px solid #198754; padding-bottom: 10px; margin-bottom: 15px; }
    .company { font-size: 18px; font-weight: bold; color: #145c2d; }
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
</style>
</head>
<body>

<div class="header">
    <div>
        <div class="company">{{ config('company.name') }}</div>
        <div style="font-size:9px;color:#6c757d;">{{ config('company.address') }}</div>
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
                <td class="text-end">{{ $item->qty_system }}</td>
                <td class="text-end">{{ $item->qty_counted }}</td>
                <td class="text-end">{{ $item->qty_variance > 0 ? '+' : '' }}{{ $item->qty_variance }}</td>
                <td class="text-end">{{ number_format($item->unit_cost, 2) }}</td>
                <td class="text-end">{{ number_format($item->qty_variance * $item->unit_cost, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="signatures">
    <div>Counted By: ______________________</div>
    <div>Approved By: ______________________</div>
    <div>Date: ______________________</div>
</div>

</body>
</html>
