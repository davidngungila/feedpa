<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment History Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        .header h2 {
            margin: 5px 0 15px 0;
            color: #555;
            font-size: 20px;
        }
        .header p {
            margin: 3px 0;
            color: #666;
            font-size: 12px;
        }
        .report-info {
            margin: 15px 0;
            text-align: left;
            display: inline-block;
        }
        .summary-stats {
            margin: 15px 0;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 5px;
            text-align: left;
            display: inline-block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .status-success {
            color: #28a745;
            font-weight: bold;
        }
        .status-pending {
            color: #ffc107;
            font-weight: bold;
        }
        .status-failed {
            color: #dc3545;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>ClickPesa Payment Management System</h1>
        <h2>Payment History Report</h2>
        
        <div class="report-info">
            <p><strong>Report Generated:</strong> {{ date('Y-m-d H:i:s') }}</p>
            <p><strong>Total Records:</strong> {{ count($payments) }} transactions</p>
            
            @if(request()->filled('start_date') || request()->filled('end_date'))
                <p><strong>Date Range:</strong> 
                    @if(request()->filled('start_date')) {{ request('start_date') }} @endif
                    @if(request()->filled('start_date') && request()->filled('end_date')) to @endif
                    @if(request()->filled('end_date')) {{ request('end_date') }} @endif
                </p>
            @endif
            
            @if(request()->filled('status'))
                <p><strong>Status Filter:</strong> {{ request('status') }}</p>
            @endif
            
            @if(request()->filled('currency'))
                <p><strong>Currency Filter:</strong> {{ request('currency') }}</p>
            @endif
            
            @if(request()->filled('order_reference'))
                <p><strong>Order Reference:</strong> {{ request('order_reference') }}</p>
            @endif
        </div>
        
        <div class="summary-stats">
            <p><strong>Report Summary:</strong></p>
            <p>Successful: {{ collect($payments)->filter(fn($p) => in_array($p['status'] ?? '', ['SUCCESS', 'SETTLED']))->count() }} | 
               Pending: {{ collect($payments)->filter(fn($p) => in_array($p['status'] ?? '', ['PROCESSING', 'PENDING']))->count() }} | 
               Failed: {{ collect($payments)->filter(fn($p) => in_array($p['status'] ?? '', ['FAILED', 'ERROR']))->count() }}</p>
            <p>Total Amount: {{ number_format(collect($payments)->sum(fn($p) => $p['amount'] ?? 0), 2) }} TZS</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Order Reference</th>
                <th>Transaction ID</th>
                <th>Status</th>
                <th class="text-right">Amount</th>
                <th>Currency</th>
                <th>Phone/Email</th>
                <th>Description</th>
                <th>Payment Method</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
                <tr>
                    <td>{{ $payment['orderReference'] ?? 'N/A' }}</td>
                    <td>{{ $payment['transactionId'] ?? 'N/A' }}</td>
                    <td>
                        @switch($payment['status'] ?? '')
                            @case('SUCCESS')
                            @case('SETTLED')
                                <span class="status-success">{{ $payment['status'] }}</span>
                                @break
                            @case('PROCESSING')
                            @case('PENDING')
                                <span class="status-pending">{{ $payment['status'] }}</span>
                                @break
                            @case('FAILED')
                            @case('ERROR')
                                <span class="status-failed">{{ $payment['status'] }}</span>
                                @break
                            @default
                                {{ $payment['status'] ?? 'N/A' }}
                        @endswitch
                    </td>
                    <td class="text-right">{{ number_format($payment['amount'] ?? 0, 2) }}</td>
                    <td>{{ $payment['currency'] ?? 'TZS' }}</td>
                    <td>{{ $payment['phone'] ?? $payment['email'] ?? 'N/A' }}</td>
                    <td>{{ $payment['description'] ?? 'N/A' }}</td>
                    <td>{{ $payment['paymentMethod'] ?? 'N/A' }}</td>
                    <td>{{ isset($payment['createdAt']) ? \Carbon\Carbon::parse($payment['createdAt'])->format('Y-m-d H:i') : 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if(!empty($payments))
        <div class="footer">
            <p>Report generated by ClickPesa Payment Management System</p>
            <p>Page {{ Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage() }} of {{ ceil(count($payments) / 50) }}</p>
        </div>
    @endif
</body>
</html>
