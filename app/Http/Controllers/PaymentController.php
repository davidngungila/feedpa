<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Payout;
use App\Services\ClickPesaAPIService;
use App\Services\MessagingServiceAPI;
use App\Services\AccountBalanceService;
use App\Support\TransactionFieldResolver;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Collection;

class PaymentController extends Controller
{
    protected ClickPesaAPIService $api;
    protected MessagingServiceAPI $messaging;
    protected AccountBalanceService $accountBalanceService;

    public function __construct(ClickPesaAPIService $api, MessagingServiceAPI $messaging, AccountBalanceService $accountBalanceService)
    {
        $this->api = $api;
        $this->messaging = $messaging;
        $this->accountBalanceService = $accountBalanceService;
    }

    public function addNote(Request $request, $orderReference)
    {
        $request->validate([
            'content' => 'required|string|max:1000'
        ]);

        $transaction = Transaction::where('order_reference', $orderReference)->firstOrFail();

        $transaction->notes()->create([
            'user_id' => auth()->id(),
            'content' => $request->content
        ]);

        return back()->with('success', 'Note added successfully!');
    }

    public function sendManualSMS(Request $request, $orderReference)
    {
        $transaction = Transaction::where('order_reference', $orderReference)->firstOrFail();

        try {
            // Check if transaction status is SUCCESS or SETTLED
            if (!in_array($transaction->status, ['SUCCESS', 'SETTLED'])) {
                return back()->with('error', 'SMS can only be sent for successful/settled transactions.');
            }

            if ($transaction->sms_sent) {
                return back()->with('info', 'SMS has already been sent for this transaction.');
            }

            $phoneNumber = $transaction->phone;
            if (!$phoneNumber) {
                return back()->with('error', 'No phone number available for this transaction.');
            }

            $paymentData = [
                'orderReference' => $transaction->order_reference,
                'id' => $transaction->transaction_id,
                'collectedAmount' => $transaction->amount,
                'collectedCurrency' => $transaction->currency,
                'paymentPhoneNumber' => $phoneNumber,
                'customer' => [
                    'customerName' => $transaction->customer_name ?? $transaction->payer_name,
                ],
                'customer_name' => $transaction->customer_name ?? $transaction->payer_name,
                'payer_name' => $transaction->payer_name,
                'createdAt' => $transaction->created_at,
            ];

            $result = $this->messaging->sendPaymentConfirmation($phoneNumber, $paymentData);

            $transaction->update([
                'sms_sent' => true,
                'sms_message' => $this->messaging->buildPaymentConfirmationMessage($paymentData),
                'sms_sent_at' => now(),
                'sms_error' => null,
            ]);

            return back()->with('success', 'SMS sent successfully!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send manual SMS: ' . $e->getMessage(), [
                'transaction_id' => $transaction->id,
                'error' => $e,
            ]);

            $transaction->update([
                'sms_sent' => false,
                'sms_error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to send SMS: ' . $e->getMessage());
        }
    }

    public function sendManualEmail(Request $request, $orderReference)
    {
        $transaction = Transaction::where('order_reference', $orderReference)->firstOrFail();

        try {
            // Check if transaction status is SUCCESS or SETTLED
            if (!in_array($transaction->status, ['SUCCESS', 'SETTLED'])) {
                return back()->with('error', 'Email can only be sent for successful/settled transactions.');
            }

            if ($transaction->email_sent) {
                return back()->with('info', 'Email has already been sent for this transaction.');
            }

            // Configure email from database
            $emailConfigService = new \App\Services\EmailConfigService();
            $emailConfigService->configureMail();

            // Build email template
            $emailTemplate = $this->buildTransactionEmailTemplate($transaction);

            $toEmail = $transaction->email ?? 'service@feedtancmg.org';
            $ccEmails = ['elulandala@gmail.com', 'davidngungila@gmail.com'];
            $recipients = array_merge([$toEmail], $ccEmails);

            \Illuminate\Support\Facades\Mail::html($emailTemplate['html'], function ($message) use ($emailTemplate, $toEmail, $ccEmails, $emailConfigService) {
                $config = $emailConfigService->getEmailConfig();
                $message->to($toEmail)
                        ->cc($ccEmails)
                        ->subject($emailTemplate['subject'])
                        ->from($config['from_address'], $config['from_name']);
            });

            $transaction->update([
                'email_sent' => true,
                'email_message' => implode(', ', $recipients),
                'email_sent_at' => now(),
                'email_error' => null,
            ]);

            return back()->with('success', 'Email sent successfully!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send manual email: ' . $e->getMessage(), [
                'transaction_id' => $transaction->id,
                'error' => $e,
            ]);

            $transaction->update([
                'email_sent' => false,
                'email_error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }

    public function retryPayment(Request $request, $orderReference)
    {
        try {
            $originalTransaction = Transaction::where('order_reference', $orderReference)->firstOrFail();

            $validated = [
                'amount' => $originalTransaction->amount,
                'phone_number' => $originalTransaction->phone,
                'payer_name' => $originalTransaction->payer_name,
                'description' => $originalTransaction->description,
            ];

            $amount = $this->api->formatAmount($validated['amount']);
            $phoneNumber = $this->api->validatePhoneNumber($validated['phone_number']);
            $memberName = trim($validated['payer_name']);
            $newOrderReference = $this->api->generateOrderReference();

            if (!$phoneNumber) {
                return back()->with('error', 'Invalid phone number. Please use format: 255712345678');
            }

            // Preview the payment first
            $preview = $this->api->previewUSSDPush($amount, $newOrderReference, $phoneNumber, true);

            if (empty($preview['activeMethods'])) {
                return back()->with('error', 'No active payment methods available for this phone number');
            }

            // Save new transaction to database
            $transaction = Transaction::create([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'order_reference' => $newOrderReference,
                'status' => 'PROCESSING',
                'amount' => $validated['amount'],
                'currency' => 'TZS',
                'phone' => $phoneNumber,
                'customer_name' => $originalTransaction->customer_name,
                'payer_name' => $memberName,
                'description' => $validated['description'],
                'type' => 'payment',
                'callback_data' => TransactionFieldResolver::initialCallbackSnapshot(
                    $validated['description'],
                    $request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest'
                        ? 'retry_payment'
                        : 'retry_payment'
                ),
            ]);

            // Initiate the payment
            $customerDetails = [
                'customerName' => $memberName,
                'description' => $validated['description'],
            ];
            $payment = $this->api->initiateUSSDPush($amount, $newOrderReference, $phoneNumber, null, $customerDetails);

            // Update transaction with API response
            if (isset($payment['transactionId'])) {
                $transaction->update([
                    'transaction_id' => $payment['transactionId'],
                    'status' => 'PENDING',
                ]);
            }

            // Check if this is an Ajax request or not
            if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => true,
                    'message' => "Malipo umekamilika! USSD umetumwa kwa {$phoneNumber}",
                    'order_reference' => $newOrderReference,
                    'amount' => $amount,
                    'phone_number' => $phoneNumber,
                    'payer_name' => $memberName,
                    'description' => $validated['description'],
                ]);
            }

            return redirect()->route('payments.status', ['reference' => $newOrderReference])
                ->with('success', "Payment initiated successfully! USSD Push sent to {$phoneNumber}");
        } catch (Exception $e) {
            Log::error('Payment retry failed: ' . $e->getMessage());
            
            // Handle insufficient funds error specifically
            if (stripos($e->getMessage(), 'Insufficient Funds') !== false) {
                // Check if this is an Ajax request
                if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage(),
                        'warning_type' => 'insufficient_funds'
                    ]);
                }
                
                return back()
                    ->with('error', $e->getMessage())
                    ->with('warning_type', 'insufficient_funds');
            }
            
            // Check if this is an Ajax request
            if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            
            return back()->with('error', $e->getMessage());
        }
    }

    private function buildTransactionEmailTemplate($transaction): array
    {
        $subject = "🔔 Arifa ya Malipo Mapya - {$transaction->order_reference}";
        
        $memberName = $transaction->customer_name ?? 'Haujulikani';
        $actualPayer = $transaction->payer_name ?? $memberName;
        $phone = $transaction->phone ?? 'Haiyupo';
        $amount = number_format($transaction->collected_amount ?? $transaction->amount ?? 0, 0);
        $currency = $transaction->currency ?? 'TZS';
        $status = $transaction->status ?? 'HAIJUIKANI';
        $reference = $transaction->order_reference ?? 'Haiyupo';
        $transactionId = $transaction->transaction_id ?? 'Haiyupo';
        $paymentMethod = $transaction->payment_method ?? 'Haujulikani';
        $date = $transaction->created_at ? $transaction->created_at->format('d M, Y H:i:s') : now()->format('d M, Y H:i:s');
        $description = $transaction->description ?? $transaction->resolved_description ?? 'Malipo yamepokelewa';
        
        $html = <<<HTML
<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$subject}</title>
    <style>
        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background: #f3f4f6;
        }
        .container {
            max-width: 650px;
            margin: 30px auto;
            padding: 0;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            padding: 30px 20px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border-radius: 16px 16px 0 0;
        }
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 28px;
        }
        .status-badge {
            display: inline-block;
            padding: 10px 24px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-settled, .status-completed {
            background-color: rgba(255,255,255,0.2);
            border: 2px solid white;
        }
        .content {
            padding: 30px;
        }
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            margin: 25px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 3px solid #10b981;
        }
        .section-title:first-child {
            margin-top: 0;
        }
        .details-grid {
            display: grid;
            gap: 15px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 16px;
            background: #f9fafb;
            border-radius: 10px;
        }
        .detail-label {
            font-weight: 600;
            color: #4b5563;
            font-size: 14px;
        }
        .detail-value {
            color: #1f2937;
            font-weight: 600;
            font-size: 14px;
        }
        .alert {
            background: #fef3c7;
            border-left: 5px solid #f59e0b;
            padding: 20px;
            border-radius: 0 12px 12px 0;
            margin: 25px 0;
        }
        .alert strong {
            color: #92400e;
        }
        .button {
            display: block;
            width: 100%;
            text-align: center;
            padding: 16px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white !important;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 16px;
            margin: 25px 0;
            transition: transform 0.2s;
        }
        .button:hover {
            transform: translateY(-2px);
        }
        .footer {
            text-align: center;
            padding: 25px;
            background: #f9fafb;
            color: #6b7280;
            font-size: 13px;
            border-radius: 0 0 16px 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔔 Malipo Mapya Yamepokelewa!</h1>
            <div class="status-badge status-completed">{$status}</div>
        </div>
        
        <div class="content">
            <p style="font-size: 16px; color: #374151;">Mambo vema Afisa,</p>
            <p style="font-size: 16px; color: #374151;">Malipo mapya yamefanikiwa. Tafadhali ingia kwenye mfumo ili kurekodi muamala huu kwenye rekodi zetu.</p>
            
            <div class="section-title">📊 Maelezo ya Malipo</div>
            <div class="details-grid">
                <div class="detail-row">
                    <span class="detail-label">Rejea</span>
                    <span class="detail-value">{$reference}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Kiasi</span>
                    <span class="detail-value">{$currency} {$amount}</span>
                </div>
            </div>
            
            <div class="section-title">👤 Maelezo ya Mwanachama</div>
            <div class="details-grid">
                <div class="detail-row">
                    <span class="detail-label">Jina la Mwanachama</span>
                    <span class="detail-value">{$memberName}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Mwenyeji wa Malipo</span>
                    <span class="detail-value">{$actualPayer}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Simu</span>
                    <span class="detail-value">{$phone}</span>
                </div>
            </div>
            
            <div class="section-title">📝 Maelezo ya Muamala</div>
            <div class="details-grid">
                <div class="detail-row">
                    <span class="detail-label">ID ya Muamala</span>
                    <span class="detail-value">{$transactionId}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Njia ya Malipo</span>
                    <span class="detail-value">{$paymentMethod}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Tarehe & Muda</span>
                    <span class="detail-value">{$date}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Nia / Maelezo</span>
                    <span class="detail-value">{$description}</span>
                </div>
            </div>
            
            <div class="alert">
                <strong>⚠️ Hatua Inahitajika:</strong> Tafadhali ingia kwenye mfumo ili kurekodi muamala huu wa malipo kwenye rekodi zetu.
            </div>
            
            <a href="https://pay.feedtancmg.org/login" class="button">🔑 Ingia kwenye Mfumo</a>
        </div>
        
        <div class="footer">
            <p><strong>FeedTan Community Microfinance Group</strong><br>
            "Tufanye Kazi Pamoja"</p>
        </div>
    </div>
</body>
</html>
HTML;

        return ['html' => $html, 'subject' => $subject];
    }

    /**
     * Show initiate payment form
     */
    public function create()
    {
        return view('payments.create');
    }

    /**
     * Initiate USSD Push Payment
     */
    public function store(Request $request)
    {
        $request->merge([
            'description' => trim((string) ($request->input('description') ?: $request->input('purpose', ''))),
        ]);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:500',
            'phone_number' => 'required|string',
            'payer_name' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'akiba_type' => 'nullable|string|max:50',
        ]);

        $description = trim($validated['description']);
        if ($description === '') {
            if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'Tafadhali ingiza maelezo ya malipo (Malipo Kwaajili Ya).',
                ], 422);
            }

            return back()
                ->with('error', 'Please enter payment description (Malipo Kwaajili Ya).')
                ->withInput();
        }

        // Combine description with akiba_type if purpose is Akiba
        if ($description === 'Akiba' && !empty($validated['akiba_type'])) {
            $description = $description . '-' . $validated['akiba_type'];
        }

        try {
            $amount = $this->api->formatAmount($validated['amount']);
            $phoneNumber = $this->api->validatePhoneNumber($validated['phone_number']);
            $memberName = trim($validated['payer_name']);
            $orderReference = $this->api->generateOrderReference();

            if (!$phoneNumber) {
                return back()->with('error', 'Invalid phone number. Please use format: 255712345678');
            }

            // Preview the payment first
            $preview = $this->api->previewUSSDPush($amount, $orderReference, $phoneNumber, true);

            if (empty($preview['activeMethods'])) {
                return back()->with('error', 'No active payment methods available for this phone number');
            }

            // Save transaction to database first (preserve member purpose for later API/sync updates)
            $transaction = Transaction::create([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'order_reference' => $orderReference,
                'status' => 'PROCESSING',
                'amount' => $validated['amount'],
                'currency' => 'TZS',
                'phone' => $phoneNumber,
                'customer_name' => $memberName,
                'payer_name' => $memberName,
                'description' => $description,
                'akiba_type' => $validated['akiba_type'] ?? null,
                'type' => 'payment',
                'callback_data' => TransactionFieldResolver::initialCallbackSnapshot(
                    $description,
                    $request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest'
                        ? 'public_swahili_form'
                        : 'payment_form'
                ),
            ]);

            // Initiate the payment with customer details
            $customerDetails = [
                'customerName' => $memberName,
                'description' => $description,
            ];
            $payment = $this->api->initiateUSSDPush($amount, $orderReference, $phoneNumber, null, $customerDetails);
            
            // Update transaction with API response
            if (isset($payment['transactionId'])) {
                $transaction->update([
                    'transaction_id' => $payment['transactionId'],
                    'status' => 'PENDING',
                ]);
            }
            
            // SMS notification removed - only send when payment is successful

            // Check if this is an Ajax request from Swahili payment page
            if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => true,
                    'message' => "Malipo umekamilika! USSD umetumwa kwa {$phoneNumber}",
                    'order_reference' => $orderReference,
                    'amount' => $amount,
                    'phone_number' => $phoneNumber,
                    'payer_name' => $memberName,
                    'description' => $description,
                ]);
            }

            return redirect()->route('payments.status', ['reference' => $orderReference])
                ->with('success', "Payment initiated successfully! USSD Push sent to {$phoneNumber}");
        } catch (Exception $e) {
            Log::error('Payment initiation failed: ' . $e->getMessage());
            
            // Handle insufficient funds error specifically
            if (stripos($e->getMessage(), 'Insufficient Funds') !== false) {
                // SMS notification removed - only send when payment is successful
                
                // Check if this is an Ajax request from Swahili payment page
                if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage(),
                        'warning_type' => 'insufficient_funds'
                    ]);
                }
                
                return back()
                    ->with('error', $e->getMessage())
                    ->with('warning_type', 'insufficient_funds')
                    ->withInput();
            }
            
            // Check if this is an Ajax request from Swahili payment page
            if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Check payment status
     */
    public function status(Request $request)
    {
        $paymentData = null;
        $error = null;
        $orderReference = $request->get('reference');

        Log::info('Payment status check requested', ['reference' => $orderReference]);

        if ($orderReference) {
            try {
                // First, try to get transaction from database
                $transaction = Transaction::where('order_reference', $orderReference)->first();
                
                if ($transaction) {
                    Log::info('Transaction found in database', ['reference' => $orderReference, 'transaction_id' => $transaction->id]);
                    
                    // Load notes
                    $transaction->load('notes.user');
                    
                    // Convert transaction to array format expected by view
                    $paymentData = [
                        'id' => $transaction->id,
                        'orderReference' => $transaction->order_reference,
                        'transaction_id' => $transaction->transaction_id,
                        'status' => $transaction->status,
                        'amount' => $transaction->amount,
                        'currency' => $transaction->currency,
                        'phone' => $transaction->phone,
                        'payer_name' => $transaction->payer_name,
                        'customer_name' => $transaction->customer_name,
                        'email' => $transaction->email,
                        'description' => $transaction->resolvedDescription(),
                        'type' => $transaction->type,
                        'payment_method' => $transaction->payment_method,
                        'paymentMethod' => $transaction->payment_method,
                        'channel' => $transaction->payment_method,
                        'sms_sent' => $transaction->sms_sent,
                        'sms_message' => $transaction->sms_message,
                        'sms_sent_at' => $transaction->sms_sent_at,
                        'sms_error' => $transaction->sms_error,
                        'email_sent' => $transaction->email_sent,
                        'email_message' => $transaction->email_message,
                        'email_sent_at' => $transaction->email_sent_at,
                        'email_error' => $transaction->email_error,
                        'created_at' => $transaction->created_at,
                        'updated_at' => $transaction->updated_at,
                        'customer' => [
                            'customerName' => $transaction->customer_name ?? $transaction->payer_name,
                            'customerPhoneNumber' => $transaction->phone,
                            'customerEmail' => $transaction->email
                        ],
                        'paymentPhoneNumber' => $transaction->phone,
                        'collectedAmount' => $transaction->amount,
                        'collectedCurrency' => $transaction->currency,
                        'createdAt' => $transaction->created_at,
                        'notes' => $transaction->notes
                    ];
                    
                    // If status is still PROCESSING or PENDING, try to get updated status from API
                    if (in_array($transaction->status, ['PROCESSING', 'PENDING'])) {
                        Log::info('Calling API for payment status', ['reference' => $orderReference]);
                        $apiResponse = $this->api->queryPaymentStatus($orderReference);
                        Log::info('API response received', ['data' => $apiResponse]);
                        
                        // Handle wrapped API response (data at index 0)
                        $apiData = null;
                        if ($apiResponse && is_array($apiResponse) && isset($apiResponse[0])) {
                            $apiData = $apiResponse[0];
                            Log::info('Unwrapped payment data from API for existing transaction', ['reference' => $orderReference, 'api_data' => $apiData]);
                        } elseif ($apiResponse && is_array($apiResponse)) {
                            $apiData = $apiResponse;
                            Log::info('Direct payment data from API for existing transaction', ['reference' => $orderReference, 'api_data' => $apiData]);
                        }
                        
                        // Update transaction with API data
                        if ($apiData && isset($apiData['status'])) {
                            $mergedCallback = TransactionFieldResolver::mergeCallbackData(
                                $transaction->callback_data,
                                $apiData
                            );
                            $resolvedDescription = TransactionFieldResolver::description(
                                $transaction->description,
                                $apiData['description'] ?? $apiData['narrative'] ?? null,
                                null,
                                $mergedCallback
                            );

                            $transaction->update([
                                'status' => $apiData['status'],
                                'transaction_id' => $apiData['id'] ?? $apiData['transaction_id'] ?? $transaction->transaction_id,
                                'payment_method' => $apiData['channel'] ?? $apiData['paymentMethod'] ?? $transaction->payment_method,
                                'amount' => $apiData['collectedAmount'] ?? $apiData['amount'] ?? $transaction->amount,
                                'currency' => $apiData['collectedCurrency'] ?? $apiData['currency'] ?? $transaction->currency,
                                'phone' => $apiData['customer']['customerPhoneNumber'] ?? $apiData['paymentPhoneNumber'] ?? $transaction->phone,
                                'customer_name' => TransactionFieldResolver::memberName(
                                    $transaction->customer_name,
                                    $apiData['customer']['customerName'] ?? $apiData['customerName'] ?? null
                                ),
                                'payer_name' => TransactionFieldResolver::payerName(
                                    $transaction->payer_name,
                                    $apiData['customer']['customerName'] ?? $apiData['payer_name'] ?? null
                                ),
                                'email' => $apiData['customer']['customerEmail'] ?? $apiData['email'] ?? $transaction->email,
                                'description' => $resolvedDescription,
                                'callback_data' => $mergedCallback,
                                'updated_at' => now()
                            ]);
                            
                            // Update payment data with API response
                            $paymentData = [
                                'id' => $transaction->id,
                                'orderReference' => $transaction->order_reference,
                                'transaction_id' => $transaction->transaction_id,
                                'status' => $transaction->status,
                                'amount' => $transaction->amount,
                                'currency' => $transaction->currency,
                                'phone' => $transaction->phone,
                                'payer_name' => $transaction->payer_name,
                                'customer_name' => $transaction->customer_name,
                                'email' => $transaction->email,
                                'description' => $transaction->resolvedDescription(
                                    $apiData['description'] ?? $apiData['narrative'] ?? null
                                ),
                                'type' => $transaction->type,
                                'payment_method' => $transaction->payment_method,
                                'sms_sent' => $transaction->sms_sent,
                                'sms_message' => $transaction->sms_message,
                                'sms_sent_at' => $transaction->sms_sent_at,
                                'sms_error' => $transaction->sms_error,
                                'email_sent' => $transaction->email_sent,
                                'email_message' => $transaction->email_message,
                                'email_sent_at' => $transaction->email_sent_at,
                                'email_error' => $transaction->email_error,
                                'created_at' => $transaction->created_at,
                                'updated_at' => $transaction->updated_at,
                                'customer' => [
                                    'customerName' => $transaction->customer_name ?? $transaction->payer_name,
                                    'customerPhoneNumber' => $transaction->phone,
                                    'customerEmail' => $transaction->email
                                ],
                                'paymentPhoneNumber' => $transaction->phone,
                                'collectedAmount' => $transaction->amount,
                                'collectedCurrency' => $transaction->currency,
                                'channel' => $transaction->payment_method
                            ];
                            
                            // SMS notifications now handled by webhooks for better reliability
                        }
                    }
                } else {
                    // Transaction not found in database, try API
                    Log::info('Transaction not found in database, trying API', ['reference' => $orderReference]);
                    
                    try {
                        $apiResponse = $this->api->queryPaymentStatus($orderReference);
                        Log::info('API response received', ['data' => $apiResponse]);
                        
                        // Handle wrapped API response (data at index 0)
                        $apiPaymentData = null;
                        if ($apiResponse && is_array($apiResponse) && isset($apiResponse[0])) {
                            $apiPaymentData = $apiResponse[0];
                            Log::info('Unwrapped payment data from API', ['reference' => $orderReference, 'payment_data' => $apiPaymentData]);
                        } elseif ($apiResponse && is_array($apiResponse)) {
                            $apiPaymentData = $apiResponse;
                            Log::info('Direct payment data from API', ['reference' => $orderReference, 'payment_data' => $apiPaymentData]);
                        }
                        
                        // SMS notifications now handled by webhooks for better reliability
                        
                        // Check if API returned valid data
                        if (empty($apiPaymentData) || !is_array($apiPaymentData) || (isset($apiPaymentData['status']) && $apiPaymentData['status'] === 'NOT_FOUND')) {
                            $error = 'Payment not found. The reference number may be incorrect or the payment may not exist in the system.';
                            Log::warning('No payment data found from API', ['reference' => $orderReference, 'api_response' => $apiResponse]);
                        } else {
                            // Create transaction in database from API data for future reference
                            Transaction::create([
                                'id' => (string) \Illuminate\Support\Str::uuid(),
                                'order_reference' => $orderReference,
                                'transaction_id' => $apiPaymentData['id'] ?? $apiPaymentData['transaction_id'] ?? null,
                                'status' => $apiPaymentData['status'] ?? 'UNKNOWN',
                                'amount' => $apiPaymentData['collectedAmount'] ?? $apiPaymentData['amount'] ?? 0,
                                'currency' => $apiPaymentData['collectedCurrency'] ?? 'TZS',
                                'phone' => $apiPaymentData['customer']['customerPhoneNumber'] ?? $apiPaymentData['paymentPhoneNumber'] ?? null,
                                'payer_name' => $apiPaymentData['customer']['customerName'] ?? $apiPaymentData['payer_name'] ?? null,
                                'customer_name' => $apiPaymentData['customer']['customerName'] ?? $apiPaymentData['payer_name'] ?? null,
                                'payment_method' => $apiPaymentData['channel'] ?? $apiPaymentData['paymentMethod'] ?? null,
                                'description' => $apiPaymentData['description'] ?? $apiPaymentData['narrative'] ?? null,
                                'type' => 'payment',
                                'sms_sent' => false,
                                'sms_message' => null,
                                'sms_sent_at' => null,
                                'sms_error' => null,
                                'created_at' => $apiPaymentData['createdAt'] ?? now(),
                                'updated_at' => $apiPaymentData['updatedAt'] ?? now()
                            ]);
                            
                            Log::info('Transaction created from API data', ['reference' => $orderReference]);
                            
                            // Set paymentData for view using the captured API data
                            $paymentData = [
                                'id' => $apiPaymentData['id'] ?? null,
                                'orderReference' => $apiPaymentData['orderReference'] ?? $orderReference,
                                'transaction_id' => $apiPaymentData['id'] ?? $apiPaymentData['transaction_id'] ?? null,
                                'status' => $apiPaymentData['status'] ?? 'UNKNOWN',
                                'amount' => $apiPaymentData['collectedAmount'] ?? $apiPaymentData['amount'] ?? 0,
                                'currency' => $apiPaymentData['collectedCurrency'] ?? 'TZS',
                                'phone' => $apiPaymentData['customer']['customerPhoneNumber'] ?? $apiPaymentData['paymentPhoneNumber'] ?? null,
                                'payer_name' => $apiPaymentData['customer']['customerName'] ?? $apiPaymentData['payer_name'] ?? null,
                                'payment_method' => $apiPaymentData['channel'] ?? $apiPaymentData['paymentMethod'] ?? null,
                                'description' => $apiPaymentData['description'] ?? null,
                                'type' => 'payment',
                                'sms_sent' => false,
                                'sms_message' => null,
                                'sms_sent_at' => null,
                                'sms_error' => null,
                                'created_at' => $apiPaymentData['createdAt'] ?? now(),
                                'updated_at' => $apiPaymentData['updatedAt'] ?? now(),
                                'customer' => $apiPaymentData['customer'] ?? null,
                                'paymentPhoneNumber' => $apiPaymentData['paymentPhoneNumber'] ?? null,
                                'collectedAmount' => $apiPaymentData['collectedAmount'] ?? $apiPaymentData['amount'] ?? 0,
                                'collectedCurrency' => $apiPaymentData['collectedCurrency'] ?? 'TZS',
                                'channel' => $apiPaymentData['channel'] ?? $apiPaymentData['paymentMethod'] ?? null
                            ];
                        }
                    } catch (Exception $apiError) {
                        Log::error('API call failed', ['reference' => $orderReference, 'error' => $apiError->getMessage()]);
                        $error = 'Payment status check failed. The payment service may be temporarily unavailable. Please try again in a few minutes.';
                    }
                }
            } catch (Exception $e) {
                // If we have database data, use it even if API fails
                if (isset($paymentData) && $paymentData) {
                    Log::warning('API failed but using database data', [
                        'reference' => $orderReference,
                        'api_error' => $e->getMessage()
                    ]);
                    // Don't set error, use the database data we already have
                } else {
                    $error = 'Payment not found. Please verify the reference number is correct and try again.';
                    Log::error('Payment status check failed: ' . $error, [
                        'reference' => $orderReference,
                        'exception' => $e->getTraceAsString()
                    ]);
                }
            }
        } else {
            $error = 'No order reference provided';
            Log::warning('No order reference in request');
        }

        // Check if user is authenticated, use appropriate view
        if (auth()->check()) {
            return view('payments.status', compact('paymentData', 'error', 'orderReference'));
        }
        return view('public.status', compact('paymentData', 'error', 'orderReference'));
    }

    /**
     * Payment history with filters
     */
    public function history(Request $request)
    {
        $activeStatus = $request->get('status', 'SETTLED');
        $selectedColumns = $request->get('columns', ['order_reference', 'transaction_id', 'status', 'amount', 'currency', 'payer_name', 'phone', 'description', 'payment_method', 'created_at']);
        $availableColumns = [
            'order_reference' => 'Reference',
            'transaction_id' => 'Transaction ID',
            'status' => 'Status',
            'amount' => 'Amount',
            'currency' => 'Currency',
            'customer_name' => 'Member Name',
            'payer_name' => 'Payer Name',
            'phone' => 'Phone',
            'email' => 'Email',
            'description' => 'Description',
            'payment_method' => 'Payment Method',
            'sms_sent' => 'SMS Sent',
            'sms_sent_at' => 'SMS Sent At',
            'email_sent' => 'Email Sent',
            'email_sent_at' => 'Email Sent At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];

        // Get payments and bill payments from database
        $paymentQuery = Transaction::query()->whereIn('type', ['payment', 'billpay']);
        $this->applyHistoryTabFilter($paymentQuery, $activeStatus);
        if ($request->filled('currency')) {
            $paymentQuery->where('currency', $request->currency);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $paymentQuery->where(function ($subQuery) use ($search) {
                $subQuery->where('order_reference', 'like', '%' . $search . '%')
                    ->orWhere('transaction_id', 'like', '%' . $search . '%')
                    ->orWhere('payer_name', 'like', '%' . $search . '%')
                    ->orWhere('customer_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        } elseif ($request->filled('order_reference')) {
            $paymentQuery->where('order_reference', 'like', '%' . $request->order_reference . '%');
        }
        if ($request->filled('phone')) {
            $paymentQuery->where('phone', 'like', '%' . $request->phone . '%');
        }
        if ($request->filled('payer_name')) {
            $paymentQuery->where('payer_name', 'like', '%' . $request->payer_name . '%');
        }
        if ($request->filled('start_date')) {
            $paymentQuery->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $paymentQuery->whereDate('created_at', '<=', $request->end_date);
        }

        // Get payouts from database
        $payoutQuery = Payout::query();
        if ($activeStatus === 'SETTLED') {
            $payoutQuery->whereIn('status', ['SUCCESS', 'SETTLED', 'COMPLETED']);
        } elseif ($activeStatus === 'FAILED') {
            $payoutQuery->whereIn('status', ['FAILED', 'ERROR', 'CANCELLED']);
        }
        if ($request->filled('currency')) {
            $payoutQuery->where('currency', $request->currency);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $payoutQuery->where(function ($subQuery) use ($search) {
                $subQuery->where('order_reference', 'like', '%' . $search . '%')
                    ->orWhere('clickpesa_payout_id', 'like', '%' . $search . '%')
                    ->orWhere('recipient_name', 'like', '%' . $search . '%')
                    ->orWhere('recipient_phone', 'like', '%' . $search . '%');
            });
        }
        if ($request->filled('start_date')) {
            $payoutQuery->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $payoutQuery->whereDate('created_at', '<=', $request->end_date);
        }

        // Automatic SMS sending for unsent transactions within 1 minute
        $unsentTransactions = Transaction::whereIn('type', ['payment', 'billpay'])
            ->whereIn('status', ['SUCCESS', 'SETTLED'])
            ->where('sms_sent', false)
            ->where('created_at', '>=', now()->subMinutes(1))
            ->get();

        foreach ($unsentTransactions as $transaction) {
            try {
                if (!$transaction->phone) {
                    continue;
                }

                $paymentData = [
                    'orderReference' => $transaction->order_reference,
                    'id' => $transaction->transaction_id,
                    'collectedAmount' => $transaction->amount,
                    'collectedCurrency' => $transaction->currency,
                    'paymentPhoneNumber' => $transaction->phone,
                    'customer' => [
                        'customerName' => $transaction->customer_name ?? $transaction->payer_name,
                    ],
                    'customer_name' => $transaction->customer_name ?? $transaction->payer_name,
                    'payer_name' => $transaction->payer_name,
                    'createdAt' => $transaction->created_at,
                ];

                $this->messaging->sendPaymentConfirmation($transaction->phone, $paymentData);

                $transaction->update([
                    'sms_sent' => true,
                    'sms_message' => $this->messaging->buildPaymentConfirmationMessage($paymentData),
                    'sms_sent_at' => now(),
                    'sms_error' => null,
                ]);

                Log::info('Automatic SMS sent for transaction: ' . $transaction->order_reference);
            } catch (\Exception $e) {
                Log::error('Failed to send automatic SMS for transaction: ' . $transaction->order_reference, [
                    'error' => $e->getMessage(),
                ]);
                $transaction->update([
                    'sms_error' => $e->getMessage(),
                ]);
            }
        }

        // Get live API account balance
        $apiLiveBalance = null;
        try {
            $tzsBalance = $this->accountBalanceService->getTzsBalance(refresh: true);
            $apiLiveBalance = $tzsBalance['balance'];
        } catch (Exception $e) {
            Log::error('Failed to retrieve API live account balance: ' . $e->getMessage());
        }

        // Combine filtered payments and payouts for display and balance calculation
        $payments = $paymentQuery->orderBy('created_at', 'asc')->get();
        $payouts = $payoutQuery->orderBy('created_at', 'asc')->get();
        
        $combined = collect();
        foreach ($payments as $payment) {
            $combined->push([
                'type' => 'payment',
                'record' => $payment,
                'created_at' => $payment->created_at,
                'entry' => 'CREDIT',
            ]);
        }
        foreach ($payouts as $payout) {
            $combined->push([
                'type' => 'payout',
                'record' => $payout,
                'created_at' => $payout->created_at,
                'entry' => 'DEBIT',
            ]);
            // Add fee entry if there is a fee
            $fee = (float) ($payout->fee ?? 0);
            if ($fee > 0) {
                $combined->push([
                    'type' => 'payout-fee',
                    'record' => $payout,
                    'fee' => $fee,
                    'created_at' => $payout->created_at,
                    'entry' => 'DEBIT',
                ]);
            }
        }
        
        $combined = $combined->sortBy('created_at')->values();

        // Calculate internal database balance and running balance for display (oldest to newest)
        $internalDbBalance = 0;
        $combinedWithBalance = $combined->map(function ($item) use (&$internalDbBalance) {
            if ($item['type'] === 'payment') {
                $amount = (float) $item['record']->amount;
                if (in_array(strtoupper($item['record']->status), ['SUCCESS', 'SETTLED'])) {
                    $internalDbBalance += $amount;
                }
            } elseif ($item['type'] === 'payout') {
                $amount = (float) $item['record']->amount;
                if (in_array(strtoupper($item['record']->status), ['SUCCESS', 'SETTLED', 'COMPLETED'])) {
                    $internalDbBalance -= $amount;
                }
            } elseif ($item['type'] === 'payout-fee') {
                $fee = (float) $item['fee'];
                if (in_array(strtoupper($item['record']->status), ['SUCCESS', 'SETTLED', 'COMPLETED'])) {
                    $internalDbBalance -= $fee;
                }
            }
            $item['running_balance'] = $internalDbBalance;
            return $item;
        });

        // Reverse to show newest first on the page
        $combinedWithBalance = $combinedWithBalance->reverse()->values();

        $settledCount = Transaction::whereIn('type', ['payment', 'billpay'])->whereIn('status', ['SUCCESS', 'SETTLED'])->count();
        $failedCount = Transaction::whereIn('type', ['payment', 'billpay'])->whereIn('status', ['FAILED', 'ERROR'])->count();
        
        Log::info('Payment history loaded from database', [
            'count' => $combinedWithBalance->count(),
            'tab' => $activeStatus,
        ]);

        return view('payments.history', compact(
            'combinedWithBalance',
            'apiLiveBalance',
            'internalDbBalance',
            'settledCount',
            'failedCount',
            'activeStatus',
            'selectedColumns',
            'availableColumns'
        ));
    }

    /**
     * Export payment history to PDF
     */
    public function exportPdf(Request $request)
    {
        try {
            // Check if this is a single payment receipt request
            if ($request->filled('order_reference') && !$request->filled('bulk')) {
                return $this->receipt($request->order_reference);
            }

            // Get filtered payments and bill payments from database
            $paymentQuery = Transaction::query()->whereIn('type', ['payment', 'billpay']);

            $this->applyHistoryTabFilter($paymentQuery, $request->get('status', 'SETTLED'));
            if ($request->filled('currency')) {
                $paymentQuery->where('currency', $request->currency);
            }
            if ($request->filled('search')) {
                $search = $request->search;
                $paymentQuery->where(function ($subQuery) use ($search) {
                    $subQuery->where('order_reference', 'like', '%' . $search . '%')
                        ->orWhere('transaction_id', 'like', '%' . $search . '%')
                        ->orWhere('payer_name', 'like', '%' . $search . '%')
                        ->orWhere('customer_name', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%');
                });
            }
            if ($request->filled('start_date')) {
                $paymentQuery->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $paymentQuery->whereDate('created_at', '<=', $request->end_date);
            }

            // Get filtered payouts from database
            $payoutQuery = Payout::query();
            $this->applyHistoryTabFilter($payoutQuery, $request->get('status', 'SUCCESS'));
            if ($request->filled('currency')) {
                $payoutQuery->where('currency', $request->currency);
            }
            if ($request->filled('search')) {
                $search = $request->search;
                $payoutQuery->where(function ($subQuery) use ($search) {
                    $subQuery->where('order_reference', 'like', '%' . $search . '%')
                        ->orWhere('clickpesa_payout_id', 'like', '%' . $search . '%')
                        ->orWhere('recipient_name', 'like', '%' . $search . '%')
                        ->orWhere('recipient_phone', 'like', '%' . $search . '%');
                });
            }
            if ($request->filled('start_date')) {
                $payoutQuery->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $payoutQuery->whereDate('created_at', '<=', $request->end_date);
            }

            $payments = $paymentQuery->orderBy('created_at', 'asc')->get();
            $payouts = $payoutQuery->orderBy('created_at', 'asc')->get();

            // Combine and process
            $combined = collect();
            foreach ($payments as $payment) {
                $combined->push([
                    'type' => 'payment',
                    'record' => $payment,
                    'created_at' => $payment->created_at,
                    'entry' => 'CREDIT',
                ]);
            }
            foreach ($payouts as $payout) {
                $combined->push([
                    'type' => 'payout',
                    'record' => $payout,
                    'created_at' => $payout->created_at,
                    'entry' => 'DEBIT',
                ]);
                // Add fee entry if there is a fee
                $fee = (float) ($payout->fee ?? 0);
                if ($fee > 0) {
                    $combined->push([
                        'type' => 'payout-fee',
                        'record' => $payout,
                        'fee' => $fee,
                        'created_at' => $payout->created_at,
                        'entry' => 'DEBIT',
                    ]);
                }
            }
            $combined = $combined->sortBy('created_at')->values();

            // Get live API account balance
        $currentBalance = null;
        try {
            $tzsBalance = $this->accountBalanceService->getTzsBalance(refresh: true);
            $currentBalance = $tzsBalance['balance'];
        } catch (Exception $e) {
            Log::error('Failed to retrieve API live account balance: ' . $e->getMessage());
        }

            // Calculate running balance (oldest to newest)
            $runningBalance = 0;
            $combinedWithBalance = $combined->map(function ($item) use (&$runningBalance) {
                if ($item['type'] === 'payment') {
                    $amount = (float) $item['record']->amount;
                    if (in_array(strtoupper($item['record']->status), ['SUCCESS', 'SETTLED'])) {
                        $runningBalance += $amount;
                    }
                } elseif ($item['type'] === 'payout') {
                    $amount = (float) $item['record']->amount;
                    if (in_array(strtoupper($item['record']->status), ['SUCCESS', 'SETTLED', 'COMPLETED'])) {
                        $runningBalance -= $amount;
                    }
                } elseif ($item['type'] === 'payout-fee') {
                    $fee = (float) $item['fee'];
                    if (in_array(strtoupper($item['record']->status), ['SUCCESS', 'SETTLED', 'COMPLETED'])) {
                        $runningBalance -= $fee;
                    }
                }

                $recordArray = [];
                if ($item['type'] === 'payment') {
                    $recordArray = $item['record']->toArray();
                    $recordArray['type'] = 'payment';
                    $recordArray['entry'] = 'CREDIT';
                    $recordArray['description'] = $item['record']->resolved_description;
                } elseif ($item['type'] === 'payout') {
                    $recordArray = $item['record']->toArray();
                    $recordArray['type'] = 'payout';
                    $recordArray['entry'] = 'DEBIT';
                    $recordArray['description'] = $item['record']->resolved_description;
                    $recordArray['order_reference'] = $item['record']->order_reference;
                    $recordArray['transaction_id'] = $item['record']->clickpesa_payout_id ?? $item['record']->transaction_id;
                    $recordArray['payer_name'] = $item['record']->recipient_name;
                    $recordArray['phone'] = $item['record']->recipient_phone ?? $item['record']->beneficiary_mobile;
                } elseif ($item['type'] === 'payout-fee') {
                    $recordArray = $item['record']->toArray();
                    $recordArray['type'] = 'payout-fee';
                    $recordArray['entry'] = 'DEBIT';
                    $recordArray['amount'] = $item['fee'];
                    $recordArray['description'] = 'Fee for payout ' . $item['record']->order_reference;
                    $recordArray['order_reference'] = $item['record']->order_reference . '-FEE';
                    $recordArray['transaction_id'] = $item['record']->clickpesa_payout_id ?? $item['record']->transaction_id;
                    $recordArray['payer_name'] = 'Payout Fee';
                    $recordArray['phone'] = '';
                    $recordArray['sms_sent'] = false;
                    $recordArray['email_sent'] = false;
                }
                $recordArray['running_balance'] = $runningBalance;
                return $recordArray;
            });

            // Selected columns
            $allowedColumns = ['order_reference', 'transaction_id', 'status', 'amount', 'currency', 'customer_name', 'payer_name', 'phone', 'email', 'description', 'payment_method', 'sms_sent', 'sms_sent_at', 'created_at', 'updated_at', 'running_balance', 'entry', 'type'];
            $columns = array_values(array_intersect($request->get('columns', ['order_reference', 'transaction_id', 'status', 'amount', 'currency', 'payer_name', 'phone', 'description', 'payment_method', 'created_at']), $allowedColumns));
            if (empty($columns)) {
                $columns = ['order_reference', 'transaction_id', 'status', 'amount', 'currency', 'payer_name', 'phone', 'description', 'payment_method', 'created_at'];
            }
            if (!in_array('running_balance', $columns)) {
                $columns[] = 'running_balance'; // Always include running balance
            }

            $pdf = Pdf::loadView('payments.exports.pdf', [
                'payments' => $combinedWithBalance->values()->toArray(),
                'columns' => $columns,
                'currentBalance' => $currentBalance
            ])
                ->setPaper('a4', 'landscape')
                ->setOption('margin-bottom', 10);

            return $pdf->download('payment-history-' . date('Y-m-d') . '.pdf');
        } catch (Exception $e) {
            Log::error('PDF export failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to export PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export payment history to Excel
     */
    public function exportExcel(Request $request)
    {
        try {
            // Get filtered payments and bill payments from database
            $query = Transaction::query()->whereIn('type', ['payment', 'billpay']);

            $this->applyHistoryTabFilter($query, $request->get('status', 'SETTLED'));
            if ($request->filled('currency')) {
                $query->where('currency', $request->currency);
            }
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('order_reference', 'like', '%' . $search . '%')
                        ->orWhere('transaction_id', 'like', '%' . $search . '%')
                        ->orWhere('payer_name', 'like', '%' . $search . '%')
                        ->orWhere('customer_name', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%');
                });
            }
            if ($request->filled('order_reference')) {
                $query->where('order_reference', 'like', '%' . $request->order_reference . '%');
            }
            if ($request->filled('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }

            $payments = $query->orderBy('created_at', 'desc')->get();
            
            // Process each payment to use resolved description and other fields
            $processedPayments = [];
            foreach ($payments as $payment) {
                $paymentArray = $payment->toArray();
                $paymentArray['description'] = $payment->resolved_description;
                $processedPayments[] = $paymentArray;
            }
            
            // Selected columns
            $allowedColumns = ['order_reference', 'transaction_id', 'status', 'amount', 'currency', 'customer_name', 'payer_name', 'phone', 'email', 'description', 'payment_method', 'sms_sent', 'sms_sent_at', 'created_at', 'updated_at'];
            $columns = array_values(array_intersect($request->get('columns', ['order_reference', 'transaction_id', 'status', 'amount', 'currency', 'payer_name', 'phone', 'email', 'description', 'payment_method', 'created_at', 'updated_at']), $allowedColumns));
            if (empty($columns)) {
                $columns = ['order_reference', 'transaction_id', 'status', 'amount', 'currency', 'payer_name', 'phone', 'email', 'description', 'payment_method', 'created_at', 'updated_at'];
            }

            return Excel::download(new \App\Exports\PaymentHistoryExport($processedPayments, $columns), 'payment-history-' . date('Y-m-d') . '.xlsx');
        } catch (Exception $e) {
            Log::error('Excel export failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to export Excel: ' . $e->getMessage());
        }
    }

    /**
     * Generate payment receipt PDF
     */
    public function receipt($orderReference)
    {
        try {
            // Try to get from database first
            $transaction = Transaction::where('order_reference', $orderReference)->first();
            
            $paymentData = null;
            $status = null;
            
            if ($transaction) {
                $status = strtoupper($transaction->status);
                $paymentData = [
                    'orderReference' => $transaction->order_reference,
                    'transaction_id' => $transaction->transaction_id,
                    'status' => $transaction->status,
                    'collectedAmount' => $transaction->amount,
                    'collectedCurrency' => $transaction->currency,
                    'paymentPhoneNumber' => $transaction->phone,
                    'channel' => $transaction->payment_method,
                    'customer' => [
                        'customerName' => $transaction->customer_name ?? $transaction->payer_name,
                        'customerEmail' => $transaction->email,
                        'customerPhoneNumber' => $transaction->phone
                    ],
                    'payer_name' => $transaction->payer_name,
                    'customer_name' => $transaction->customer_name,
                    'description' => $transaction->resolvedDescription(),
                    'createdAt' => $transaction->created_at,
                    'id' => $transaction->transaction_id
                ];
            } else {
                // Fallback to API
                $apiResponse = $this->api->queryPaymentStatus($orderReference);
                if ($apiResponse && is_array($apiResponse)) {
                    $paymentData = isset($apiResponse[0]) ? $apiResponse[0] : $apiResponse;
                    $paymentData['orderReference'] = $paymentData['orderReference'] ?? $orderReference;
                    $status = strtoupper($paymentData['status'] ?? '');
                }
            }
            
            if (!$paymentData) {
                return back()->with('error', 'Payment not found');
            }
            
            // Only allow receipt for SUCCESS or SETTLED transactions
            if (!in_array($status, ['SUCCESS', 'SETTLED'])) {
                return back()->with('error', 'Receipt can only be generated for successful or settled transactions');
            }

            // Generate QR code with full payment details
            $qrContent = "FEEDTAN DIGITAL PAYMENT SYSTEM\n" .
                       "Order Reference: " . ($paymentData['orderReference'] ?? 'N/A') . "\n" .
                       "Transaction ID: " . ($paymentData['id'] ?? $paymentData['transaction_id'] ?? 'N/A') . "\n" .
                       "Amount: " . number_format($paymentData['collectedAmount'] ?? $paymentData['amount'] ?? 0, 2) . " " . ($paymentData['collectedCurrency'] ?? $paymentData['currency'] ?? 'TZS') . "\n" .
                       "Status: " . ($paymentData['status'] ?? 'UNKNOWN') . "\n" .
                       "Phone: " . ($paymentData['paymentPhoneNumber'] ?? $paymentData['phone'] ?? 'N/A') . "\n" .
                       "Channel: " . ($paymentData['channel'] ?? $paymentData['payment_method'] ?? 'N/A') . "\n" .
                       "Member: " . ($paymentData['customer_name'] ?? $paymentData['customer']['customerName'] ?? 'N/A') . "\n" .
                       "Payer: " . ($paymentData['payer_name'] ?? 'N/A') . "\n" .
                       "Date: " . (isset($paymentData['createdAt']) ? \Carbon\Carbon::parse($paymentData['createdAt'])->format('Y-m-d H:i:s') : 'N/A');
            
            $qrCodeSvg = QrCode::format('svg')->size(150)->encoding('UTF-8')->errorCorrection('H')->generate($qrContent);
            $qrCodeImage = 'data:image/svg+xml;base64,' . base64_encode($qrCodeSvg);

            $pdf = Pdf::loadView('payments.receipt', ['paymentData' => $paymentData, 'qrCodeImage' => $qrCodeImage])
                ->setPaper('a4', 'portrait')
                ->setOption('margin-bottom', 20);

            return $pdf->download('payment-receipt-' . $orderReference . '.pdf');
        } catch (Exception $e) {
            Log::error('Receipt generation failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate receipt: ' . $e->getMessage());
        }
    }

    /**
     * API endpoint to get payment status (for AJAX calls)
     */
    public function apiStatus(Request $request)
    {
        $request->validate([
            'order_reference' => 'required|string'
        ]);

        try {
            $orderReference = $request->order_reference;
            $transaction = Transaction::where('order_reference', $orderReference)->first();
            $paymentData = null;
            
            if ($transaction) {
                // Update from API and get transaction
                $apiResponse = $this->api->queryPaymentStatus($orderReference);
                
                if ($apiResponse) {
                    $apiData = null;
                    if (is_array($apiResponse) && isset($apiResponse[0])) {
                        $apiData = $apiResponse[0];
                    } elseif (is_array($apiResponse)) {
                        $apiData = $apiResponse;
                    }
                    
                    if ($apiData && isset($apiData['status'])) {
                        $mergedCallback = TransactionFieldResolver::mergeCallbackData(
                            $transaction->callback_data,
                            $apiData
                        );
                        $resolvedDescription = TransactionFieldResolver::description(
                            $transaction->description,
                            $apiData['description'] ?? $apiData['narrative'] ?? null,
                            null,
                            $mergedCallback
                        );

                        $transaction->update([
                            'status' => $apiData['status'],
                            'transaction_id' => $apiData['id'] ?? $apiData['transaction_id'] ?? $transaction->transaction_id,
                            'payment_method' => $apiData['channel'] ?? $apiData['paymentMethod'] ?? $transaction->payment_method,
                            'amount' => $apiData['collectedAmount'] ?? $apiData['amount'] ?? $transaction->amount,
                            'currency' => $apiData['collectedCurrency'] ?? $apiData['currency'] ?? $transaction->currency,
                            'phone' => $apiData['customer']['customerPhoneNumber'] ?? $apiData['paymentPhoneNumber'] ?? $transaction->phone,
                            'customer_name' => TransactionFieldResolver::memberName(
                                $transaction->customer_name,
                                $apiData['customer']['customerName'] ?? $apiData['customerName'] ?? null
                            ),
                            'payer_name' => TransactionFieldResolver::payerName(
                                $transaction->payer_name,
                                $apiData['customer']['customerName'] ?? $apiData['payer_name'] ?? null
                            ),
                            'email' => $apiData['customer']['customerEmail'] ?? $apiData['email'] ?? $transaction->email,
                            'description' => $resolvedDescription,
                            'callback_data' => $mergedCallback,
                            'updated_at' => now()
                        ]);
                    }
                }
                
                $paymentData = [
                    'id' => $transaction->id,
                    'orderReference' => $transaction->order_reference,
                    'transaction_id' => $transaction->transaction_id,
                    'status' => $transaction->status,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                    'phone' => $transaction->phone,
                    'payer_name' => $transaction->payer_name,
                    'customer_name' => $transaction->customer_name,
                    'email' => $transaction->email,
                    'description' => $transaction->resolvedDescription(),
                    'type' => $transaction->type,
                    'payment_method' => $transaction->payment_method,
                    'created_at' => $transaction->created_at,
                    'updated_at' => $transaction->updated_at
                ];
            } else {
                $paymentData = $this->api->queryPaymentStatus($orderReference);
            }
            
            return response()->json([
                'success' => true,
                'data' => $paymentData,
                'transaction' => $transaction ? $transaction->toArray() : null
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Send SMS notification for payment initiation
     */
    private function sendPaymentInitiationNotification(string $phoneNumber, string $orderReference, float $amount, string $payerName = '')
    {
        try {
            if (!config('messaging.enabled') || !config('messaging.notifications.payment_confirmation')) {
                Log::info('SMS notifications disabled, skipping payment initiation notification');
                return;
            }

            $message = "FEEDTAN: Payment Initiated\n" .
                       "Reference: {$orderReference}\n" .
                       "Payer: {$payerName}\n" .
                       "Amount: " . number_format($amount, 2) . " TZS\n" .
                       "Method: USSD Push\n" .
                       "Phone: {$phoneNumber}\n" .
                       "Status: PENDING\n" .
                       "Please complete the payment on your phone.";

            $result = $this->messaging->sendSMS($phoneNumber, $message);
            
            Log::info('Payment initiation SMS sent successfully', [
                'reference' => $orderReference,
                'payer_name' => $payerName,
                'phone_number' => $phoneNumber,
                'amount' => $amount,
                'result' => $result
            ]);

        } catch (Exception $e) {
            Log::error('Failed to send payment initiation SMS', [
                'reference' => $orderReference,
                'payer_name' => $payerName,
                'phone_number' => $phoneNumber,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send SMS notification for insufficient funds
     */
    private function sendInsufficientFundsNotification(string $phoneNumber, string $orderReference, float $amount, string $payerName = '')
    {
        try {
            if (!config('messaging.enabled') || !config('messaging.notifications.insufficient_funds')) {
                Log::info('SMS notifications disabled, skipping insufficient funds notification');
                return;
            }

            $paymentData = [
                'reference' => $orderReference,
                'amount' => $amount,
                'currency' => 'TZS',
                'phone_number' => $phoneNumber,
                'payer_name' => $payerName
            ];

            $result = $this->messaging->sendInsufficientFundsNotification($phoneNumber, $paymentData);
            
            Log::info('Insufficient funds SMS sent successfully', [
                'reference' => $orderReference,
                'payer_name' => $payerName,
                'phone_number' => $phoneNumber,
                'amount' => $amount,
                'result' => $result
            ]);

        } catch (Exception $e) {
            Log::error('Failed to send insufficient funds SMS', [
                'reference' => $orderReference,
                'payer_name' => $payerName,
                'phone_number' => $phoneNumber,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send SMS notification for successful payment
     */
    public function sendPaymentSuccessNotification(array $paymentData)
    {
        try {
            if (!config('messaging.enabled') || !config('messaging.notifications.payment_confirmation')) {
                Log::info('SMS notifications disabled, skipping payment success notification');
                return;
            }

            $phoneNumber = $paymentData['paymentPhoneNumber'] ?? null;
            if (!$phoneNumber) {
                Log::warning('No phone number available for payment success notification');
                return;
            }

            $result = $this->messaging->sendPaymentConfirmation($phoneNumber, $paymentData);
            
            Log::info('Payment success SMS sent successfully', [
                'reference' => $paymentData['orderReference'] ?? 'N/A',
                'phone_number' => $phoneNumber,
                'result' => $result
            ]);

        } catch (Exception $e) {
            Log::error('Failed to send payment success SMS', [
                'payment_data' => $paymentData,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Resend USSD notification for existing payment
     */
    public function resendUssd(Request $request)
    {
        $validated = $request->validate([
            'order_reference' => 'required|string|max:50'
        ]);

        try {
            $orderReference = $validated['order_reference'];
            
            // Try to get payment data using multiple methods (same as other methods)
            $paymentData = null;
            
            try {
                // First try queryPaymentStatus
                $paymentData = $this->api->queryPaymentStatus($orderReference);
                Log::info('Payment data from queryPaymentStatus for USSD resend', ['orderReference' => $orderReference, 'data' => $paymentData]);
            } catch (Exception $e) {
                Log::warning('queryPaymentStatus failed for USSD resend, trying queryAllPayments', ['orderReference' => $orderReference, 'error' => $e->getMessage()]);
            }
            
            // If queryPaymentStatus failed or returned empty, try queryAllPayments
            if (!$paymentData || empty($paymentData)) {
                try {
                    $params = [
                        'limit' => 1,
                        'orderReference' => $orderReference
                    ];
                    $response = $this->api->queryAllPayments($params);
                    Log::info('Payment data from queryAllPayments for USSD resend', ['orderReference' => $orderReference, 'response' => $response]);
                    
                    if (isset($response['data']) && !empty($response['data'])) {
                        $paymentData = $response['data'][0]; // Get the first (and only) payment
                    }
                } catch (Exception $e) {
                    Log::error('queryAllPayments also failed for USSD resend', ['orderReference' => $orderReference, 'error' => $e->getMessage()]);
                }
            }
            
            // Extract payment data from array wrapper if needed
            if (is_array($paymentData) && isset($paymentData[0])) {
                $paymentData = $paymentData[0];
            }
            
            if (!$paymentData || !isset($paymentData['status'])) {
                Log::warning('Payment not found for USSD resend', ['orderReference' => $orderReference, 'paymentData' => $paymentData]);
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found or invalid reference'
                ], 404);
            }

            // Check if payment can be resent (only for pending, processing, or failed)
            $status = $paymentData['status'] ?? '';
            if (!in_array($status, ['PENDING', 'PROCESSING', 'FAILED'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'USSD can only be resent for pending, processing, or failed payments'
                ], 400);
            }

            // Extract payment details
            $amount = $paymentData['collectedAmount'] ?? $paymentData['amount'] ?? 0;
            $phoneNumber = $paymentData['paymentPhoneNumber'] ?? 
                          $paymentData['customer']['customerPhoneNumber'] ?? 
                          $paymentData['phoneNumber'] ?? null;
            $payerName = $paymentData['payer_name'] ?? 
                        $paymentData['customer']['customerName'] ?? 
                        $paymentData['customerName'] ?? 'Customer';

            if (!$phoneNumber) {
                return response()->json([
                    'success' => false,
                    'message' => 'Phone number not found for this payment'
                ], 400);
            }

            // Validate phone number format
            if (!$this->api->validatePhoneNumber($phoneNumber)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid phone number format'
                ], 400);
            }

            // Create new payment request to resend USSD
            $newOrderReference = $this->api->generateOrderReference();
            $formattedAmount = $this->api->formatAmount($amount);
            
            // Log the API request details for debugging
            Log::info('Attempting to resend USSD', [
                'original_reference' => $orderReference,
                'new_reference' => $newOrderReference,
                'phone_number' => $phoneNumber,
                'amount' => $formattedAmount,
                'payer_name' => $payerName,
                'description' => 'USSD Resend for original payment: ' . $orderReference
            ]);

            $ussdResponse = $this->api->createUssdPushPayment([
                'amount' => $formattedAmount,
                'currency' => 'TZS',
                'customer_phone' => $phoneNumber,
                'customer_name' => $payerName,
                'order_reference' => $newOrderReference,
                'description' => 'USSD Resend for original payment: ' . $orderReference
            ]);

            Log::info('USSD API response received', [
                'response' => $ussdResponse,
                'response_type' => gettype($ussdResponse),
                'response_keys' => is_array($ussdResponse) ? array_keys($ussdResponse) : 'not_array'
            ]);

            if ($ussdResponse && isset($ussdResponse['status']) && in_array($ussdResponse['status'], ['SUCCESS', 'PROCESSING', 'PENDING'])) {
                Log::info('USSD resent successfully', [
                    'original_reference' => $orderReference,
                    'new_reference' => $newOrderReference,
                    'phone_number' => $phoneNumber,
                    'amount' => $amount,
                    'payer_name' => $payerName
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "USSD notification sent successfully to {$phoneNumber}",
                    'data' => [
                        'new_reference' => $newOrderReference,
                        'phone_number' => $phoneNumber,
                        'amount' => $amount
                    ]
                ]);

            } else {
                $errorMessage = 'Failed to send USSD notification';
                
                // Try to extract more specific error information
                if (!$ussdResponse) {
                    $errorMessage = 'No response from USSD API';
                } elseif (!is_array($ussdResponse)) {
                    $errorMessage = 'Invalid response format from USSD API';
                } elseif (!isset($ussdResponse['status'])) {
                    $errorMessage = 'USSD API response missing status field';
                } elseif (isset($ussdResponse['message'])) {
                    $errorMessage = 'USSD API Error: ' . $ussdResponse['message'];
                } elseif (isset($ussdResponse['error'])) {
                    $errorMessage = 'USSD API Error: ' . $ussdResponse['error'];
                } elseif (isset($ussdResponse['status']) && !in_array($ussdResponse['status'], ['SUCCESS', 'PROCESSING', 'PENDING'])) {
                    $errorMessage = 'USSD API returned status: ' . $ussdResponse['status'];
                }

                Log::error('Failed to resend USSD', [
                    'reference' => $orderReference,
                    'phone_number' => $phoneNumber,
                    'response' => $ussdResponse,
                    'error_message' => $errorMessage
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $errorMessage . '. Please try again.',
                    'debug_info' => [
                        'api_response' => $ussdResponse,
                        'phone_number' => $phoneNumber,
                        'amount' => $amount
                    ]
                ], 500);
            }

        } catch (Exception $e) {
            Log::error('Error resending USSD', [
                'order_reference' => $validated['order_reference'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while resending USSD. Please try again.'
            ], 500);
        }
    }

    private function applyHistoryTabFilter($query, string $activeStatus = 'SETTLED'): void
    {
        if ($activeStatus === 'FAILED') {
            $query->whereIn('status', ['FAILED', 'ERROR']);
        } else {
            $query->whereIn('status', ['SETTLED', 'SUCCESS']);
        }
    }
}
