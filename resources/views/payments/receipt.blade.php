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
             padding: 20px; 
         } 
         .space-y-6 > * + * {
             margin-top: 1.5rem;
         }
         .space-y-3 > * + * {
             margin-top: 0.75rem;
         }
         .rounded-lg {
             border-radius: 0.5rem;
         }
         .bg-light {
             background-color: #f8f9fa !important;
         }
         .border-light {
             border-color: #f8f9fa !important;
         }
         .mono {
             font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
         }
         .tracking-wider {
             letter-spacing: 0.05em;
         }
         .text-xs { font-size: 0.75rem; }
         .text-sm { font-size: 0.875rem; }
         .text-lg { font-size: 1.125rem; }
         .text-muted { color: #6c757d; }
         .uppercase { text-transform: uppercase; }
         .font-bold { font-weight: bold; }
         .flex { display: flex; }
         .items-center { align-items: center; }
         .justify-between { justify-content: space-between; }
         .p-4 { padding: 1rem; }
         .p-3 { padding: 0.75rem; }
         .grid { display: block; width: 100%; }
         .col-2 { width: 48%; display: inline-block; vertical-align: top; margin-bottom: 10px; }
         .border { border: 1px solid #dee2e6; }
         .text-right { text-align: right; }
         .badge {
             padding: 0.25em 0.6em;
             font-size: 75%;
             font-weight: 700;
             border-radius: 0.25rem;
             display: inline-block;
         }
         .badge-success { background-color: #dcfce7; color: #166534; }
         .badge-warning { background-color: #fef3c7; color: #92400e; }
         .text-green-600 { color: #16a34a; }
         .italic { font-style: italic; }
         .min-h-60 { min-height: 60px; }
         .header {
             text-align: center;
             margin-bottom: 30px;
         }
         .logo {
             font-size: 24px;
             font-weight: 900;
             color: #16a34a;
         }
    </style> 
 </head> 
 <body> 
     @php
        $isSuccessful = in_array($paymentData['status'] ?? '', ['SUCCESS', 'SETTLED']);
        $statusColor = $isSuccessful ? 'success' : 'warning';
        $statusText = $isSuccessful ? 'Verified' : 'Pending Verification';
     @endphp
     
     <div class="container"> 
         <div class="header"> 
             <div class="logo">FEEDTAN CMG</div> 
             <div class="text-xs text-muted uppercase font-bold tracking-wider">OFFICIAL PAYMENT RECEIPT</div> 
         </div>

         <div class="space-y-6"> 
           <!-- HEADER INFO --> 
           <div class="flex items-center justify-between p-4 bg-light rounded-lg"> 
             <div style="float: left;"> 
               <div class="text-xs text-muted uppercase font-bold tracking-wider">Order Reference</div> 
               <div class="text-lg font-bold mono">{{ $paymentData['orderReference'] ?? 'N/A' }}</div> 
             </div> 
             <div class="text-right" style="float: right;"> 
               <div class="text-xs text-muted uppercase font-bold tracking-wider">Status</div> 
               <span class="badge badge-{{ $statusColor }}"> 
                 {{ $statusText }} 
               </span> 
             </div> 
             <div style="clear: both;"></div>
           </div> 
         
           <!-- TRANSACTION DETAILS --> 
           <div class="grid" style="margin-top: 20px;"> 
             <div class="col-2 p-3 border border-light rounded-lg" style="margin-right: 2%;"> 
               <div class="text-xs text-muted mb-1">Customer / Payer</div> 
               <div class="font-bold">{{ strtoupper($paymentData['customer']['customerName'] ?? $paymentData['payer_name'] ?? 'Anonymous') }}</div> 
               <div class="text-xs text-muted">{{ $paymentData['phone'] ?? $paymentData['paymentPhoneNumber'] ?? 'N/A' }}</div> 
             </div> 
             <div class="col-2 p-3 border border-light rounded-lg"> 
               <div class="text-xs text-muted mb-1">Amount</div> 
               <div class="font-bold text-green-600 text-lg">{{ $paymentData['collectedCurrency'] ?? $paymentData['currency'] ?? 'TZS' }} {{ number_format($paymentData['collectedAmount'] ?? $paymentData['amount'] ?? 0, 2) }}</div> 
             </div> 
             <div class="col-2 p-3 border border-light rounded-lg" style="margin-right: 2%;"> 
               <div class="text-xs text-muted mb-1">Transaction ID</div> 
               <div class="font-bold text-xs">{{ $paymentData['id'] ?? $paymentData['transaction_id'] ?? 'N/A' }}</div> 
             </div> 
             <div class="col-2 p-3 border border-light rounded-lg"> 
               <div class="text-xs text-muted mb-1">Date</div> 
               <div class="font-bold">{{ isset($paymentData['createdAt']) ? \Carbon\Carbon::parse($paymentData['createdAt'])->format('M d, Y') : date('M d, Y') }}</div> 
               <div class="text-xs text-muted">{{ isset($paymentData['createdAt']) ? \Carbon\Carbon::parse($paymentData['createdAt'])->format('H:i:s') : date('H:i:s') }}</div>
             </div> 
             <div class="col-2 p-3 border border-light rounded-lg" style="margin-right: 2%;"> 
               <div class="text-xs text-muted mb-1">Payment Method</div> 
               <div class="font-bold">{{ ucfirst($paymentData['channel'] ?? $paymentData['paymentMethod'] ?? 'N/A') }}</div> 
             </div> 
             <div class="col-2 p-3 border border-light rounded-lg"> 
               <div class="text-xs text-muted mb-1">Recorded By</div> 
               <div class="font-bold text-xs">System</div> 
             </div> 
           </div> 
         
           <!-- NOTES / COMMENTS --> 
           <div style="margin-top: 20px;"> 
             <label class="text-xs text-muted uppercase font-bold mb-2 block" style="display: block;">Description / Notes</label> 
             <div class="p-3 bg-light rounded-lg text-sm italic min-h-60"> 
               {{ $paymentData['description'] ?? $paymentData['message'] ?? 'No additional notes provided for this transaction.' }} 
             </div> 
           </div> 

           <!-- QR CODE -->
           <div style="text-align: center; margin-top: 30px;">
                <img src="{{ $qrCodeImage ?? '' }}" style="width: 30mm; height: 30mm; border: 1px solid #eee; padding: 5px; border-radius: 5px;">
                <div class="text-xs text-muted mt-2">Scan to verify transaction</div>
           </div>
         
           <!-- FOOTER --> 
           <div style="margin-top: 40px; text-align: center; border-top: 1px dashed #dee2e6; padding-top: 20px;"> 
             <div class="text-xs text-muted font-bold">FEEDTAN CMG - PAYMENT SYSTEM</div>
             <div class="text-xs text-muted">Powered by ClickPesa</div>
             <div class="text-xs text-green-600 mt-1">www.feedtan.co.tz • info@feedtan.co.tz</div>
             <div style="margin-top: 15px; font-size: 8px; color: #9ca3af;"> 
                 This document is electronically generated and verified by FEEDTAN CMG Payment System. 
             </div> 
           </div> 
         </div> 
     </div> 
 </body> 
 </html> 
