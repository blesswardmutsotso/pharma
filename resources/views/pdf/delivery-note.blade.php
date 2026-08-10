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
    .signatures .sig-block { width: 30%; }
    .signatures .sig-line { border-bottom: 1px solid #212529; height: 30px; margin-bottom: 4px; }
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
                <br>
                @if (config('company.phone_mobile') ?: config('company.phone_sales') ?: config('company.phone')) Tel: {{ config('company.phone_mobile') ?: config('company.phone_sales') ?: config('company.phone') }} @endif
                @if (config('company.email_sales') ?: config('company.email')) &nbsp;·&nbsp; {{ config('company.email_sales') ?: config('company.email') }} @endif
            </div>
        </div>
    </div>
    <div>
        <div class="doc-title">DELIVERY NOTE</div>
        <div class="doc-number">{{ $deliveryNote->delivery_note_no }}</div>
    </div>
</div>

<div class="meta-grid">
    <div class="meta-box">
        <div class="label">Deliver To</div>
        <div><strong>{{ $deliveryNote->client?->name }}</strong></div>
        <div>{{ $deliveryNote->client?->fullAddress() }}</div>
    </div>
    <div class="meta-box" style="text-align:right;">
        <div><span class="label">Delivery Date:</span> {{ $deliveryNote->delivery_date?->format('Y-m-d') }}</div>
        <div><span class="label">Sales Order:</span> {{ $deliveryNote->salesOrder?->so_number }}</div>
        <div><span class="label">Delivering Branch:</span> {{ $deliveryNote->branch?->name ?? '—' }}</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Product</th>
            <th>Batch Number</th>
            <th>Expiry</th>
            <th class="text-end">Qty</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($deliveryNote->items as $item)
            <tr>
                <td>{{ $item->product_code }} — {{ $item->product_description }}</td>
                <td>{{ $item->batch_number ?? '—' }}</td>
                <td>{{ $item->expiry_date?->format('Y-m-d') ?? '—' }}</td>
                <td class="text-end">{{ $item->qty }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

@include('pdf.partials.banking-details')

<div class="signatures">
    <div class="sig-block">
        <div class="sig-line"></div>
        Delivered By: {{ $deliveryNote->deliveredBy?->name ?? '______________________' }}
    </div>
    <div class="sig-block">
        <div class="sig-line"></div>
        Received By (Name &amp; Signature)
    </div>
    <div class="sig-block">
        <div class="sig-line"></div>
        Date
    </div>
</div>

</body>
</html>
