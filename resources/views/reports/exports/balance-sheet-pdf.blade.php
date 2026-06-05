<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Balance Sheet</title>
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
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 30px;
        }
        .section {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }
        .section-header {
            background-color: #16a34a;
            color: white;
            padding: 10px 15px;
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
            <div class="logo" style="font-size: 18px;">FeedTan Community Microfinance Group</div>
            <div class="sub-header" style="font-size: 10px; margin-top: 4px;">P.O.Box 7744, Ushirika Sokoine Road, Moshi, Kilimanjaro, Tanzania</div>
            <div class="sub-header">DIGITAL PAYMENT SYSTEM</div>
            <div class="report-title">BALANCE SHEET</div>
            <div class="text-sm mt-2">As of {{ $balanceSheet['as_of_date'] }}</div>
        </div>
        
        <div class="grid">
            <!-- Assets -->
            <div class="section">
                <div class="section-header">Assets</div>
                <div class="section-body">
                    @foreach($balanceSheet['assets'] as $asset)
                    <div class="row">
                        <span>{{ $asset['name'] }}</span>
                        <span style="font-family: monospace;">{{ number_format($asset['value'], 2) }}</span>
                    </div>
                    @endforeach
                    <div class="row total-row">
                        <span>Total Assets</span>
                        <span style="font-family: monospace;">{{ number_format($balanceSheet['totals']['assets'], 2) }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Liabilities & Equity -->
            <div class="section">
                <div class="section-header">Liabilities & Equity</div>
                <div class="section-body">
                    @if(!empty($balanceSheet['liabilities']))
                    <h4 style="margin: 10px 0 5px; font-size: 12px;">Liabilities</h4>
                    @foreach($balanceSheet['liabilities'] as $liability)
                    <div class="row">
                        <span>{{ $liability['name'] }}</span>
                        <span style="font-family: monospace;">{{ number_format($liability['value'], 2) }}</span>
                    </div>
                    @endforeach
                    @endif
                    
                    <h4 style="margin: 15px 0 5px; font-size: 12px;">Equity</h4>
                    @foreach($balanceSheet['equity'] as $equity)
                    <div class="row">
                        <span>{{ $equity['name'] }}</span>
                        <span style="font-family: monospace;">{{ number_format($equity['value'], 2) }}</span>
                    </div>
                    @endforeach
                    
                    <div class="row total-row">
                        <span>Total Liabilities & Equity</span>
                        <span style="font-family: monospace;">{{ number_format($balanceSheet['totals']['total_liabilities_equity'], 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>FEEDTAN DIGITAL PAYMENT SYSTEM</strong></p>
            <p>Powered by FeedTan Team</p>
            <p style="color: #16a34a;">www.feedtancmg.org • service@feedtancmg.org</p>
            <p style="font-size: 9px; color: #9ca3af;">
                Report generated on {{ date('Y-m-d H:i:s') }}
            </p>
        </div>
    </div>
</body>
</html>
