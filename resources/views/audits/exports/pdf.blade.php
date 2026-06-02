<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Audit Logs Report - FEEDTAN</title>
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
         .action-login {
             color: #166534;
             font-weight: bold;
             background: #dcfce7;
             padding: 2px 6px;
             border-radius: 4px;
             font-size: 9px;
             text-transform: uppercase;
         }
         .action-logout {
             color: #4b5563;
             font-weight: bold;
             background: #f3f4f6;
             padding: 2px 6px;
             border-radius: 4px;
             font-size: 9px;
             text-transform: uppercase;
         }
         .action-login-failed {
             color: #991b1b;
             font-weight: bold;
             background: #fee2e2;
             padding: 2px 6px;
             border-radius: 4px;
             font-size: 9px;
             text-transform: uppercase;
         }
         .action-other {
             color: #1e40af;
             font-weight: bold;
             background: #dbeafe;
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
             <div class="logo">FEEDTAN</div> 
             <div class="sub-header">DIGITAL PAYMENT SYSTEM</div> 
             <div class="report-title">AUDIT LOGS REPORT</div> 
         </div> 
        
         <div class="summary-section">
            <div class="report-info">
                <div class="label">Report Generated On:</div>
                <div class="value">{{ date('l, d F Y H:i:s') }}</div>
                
                <div class="label" style="margin-top: 8px;">Total Records:</div>
                <div class="value">{{ count($audits) }} log entries</div>
                
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
                <div class="label" style="color: #16a34a;">Log Type Summary</div>
                <div class="value" style="margin-top: 5px;">
                    <span style="color: #166534;">Login:</span> {{ collect($audits)->filter(fn($a) => $a['action'] === 'login')->count() }}
                </div>
                <div class="value">
                    <span style="color: #4b5563;">Logout:</span> {{ collect($audits)->filter(fn($a) => $a['action'] === 'logout')->count() }}
                </div>
                <div class="value">
                    <span style="color: #991b1b;">Failed Login:</span> {{ collect($audits)->filter(fn($a) => $a['action'] === 'login_failed')->count() }}
                </div>
                <div class="value">
                    <span style="color: #1e40af;">Other:</span> {{ collect($audits)->filter(fn($a) => !in_array($a['action'], ['login', 'logout', 'login_failed']))->count() }}
                </div>
            </div>
        </div>

    <table>
        <thead>
            <tr>
                <th>Date & Time</th>
                <th>User</th>
                <th>Action</th>
                <th>Details</th>
                <th>IP Address</th>
                <th>Location</th>
                <th>Device</th>
            </tr>
        </thead>
        <tbody>
            @foreach($audits as $audit)
                <tr>
                    <td>{{ isset($audit['created_at']) ? \Carbon\Carbon::parse($audit['created_at'])->format('Y-m-d H:i:s') : 'N/A' }}</td>
                    <td>{{ $audit['user_name'] }}<br><span style="color: #6b7280; font-size:9px;">{{ $audit['user_email'] }}</span></td>
                    <td>
                        @php
                            $actionClass = match(true) {
                                $audit['action'] === 'login' => 'action-login',
                                $audit['action'] === 'logout' => 'action-logout',
                                $audit['action'] === 'login_failed' => 'action-login-failed',
                                default => 'action-other'
                            };
                        @endphp
                        <span class="{{ $actionClass }}">{{ ucwords(str_replace('_', ' ', $audit['action'])) }}</span>
                    </td>
                    <td>{{ $audit['details'] ?? 'N/A' }}</td>
                    <td>{{ $audit['ip_address'] ?? 'N/A' }}</td>
                    <td>{{ ($audit['city'] ? $audit['city'] . ', ' : '') . ($audit['country'] ?? 'N/A') }}</td>
                    <td>{{ $audit['device_type'] ?? 'N/A' }}<br><span style="color: #6b7280; font-size:9px;">{{ $audit['device_browser'] ?? '' }} / {{ $audit['device_platform'] ?? '' }}</span></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if(!empty($audits))
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
