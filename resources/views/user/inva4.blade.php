<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            font-weight: bold;
            color: #000;
            margin: 0 auto;
            width: 210mm;
            padding: 20px;
        }
        .invoice-container {
            width: 100%;
            border: 2px solid #000;
            padding: 20px;
        }
        .header {
            text-align: center;
            font-size: 22px;
            font-weight: 800;
            color: #000;
        }
        .sub-header {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            color: #111;
            margin-bottom: 20px;
        }
        .line {
            border-top: 2px solid black;
            margin: 10px 0;
        }
        .details {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            font-weight: bold;
            color: #000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table, th, td {
            border: 1px solid #000;
            text-align: left;
            padding: 8px;
            font-weight: bold;
            color: #000;
        }
        th {
            background: #e0e0e0;
            font-weight: 800;
        }
        .totals {
            text-align: right;
            margin-top: 10px;
            font-weight: bold;
            color: #000;
        }
        .totals div {
            margin-bottom: 5px;
        }
        .footer {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            color: #333;
            margin-top: 20px;
        }
        .right {
            text-align: right;
        }
    </style>
</head>
<body>

    <div class="invoice-container">
        <div class="header">Your Business Name</div>
        <div class="sub-header">Address | Phone: +123 456 789</div>

        <div class="details">
            <div>Date: {{ now()->format('d/m/Y H:i') }}</div>
            <div>Invoice #: {{ $invoice->id }}</div>
        </div>

        <div class="line"></div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Discount</th>
                    <th>Tax</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->description }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td class="right">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="right">{{ number_format($item->discount, 2) }}</td>
                        <td class="right">{{ number_format($item->tax, 2) }}</td>
                        <td class="right">{{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div><strong>Subtotal:</strong> {{ number_format($invoice->total, 2) }}</div>
            @if ($invoice->discount > 0)
                <div><strong>Discount:</strong> -{{ number_format($invoice->discount, 2) }}</div>
            @endif
            @if ($invoice->tax > 0)
                <div><strong>Tax:</strong> {{ number_format($invoice->tax, 2) }}</div>
            @endif
            <div class="line"></div>
            <div><strong>Grand Total:</strong> {{ number_format($invoice->total - $invoice->discount + $invoice->tax, 2) }}</div>
        </div>

        <div class="line"></div>

        <div class="footer">
            <div>Payment Method: {{ strtoupper($invoice->payment_method) }}</div>
            <div>Thank you for your business!</div>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>

</body>
</html>
