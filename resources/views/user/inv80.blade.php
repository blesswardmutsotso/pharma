<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            font-weight: bold;
            color: #000;
            margin: 0;
            padding: 0;
            width: 80mm;
        }
        .receipt-header {
            text-align: center;
            font-size: 18px;
            font-weight: 800;
            color: #000;
        }
        .receipt-details {
            margin-top: 10px;
        }
        .receipt-details td {
            padding: 5px;
            font-weight: bold;
            color: #000;
        }
        .receipt-footer {
            margin-top: 20px;
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="receipt-header">
        <p>Receipt</p>
        <p>Invoice No: {{ $receipt->invoice_no }}</p>
    </div>
    <table class="receipt-details" width="100%">
        <tr>
            <td>Receipt Type:</td>
            <td>{{ $receipt->receipt_type }}</td>
        </tr>
        <tr>
            <td>Currency:</td>
            <td>{{ $receipt->receipt_currency }}</td>
        </tr>
        <tr>
            <td>Total:</td>
            <td>{{ number_format($receipt->receipt_total, 2) }}</td>
        </tr>
        <tr>
            <td>Date:</td>
            <td>{{ $receipt->receipt_date }}</td>
        </tr>
    </table>
    <div class="receipt-footer">
        <p>Thank you for your business!</p>
    </div>
</body>
</html>
