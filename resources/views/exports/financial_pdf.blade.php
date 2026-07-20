<!DOCTYPE html>
<html>
<head>
    <title>Financial Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; }
    </style>
</head>
<body>
    <h2>Financial Report</h2>
    <p><strong>Report Type:</strong> {{ $reportType }}</p>
    <p><strong>Period:</strong> {{ $startDate }} to {{ $endDate }}</p>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Invoice No</th>
                <th>User</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $index => $sale)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $sale->created_at->format('Y-m-d') }}</td>
                    <td>{{ $sale->invoice_no }}</td>
                    <td>{{ $sale->user->name ?? 'N/A' }}</td>
                    <td>{{ number_format($sale->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
