<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\ClickPesaAPIService;
use App\Support\TransactionFieldResolver;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EcommercePaymentController extends Controller
{
    protected ClickPesaAPIService $api;

    public function __construct(ClickPesaAPIService $api)
    {
        $this->api = $api;
    }

    /**
     * Initiate a payment from e-commerce system
     */
    public function initiatePayment(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:500',
            'phone_number' => 'required|string',
            'payer_name' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'order_reference' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'callback_url' => 'nullable|url|max:255',
            'metadata' => 'nullable|array',
        ]);

        try {
            $amount = $this->api->formatAmount($validated['amount']);
            $phoneNumber = $this->api->validatePhoneNumber($validated['phone_number']);
            
            if (!$phoneNumber) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid phone number. Please use format: 255712345678'
                ], 422);
            }

            // Generate order reference or use provided one
            $orderReference = !empty($validated['order_reference']) 
                ? $validated['order_reference'] 
                : $this->api->generateOrderReference('ECOMM');

            // Preview payment to check available methods
            $preview = $this->api->previewUSSDPush($amount, $orderReference, $phoneNumber, true);
            
            if (empty($preview['activeMethods'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active payment methods available for this phone number'
                ], 422);
            }

            // Save transaction to database
            $transaction = Transaction::create([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'order_reference' => $orderReference,
                'status' => 'PROCESSING',
                'amount' => $validated['amount'],
                'currency' => 'TZS',
                'phone' => $phoneNumber,
                'customer_name' => $validated['payer_name'],
                'payer_name' => $validated['payer_name'],
                'email' => $validated['email'] ?? null,
                'description' => $validated['description'],
                'type' => 'ecommerce_payment',
                'callback_data' => array_merge(
                    TransactionFieldResolver::initialCallbackSnapshot(
                        $validated['description'],
                        'ecommerce_api'
                    ),
                    [
                        'callback_url' => $validated['callback_url'] ?? null,
                        'metadata' => $validated['metadata'] ?? null,
                    ]
                ),
            ]);

            // Prepare customer details for payment initiation
            $customerDetails = [
                'customerName' => $validated['payer_name'],
                'description' => $validated['description'],
            ];
            
            if (!empty($validated['email'])) {
                $customerDetails['email'] = $validated['email'];
            }

            // Initiate the payment
            $payment = $this->api->initiateUSSDPush($amount, $orderReference, $phoneNumber, null, $customerDetails);

            // Update transaction with API response
            if (isset($payment['transactionId'])) {
                $transaction->update([
                    'transaction_id' => $payment['transactionId'],
                    'status' => 'PENDING',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment initiated successfully. USSD push sent to ' . $phoneNumber,
                'data' => [
                    'order_reference' => $orderReference,
                    'transaction_id' => $payment['transactionId'] ?? null,
                    'amount' => $amount,
                    'phone_number' => $phoneNumber,
                    'status' => $transaction->status,
                ]
            ], 200);

        } catch (Exception $e) {
            Log::error('E-commerce payment initiation failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check payment status
     */
    public function checkStatus($orderReference)
    {
        try {
            // First, try to get transaction from database
            $transaction = Transaction::where('order_reference', $orderReference)->first();
            
            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found with this order reference'
                ], 404);
            }

            // If status is still PROCESSING or PENDING, try to get updated status from API
            if (in_array($transaction->status, ['PROCESSING', 'PENDING'])) {
                try {
                    $apiResponse = $this->api->queryPaymentStatus($orderReference);
                    
                    // Handle wrapped API response (data at index 0)
                    $apiData = null;
                    if ($apiResponse && is_array($apiResponse) && isset($apiResponse[0])) {
                        $apiData = $apiResponse[0];
                    } elseif ($apiResponse && is_array($apiResponse)) {
                        $apiData = $apiResponse;
                    }
                    
                    // Update transaction with API data
                    if ($apiData && isset($apiData['status'])) {
                        $mergedCallback = TransactionFieldResolver::mergeCallbackData(
                            $transaction->callback_data,
                            $apiData
                        );
                        
                        $transaction->update([
                            'status' => $apiData['status'],
                            'transaction_id' => $apiData['id'] ?? $apiData['transaction_id'] ?? $transaction->transaction_id,
                            'payment_method' => $apiData['channel'] ?? $apiData['paymentMethod'] ?? $transaction->payment_method,
                            'amount' => $apiData['collectedAmount'] ?? $apiData['amount'] ?? $transaction->amount,
                            'currency' => $apiData['collectedCurrency'] ?? $apiData['currency'] ?? $transaction->currency,
                            'callback_data' => $mergedCallback,
                            'updated_at' => now()
                        ]);
                    }
                } catch (Exception $apiException) {
                    Log::error('Failed to update transaction from API: ' . $apiException->getMessage());
                    // Continue with existing transaction data
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'order_reference' => $transaction->order_reference,
                    'transaction_id' => $transaction->transaction_id,
                    'status' => $transaction->status,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                    'phone_number' => $transaction->phone,
                    'payer_name' => $transaction->payer_name,
                    'email' => $transaction->email,
                    'description' => $transaction->description,
                    'payment_method' => $transaction->payment_method,
                    'created_at' => $transaction->created_at->toISOString(),
                    'updated_at' => $transaction->updated_at->toISOString(),
                ]
            ], 200);

        } catch (Exception $e) {
            Log::error('E-commerce payment status check failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get transaction history (optional, for e-commerce system)
     */
    public function transactionHistory(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'status' => 'nullable|string',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Transaction::where('type', 'ecommerce_payment');

        if (!empty($validated['phone_number'])) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $validated['phone_number']);
            $query->where('phone', $cleanPhone);
        }

        if (!empty($validated['start_date'])) {
            $query->whereDate('created_at', '>=', $validated['start_date']);
        }

        if (!empty($validated['end_date'])) {
            $query->whereDate('created_at', '<=', $validated['end_date']);
        }

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        $perPage = $validated['per_page'] ?? 20;
        $transactions = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $transactions->items(),
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
                'last_page' => $transactions->lastPage(),
            ]
        ], 200);
    }
}
