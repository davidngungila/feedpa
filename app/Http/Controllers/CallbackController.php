<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CallbackController extends Controller
{
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
        $status = $data['status'] ?? null;
        $transactionId = $data['transaction_id'] ?? $data['id'] ?? null;
        $orderReference = $data['orderReference'] ?? null;
        $amount = $data['amount'] ?? $data['collectedAmount'] ?? null;
        $phone = $data['phone'] ?? $data['paymentPhoneNumber'] ?? null;

        try {
            // Find transaction by order reference
            $transaction = Transaction::where('order_reference', $orderReference)->first();

            if ($transaction) {
                // Update transaction status
                $transaction->update([
                    'status' => $status,
                    'transaction_id' => $transactionId,
                    'amount' => $amount,
                    'phone' => $phone,
                    'callback_data' => $data,
                    'callback_received_at' => now()
                ]);

                Log::info('Transaction updated successfully', [
                    'order_reference' => $orderReference,
                    'status' => $status,
                    'transaction_id' => $transactionId
                ]);

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
     * Determine transaction type from callback data
     */
    private function determineTransactionType(array $data): string
    {
        if (isset($data['payoutReference'])) {
            return 'payout';
        }
        
        if (isset($data['billPayNumber'])) {
            return 'billpay';
        }
        
        return 'payment';
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
