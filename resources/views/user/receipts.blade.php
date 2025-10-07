<!DOCTYPE html>
<html>
<head>
    <title>User Receipts</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .header { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Receipts for {{ $user->name }}</h1>
        <p>Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>

    @if($receipts->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Transaction ID</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($receipts as $receipt)
                    <tr>
                        <td>{{ $receipt->created_at->format('Y-m-d') }}</td>
                        <td>{{ $receipt->transaction_id }}</td>
                        <td>{{ $receipt->description }}</td>
                        <td>${{ number_format($receipt->amount, 2) }}</td>
                        <td>{{ ucfirst($receipt->status) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No receipts found.</p>
    @endif
</body>
</html>
