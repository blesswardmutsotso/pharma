<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            font-weight: bold;
            color: #000;
        }
        .container {
            width: 100%;
            padding: 20px;
        }
        .receipt-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .receipt-header h2 { font-size: 22px; font-weight: 800; color: #000; }
        .receipt-header p { font-size: 14px; font-weight: bold; color: #111; }
        .qr-code {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="receipt-header">
            <h2>Device Receipt</h2>
            <p>Device Model: {{ $device->deviceModelName }}</p>
            <p>Device ID: {{ $device->deviceID }}</p>
            <p>Receipt Total: ${{ $device->receiptTotal }}</p>
        </div>

        <!-- QR Code -->
        <div class="qr-code">
            <img src="data:image/png;base64,{{ base64_encode($qrImage) }}" alt="QR Code" />
        </div>

        <p>Thank you for your purchase!</p>
    </div>
</body>
</html>
