<!DOCTYPE html>
<html>
<head>
    <title>Payment Receipt #{{ $payment->id }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .receipt-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .company-info {
            text-align: right;
        }
        .receipt-details {
            margin-bottom: 20px;
        }
        .receipt-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .receipt-details th, .receipt-details td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .total {
            text-align: right;
            font-weight: bold;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 0.8em;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="receipt-header">
        <div class="logo">
            <h1>{{ config('app.name') }}</h1>
        </div>
        <div class="company-info">
            <strong>Receipt #{{ $payment->id }}</strong><br>
            Date: {{ $payment->created_at->format('F d, Y') }}<br>
            Time: {{ $payment->created_at->format('H:i:s') }}
        </div>
    </div>

    <div class="receipt-details">
        <h2>Payment Details</h2>
        <table>
            <tr>
                <th>Customer Name</th>
                <td>{{ $user->name }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $user->email }}</td>
            </tr>
            <tr>
                <th>Payment Type</th>
                <td>{{ $payment->description ?? 'General Payment' }}</td>
            </tr>
            <tr>
                <th>Payment Method</th>
                <td>{{ $payment->payment_method ?? 'Not Specified' }}</td>
            </tr>
            <tr>
                <th>Transaction ID</th>
                <td>{{ $payment->transaction_id ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>
                    <strong style="color: {{ $payment->status == 'success' ? 'green' : 'red' }}">
                        {{ ucfirst($payment->status) }}
                    </strong>
                </td>
            </tr>
            <tr>
                <th>Amount</th>
                <td>{{ number_format($payment->amount, 2) }} KES</td>
            </tr>
        </table>
    </div>

    <div class="total">
        <h3>Total Paid: {{ number_format($payment->amount, 2) }} KES</h3>
    </div>

    <div class="footer">
        <p>
            Thank you for your payment. This is an official receipt.<br>
            Generated on {{ now()->format('F d, Y H:i:s') }}
        </p>
    </div>
</body>
</html>
