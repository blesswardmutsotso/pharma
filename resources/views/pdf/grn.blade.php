<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #212529; }
    .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #b80330; padding-bottom: 10px; margin-bottom: 15px; }
    .company-box { display: flex; align-items: center; gap: 14px; border: 2px solid #b80330; border-radius: 6px; padding: 10px 16px; }
    .company { font-size: 18px; font-weight: bold; color: #b80330; }
    .doc-title { font-size: 20px; font-weight: bold; text-align: right; color: #212529; }
    .doc-number { font-size: 13px; text-align: right; color: #6c757d; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th { background: #f8f9fa; border-bottom: 2px solid #dee2e6; text-align: left; padding: 6px 8px; font-size: 10px; text-transform: uppercase; }
    td { padding: 6px 8px; border-bottom: 1px solid #f1f3f5; }
    .text-end { text-align: right; }
    .meta-grid { display: flex; justify-content: space-between; margin-bottom: 15px; }
    .meta-box { font-size: 11px; }
    .meta-box .label { color: #6c757d; font-size: 9px; text-transform: uppercase; }
    .watermark { position: fixed; top: 40%; left: 15%; font-size: 90px; color: rgba(220,53,69,0.25); transform: rotate(-30deg); font-weight: bold; z-index: -1; }
    .signatures { margin-top: 40px; display: flex; justify-content: space-between; font-size: 10px; }
    .company-logo { width: 150px; height: 62px; object-fit: contain; }
</style>
</head>
<body>

@if ($isDuplicate)
    <div class="watermark">DUPLICATE</div>
@endif

<div class="header">
    <div class="company-box">
        @if (file_exists(public_path('logo.png')))
            <img src="{{ public_path('logo.png') }}" class="company-logo">
        @endif
        <div>
            <div class="company">{{ config('company.name') }}</div>
            <div style="font-size:9px;color:#6c757d;">
                {{ config('company.address') }}
                @if (config('company.tin')) &nbsp;·&nbsp; TIN: {{ config('company.tin') }} @endif
                <br>
                @if (config('company.phone_mobile') ?: config('company.phone_sales') ?: config('company.phone')) Tel: {{ config('company.phone_mobile') ?: config('company.phone_sales') ?: config('company.phone') }} @endif
                @if (config('company.email_sales') ?: config('company.email')) &nbsp;·&nbsp; {{ config('company.email_sales') ?: config('company.email') }} @endif
            </div>
        </div>
    </div>
    <div>
        <div class="doc-title">GOODS RECEIVED NOTE</div>
        <div class="doc-number">{{ $grn->grn_number }}</div>
    </div>
</div>

<div class="meta-grid">
    <div class="meta-box">
        <div class="label">Supplier</div>
        <div><strong>{{ $grn->supplier?->name }}</strong></div>
        <div>{{ $grn->supplier?->address }}</div>
    </div>
    <div class="meta-box" style="text-align:right;">
        <div><span class="label">Received Date:</span> {{ $grn->received_date?->format('Y-m-d') }}</div>
        <div><span class="label">Purchase Order:</span> {{ $grn->purchaseOrder?->po_number ?? '—' }}</div>
        <div><span class="label">Currency:</span> {{ $grn->currency() }}</div>
        <div><span class="label">Status:</span> {{ ucfirst($grn->status) }}</div>
        <div><span class="label">Receiving Branch:</span> {{ $grn->branch?->name ?? '—' }}</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Product</th>
            <th>Batch</th>
            <th>Expiry</th>
            <th class="text-end">Qty Received</th>
            <th class="text-end">Unit Cost</th>
            <th class="text-end">Total</th>
            <th>Condition</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($grn->items as $item)
            <tr>
                <td>{{ $item->product_code }} — {{ $item->product_description }}</td>
                <td>{{ $item->batch_number }}</td>
                <td>{{ $item->expiry_date?->format('Y-m-d') }}</td>
                <td class="text-end">{{ $item->qty_received }}</td>
                <td class="text-end">{{ number_format($item->unit_cost, 2) }}</td>
                <td class="text-end">{{ number_format($item->lineTotal(), 2) }}</td>
                <td>{{ ucfirst($item->status) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5" style="text-align:right;font-weight:bold;">Total ({{ $grn->currency() }})</td>
            <td style="text-align:right;font-weight:bold;">{{ number_format($grn->grandTotal(), 2) }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>

@include('pdf.partials.banking-details')

<div class="signatures">
    <div>Received By: ______________________</div>
    <div>Checked By: ______________________</div>
    <div>Supplier Rep: ______________________</div>
</div>

</body>
</html>
