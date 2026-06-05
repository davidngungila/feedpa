<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment History Report - FEEDTAN</title>
    <style> 
         body { 
             font-family: 'Helvetica', 'Arial', sans-serif; 
             color: #333; 
             line-height: 1.4; 
             margin: 0; 
             padding: 0; 
         } 
         .container { 
             width: 100%; 
             padding: 15px; 
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
             background: #f3f4f6; 
             padding: 5px 15px; 
             display: inline-block; 
             border-radius: 4px; 
         }
         .summary-section {
             display: flex;
             justify-content: space-between;
             margin-bottom: 20px;
         }
         .report-info, .summary-stats {
             border: 1px solid #e5e7eb;
             border-radius: 6px;
             padding: 12px;
             background: #fff;
             width: 48%;
         }
         .summary-stats {
             background: linear-gradient(to right, #f0fdf4, #ffffff); 
             border: 1px solid #bcf0da;
         }
         .label {
             font-weight: 800; 
             color: #4b5563; 
             text-transform: uppercase; 
             font-size: 10px; 
         }
         .value {
             font-weight: 700; 
             color: #111; 
             font-size: 12px; 
         }
         table {
             width: 100%;
             border-collapse: collapse;
             margin-top: 10px;
         }
         th, td {
             border: 1px solid #e5e7eb;
             padding: 8px;
             text-align: left;
             word-wrap: break-word;
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
         .status-success {
             color: #166534;
             font-weight: bold;
             background: #dcfce7;
             padding: 2px 6px;
             border-radius: 4px;
             font-size: 9px;
             text-transform: uppercase;
         }
         .status-pending {
             color: #92400e;
             font-weight: bold;
             background: #fef3c7;
             padding: 2px 6px;
             border-radius: 4px;
             font-size: 9px;
             text-transform: uppercase;
         }
         .status-failed {
             color: #991b1b;
             font-weight: bold;
             background: #fee2e2;
             padding: 2px 6px;
             border-radius: 4px;
             font-size: 9px;
             text-transform: uppercase;
         }
         .text-right {
             text-align: right;
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
             <div class="report-title">PAYMENT HISTORY REPORT</div> 
         </div> 
        
         <div class="summary-section">
            <div class="report-info">
                <div class="label">Report Generated On:</div>
                <div class="value">{{ date('l, d F Y H:i:s') }}</div>
                
                <div class="label" style="margin-top: 8px;">Total Records:</div>
                <div class="value">{{ count($payments) }} transactions</div>
                
                @if(request()->filled('start_date') || request()->filled('end_date'))
                    <div class="label" style="margin-top: 8px;">Date Range:</div>
                    <div class="value">
                        @if(request()->filled('start_date')) {{ request('start_date') }} @endif
                        @if(request()->filled('start_date') && request()->filled('end_date')) to @endif
                        @if(request()->filled('end_date')) {{ request('end_date') }} @endif
                    </div>
                @endif
            </div>
            
            <div class="summary-stats">
                <div class="label" style="color: #16a34a;">Report Summary</div>
                <div class="value" style="margin-top: 5px;">
                    <span style="color: #166534;">Successful:</span> {{ collect($payments)->filter(fn($p) => in_array($p['status'] ?? '', ['SUCCESS', 'SETTLED']))->count() }}
                </div>
                <div class="value">
                    <span style="color: #92400e;">Pending:</span> {{ collect($payments)->filter(fn($p) => in_array($p['status'] ?? '', ['PROCESSING', 'PENDING']))->count() }}
                </div>
                <div class="value">
                    <span style="color: #991b1b;">Failed:</span> {{ collect($payments)->filter(fn($p) => in_array($p['status'] ?? '', ['FAILED', 'ERROR']))->count() }}
                </div>
                <div class="value" style="margin-top: 8px; font-size: 14px; color: #15803d;">
                    <span style="font-size: 10px; color: #16a34a;">Total Amount:</span>
                    <br>{{ number_format(collect($payments)->sum(fn($p) => $p['amount'] ?? 0), 2) }} TZS
                </div>
            </div>
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
            <strong>FEEDTAN DIGITAL PAYMENT SYSTEM</strong><br>
            Powered by FeedTan Team<br>
            <span style="color: #16a34a;">www.feedtan.co.tz • info@feedtan.co.tz</span><br>
            <div style="margin-top: 10px; font-size: 8px; color: #9ca3af;">
                This document is electronically generated and verified by FEEDTAN DIGITAL PAYMENT SYSTEM.
            </div>
        </div>
    @endif
     </div> 
</body>
</html>