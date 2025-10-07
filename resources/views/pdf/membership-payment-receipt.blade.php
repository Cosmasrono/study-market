<!DOCTYPE html>
<html>
<head>
    <title>Membership Payment Receipt</title>
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
            text-align: center;
            border-bottom: 2px solid #8C1C13;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .receipt-header h1 {
            color: #8C1C13;
            margin: 0;
        }
        .receipt-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .payment-info {
            width: 100%;
        }
        .payment-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .payment-info th, .payment-info td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .payment-info th {
            background-color: #f2f2f2;
        }
        .total {
            text-align: right;
            font-weight: bold;
            margin-top: 20px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 0.8em;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="receipt-header">
        <h1>Membership Payment Receipt</h1>
        <p>Inzoberi School of Professionals</p>
    </div>

    <div class="receipt-details">
        <div class="payment-info">
            <table>
                <tr>
                    <th>Transaction Details</th>
                    <th>Member Information</th>
                </tr>
                <tr>
                    <td>
                        <strong>Transaction ID:</strong> {{ $payment->transaction_id }}<br>
                        <strong>Payment Date:</strong> {{ $payment->paid_at->format('M d, Y H:i A') }}<br>
                        <strong>Payment Method:</strong> {{ ucfirst($payment->payment_method) }}<br>
                        <strong>Subscription:</strong> {{ $payment->formatted_duration }}<br>
                        <strong>Status:</strong> <span style="color: green;">{{ ucfirst($payment->status) }}</span>
                    </td>
                    <td>
                        <strong>Name:</strong> {{ $user->name }}<br>
                        <strong>Email:</strong> {{ $user->email }}<br>
                        <strong>Phone:</strong> {{ $user->phone ?? 'N/A' }}<br>
                        <strong>Membership Expires:</strong> {{ $user->subscription_end_date->format('M d, Y') }}
                    </td>
                </tr>
            </table>

            <div class="total">
                <h3>Total Paid: KES {{ number_format($payment->amount, 2) }}</h3>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>This is a computer-generated receipt. No signature required.</p>
        <p>&copy; {{ date('Y') }} Inzoberi School of Professionals. All Rights Reserved.</p>
    </div>
</body>
</html>
