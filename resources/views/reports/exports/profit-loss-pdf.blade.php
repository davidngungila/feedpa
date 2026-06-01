<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Profit & Loss Statement</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
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
        .section {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .section-header-green {
            background-color: #16a34a;
            color: white;
            padding: 10px 15px;
            font-weight: bold;
        }
        .section-header-red {
            background-color: #991b1b;
            color: white;
            padding: 10px 15px;
            font-weight: bold;
        }
        .section-header-primary {
            background-color: #16a34a;
            color: white;
            padding: 15px;
            font-weight: bold;
        }
        .section-body {
            padding: 15px;
        }
        .row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
        }
        .total-row {
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
            margin-top: 10px;
            font-weight: bold;
        }
        .net-profit {
            display: flex;
            justify-content: space-between;
            font-size: 16px;
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
            <div class="report-title">PROFIT & LOSS STATEMENT</div>
            <div class="text-sm mt-2">
                For the period {{ $profitLoss['start_date'] }} to {{ $profitLoss['end_date'] }}
            </div>
        </div>
        
        <!-- Revenue -->
        <div class="section">
            <div class="section-header-green">Revenue</div>
            <div class="section-body">
                @foreach($profitLoss['revenue'] as $item)
                <div class="row">
                    <span>{{ $item['name'] }}</span>
                    <span style="font-family: monospace;">{{ number_format($item['amount'], 2) }}</span>
                </div>
                @endforeach
                <div class="row total-row" style="color: #16a34a;">
                    <span>Total Revenue</span>
                    <span style="font-family: monospace;">{{ number_format($profitLoss['totals']['total_revenue'], 2) }}</span>
                </div>
            </div>
        </div>
        
        <!-- Expenses -->
        @if(!empty($profitLoss['expenses']))
        <div class="section">
            <div class="section-header-red">Expenses</div>
            <div class="section-body">
                @foreach($profitLoss['expenses'] as $item)
                <div class="row">
                    <span>{{ $item['name'] }}</span>
                    <span style="font-family: monospace;">{{ number_format($item['amount'], 2) }}</span>
                </div>
                @endforeach
                <div class="row total-row" style="color: #991b1b;">
                    <span>Total Expenses</span>
                    <span style="font-family: monospace;">{{ number_format($profitLoss['totals']['total_expenses'], 2) }}</span>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Net Profit -->
        <div class="section">
            <div class="section-header-primary">
                <div class="net-profit">
                    <span>Net Profit</span>
                    <span style="font-family: monospace; font-size: 20px;">{{ number_format($profitLoss['totals']['net_profit'], 2) }} TZS</span>
                </div>
            </div>
        </div>
        
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
