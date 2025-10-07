<!DOCTYPE html>
<html>
<head>
    <title>Payment Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        .report {
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .summary {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="report">
        <div class="header">
            <h1>Payment Report</h1>
            <p>{{ config('app.name') }}</p>
            <p>Report Period: {{ $start_date->format('M d, Y') }} - {{ $end_date->format('M d, Y') }}</p>
        </div>

        <div class="summary">
            <h2>Summary</h2>
            <p><strong>Total Payments:</strong> {{ $total_payments }}</p>
            <p><strong>Total Revenue:</strong> {{ number_format($total_revenue, 2) }} KES</p>
        </div>

        <h2>Payments by Method</h2>
        <table>
            <thead>
                <tr>
                    <th>Payment Method</th>
                    <th>Number of Payments</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments_by_method as $method => $data)
                    <tr>
                        <td>{{ $method }}</td>
                        <td>{{ $data['count'] }}</td>
                        <td>{{ number_format($data['total'], 2) }} KES</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h2>Detailed Payments</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>User</th>
                    <th>Method</th>
                    <th>Amount</th>
                    <th>Transaction ID</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $payment)
                    <tr>
                        <td>{{ $payment->created_at->format('M d, Y H:i') }}</td>
                        <td>{{ $payment->user->name }}</td>
                        <td>{{ $payment->payment_method }}</td>
                        <td>{{ number_format($payment->amount, 2) }} KES</td>
                        <td>{{ $payment->transaction_id }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            <p>Generated on: {{ now()->format('M d, Y H:i') }}</p>
        </div>
    </div>
</body>
</html>
