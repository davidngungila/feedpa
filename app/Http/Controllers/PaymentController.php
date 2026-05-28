<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\ClickPesaAPIService;
use App\Services\MessagingServiceAPI;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PaymentController extends Controller
{
    protected ClickPesaAPIService $api;
    protected MessagingServiceAPI $messaging;

    public function __construct(ClickPesaAPIService $api, MessagingServiceAPI $messaging)
    {
        $this->api = $api;
        $this->messaging = $messaging;
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
        $validated = $request->validate([
            'payer_name' => 'required|string|min:2|max:100',
            'amount' => 'required|numeric|min:100|max:1000000',
            'phone_number' => 'required|string|regex:/^255[67]\d{8}$/',
            'description' => 'nullable|string|max:255'
        ]);

        try {
            $amount = $this->api->formatAmount($validated['amount']);
            $phoneNumber = $this->api->validatePhoneNumber($validated['phone_number']);
            $payerName = $validated['payer_name'];
            $description = $request->input('description') ?: 'Malipo ya FEEDTAN';
            $orderReference = $this->api->generateOrderReference();

            if (!$phoneNumber) {
                return back()->with('error', 'Invalid phone number. Please use format: 255712345678');
            }

            // Preview the payment first
            $preview = $this->api->previewUSSDPush($amount, $orderReference, $phoneNumber, true);

            if (empty($preview['activeMethods'])) {
                return back()->with('error', 'No active payment methods available for this phone number');
            }

            // Save transaction to database first
            $transaction = Transaction::create([
                'order_reference' => $orderReference,
                'status' => 'PROCESSING',
                'amount' => $validated['amount'],
                'currency' => 'TZS',
                'phone' => $phoneNumber,
                'customer_name' => $payerName, // Store "Jina La Mwanachama" in customer_name
                'payer_name' => $payerName,    // Also set initial payer_name
                'description' => $description,
                'type' => 'payment',
                'callback_data' => null,
            ]);

            // Initiate the payment with customer details
            $customerDetails = [
                'customerName' => $payerName,
                'description' => $description
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
                    'payer_name' => $payerName
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
                        'description' => $transaction->description,
                        'type' => $transaction->type,
                        'payment_method' => $transaction->payment_method,
                        'sms_sent' => $transaction->sms_sent,
                        'sms_message' => $transaction->sms_message,
                        'sms_sent_at' => $transaction->sms_sent_at,
                        'sms_error' => $transaction->sms_error,
                        'created_at' => $transaction->created_at,
                        'updated_at' => $transaction->updated_at
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
                            $transaction->update([
                                'status' => $apiData['status'],
                                'transaction_id' => $apiData['id'] ?? $apiData['transaction_id'] ?? $transaction->transaction_id,
                                'payment_method' => $apiData['channel'] ?? $apiData['paymentMethod'] ?? $transaction->payment_method,
                                'amount' => $apiData['collectedAmount'] ?? $apiData['amount'] ?? $transaction->amount,
                                'currency' => $apiData['collectedCurrency'] ?? $apiData['currency'] ?? $transaction->currency,
                                'phone' => $apiData['customer']['customerPhoneNumber'] ?? $apiData['paymentPhoneNumber'] ?? $transaction->phone,
                                'payer_name' => $apiData['customer']['customerName'] ?? $apiData['payer_name'] ?? $transaction->payer_name,
                                'email' => $apiData['customer']['customerEmail'] ?? $apiData['email'] ?? $transaction->email,
                                // Strictly preserve local description if it has any content
                                'description' => (!empty($transaction->description) && $transaction->description !== 'N/A') ? $transaction->description : ($apiData['description'] ?? $apiData['narrative'] ?? $transaction->description),
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
                                'description' => $transaction->description,
                                'type' => $transaction->type,
                                'payment_method' => $transaction->payment_method,
                                'sms_sent' => $transaction->sms_sent,
                                'sms_message' => $transaction->sms_message,
                                'sms_sent_at' => $transaction->sms_sent_at,
                                'sms_error' => $transaction->sms_error,
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
                                'order_reference' => $orderReference,
                                'transaction_id' => $apiPaymentData['id'] ?? $apiPaymentData['transaction_id'] ?? null,
                                'status' => $apiPaymentData['status'] ?? 'UNKNOWN',
                                'amount' => $apiPaymentData['collectedAmount'] ?? $apiPaymentData['amount'] ?? 0,
                                'currency' => $apiPaymentData['collectedCurrency'] ?? 'TZS',
                                'phone' => $apiPaymentData['customer']['customerPhoneNumber'] ?? $apiPaymentData['paymentPhoneNumber'] ?? null,
                                'payer_name' => $apiPaymentData['customer']['customerName'] ?? $apiPaymentData['payer_name'] ?? null,
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

        return view('payments.status', compact('paymentData', 'error', 'orderReference'));
    }

    /**
     * Payment history with filters
     */
    public function history(Request $request)
    {
        $query = Transaction::query()->where('type', 'payment');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('currency')) {
            $query->where('currency', $request->currency);
        }
        if ($request->filled('order_reference')) {
            $query->where('order_reference', 'like', '%' . $request->order_reference . '%');
        }
        if ($request->filled('phone')) {
            $query->where('phone', 'like', '%' . $request->phone . '%');
        }
        if ($request->filled('payer_name')) {
            $query->where('payer_name', 'like', '%' . $request->payer_name . '%');
        }
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $totalCount = $query->count();
        $payments = $query->orderBy('created_at', 'desc')->paginate($request->get('limit', 20));

        // Calculate statistics
        $successCount = Transaction::where('type', 'payment')->whereIn('status', ['SUCCESS', 'SETTLED'])->count();
        $pendingCount = Transaction::where('type', 'payment')->whereIn('status', ['PENDING', 'PROCESSING'])->count();
        $failedCount = Transaction::where('type', 'payment')->where('status', 'FAILED')->count();
        
        Log::info('Payment history loaded from database', [
            'count' => $payments->count(),
            'total' => $totalCount
        ]);

        return view('payments.history', compact('payments', 'totalCount', 'successCount', 'pendingCount', 'failedCount'));
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

            // Get filtered payments from database
            $query = Transaction::query()->where('type', 'payment');

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('currency')) {
                $query->where('currency', $request->currency);
            }
            if ($request->filled('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }

            $payments = $query->orderBy('created_at', 'desc')->get()->toArray();
            
            // Selected columns
            $columns = $request->get('columns', ['order_reference', 'transaction_id', 'status', 'amount', 'currency', 'payer_name', 'phone', 'description', 'payment_method', 'created_at']);

            $pdf = Pdf::loadView('payments.exports.pdf', [
                'payments' => $payments,
                'columns' => $columns
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
            // Get filtered payments from database
            $query = Transaction::query()->where('type', 'payment');

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('currency')) {
                $query->where('currency', $request->currency);
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

            $payments = $query->orderBy('created_at', 'desc')->get()->toArray();
            
            // Selected columns
            $columns = $request->get('columns', ['order_reference', 'transaction_id', 'status', 'amount', 'currency', 'payer_name', 'phone', 'email', 'description', 'payment_method', 'created_at', 'updated_at']);

            return Excel::download(new \App\Exports\PaymentHistoryExport($payments, $columns), 'payment-history-' . date('Y-m-d') . '.xlsx');
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
            if ($transaction) {
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
                    'description' => $transaction->description,
                    'createdAt' => $transaction->created_at,
                    'id' => $transaction->transaction_id
                ];
            } else {
                // Fallback to API
                $apiResponse = $this->api->queryPaymentStatus($orderReference);
                if ($apiResponse && is_array($apiResponse)) {
                    $paymentData = isset($apiResponse[0]) ? $apiResponse[0] : $apiResponse;
                    $paymentData['orderReference'] = $paymentData['orderReference'] ?? $orderReference;
                }
            }
            
            if (!$paymentData) {
                return back()->with('error', 'Payment not found');
            }

            // Generate QR code with full payment details
            $qrContent = "ClickPesa Payment Receipt\n" .
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
            $paymentData = $this->api->queryPaymentStatus($request->order_reference);
            
            return response()->json([
                'success' => true,
                'data' => $paymentData
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
}
