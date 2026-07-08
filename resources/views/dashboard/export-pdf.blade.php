<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Report - {{ $periodLabel }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #065f46;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #065f46;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
            font-size: 12px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            background: #f9fafb;
        }
        .stat-card h3 {
            margin: 0 0 5px 0;
            font-size: 14px;
            color: #065f46;
        }
        .stat-card .value {
            font-size: 20px;
            font-weight: bold;
            color: #064e3b;
        }
        .stat-card .label {
            color: #666;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #065f46;
            color: white;
        }
        .insights, .recommendations {
            margin-bottom: 20px;
        }
        .insight-item {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 10px;
            background: #f9fafb;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>FeedTan Digital - Dashboard Report</h1>
        <p>Period: {{ $periodLabel }}</p>
        <p>Exported on: {{ $exportDate }}</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Revenue</h3>
            <div class="value">TZS {{ number_format($stats['period_settled_amount'], 0) }}</div>
            <div class="label">Settled payments in period</div>
        </div>
        <div class="stat-card">
            <h3>Successful Transactions</h3>
            <div class="value">{{ number_format($stats['period_successful_count'], 0) }}</div>
            <div class="label">Successful transactions</div>
        </div>
        <div class="stat-card">
            <h3>Success Rate</h3>
            <div class="value">{{ $stats['success_rate'] }}%</div>
            <div class="label">Success rate</div>
        </div>
        <div class="stat-card">
            <h3>Average Transaction</h3>
            <div class="value">TZS {{ number_format($stats['average_transaction_value'], 0) }}</div>
            <div class="label">Average value per transaction</div>
        </div>
    </div>

    @if(!empty($aiInsights['insights']))
    <div class="insights">
        <h2 style="color: #065f46; font-size: 16px; margin-bottom: 10px;">Key Insights</h2>
        @foreach($aiInsights['insights'] as $insight)
        <div class="insight-item">
            <strong>{{ $insight['title'] }}</strong>
            <p>{{ $insight['message'] }}</p>
        </div>
        @endforeach
    </div>
    @endif

    @if(!empty($aiInsights['recommendations']))
    <div class="recommendations">
        <h2 style="color: #065f46; font-size: 16px; margin-bottom: 10px;">Recommendations</h2>
        <ul>
            @foreach($aiInsights['recommendations'] as $rec)
            <li>{{ $rec }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if(!empty($stats['top_customers']))
    <div>
        <h2 style="color: #065f46; font-size: 16px; margin-bottom: 10px;">Top Members</h2>
        <table>
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Phone</th>
                    <th>Count</th>
                    <th>Total (TZS)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stats['top_customers'] as $customer)
                <tr>
                    <td>{{ $customer['name'] }}</td>
                    <td>{{ $customer['phone'] }}</td>
                    <td>{{ $customer['count'] }}</td>
                    <td>{{ number_format($customer['total_amount'], 0) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(!empty($stats['top_purposes']))
    <div>
        <h2 style="color: #065f46; font-size: 16px; margin-bottom: 10px;">Top Payment Purposes</h2>
        <table>
            <thead>
                <tr>
                    <th>Purpose</th>
                    <th>Count</th>
                    <th>Total (TZS)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stats['top_purposes'] as $purpose)
                <tr>
                    <td>{{ $purpose['description'] }}</td>
                    <td>{{ $purpose['count'] }}</td>
                    <td>{{ number_format($purpose['total_amount'], 0) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(!empty($stats['payment_methods']))
    <div>
        <h2 style="color: #065f46; font-size: 16px; margin-bottom: 10px;">Payment Methods</h2>
        <table>
            <thead>
                <tr>
                    <th>Method</th>
                    <th>Count</th>
                    <th>Total (TZS)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stats['payment_methods'] as $method)
                <tr>
                    <td>{{ $method['name'] }}</td>
                    <td>{{ $method['count'] }}</td>
                    <td>{{ number_format($method['total_amount'], 0) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

</body>
</html>