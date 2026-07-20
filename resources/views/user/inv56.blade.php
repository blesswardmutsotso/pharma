<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            font-weight: bold;
            color: #000;
            max-width: 56mm;
            margin: 0 auto;
            text-align: center;
        }
        .receipt {
            width: 100%;
            padding: 2px;
        }
        .title {
            font-size: 14px;
            font-weight: 800;
            color: #000;
        }
        .line {
            border-top: 1px dashed #333;
            margin: 3px 0;
        }
        .item {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            font-weight: bold;
            color: #000;
        }
        .total {
            font-weight: 800;
            color: #000;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="title">Your Business Name</div>
        <div>Address Line 1</div>
        <div>Phone: +123 456 789</div>
        <div class="line"></div>

        <div>Date: {{ now()->format('d/m/Y H:i') }}</div>
        <div>Receipt #: {{ $invoice->id }}</div>
        <div class="line"></div>

        @foreach($invoice->items as $item)
            <div class="item">
                <span>{{ Str::limit($item->description, 10) }}</span>
                <span>{{ $item->quantity }} x {{ number_format($item->unit_price, 2) }}</span>
                <span>{{ number_format($item->total, 2) }}</span>
            </div>
        @endforeach

        <div class="line"></div>
        <div class="item total">
            <span>Subtotal:</span>
            <span>{{ number_format($invoice->total, 2) }}</span>
        </div>
        @if ($invoice->discount > 0)
            <div class="item">
                <span>Discount:</span>
                <span>-{{ number_format($invoice->discount, 2) }}</span>
            </div>
        @endif
        @if ($invoice->tax > 0)
            <div class="item">
                <span>Tax:</span>
                <span>{{ number_format($invoice->tax, 2) }}</span>
            </div>
        @endif
        <div class="line"></div>
        <div class="item total">
            <span>Total:</span>
            <span>{{ number_format($invoice->total - $invoice->discount + $invoice->tax, 2) }}</span>
        </div>
        <div class="line"></div>
        
        <div>Payment: {{ strtoupper($invoice->payment_method) }}</div>
        <div class="line"></div>
        
        <div>Thank you!</div>
        <div>Visit Again</div>
        <div class="line"></div>
        
        <div style="font-size: 9px;">Powered by Your Company</div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
