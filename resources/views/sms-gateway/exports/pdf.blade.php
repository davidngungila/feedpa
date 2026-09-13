<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>SMS Report - FEEDTAN</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.4; margin: 0; padding: 0; }
        .container { width: 100%; padding: 15px; }
        .header { text-align: center; border-bottom: 2px solid #16a34a; padding-bottom: 15px; margin-bottom: 20px; }
        .logo { font-size: 18px; font-weight: 900; color: #16a34a; text-transform: uppercase; }
        .sub-header { font-size: 10px; color: #16a34a; font-weight: bold; margin-top: 2px; text-transform: uppercase; }
        .report-title { font-size: 16px; margin-top: 8px; color: #111; font-weight: 900; background: #f3f4f6; padding: 5px 15px; display: inline-block; border-radius: 4px; }
        .summary-section { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .report-info, .summary-stats { border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px; background: #fff; width: 48%; }
        .summary-stats { background: #f0fdf4; border-color: #bcf0da; }
        .label { font-weight: 800; color: #4b5563; text-transform: uppercase; font-size: 10px; }
        .value { font-weight: 700; color: #111; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #e5e7eb; padding: 6px; text-align: left; word-wrap: break-word; }
        th { background-color: #16a34a; color: white; font-weight: 800; font-size: 9px; text-transform: uppercase; }
        td { font-size: 9px; }
        .badge-recorded { color: #166534; background: #dcfce7; padding: 1px 5px; border-radius: 4px; font-size: 8px; font-weight: bold; text-transform: uppercase; }
        .badge-not { color: #92400e; background: #fef3c7; padding: 1px 5px; border-radius: 4px; font-size: 8px; font-weight: bold; text-transform: uppercase; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #6b7280; border-top: 1px dashed #e5e7eb; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">FeedTan Community Microfinance Group</div>
            <div class="sub-header" style="margin-top:4px;">P.O.Box 7744, Ushirika Sokoine Road, Moshi, Kilimanjaro, Tanzania</div>
            <div class="sub-header">DIGITAL PAYMENT SYSTEM — SMS GATEWAY</div>
            <div class="report-title">SMS REPORT — FULL DETAILS</div>
        </div>

        <div class="summary-section">
            <div class="report-info">
                <div class="label">Report Generated On:</div>
                <div class="value">{{ date('l, d F Y H:i:s') }}</div>
                <div class="label" style="margin-top:8px;">Total Records:</div>
                <div class="value">{{ count($messages) }} SMS</div>
                @if(request()->filled('date_from') || request()->filled('date_to') || request()->filled('start_date') || request()->filled('end_date'))
                    <div class="label" style="margin-top:8px;">Date Range (From → To):</div>
                    <div class="value">
                        {{ request('date_from') ?? request('start_date') ?? '—' }} @if(request('date_from') || request('start_date')) → @endif {{ request('date_to') ?? request('end_date') ?? '—' }}
                    </div>
                @endif
                @if(request()->filled('search') || request()->filled('sender') || request()->filled('device_id') || request()->filled('provider_id') || request()->filled('recorded'))
                    <div class="label" style="margin-top:8px;">Filters:</div>
                    <div class="value" style="font-size:10px;">
                        @if(request('search')) Search: {{ request('search') }}<br>@endif
                        @if(request('sender')) Sender: {{ request('sender') }}<br>@endif
                        @if(request('device_id')) Device ID: {{ request('device_id') }}<br>@endif
                        @if(request('provider_id')) Provider ID: {{ request('provider_id') }}<br>@endif
                        @if(request('recorded') !== null && request('recorded') !== '') Recorded: {{ request('recorded')=='1'?'Recorded':'Not Recorded' }}<br>@endif
                    </div>
                @endif
            </div>
            <div class="summary-stats">
                <div class="label" style="color:#16a34a;">Summary</div>
                <div class="value" style="margin-top:5px;"><span style="color:#166534;">Recorded:</span> {{ collect($messages)->where('is_recorded', true)->count() }}</div>
                <div class="value"><span style="color:#92400e;">Not Recorded:</span> {{ collect($messages)->where('is_recorded', false)->count() }}</div>
                <div class="value"><span style="color:#1d4ed8;">With Comment:</span> {{ collect($messages)->filter(fn($m)=>!empty($m->admin_comment))->count() }}</div>
                <div class="value" style="margin-top:8px; font-size:10px; color:#6b7280;">Full contents include Message Body, Provider, Amount, Ref, Comment, Device, Timestamps</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Device</th>
                    <th>Provider</th>
                    <th>Sender</th>
                    <th style="width:28%;">Message Body</th>
                    <th>Amount</th>
                    <th>Ref / Txn ID</th>
                    <th>Counterparty</th>
                    <th>Recorded</th>
                    <th>Comment</th>
                </tr>
            </thead>
            <tbody>
                @foreach($messages as $sms)
                    <tr>
                        <td>{{ $sms->sms_timestamp ? $sms->sms_timestamp->format('Y-m-d H:i') : ($sms->created_at ? $sms->created_at->format('Y-m-d H:i') : 'N/A') }}</td>
                        <td>{{ $sms->device ? $sms->device->device_code : 'N/A' }}</td>
                        <td>{{ $sms->provider ? $sms->provider->code : ($sms->parsed_data['provider_code'] ?? '—') }}</td>
                        <td>{{ $sms->sender }}</td>
                        <td style="font-size:8px;">{{ $sms->body }}</td>
                        <td style="text-align:right; font-weight:bold;">{{ $sms->smsTransaction && $sms->smsTransaction->amount ? 'TZS '.number_format($sms->smsTransaction->amount,2) : '—' }}</td>
                        <td style="font-family:monospace; font-size:8px;">{{ $sms->smsTransaction && $sms->smsTransaction->reference ? $sms->smsTransaction->reference : '—' }}</td>
                        <td>{{ $sms->smsTransaction && $sms->smsTransaction->counterparty ? $sms->smsTransaction->counterparty : '—' }} @if($sms->smsTransaction && $sms->smsTransaction->counterparty_name)<br><span style="font-size:7px; color:#6b7280;">{{ $sms->smsTransaction->counterparty_name }}</span>@endif</td>
                        <td>
                            @if($sms->is_recorded)
                                <span class="badge-recorded">Recorded</span>
                            @else
                                <span class="badge-not">Not Recorded</span>
                            @endif
                        </td>
                        <td style="font-size:8px;">{{ $sms->admin_comment ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if(count($messages) > 0)
            <div class="footer">
                <strong>FEEDTAN DIGITAL PAYMENT SYSTEM — SMS GATEWAY</strong><br>
                Full SMS contents exported with filters. www.feedtancmg.org • service@feedtancmg.org<br>
                <div style="margin-top:10px; font-size:8px; color:#9ca3af;">Electronically generated • {{ date('Y-m-d H:i:s') }}</div>
            </div>
        @endif
    </div>
</body>
</html>
