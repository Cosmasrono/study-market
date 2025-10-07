<!DOCTYPE html>
<html>
<head>
    <title>User Comprehensive Report</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .header { text-align: center; margin-bottom: 20px; }
        .stats { background-color: #f2f2f2; padding: 15px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Comprehensive Report for {{ $user->name }}</h1>
        <p>Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>

    <div class="stats">
        <h2>Account Statistics</h2>
        <p><strong>Membership Status:</strong> {{ $stats['membership_status'] }}</p>
        <p><strong>Subscription Type:</strong> {{ $stats['subscription_type'] }}</p>
        <p><strong>Total Payments:</strong> {{ $stats['total_payments'] }}</p>
        <p><strong>Total Amount Spent:</strong> ${{ number_format($stats['total_spent'], 2) }}</p>
        <p><strong>Exam Attempts:</strong> {{ $stats['exam_attempts'] }}</p>
        <p><strong>Successful Exams:</strong> {{ $stats['successful_exams'] }}</p>
    </div>

    <h2>Recent Transactions</h2>
    @if($transactions->count() > 0)
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
                @foreach($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->created_at->format('Y-m-d') }}</td>
                        <td>{{ $transaction->transaction_id }}</td>
                        <td>{{ $transaction->description }}</td>
                        <td>${{ number_format($transaction->amount, 2) }}</td>
                        <td>{{ ucfirst($transaction->status) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No recent transactions found.</p>
    @endif

    <h2>Recent Exam Attempts</h2>
    @if($examAttempts->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Exam</th>
                    <th>Score</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($examAttempts as $attempt)
                    <tr>
                        <td>{{ $attempt->created_at->format('Y-m-d') }}</td>
                        <td>{{ $attempt->exam->name }}</td>
                        <td>{{ number_format($attempt->score_percentage, 2) }}%</td>
                        <td>
                            <span class="{{ $attempt->score_percentage >= 50 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $attempt->score_percentage >= 50 ? 'Passed' : 'Failed' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No recent exam attempts found.</p>
    @endif
</body>
</html>
