<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Customer Report</title>
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
        .filters {
            margin-bottom: 15px;
            font-size: 11px;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #16a34a;
            color: white;
            font-weight: 800;
            font-size: 10px;
            text-transform: uppercase;
        }
        td {
            font-size: 10px;
        }
        .text-right {
            text-align: right;
        }
        .text-left {
            text-align: left;
        }
        .status-settled {
            color: #16a34a;
            font-weight: bold;
        }
        .status-failed {
            color: #991b1b;
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
        .total-section {
            margin-top: 15px;
            padding: 10px;
            background-color: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo" style="font-size: 18px;">FeedTan Community Microfinance Group</div>
            <div class="sub-header" style="font-size: 10px; margin-top: 4px;">P.O.Box 7744, Ushirika Sokoine Road, Moshi, Kilimanjaro, Tanzania</div>
            <div class="sub-header">DIGITAL PAYMENT SYSTEM</div>
            <div class="report-title">CUSTOMER REPORT</div>
        </div>
        
        <div class="filters">
            @if($customerName)
                <p><strong>Customer:</strong> {{ $customerName }}</p>
            @endif
            @if($phone)
                <p><strong>Phone:</strong> {{ $phone }}</p>
            @endif
            @if($startDate || $endDate)
                <p>
                    @if($startDate) <strong>From:</strong> {{ $startDate }} @endif
                    @if($endDate) <strong>To:</strong> {{ $endDate }} @endif
                </p>
            @endif
        </div>
        
        <div class="total-section">
            <p><strong>Total Settled Amount:</strong> TZS {{ number_format($totalAmount, 2) }}</p>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th style="width: 12%;">Date</th>
                    <th style="width: 12%;">Reference</th>
                    <th style="width: 15%;">Customer Name</th>
                    <th style="width: 15%;">Payer Name</th>
                    <th style="width: 10%;">Phone</th>
                    <th style="width: 18%;">Description</th>
                    <th style="width: 8%;" class="text-right">Amount</th>
                    <th style="width: 5%;">Currency</th>
                    <th style="width: 5%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $payment)
                <tr>
                    <td>{{ $payment->created_at->format('Y-m-d H:i') }}</td>
                    <td style="font-family: monospace;">{{ $payment->order_reference }}</td>
                    <td>{{ $payment->customer_name ?? $payment->payer_name }}</td>
                    <td>{{ $payment->payer_name }}</td>
                    <td style="font-family: monospace;">{{ $payment->phone }}</td>
                    <td>{{ $payment->description }}</td>
                    <td class="text-right" style="font-family: monospace;">{{ number_format($payment->amount, 2) }}</td>
                    <td>{{ $payment->currency ?? 'TZS' }}</td>
                    <td class="{{ in_array(strtoupper($payment->status), ['SETTLED', 'SUCCESS']) ? 'status-settled' : (in_array(strtoupper($payment->status), ['FAILED', 'ERROR']) ? 'status-failed' : '') }}">
                        {{ $payment->status }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
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
