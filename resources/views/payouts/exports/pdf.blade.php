<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payout History Report</title>
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
        <h1>FEEDTAN DIGITAL PAYMENT SYSTEM</h1>
        <h2>Payout History Report</h2>
        
        <div class="report-info">
            <p><strong>Report Generated:</strong> {{ date('Y-m-d H:i:s') }}</p>
            <p><strong>Total Records:</strong> {{ count($payouts) }} payouts</p>
            
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
            <p>Successful: {{ collect($payouts)->filter(fn($p) => in_array($p['status'] ?? '', ['SUCCESS', 'SETTLED']))->count() }} | 
               Pending: {{ collect($payouts)->filter(fn($p) => !in_array($p['status'] ?? '', ['SUCCESS', 'SETTLED', 'FAILED', 'ERROR', 'CANCELLED']))->count() }} | 
               Failed: {{ collect($payouts)->filter(fn($p) => in_array($p['status'] ?? '', ['FAILED', 'ERROR', 'CANCELLED']))->count() }}</p>
            <p>Total Amount: {{ number_format(collect($payouts)->sum(fn($p) => $p['amount'] ?? 0), 2) }} TZS</p>
        </div>
        <div style="clear: both;"></div>
    </div>

    @php
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
            @foreach($payouts as $payout)
                <tr>
                    @foreach($columns as $col)
                        <td class="{{ $col === 'amount' || $col === 'fee' ? 'text-right' : '' }}">
                            @if($col === 'status')
                                @switch($payout[$col] ?? '')
                                    @case('SUCCESS')
                                    @case('SETTLED')
                                        <span class="status-success">{{ $payout[$col] }}</span>
                                        @break
                                    @case('PROCESSING')
                                    @case('PENDING')
                                        <span class="status-pending">{{ $payout[$col] }}</span>
                                        @break
                                    @case('FAILED')
                                    @case('ERROR')
                                    @case('CANCELLED')
                                        <span class="status-failed">{{ $payout[$col] }}</span>
                                        @break
                                    @default
                                        {{ $payout[$col] ?? 'N/A' }}
                                @endswitch
                            @elseif($col === 'amount' || $col === 'fee')
                                {{ number_format($payout[$col] ?? 0, 2) }}
                            @elseif($col === 'created_at' || $col === 'updated_at')
                                {{ isset($payout[$col]) ? \Carbon\Carbon::parse($payout[$col])->format('Y-m-d H:i') : 'N/A' }}
                            @else
                                {{ $payout[$col] ?? 'N/A' }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    @if(!empty($payouts))
        <div class="footer">
            <p>Report generated by FEEDTAN DIGITAL PAYMENT SYSTEM</p>
        </div>
    @endif
</body>
</html>
