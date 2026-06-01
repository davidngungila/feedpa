<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Trial Balance</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #16a34a;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .logo {
            font-size: 22px;
            font-weight: 900;
            color: #16a34a;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .sub-header {
            font-size: 11px;
            color: #16a34a;
            font-weight: bold;
            margin-top: 2px;
            text-transform: uppercase;
        }
        .report-title {
            font-size: 18px;
            margin-top: 8px;
            color: #111;
            font-weight: 900;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #16a34a;
            color: white;
            font-weight: 800;
            font-size: 11px;
            text-transform: uppercase;
        }
        td {
            font-size: 11px;
        }
        .text-right {
            text-align: right;
        }
        .text-left {
            text-align: left;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px dashed #e5e7eb;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">FEEDTAN</div>
            <div class="sub-header">DIGITAL PAYMENT SYSTEM</div>
            <div class="report-title">TRIAL BALANCE</div>
            @if($startDate || $endDate)
            <div class="text-sm mt-2">
                @if($startDate) From {{ $startDate }} @endif
                @if($endDate) to {{ $endDate }} @endif
            </div>
            @endif
        </div>
        
        <table>
            <thead>
                <tr>
                    <th class="text-left">Account</th>
                    <th class="text-right">Debit (TZS)</th>
                    <th class="text-right">Credit (TZS)</th>
                    <th class="text-right">Balance (TZS)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($trialBalance['accounts'] as $account)
                <tr>
                    <td style="font-weight: bold;">{{ $account['name'] }}</td>
                    <td class="text-right" style="font-family: monospace;">{{ number_format($account['debit'], 2) }}</td>
                    <td class="text-right" style="font-family: monospace;">{{ number_format($account['credit'], 2) }}</td>
                    <td class="text-right" style="font-family: monospace; {{ $account['balance'] >= 0 ? 'color: #16a34a;' : 'color: #991b1b;' }}">
                        {{ number_format($account['balance'], 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: #f3f4f6;">
                    <td style="font-weight: bold;">Total</td>
                    <td class="text-right" style="font-weight: bold; font-family: monospace;">{{ number_format($trialBalance['totals']['debit'], 2) }}</td>
                    <td class="text-right" style="font-weight: bold; font-family: monospace;">{{ number_format($trialBalance['totals']['credit'], 2) }}</td>
                    <td class="text-right" style="font-weight: bold; font-family: monospace;">
                        {{ number_format($trialBalance['totals']['debit'] - $trialBalance['totals']['credit'], 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>
        
        <div class="footer">
            <p><strong>FEEDTAN DIGITAL PAYMENT SYSTEM</strong></p>
            <p>Powered by FeedTan Team</p>
            <p style="color: #16a34a;">www.feedtan.co.tz • info@feedtan.co.tz</p>
            <p style="font-size: 9px; color: #9ca3af;">
                Report generated on {{ date('Y-m-d H:i:s') }}
            </p>
        </div>
    </div>
</body>
</html>
