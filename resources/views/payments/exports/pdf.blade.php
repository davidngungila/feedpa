<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment History Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 18px;
        }
        .header h2 {
            margin: 5px 0 10px 0;
            color: #555;
            font-size: 14px;
        }
        .header p {
            margin: 2px 0;
            color: #666;
            font-size: 10px;
        }
        .report-info {
            margin: 10px 0;
            text-align: left;
            display: inline-block;
        }
        .summary-stats {
            margin: 10px 0;
            padding: 8px;
            background: #f5f5f5;
            border-radius: 5px;
            text-align: left;
            display: inline-block;
            float: right;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            word-wrap: break-word;
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
            margin-top: 20px;
            text-align: center;
            color: #666;
            font-size: 8px;
            clear: both;
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
        </div>
        
        <div class="summary-stats">
            <p><strong>Report Summary:</strong></p>
            <p>Successful: {{ collect($payments)->filter(fn($p) => in_array($p['status'] ?? '', ['SUCCESS', 'SETTLED']))->count() }} | 
               Pending: {{ collect($payments)->filter(fn($p) => in_array($p['status'] ?? '', ['PROCESSING', 'PENDING']))->count() }} | 
               Failed: {{ collect($payments)->filter(fn($p) => in_array($p['status'] ?? '', ['FAILED', 'ERROR']))->count() }}</p>
            <p>Total Amount: {{ number_format(collect($payments)->sum(fn($p) => $p['amount'] ?? 0), 2) }} TZS</p>
        </div>
        <div style="clear: both;"></div>
    </div>

    @php
        $columns = request()->get('columns', ['order_reference', 'transaction_id', 'status', 'amount', 'currency', 'payer_name', 'phone', 'description', 'payment_method', 'created_at']);
        $headers = [];
        foreach($columns as $col) {
            $headers[] = ucwords(str_replace('_', ' ', $col));
        }
    @endphp

    <table>
        <thead>
            <tr>
                @foreach($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
                <tr>
                    @foreach($columns as $col)
                        <td class="{{ $col === 'amount' ? 'text-right' : '' }}">
                            @if($col === 'status')
                                @switch($payment[$col] ?? '')
                                    @case('SUCCESS')
                                    @case('SETTLED')
                                        <span class="status-success">{{ $payment[$col] }}</span>
                                        @break
                                    @case('PROCESSING')
                                    @case('PENDING')
                                        <span class="status-pending">{{ $payment[$col] }}</span>
                                        @break
                                    @case('FAILED')
                                    @case('ERROR')
                                        <span class="status-failed">{{ $payment[$col] }}</span>
                                        @break
                                    @default
                                        {{ $payment[$col] ?? 'N/A' }}
                                @endswitch
                            @elseif($col === 'amount')
                                {{ number_format($payment[$col] ?? 0, 2) }}
                            @elseif($col === 'created_at' || $col === 'updated_at')
                                {{ isset($payment[$col]) ? \Carbon\Carbon::parse($payment[$col])->format('Y-m-d H:i') : 'N/A' }}
                            @else
                                {{ $payment[$col] ?? 'N/A' }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    @if(!empty($payments))
        <div class="footer">
            <p>Report generated by ClickPesa Payment Management System</p>
        </div>
    @endif
</body>
</html>
