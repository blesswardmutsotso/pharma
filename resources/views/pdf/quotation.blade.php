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
    .watermark { position: fixed; top: 40%; left: 15%; font-size: 90px; color: rgba(220,53,69,0.25); transform: rotate(-30deg); font-weight: bold; z-index: -1; }
</style>
</head>
<body>

@if ($isDuplicate)
    <div class="watermark">DUPLICATE</div>
@endif

<div class="header">
    <div class="company">LeafLight Pharma Wholesale</div>
    <div>
        <div class="doc-title">QUOTATION</div>
        <div class="doc-number">{{ $quotation->quote_number }}</div>
    </div>
</div>

<div class="meta-grid">
    <div class="meta-box">
        <div class="label">Quote For</div>
        <div><strong>{{ $quotation->client?->name }}</strong></div>
        <div>{{ $quotation->client?->fullAddress() }}</div>
    </div>
    <div class="meta-box" style="text-align:right;">
        <div><span class="label">Quote Date:</span> {{ $quotation->quote_date?->format('Y-m-d') }}</div>
        <div><span class="label">Valid Until:</span> {{ $quotation->valid_until?->format('Y-m-d') ?? '—' }}</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Product</th>
            <th class="text-end">Qty</th>
            <th class="text-end">Unit Price</th>
            <th class="text-end">Discount</th>
            <th class="text-end">Line Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($quotation->items as $item)
            <tr>
                <td>{{ $item->product_code }} — {{ $item->product_description }}</td>
                <td class="text-end">{{ $item->qty }}</td>
                <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-end">{{ number_format($item->discount, 2) }}</td>
                <td class="text-end">{{ number_format($item->line_total, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot class="totals">
        <tr><td colspan="4" class="text-end">Total</td><td class="text-end">{{ number_format($quotation->items->sum('line_total'), 2) }}</td></tr>
    </tfoot>
</table>

<p style="margin-top:20px;font-size:10px;color:#6c757d;">This quotation is valid until the date shown above. Prices are subject to stock availability at time of order confirmation.</p>

</body>
</html>
