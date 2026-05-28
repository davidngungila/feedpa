<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt - {{ $paymentData['orderReference'] ?? 'N/A' }}</title>
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
        $isSuccessful = in_array($paymentData['status'] ?? '', ['SUCCESS', 'SETTLED']);
        $customerName = $paymentData['customer_name'] ?? $paymentData['customer']['customerName'] ?? 'Anonymous';
        $payerName = $paymentData['payer_name'] ?? $customerName;
        $isPayerDifferent = strtolower($customerName) !== strtolower($payerName);
     @endphp
     <div class="watermark">{{ $isSuccessful ? 'OFFICIAL' : 'PROVISIONAL' }}</div> 
     
     <div class="container"> 
         <div class="header"> 
             <div class="logo">FEEDTAN CMG</div> 
             <div class="sub-header">CLICKPESA PAYMENT MANAGEMENT SYSTEM</div> 
             <div class="receipt-title">OFFICIAL PAYMENT RECEIPT</div> 
         </div> 
 
         <table style="width: 100%; margin-bottom: 15px;"> 
             <tr> 
                 <td> 
                     <div class="label">Order Reference:</div> 
                     <div class="value" style="font-size: 16px; color: #16a34a;">#{{ $paymentData['orderReference'] ?? 'N/A' }}</div> 
                 </td> 
                 <td style="text-align: right;"> 
                     <div class="label">Date Issued:</div> 
                     <div class="value">{{ isset($paymentData['createdAt']) ? \Carbon\Carbon::parse($paymentData['createdAt'])->format('l, d F Y') : date('l, d F Y') }}</div> 
                     <div class="value" style="font-size: 10px; color: #6b7280; font-weight: normal;">Time: {{ isset($paymentData['createdAt']) ? \Carbon\Carbon::parse($paymentData['createdAt'])->format('H:i:s') : date('H:i:s') }}</div> 
                 </td> 
             </tr> 
         </table> 
 
         <div class="info-card"> 
             <table class="details-table"> 
                 <tr> 
                     <td class="label">Received From (Member):</td> 
                     <td class="value" style="font-size: 14px;">{{ strtoupper($customerName) }}</td> 
                     <td rowspan="{{ $isPayerDifferent ? 5 : 4 }}" class="qr-code-box"> 
                         <img src="{{ $qrCodeImage ?? '' }}" style="width: 25mm; height: 25mm;"> 
                     </td> 
                 </tr> 
                 @if($isPayerDifferent)
                 <tr> 
                     <td class="label">Paid By (Payer):</td> 
                     <td class="value">{{ strtoupper($payerName) }}</td> 
                 </tr> 
                 @endif
                 <tr> 
                     <td class="label">Transaction ID:</td> 
                     <td class="value">{{ $paymentData['id'] ?? $paymentData['transaction_id'] ?? 'N/A' }}</td> 
                 </tr> 
                 <tr> 
                     <td class="label">Description:</td> 
                     <td class="value">{{ strtoupper($paymentData['description'] ?? 'PAYMENT TRANSACTION') }}</td> 
                 </tr> 
                 <tr> 
                     <td class="label">Payment Mode:</td> 
                     <td class="value"> 
                         {{ strtoupper($paymentData['channel'] ?? $paymentData['payment_method'] ?? 'N/A') }} 
                         <span class="status-badge {{ $isSuccessful ? 'status-verified' : 'status-pending' }}"> 
                             • {{ $isSuccessful ? 'CONFIRMED' : 'AWAITING CLEARANCE' }} 
                         </span> 
                     </td> 
                 </tr> 
             </table> 
         </div> 
 
         <div class="amount-section"> 
             <div class="amount-label">Authorized Amount</div> 
             <div class="amount-value">{{ $paymentData['collectedCurrency'] ?? $paymentData['currency'] ?? 'TZS' }} {{ number_format($paymentData['collectedAmount'] ?? $paymentData['amount'] ?? 0, 2) }}</div> 
             @php 
                 $amount = $paymentData['collectedAmount'] ?? $paymentData['amount'] ?? 0;
                 $f = new NumberFormatter("en", NumberFormatter::SPELLOUT); 
                 $words = $f->format($amount); 
             @endphp 
             <div class="amount-words">Amount in words: {{ $words }} {{ $paymentData['collectedCurrency'] ?? $paymentData['currency'] ?? 'Tanzanian Shillings' }} Only</div> 
         </div> 
 
         <div style="font-size: 10px; color: #4b5563; padding: 10px; background: #f9fafb; border-radius: 6px; border-left: 3px solid #16a34a;"> 
             <strong>Transaction Reference:</strong> {{ $paymentData['orderReference'] ?? 'N/A' }}<br> 
             @if(isset($paymentData['phone']) || isset($paymentData['paymentPhoneNumber'])) 
                 <strong>Customer Phone:</strong> {{ $paymentData['phone'] ?? $paymentData['paymentPhoneNumber'] ?? 'N/A' }}
             @endif 
             @if(isset($paymentData['notes']) && $paymentData['notes'])
                 <br><strong>System Remarks:</strong> {{ $paymentData['notes'] }}
             @endif
         </div> 
 
         <table class="signature-grid"> 
             <tr> 
                 <td style="text-align: center; width: 50%;"> 
                     <div class="sig-line"></div> 
                     <div class="sig-text">TREASURER / SECRETARY</div> 
                     <div style="font-size: 8px; color: #9ca3af;">(Digital Seal Applied)</div> 
                 </td> 
                 <td style="text-align: center; width: 50%;"> 
                     <div class="sig-line"></div> 
                     <div class="sig-text">MEMBER'S ACKNOWLEDGMENT</div> 
                 </td> 
             </tr> 
         </table> 
 
         <div class="footer"> 
             <strong>FEEDTAN CMG - PAYMENT SYSTEM</strong><br> 
             Powered by ClickPesa<br> 
             <span style="color: #16a34a;">www.feedtan.co.tz • info@feedtan.co.tz</span><br> 
             <div style="margin-top: 10px; font-size: 8px; color: #9ca3af;"> 
                 This document is electronically generated and verified by FEEDTAN CMG Payment System. 
             </div> 
         </div> 
     </div> 
 </body> 
 </html>