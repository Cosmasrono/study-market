<!DOCTYPE html>
<html>
<head>
    <title>Membership Report</title>
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
        .chart {
            width: 100%;
            height: 200px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="report">
        <div class="header">
            <h1>Membership Report</h1>
            <p>{{ config('app.name') }}</p>
            <p>Report Period: {{ $start_date->format('M d, Y') }} - {{ $end_date->format('M d, Y') }}</p>
        </div>

        <div class="summary">
            <h2>Membership Overview</h2>
            <p><strong>Total Users:</strong> {{ $total_users }}</p>
            <p><strong>Active Memberships:</strong> {{ $active_memberships }}</p>
            <p><strong>Expired Memberships:</strong> {{ $expired_memberships }}</p>
        </div>

        <h2>Detailed User Membership Status</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Membership Status</th>
                    <th>Expires At</th>
                    <th>Total Payments</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($user->hasMembership())
                                <span style="color: green;">Active</span>
                            @elseif($user->membershipExpired())
                                <span style="color: red;">Expired</span>
                            @else
                                <span style="color: gray;">No Membership</span>
                            @endif
                        </td>
                        <td>
                            {{ $user->membership_expires_at ? $user->membership_expires_at->format('M d, Y') : 'N/A' }}
                        </td>
                        <td>
                            {{ $user->membershipPayments->count() }} 
                            ({{ number_format($user->membershipPayments->sum('amount'), 2) }} KES)
                        </td>
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
