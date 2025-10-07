<!DOCTYPE html>
<html>
<head>
    <title>Payment Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        .receipt {
            max-width: 600px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .receipt-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .payment-info {
            margin-top: 20px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 0.8em;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <h1>Payment Receipt</h1>
            <p>{{ config('app.name') }}</p>
        </div>

        <div class="receipt-details">
            <div>
                <strong>Receipt Number:</strong> {{ $receipt_number }}<br>
                <strong>Date:</strong> {{ $generated_at->format('M d, Y H:i') }}
            </div>
            <div>
                <strong>Customer Details</strong><br>
                {{ $user->name }}<br>
                {{ $user->email }}
            </div>
        </div>

        <div class="payment-info">
            <h2>Payment Details</h2>
            <table width="100%">
                <tr>
                    <td><strong>Payment Method:</strong></td>
                    <td>{{ $payment->payment_method }}</td>
                </tr>
                <tr>
                    <td><strong>Amount Paid:</strong></td>
                    <td>{{ number_format($payment->amount, 2) }} KES</td>
                </tr>
                <tr>
                    <td><strong>Transaction ID:</strong></td>
                    <td>{{ $payment->transaction_id }}</td>
                </tr>
                <tr>
                    <td><strong>Payment Date:</strong></td>
                    <td>{{ $payment->created_at->format('M d, Y H:i') }}</td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <p>Thank you for your payment. This is a computer-generated receipt.</p>
        </div>
    </div>
</body>
</html>
