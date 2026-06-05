<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Billing Statement</title>
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
        .badge { padding: 2px 5px; border-radius: 4px; font-size: 8px; font-weight: bold; }
        .bg-success { background: #dcfce7; color: #166534; }
        .bg-gray { background: #e5e7eb; color: #4b5563; }
        .footer { margin-top: 30px; text-align: center; font-size: 8px; color: #9ca3af; border-top: 1px dashed #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>FeedTan Community Microfinance Group</h1>
        <h2 style="font-size: 10px; margin-top: 2px; margin-bottom: 8px;">P.O.Box 7744, Ushirika Sokoine Road, Moshi, Kilimanjaro, Tanzania</h2>
        <h2>DIGITAL PAYMENT SYSTEM</h2>
        <h2>Billing Statement</h2>
        <p>Generated on: {{ $date }} | Currency: {{ $currency }}</p>
    </div>

    <table class="stats-table">
        <tr>
            <td>
                <div class="stats-label">Total Bills</div>
                <div class="stats-value">{{ number_format($totalBills) }}</div>
            </td>
            <td>
                <div class="stats-label">Total Amount</div>
                <div class="stats-value text-success">{{ $currency }} {{ number_format($totalAmount, 2) }}</div>
            </td>
            @if($totalPaid > 0)
            <td>
                <div class="stats-label">Total Paid</div>
                <div class="stats-value text-success">{{ $currency }} {{ number_format($totalPaid, 2) }}</div>
            </td>
            @endif
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th width="12%">Date & Time</th>
                <th width="15%">Control Number</th>
                <th width="25%">Description</th>
                <th width="12%">Type</th>
                <th width="12%">Status</th>
                <th width="12%" class="text-right">Amount</th>
                @if($showPaid)
                <th width="12%" class="text-right">Paid</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($bills as $bill)
                <tr>
                    <td>{{ $bill->created_at->format('Y-m-d H:i') }}</td>
                    <td><code>{{ $bill->bill_pay_number }}</code></td>
                    <td>
                        {{ $bill->bill_description ?? 'No Description' }}
                        @if($bill->customer_name)
                            <br><small>Customer: {{ $bill->customer_name }}</small>
                        @endif
                    </td>
                    <td>{{ strtoupper($bill->bill_type) }}</td>
                    <td>
                        @php $status = strtoupper($bill->bill_status ?? 'UNKNOWN'); @endphp
                        <span class="badge {{ $status == 'ACTIVE' ? 'bg-success' : 'bg-gray' }}">
                            {{ $status }}
                        </span>
                    </td>
                    <td class="text-right">{{ $bill->bill_currency }} {{ number_format($bill->bill_amount, 2) }}</td>
                    @if($showPaid)
                    <td class="text-right text-success">{{ $bill->bill_currency }} {{ number_format($bill->total_paid, 2) }}</td>
                    @endif
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
