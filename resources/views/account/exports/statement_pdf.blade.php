<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Account Statement - {{ strtoupper($tab) }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; margin: 10px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #16a34a; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #333; font-size: 18px; }
        .header h2 { margin: 5px 0; color: #16a34a; font-size: 14px; }
        .stats-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #f9fafb; }
        .stats-table td { padding: 10px; border: 1px solid #e5e7eb; }
        .stats-label { font-weight: bold; color: #4b5563; text-transform: uppercase; font-size: 8px; }
        .stats-value { font-size: 14px; font-weight: bold; color: #111; }
        table.main-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.main-table th, table.main-table td { border: 1px solid #ddd; padding: 6px; text-align: left; word-wrap: break-word; }
        table.main-table th { background-color: #f3f4f6; font-weight: bold; }
        .text-right { text-align: right; }
        .text-success { color: #16a34a; }
        .text-danger { color: #dc2626; }
        .badge { padding: 2px 5px; border-radius: 4px; font-size: 8px; font-weight: bold; }
        .bg-success { background: #dcfce7; color: #166534; }
        .bg-warning { background: #fef3c7; color: #92400e; }
        .bg-danger { background: #fee2e2; color: #991b1b; }
        .footer { margin-top: 30px; text-align: center; font-size: 8px; color: #9ca3af; border-top: 1px dashed #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>FeedTan Community Microfinance Group</h1>
        <h2 style="font-size: 10px; margin-top: 2px; margin-bottom: 8px;">P.O.Box 7744, Ushirika Sokoine Road, Moshi, Kilimanjaro, Tanzania</h2>
        <h2>DIGITAL PAYMENT SYSTEM</h2>
        <h2>Account Statement ({{ strtoupper($tab) }} SOURCE)</h2>
        <p>Generated on: {{ $date }} | Currency: {{ $currency }}</p>
    </div>

    <table class="stats-table">
        <tr>
            <td>
                <div class="stats-label">Total Transactions</div>
                <div class="stats-value">{{ number_format($stats['total']) }}</div>
            </td>
            <td>
                <div class="stats-label">Total Credits</div>
                <div class="stats-value text-success">{{ number_format($stats['total_credits'], 2) }}</div>
            </td>
            <td>
                <div class="stats-label">Total Debits</div>
                <div class="stats-value text-danger">{{ number_format($stats['total_debits'], 2) }}</div>
            </td>
            <td>
                <div class="stats-label">Successful</div>
                <div class="stats-value">{{ number_format($stats['successful']) }}</div>
            </td>
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th width="12%">Date & Time</th>
                <th width="15%">Reference</th>
                <th width="33%">Description / Purpose</th>
                <th width="15%">Payer Info</th>
                <th width="10%">Status</th>
                <th width="15%" class="text-right">Amount ({{ $currency }})</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $t)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($t['date'])->format('Y-m-d H:i') }}</td>
                    <td><code>{{ $t['reference'] ?? 'N/A' }}</code></td>
                    <td>{{ $t['description'] ?? 'N/A' }}</td>
                    <td>
                        {{ $t['payer_name'] ?? 'N/A' }}<br>
                        <small>{{ $t['phone'] ?? '' }}</small>
                    </td>
                    <td>
                        @php $status = strtoupper($t['status'] ?? 'UNKNOWN'); @endphp
                        <span class="badge {{ in_array($status, ['SUCCESS', 'SETTLED']) ? 'bg-success' : (in_array($status, ['PENDING', 'PROCESSING']) ? 'bg-warning' : 'bg-danger') }}">
                            {{ $status }}
                        </span>
                    </td>
                    <td class="text-right {{ ($t['entry'] ?? 'CREDIT') == 'DEBIT' ? 'text-danger' : 'text-success' }}">
                        {{ ($t['entry'] ?? 'CREDIT') == 'DEBIT' ? '-' : '+' }} {{ number_format($t['amount'], 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This is an electronically generated document from FEEDTAN DIGITAL PAYMENT SYSTEM.</p>
        <p>© {{ date('Y') }} FEEDTAN DIGITAL PAYMENT SYSTEM. Powered by FeedTan Team.</p>
    </div>
</body>
</html>
