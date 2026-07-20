<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #212529; font-size: 14px; }
        h2 { color: #198754; font-size: 16px; margin: 24px 0 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { text-align: left; padding: 6px 8px; border-bottom: 1px solid #e9ecef; font-size: 13px; }
        th { background: #f8f9fa; }
        .empty { color: #6c757d; font-style: italic; }
    </style>
</head>
<body>
    <p>Good morning — here is today's stock and account summary for LeafLight Pharma.</p>

    <h2>Low Stock ({{ count($lowStock) }})</h2>
    @if (count($lowStock))
        <table>
            <tr><th>SKU</th><th>Product</th><th>On Hand</th><th>Reorder Point</th></tr>
            @foreach ($lowStock as $row)
                <tr><td>{{ $row['code'] }}</td><td>{{ $row['name'] }}</td><td>{{ $row['qty'] }}</td><td>{{ $row['reorder_point'] }}</td></tr>
            @endforeach
        </table>
    @else
        <p class="empty">Nothing below reorder point.</p>
    @endif

    <h2>Batches Expiring Within 90 Days ({{ count($expiringBatches) }})</h2>
    @if (count($expiringBatches))
        <table>
            <tr><th>SKU</th><th>Batch</th><th>Expiry</th><th>Qty</th></tr>
            @foreach ($expiringBatches as $row)
                <tr><td>{{ $row['code'] }}</td><td>{{ $row['batch'] }}</td><td>{{ $row['expiry'] }}</td><td>{{ $row['qty'] }}</td></tr>
            @endforeach
        </table>
    @else
        <p class="empty">No batches expiring soon.</p>
    @endif

    <h2>Overdue Purchase Orders ({{ count($overduePurchaseOrders) }})</h2>
    @if (count($overduePurchaseOrders))
        <table>
            <tr><th>PO Number</th><th>Supplier</th><th>Expected Delivery</th><th>Days Overdue</th></tr>
            @foreach ($overduePurchaseOrders as $row)
                <tr><td>{{ $row['number'] }}</td><td>{{ $row['supplier'] }}</td><td>{{ $row['expected'] }}</td><td>{{ $row['days_overdue'] }}</td></tr>
            @endforeach
        </table>
    @else
        <p class="empty">No overdue purchase orders.</p>
    @endif

    <h2>Overdue Invoices ({{ count($overdueInvoices) }})</h2>
    @if (count($overdueInvoices))
        <table>
            <tr><th>Invoice No.</th><th>Client</th><th>Due Date</th><th>Balance</th><th>Days Overdue</th></tr>
            @foreach ($overdueInvoices as $row)
                <tr><td>{{ $row['number'] }}</td><td>{{ $row['client'] }}</td><td>{{ $row['due'] }}</td><td>{{ $row['balance'] }}</td><td>{{ $row['days_overdue'] }}</td></tr>
            @endforeach
        </table>
    @else
        <p class="empty">No overdue invoices.</p>
    @endif

    <p style="margin-top:24px;color:#6c757d;font-size:12px;">This is an automated daily digest from LeafLight Pharma ERP.</p>
</body>
</html>
