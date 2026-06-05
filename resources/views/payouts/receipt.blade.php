<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payout Receipt - {{ $payoutData['orderReference'] ?? 'N/A' }}</title>
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
             padding: 10px; 
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
         .receipt-title { 
             font-size: 18px; 
             margin-top: 8px; 
             color: #111; 
             font-weight: 900; 
             background: #f3f4f6; 
             padding: 5px; 
             display: inline-block; 
             border-radius: 4px; 
         } 
         .watermark { 
             position: absolute; 
             top: 50%; 
             left: 50%; 
             transform: translate(-50%, -50%) rotate(-45deg); 
             font-size: 100px; 
             color: rgba(22, 163, 74, 0.05); 
             z-index: -1; 
             font-weight: bold; 
             white-space: nowrap; 
         } 
         .details-table { 
             width: 100%; 
             border-collapse: collapse; 
             margin-bottom: 20px; 
         } 
         .details-table td { 
             padding: 6px 0; 
             vertical-align: top; 
         } 
         .label { 
             font-weight: 800; 
             color: #4b5563; 
             width: 130px; 
             text-transform: uppercase; 
             font-size: 10px; 
         } 
         .value { 
             font-weight: 700; 
             color: #111; 
             font-size: 12px; 
         } 
         .amount-section { 
             background: linear-gradient(to right, #f0fdf4, #ffffff); 
             border: 1px solid #bcf0da; 
             padding: 15px; 
             margin-bottom: 20px; 
             border-radius: 8px; 
             position: relative; 
         } 
         .amount-label { 
             font-size: 10px; 
             color: #16a34a; 
             font-weight: 900; 
             text-transform: uppercase; 
             margin-bottom: 3px; 
         } 
         .amount-value { 
             font-size: 28px; 
             font-weight: 900; 
             color: #15803d; 
         } 
         .amount-words { 
             font-size: 11px; 
             font-style: italic; 
             color: #6b7280; 
             margin-top: 5px; 
             text-transform: capitalize; 
         } 
         .info-grid { 
             width: 100%; 
             margin-bottom: 20px; 
         } 
         .info-card { 
             border: 1px solid #e5e7eb; 
             border-radius: 6px; 
             padding: 12px; 
             background: #fff; 
         } 
         .qr-code-box { 
             text-align: right; 
         } 
         .status-badge { 
             display: inline-block; 
             padding: 3px 8px; 
             border-radius: 9999px; 
             font-size: 9px; 
             font-weight: 900; 
             text-transform: uppercase; 
         } 
         .status-verified { background: #dcfce7; color: #166534; } 
         .status-pending { background: #fef3c7; color: #92400e; } 
         .status-failed { background: #fee2e2; color: #991b1b; } 
         .footer { 
             margin-top: 40px; 
             text-align: center; 
             font-size: 10px; 
             color: #6b7280; 
             border-top: 1px dashed #e5e7eb; 
             padding-top: 15px; 
         } 
         .signature-grid { 
             margin-top: 50px; 
             width: 100%; 
         } 
         .sig-line { 
             border-top: 1px solid #374151; 
             width: 160px; 
             margin: 0 auto 5px; 
         } 
         .sig-text { 
             font-size: 9px; 
             font-weight: bold; 
             color: #4b5563; 
         } 
     </style> 
 </head> 
 <body> 
     @php
        $isSuccessful = in_array($payoutData['status'] ?? '', ['SUCCESS', 'SETTLED']);
        $isFailed = in_array($payoutData['status'] ?? '', ['FAILED', 'ERROR', 'CANCELLED']);
        $recipientName = $payoutData['recipient_name'] ?? $payoutData['beneficiary']['accountName'] ?? 'Anonymous';
     @endphp
     <div class="watermark">{{ $isSuccessful ? 'OFFICIAL' : ($isFailed ? 'CANCELLED' : 'PROVISIONAL') }}</div> 
     
     <div class="container"> 
         <div class="header"> 
             <div class="logo" style="font-size: 18px;">FeedTan Community Microfinance Group</div> 
             <div class="sub-header" style="font-size: 10px; margin-top: 4px;">P.O.Box 7744, Ushirika Sokoine Road, Moshi, Kilimanjaro, Tanzania</div> 
             <div class="sub-header">DIGITAL PAYMENT SYSTEM</div> 
             <div class="receipt-title">OFFICIAL PAYOUT RECEIPT</div> 
         </div> 

         <table style="width: 100%; margin-bottom: 15px;"> 
             <tr> 
                 <td> 
                     <div class="label">Order Reference:</div> 
                     <div class="value" style="font-size: 16px; color: #16a34a;">#{{ $payoutData['orderReference'] ?? 'N/A' }}</div> 
                 </td> 
                 <td style="text-align: right;"> 
                     <div class="label">Date Issued:</div> 
                     <div class="value">{{ isset($payoutData['createdAt']) ? \Carbon\Carbon::parse($payoutData['createdAt'])->format('l, d F Y') : date('l, d F Y') }}</div> 
                     <div class="value" style="font-size: 10px; color: #6b7280; font-weight: normal;">Time: {{ isset($payoutData['createdAt']) ? \Carbon\Carbon::parse($payoutData['createdAt'])->format('H:i:s') : date('H:i:s') }}</div> 
                 </td> 
             </tr> 
         </table> 

         <div class="info-card"> 
             <table class="details-table"> 
                 <tr> 
                     <td class="label">Paid To (Beneficiary):</td> 
                     <td class="value" style="font-size: 14px;">{{ strtoupper($recipientName) }}</td> 
                     <td rowspan="6" class="qr-code-box"> 
                         <img src="{{ $qrCodeImage ?? '' }}" style="width: 25mm; height: 25mm;"> 
                     </td> 
                 </tr> 
                 <tr> 
                     <td class="label">Transaction ID:</td> 
                     <td class="value">{{ $payoutData['id'] ?? $payoutData['transaction_id'] ?? 'N/A' }}</td> 
                 </tr> 
                 <tr> 
                     <td class="label">Description:</td> 
                     <td class="value">{{ strtoupper((!empty($payoutData['description']) && $payoutData['description'] !== 'N/A' ? $payoutData['description'] : ($payoutData['notes'] ?? 'Malipo kutoka FEEDTAN'))) }}</td> 
                 </tr> 
                 <tr> 
                     <td class="label">Payout Mode:</td> 
                     <td class="value"> 
                         {{ strtoupper($payoutData['channel'] ?? $payoutData['channel_provider'] ?? $payoutData['payout_type'] ?? 'N/A') }} 
                         <span class="status-badge {{ $isSuccessful ? 'status-verified' : ($isFailed ? 'status-failed' : 'status-pending') }}"> 
                             • {{ $isSuccessful ? 'CONFIRMED' : ($isFailed ? 'FAILED' : 'AWAITING CLEARANCE') }} 
                         </span> 
                     </td> 
                 </tr> 
                 @if(!empty($payoutData['beneficiary']['accountNumber']))
                 <tr> 
                     <td class="label">Account Number:</td> 
                     <td class="value">{{ $payoutData['beneficiary']['accountNumber'] ?? 'N/A' }}</td> 
                 </tr> 
                 @endif
                 @if(!empty($payoutData['beneficiary']['beneficiaryMobileNumber']))
                 <tr> 
                     <td class="label">Mobile Number:</td> 
                     <td class="value">{{ $payoutData['beneficiary']['beneficiaryMobileNumber'] ?? $payoutData['recipient_phone'] ?? 'N/A' }}</td> 
                 </tr> 
                 @endif
             </table> 
         </div> 

         <div class="amount-section"> 
             <div class="amount-label">Payout Amount</div> 
             <div class="amount-value">{{ $payoutData['currency'] ?? 'TZS' }} {{ number_format($payoutData['amount'] ?? 0, 2) }}</div> 
             @if(!empty($payoutData['fee']))
             <div style="margin-top: 8px; font-size: 11px; color: #6b7280;">Transaction Fee: {{ $payoutData['currency'] ?? 'TZS' }} {{ number_format($payoutData['fee'], 2) }}</div>
             @endif
             @php 
                 $amount = $payoutData['amount'] ?? 0;
                 $f = new NumberFormatter("en", NumberFormatter::SPELLOUT); 
                 $words = $f->format($amount); 
             @endphp 
             <div class="amount-words">Amount in words: {{ $words }} {{ $payoutData['currency'] ?? 'Tanzanian Shillings' }} Only</div> 
         </div> 

         <div style="font-size: 10px; color: #4b5563; padding: 10px; background: #f9fafb; border-radius: 6px; border-left: 3px solid #16a34a;"> 
             <strong>Payout Reference:</strong> {{ $payoutData['orderReference'] ?? 'N/A' }}<br> 
             @if(isset($payoutData['recipient_phone']) || isset($payoutData['beneficiary']['beneficiaryMobileNumber'])) 
                 <strong>Beneficiary Phone:</strong> {{ $payoutData['recipient_phone'] ?? $payoutData['beneficiary']['beneficiaryMobileNumber'] ?? 'N/A' }}
             @endif 
             @if(isset($payoutData['beneficiary']['beneficiaryEmail'])) 
                 <br><strong>Beneficiary Email:</strong> {{ $payoutData['beneficiary']['beneficiaryEmail'] ?? 'N/A' }}
             @endif 
             @if(isset($payoutData['notes']) && $payoutData['notes'])
                 <br><strong>System Remarks:</strong> {{ $payoutData['notes'] }}
             @endif
         </div> 

         <table class="signature-grid"> 
             <tr> 
                 <td style="text-align: center; width: 50%;"> 
                     @php
                        $signImagePath = public_path('sign.png');
                        $signImageData = file_get_contents($signImagePath);
                        $signImageBase64 = 'data:image/png;base64,' . base64_encode($signImageData);
                    @endphp
                    <img src="{{ $signImageBase64 }}" style="width: 120px; height: 50px; object-fit: contain; margin-bottom: 5px;">
                     <div class="sig-line"></div> 
                     <div class="sig-text">TREASURER / SECRETARY</div> 
                     <div style="font-size: 8px; color: #9ca3af;">(Digital Seal Applied)</div> 
                 </td> 
                 <td style="text-align: center; width: 50%;"> 
                     <div class="sig-line"></div> 
                     <div class="sig-text">BENEFICIARY'S ACKNOWLEDGMENT</div> 
                 </td> 
             </tr> 
         </table> 

         <div class="footer">
            <strong>FEEDTAN DIGITAL PAYMENT SYSTEM</strong><br>
            Powered by FeedTan Team<br>
            <span style="color: #16a34a;">www.feedtan.co.tz • info@feedtan.co.tz</span><br>
            <div style="margin-top: 10px; font-size: 8px; color: #9ca3af;">
                This document is electronically generated and verified by FEEDTAN DIGITAL PAYMENT SYSTEM.
            </div>
        </div> 
     </div> 
 </body> 
 </html>
