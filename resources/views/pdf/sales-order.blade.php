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
    <div style="display:flex;align-items:center;gap:12px;">
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
        <div class="doc-title">INVOICE</div>
        <div class="doc-number">{{ $salesOrder->so_number }}</div>
    </div>
</div>

<div class="meta-grid">
    <div class="meta-box">
        <div class="label">Client</div>
        <div><strong>{{ $salesOrder->client?->name }}</strong></div>
        <div>{{ $salesOrder->client?->fullAddress() }}</div>
    </div>
    <div class="meta-box" style="text-align:right;">
        <div><span class="label">Order Date:</span> {{ $salesOrder->order_date?->format('Y-m-d') }}</div>
        <div><span class="label">Required Date:</span> {{ $salesOrder->required_date?->format('Y-m-d') ?? '—' }}</div>
        <div><span class="label">Client PO Number:</span> {{ $salesOrder->client_po_number ?? '—' }}</div>
        <div><span class="label">Currency:</span> {{ $salesOrder->currency }}</div>
        <div><span class="label">Status:</span> {{ ucfirst($salesOrder->status) }}</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Product</th>
            <th class="text-end">Qty Ordered</th>
            <th class="text-end">Unit Price</th>
            <th class="text-end">Discount</th>
            <th class="text-end">Line Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($salesOrder->items as $item)
            <tr>
                <td>{{ $item->product_code }} — {{ $item->product_description }}</td>
                <td class="text-end">{{ $item->qty_ordered }}</td>
                <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-end">{{ number_format($item->discount, 2) }}</td>
                <td class="text-end">{{ number_format($item->line_total, 2) }}</td>
            </tr>
            @foreach ($item->batchAllocations as $allocation)
                <tr style="font-size:9px;color:#6c757d;">
                    <td colspan="4">Batch {{ $allocation->stockBatch->batch_number }} (exp {{ $allocation->stockBatch->expiry_date?->format('Y-m-d') }})</td>
                    <td class="text-end">{{ $allocation->qty_allocated }} units</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
    <tfoot class="totals">
        <tr><td colspan="4" class="text-end">Total ({{ $salesOrder->currency }})</td><td class="text-end">{{ number_format($salesOrder->items->sum('line_total'), 2) }}</td></tr>
    </tfoot>
</table>

@include('pdf.partials.banking-details')

@if ($salesOrder->notes)
    <p style="margin-top:15px;font-size:10px;"><strong>Notes:</strong> {{ $salesOrder->notes }}</p>
@endif

<div class="signatures">
    <div>Authorised By: {{ $salesOrder->confirmedBy?->name ?? $salesOrder->createdBy?->name ?? '______________________' }}</div>
    <div>Date: {{ $salesOrder->order_date?->format('Y-m-d') }}</div>
</div>

</body>
</html>
