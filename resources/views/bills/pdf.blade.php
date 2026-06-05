<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bill - {{ $bill->bill_pay_number }}</title>
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
         .bill-title {
             font-size: 18px;
             margin-top: 8px;
             color: #111;
             font-weight: 900;
             background: #f3f4f6;
             padding: 5px 15px;
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
         .status-active { background: #dcfce7; color: #166534; }
         .status-inactive { background: #fee2e2; color: #991b1b; }
         .status-order { background: #dbeafe; color: #1e40af; }
         .status-customer { background: #ede9fe; color: #5b21b6; }
         .footer {
             margin-top: 40px;
             text-align: center;
             font-size: 10px;
             color: #6b7280;
             border-top: 1px dashed #e5e7eb;
             padding-top: 15px;
         }
     </style>
 </head>
 <body>
     <div class="watermark">{{ $bill->bill_status === 'ACTIVE' ? 'OFFICIAL' : 'PROVISIONAL' }}</div>

     <div class="container">
         <div class="header">
             <div class="logo" style="font-size: 18px;">FeedTan Community Microfinance Group</div>
             <div class="sub-header" style="font-size: 10px; margin-top: 4px;">P.O.Box 7744, Ushirika Sokoine Road, Moshi, Kilimanjaro, Tanzania</div>
             <div class="sub-header">DIGITAL PAYMENT SYSTEM</div>
             <div class="bill-title">OFFICIAL BILL</div>
         </div>

         <table style="width: 100%; margin-bottom: 15px;">
             <tr>
                 <td>
                     <div class="label">Control Number:</div>
                     <div class="value" style="font-size: 16px; color: #16a34a;">{{ $bill->bill_pay_number }}</div>
                 </td>
                 <td style="text-align: right;">
                     <div class="label">Date Issued:</div>
                     <div class="value">{{ $bill->created_at->format('l, d F Y') }}</div>
                     <div class="value" style="font-size: 10px; color: #6b7280; font-weight: normal;">Time: {{ $bill->created_at->format('H:i:s') }}</div>
                 </td>
             </tr>
         </table>

         <div class="info-card">
             <table class="details-table">
                 <tr>
                     <td class="label">Bill Type:</td>
                     <td class="value">
                         {{ strtoupper($bill->bill_type) }}
                         <span class="status-badge {{ $bill->bill_type === 'order' ? 'status-order' : 'status-customer' }}">
                             • {{ $bill->bill_type === 'order' ? 'ORDER' : 'CUSTOMER' }}
                         </span>
                     </td>
                     <td rowspan="{{ $bill->bill_type === 'customer' ? 6 : 4 }}" class="qr-code-box">
                         <img src="{{ $qrCodeImage }}" style="width: 25mm; height: 25mm;">
                     </td>
                 </tr>
                 <tr>
                     <td class="label">Description:</td>
                     <td class="value">{{ strtoupper($bill->bill_description) }}</td>
                 </tr>
                 <tr>
                     <td class="label">Payment Mode:</td>
                     <td class="value">{{ str_replace('_', ' ', $bill->bill_payment_mode) }}</td>
                 </tr>
                 <tr>
                     <td class="label">Status:</td>
                     <td class="value">
                         {{ strtoupper($bill->bill_status) }}
                         <span class="status-badge {{ $bill->bill_status === 'ACTIVE' ? 'status-active' : 'status-inactive' }}">
                             • {{ $bill->bill_status === 'ACTIVE' ? 'ACTIVE' : 'INACTIVE' }}
                         </span>
                     </td>
                 </tr>
                 @if($bill->bill_type === 'customer')
                     @if($bill->customer_name)
                     <tr>
                         <td class="label">Customer Name:</td>
                         <td class="value" style="font-size: 14px;">{{ strtoupper($bill->customer_name) }}</td>
                     </tr>
                     @endif
                     @if($bill->customer_phone)
                     <tr>
                         <td class="label">Customer Phone:</td>
                         <td class="value">{{ $bill->customer_phone }}</td>
                     </tr>
                     @endif
                     @if($bill->customer_email)
                     <tr>
                         <td class="label">Customer Email:</td>
                         <td class="value">{{ $bill->customer_email }}</td>
                     </tr>
                     @endif
                 @endif
                 @if($bill->bill_reference)
                 <tr>
                     <td class="label">Reference:</td>
                     <td class="value">{{ $bill->bill_reference }}</td>
                 </tr>
                 @endif
             </table>
         </div>

         <div class="amount-section">
             <div class="amount-label">Bill Amount</div>
             <div class="amount-value">{{ $bill->bill_currency }} {{ number_format($bill->bill_amount, 2) }}</div>
             @if($bill->total_paid > 0)
                 <div style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed #bcf0da;">
                     <div class="amount-label" style="color: #059669;">Amount Paid</div>
                     <div style="font-size: 22px; font-weight: 900; color: #059669;">{{ $bill->bill_currency }} {{ number_format($bill->total_paid, 2) }}</div>
                 </div>
             @endif
         </div>

         <div style="font-size: 10px; color: #4b5563; padding: 10px; background: #f9fafb; border-radius: 6px; border-left: 3px solid #16a34a;">
             <strong>Bill Control Number:</strong> {{ $bill->bill_pay_number }}<br>
             <strong>Bill Type:</strong> {{ strtoupper($bill->bill_type) }}
             @if($bill->notes)
                 <br><strong>Notes:</strong> {{ $bill->notes }}
             @endif
         </div>

         <div class="footer">
            <strong>FEEDTAN DIGITAL PAYMENT SYSTEM</strong><br>
            Powered by FeedTan Team<br>
            <span style="color: #16a34a;">www.feedtancmg.org • service@feedtancmg.org</span><br>
            <div style="margin-top: 10px; font-size: 8px; color: #9ca3af;">
                This document is electronically generated and verified by FEEDTAN DIGITAL PAYMENT SYSTEM.
            </div>
        </div>
     </div>
 </body>
 </html>
