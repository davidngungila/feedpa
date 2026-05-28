<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\MessagingServiceAPI;
use App\Services\EmailNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CallbackController extends Controller
{
    protected MessagingServiceAPI $messaging;
    protected EmailNotificationService $emailNotification;

    public function __construct(MessagingServiceAPI $messaging, EmailNotificationService $emailNotification)
    {
        $this->messaging = $messaging;
        $this->emailNotification = $emailNotification;
    }

    /**
     * Handle ClickPesa webhook callbacks
     */
    public function handle(Request $request)
    {
        // Log incoming callback for debugging
        Log::info('ClickPesa Callback Received', [
            'payload' => $request->all(),
            'headers' => $request->headers->all()
        ]);

        // Validate callback signature if secret key is configured
        $secretKey = config('clickpesa.callback.secret_key');
        if ($secretKey) {
            $signature = $request->header('X-Signature');
            $payload = $request->getContent();
            $expectedSignature = hash_hmac('sha256', $payload, $secretKey);
            
            if ($signature !== $expectedSignature) {
                Log::warning('Invalid callback signature', [
                    'received' => $signature,
                    'expected' => $expectedSignature
                ]);
                return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 401);
            }
        }

        $data = $request->all();
        $event = $data['event'] ?? null;
        $status = $data['status'] ?? null;
        $transactionId = $data['transaction_id'] ?? $data['id'] ?? null;
        $orderReference = $data['orderReference'] ?? null;
        $amount = $data['amount'] ?? $data['collectedAmount'] ?? null;
        $phone = $data['phone'] ?? $data['paymentPhoneNumber'] ?? $data['customer']['customerPhoneNumber'] ?? null;
        $payerName = $data['payer_name'] ?? $data['customer']['customerName'] ?? null;

        try {
            // Find transaction by order reference
            $transaction = Transaction::where('order_reference', $orderReference)->first();

            if ($transaction) {
                // Avoid overwriting existing names with phone numbers from callback
                $finalPayerName = $payerName ?? $transaction->payer_name;
                if ($payerName && is_numeric($payerName) && strlen($payerName) > 5 && $transaction->payer_name && !is_numeric($transaction->payer_name)) {
                    $finalPayerName = $transaction->payer_name;
                }

                $finalCustomerName = $transaction->customer_name ?? $payerName;
                if ($payerName && is_numeric($payerName) && strlen($payerName) > 5 && $transaction->customer_name && !is_numeric($transaction->customer_name)) {
                    $finalCustomerName = $transaction->customer_name;
                }

                // Update transaction status
                $transaction->update([
                    'status' => $status,
                    'transaction_id' => $transactionId,
                    'amount' => $amount,
                    'phone' => $phone,
                    'payer_name' => $finalPayerName,
                    'customer_name' => $finalCustomerName,
                    'callback_data' => $data,
                    'callback_received_at' => now()
                ]);

                Log::info('Transaction updated successfully', [
                    'order_reference' => $orderReference,
                    'status' => $status,
                    'transaction_id' => $transactionId
                ]);

                // Send notifications only when PAYMENT RECEIVED event is triggered
                if ($event === 'PAYMENT RECEIVED' && in_array($status, ['SUCCESS', 'SETTLED'])) {
                    $this->sendPaymentSuccessNotification($data, $transaction);
                    $this->sendPaymentSuccessEmailNotification($data);
                }

                return response()->json(['status' => 'success', 'message' => 'Callback processed']);
            } else {
                Log::warning('Transaction not found for callback', [
                    'order_reference' => $orderReference,
                    'transaction_id' => $transactionId
                ]);

                // Create new transaction if not found (for payouts or other scenarios)
                Transaction::create([
                    'order_reference' => $orderReference,
                    'transaction_id' => $transactionId,
                    'status' => $status,
                    'amount' => $amount,
                    'phone' => $phone,
                    'callback_data' => $data,
                    'callback_received_at' => now(),
                    'type' => $this->determineTransactionType($data)
                ]);

                return response()->json(['status' => 'success', 'message' => 'New transaction created']);
            }
        } catch (\Exception $e) {
            Log::error('Error processing callback', [
                'error' => $e->getMessage(),
                'order_reference' => $orderReference,
                'transaction_id' => $transactionId
            ]);

            return response()->json(['status' => 'error', 'message' => 'Processing failed'], 500);
        }
    }

    /**
     * Send SMS notification for successful payment via webhook
     */
    private function sendPaymentSuccessNotification(array $webhookData, $transaction)
    {
        try {
            if (!config('messaging.enabled') || !config('messaging.notifications.payment_confirmation')) {
                Log::info('SMS notifications disabled, skipping payment success notification');
                return;
            }

            $phoneNumber = $webhookData['customer']['customerPhoneNumber'] ?? $transaction->phone;
            if (!$phoneNumber) {
                Log::warning('No phone number available for payment success notification');
                return;
            }

            // Prepare payment data in the format expected by messaging service
            $paymentData = [
                'orderReference' => $webhookData['orderReference'] ?? $transaction->order_reference,
                'id' => $webhookData['id'] ?? $transaction->transaction_id,
                'status' => $webhookData['status'],
                'collectedAmount' => $webhookData['collectedAmount'] ?? $transaction->amount,
                'collectedCurrency' => $webhookData['collectedCurrency'] ?? 'TZS',
                'paymentPhoneNumber' => $phoneNumber,
                'channel' => $webhookData['channel'] ?? 'Mobile Money',
                'customer' => [
                    'customerName' => $webhookData['customer']['customerName'] ?? $transaction->payer_name,
                    'customerEmail' => $webhookData['customer']['customerEmail'] ?? null,
                    'customerPhoneNumber' => $phoneNumber
                ],
                'createdAt' => $webhookData['createdAt'] ?? $transaction->created_at,
                'updatedAt' => $webhookData['updatedAt'] ?? now()
            ];

            $result = $this->messaging->sendPaymentConfirmation($phoneNumber, $paymentData);
            
            // Update transaction with SMS tracking details
            if ($result) {
                $transaction->update([
                    'sms_sent' => true,
                    'sms_message' => $this->generateSMSMessage($paymentData),
                    'sms_sent_at' => now(),
                    'sms_error' => null
                ]);
                
                Log::info('Payment success SMS sent via webhook', [
                    'reference' => $paymentData['orderReference'],
                    'phone_number' => $phoneNumber,
                    'amount' => $paymentData['collectedAmount'],
                    'result' => $result
                ]);
            } else {
                $transaction->update([
                    'sms_sent' => false,
                    'sms_message' => $this->generateSMSMessage($paymentData),
                    'sms_sent_at' => null,
                    'sms_error' => 'Failed to send SMS'
                ]);
                
                Log::error('Failed to send payment success SMS', [
                    'reference' => $paymentData['orderReference'],
                    'phone_number' => $phoneNumber,
                    'amount' => $paymentData['collectedAmount']
                ]);
            }

        } catch (Exception $e) {
            // Update transaction with error details
            $transaction->update([
                'sms_sent' => false,
                'sms_message' => $this->generateSMSMessage($webhookData),
                'sms_sent_at' => null,
                'sms_error' => $e->getMessage()
            ]);
            
            Log::error('Failed to send payment success SMS via webhook', [
                'webhook_data' => $webhookData,
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send email notification for successful payment
     */
    private function sendPaymentSuccessEmailNotification(array $webhookData)
    {
        try {
            $this->emailNotification->sendPaymentSuccessNotification($webhookData);
            
            Log::info('Payment success email notification sent', [
                'reference' => $webhookData['orderReference'] ?? 'N/A',
                'amount' => $webhookData['collectedAmount'] ?? 0
            ]);
            
        } catch (Exception $e) {
            Log::error('Failed to send payment success email notification', [
                'webhook_data' => $webhookData,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Generate SMS message for payment confirmation
     */
    private function generateSMSMessage(array $paymentData): string
    {
        $amount = number_format($paymentData['collectedAmount'] ?? 0, 0);
        $reference = $paymentData['orderReference'] ?? 'N/A';
        $customerName = $paymentData['customer']['customerName'] ?? 'Mteja';
        $phoneNumber = $paymentData['customer']['customerPhoneNumber'] ?? $paymentData['paymentPhoneNumber'] ?? '255622239304';
        
        return "Malipo yamefanikiwa. Tumepokea kiasi cha TZS {$amount} kutoka kwa {$phoneNumber}  tarehe " . \Carbon\Carbon::parse($paymentData['createdAt'] ?? now())->format('d M Y, H:i') . ".  Rejea: {$reference}. Asante kwa kutumia huduma zetu.";
    }

    /**
     * Determine transaction type from callback data
     */
    private function determineTransactionType(array $data): string
    {
        return match (strtolower($data['type'] ?? 'payment')) {
            'payment' => 'payment',
            'payout' => 'payout',
            'refund' => 'refund',
            default => 'payment'
        };
    }

    /**
     * Test callback endpoint for development
     */
    public function test(Request $request)
    {
        if (!app()->environment('local', 'testing')) {
            return response()->json(['error' => 'Test endpoint only available in development'], 403);
        }

        Log::info('Test callback received', [
            'payload' => $request->all()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Test callback received',
            'timestamp' => now()->toISOString()
        ]);
    }
}
