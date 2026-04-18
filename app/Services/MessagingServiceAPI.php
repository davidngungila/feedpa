<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class MessagingServiceAPI
{
    private string $baseUrl;
    private string $token;
    private string $senderId;

    public function __construct()
    {
        $this->baseUrl = 'https://messaging-service.co.tz';
        $this->token = config('messaging.token', 'f9a89f439206e27169ead766463ca92c');
        $this->senderId = config('messaging.sender_id', 'FEEDTAN');
    }

    /**
     * Send SMS via Messaging Service API V2
     */
    public function sendSMS(string $to, string $message, array $options = []): array
    {
        try {
            $url = $this->baseUrl . '/api/sms/v2/text/single';
            
            $data = [
                'from' => $options['from'] ?? $this->senderId,
                'to' => $this->formatPhoneNumber($to),
                'text' => $message
            ];

            // Add optional parameters
            if (isset($options['date']) && isset($options['time'])) {
                $data['date'] = $options['date'];
                $data['time'] = $options['time'];
            }

            Log::info('Sending SMS via Messaging Service', [
                'to' => $data['to'],
                'message' => substr($message, 0, 100) . '...',
                'url' => $url
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->timeout(30)->post($url, $data);

            if ($response->successful()) {
                Log::info('SMS sent successfully', ['response' => $response->json()]);
                return $response->json();
            } else {
                $errorMessage = $this->getErrorMessage($response);
                Log::error('SMS sending failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'error' => $errorMessage
                ]);
                throw new Exception($errorMessage);
            }
        } catch (Exception $e) {
            Log::error('SMS API error: ' . $e->getMessage(), [
                'to' => $to,
                'message' => substr($message, 0, 100)
            ]);
            throw $e;
        }
    }

    /**
     * Send bill generation notification
     */
    public function sendBillNotification(string $phoneNumber, array $billData): array
    {
        $message = $this->formatBillMessage($billData);
        
        return $this->sendSMS($phoneNumber, $message);
    }

    /**
     * Send payment confirmation notification
     */
    public function sendPaymentConfirmation(string $phoneNumber, array $paymentData): array
    {
        $message = $this->formatPaymentMessage($paymentData);
        
        return $this->sendSMS($phoneNumber, $message);
    }

    /**
     * Send insufficient funds notification
     */
    public function sendInsufficientFundsNotification(string $phoneNumber, array $paymentData): array
    {
        $message = $this->formatInsufficientFundsMessage($paymentData);
        
        return $this->sendSMS($phoneNumber, $message);
    }

    /**
     * Send scheduled SMS
     */
    public function sendScheduledSMS(string $to, string $message, string $date, string $time, array $options = []): array
    {
        return $this->sendSMS($to, $message, array_merge($options, [
            'date' => $date,
            'time' => $time
        ]));
    }

    /**
     * Send SMS via link (GET method)
     */
    public function sendSMSViaLink(string $to, string $message): array
    {
        try {
            $url = $this->baseUrl . '/link/sms/v2/text/single';
            
            $params = [
                'token' => $this->token,
                'from' => $this->senderId,
                'to' => $this->formatPhoneNumber($to),
                'text' => $message
            ];

            Log::info('Sending SMS via link', ['to' => $params['to']]);

            $response = Http::timeout(30)->get($url, $params);

            if ($response->successful()) {
                return $response->json();
            } else {
                $errorMessage = $this->getErrorMessage($response);
                throw new Exception($errorMessage);
            }
        } catch (Exception $e) {
            Log::error('SMS link API error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Format bill notification message
     */
    private function formatBillMessage(array $billData): string
    {
        $amount = number_format($billData['amount'] ?? 0, 2);
        $currency = $billData['currency'] ?? 'TZS';
        $reference = $billData['reference'] ?? 'N/A';
        $description = $billData['description'] ?? 'Bill Payment';
        $dueDate = $billData['due_date'] ?? 'ASAP';

        return "FEEDTAN: Bill Generated\n" .
               "Reference: {$reference}\n" .
               "Description: {$description}\n" .
               "Amount: {$amount} {$currency}\n" .
               "Due Date: {$dueDate}\n" .
               "Pay using FEEDTAN control number: {$reference}\n" .
               "Thank you for using FEEDTAN services.";
    }

    /**
     * Format payment confirmation message
     */
    private function formatPaymentMessage(array $paymentData): string
    {
        $amount = number_format($paymentData['amount'] ?? 0, 2);
        $currency = $paymentData['currency'] ?? 'TZS';
        $reference = $paymentData['reference'] ?? 'N/A';
        $transactionId = $paymentData['transaction_id'] ?? 'N/A';
        $paymentMethod = $paymentData['payment_method'] ?? 'Mobile Money';

        return "FEEDTAN: Payment Confirmed\n" .
               "Transaction ID: {$transactionId}\n" .
               "Reference: {$reference}\n" .
               "Amount: {$amount} {$currency}\n" .
               "Method: {$paymentMethod}\n" .
               "Status: SUCCESS\n" .
               "Thank you for your payment!";
    }

    /**
     * Format insufficient funds message
     */
    private function formatInsufficientFundsMessage(array $paymentData): string
    {
        $amount = number_format($paymentData['amount'] ?? 0, 2);
        $currency = $paymentData['currency'] ?? 'TZS';
        $reference = $paymentData['reference'] ?? 'N/A';
        $phoneNumber = $paymentData['phone_number'] ?? 'N/A';

        return "FEEDTAN: Payment Failed\n" .
               "Reference: {$reference}\n" .
               "Amount: {$amount} {$currency}\n" .
               "Phone: {$phoneNumber}\n" .
               "Status: INSUFFICIENT FUNDS\n" .
               "Please top up your account and try again.\n" .
               "Contact support if you need assistance.";
    }

    /**
     * Format phone number to international format
     */
    private function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove any non-digit characters
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Ensure it starts with 255 for Tanzania
        if (strlen($cleaned) === 9 && in_array(substr($cleaned, 0, 1), ['6', '7'])) {
            return '255' . $cleaned;
        }
        
        if (strlen($cleaned) === 12 && substr($cleaned, 0, 3) === '255') {
            return $cleaned;
        }
        
        // If already in correct format, return as is
        if (strlen($cleaned) === 12 && substr($cleaned, 0, 3) === '255') {
            return $cleaned;
        }
        
        // Default: assume Tanzania number and add 255
        return '255' . ltrim($cleaned, '0');
    }

    /**
     * Get user-friendly error message from API response
     */
    private function getErrorMessage($response): string
    {
        $status = $response->status();
        $body = $response->body();

        // Try to parse JSON response
        try {
            $json = $response->json();
            if (isset($json['error'])) {
                return "Messaging API Error: " . $json['error'];
            }
        } catch (Exception $e) {
            // JSON parsing failed, use body
        }

        // Handle specific HTTP status codes
        switch ($status) {
            case 400:
                return "Invalid Request: Please check your message format and parameters.";
            case 401:
                return "Authentication Failed: Invalid API token. Please check your messaging service credentials.";
            case 403:
                return "Access Denied: Insufficient permissions to send SMS.";
            case 429:
                return "Rate Limit Exceeded: Too many requests. Please try again later.";
            case 500:
                return "Server Error: Messaging service is temporarily unavailable. Please try again later.";
            default:
                return "SMS Error: Unable to send message (HTTP {$status}). Please try again.";
        }
    }

    /**
     * Get SMS delivery status
     */
    public function getDeliveryStatus(string $messageId): array
    {
        try {
            $url = $this->baseUrl . '/api/sms/v2/status/' . $messageId;
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Accept' => 'application/json'
            ])->timeout(30)->get($url);

            if ($response->successful()) {
                return $response->json();
            } else {
                throw new Exception("Failed to get delivery status: " . $response->status());
            }
        } catch (Exception $e) {
            Log::error('Delivery status check failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Test SMS sending (no charges)
     */
    public function testSMS(string $to, string $message): array
    {
        try {
            $url = $this->baseUrl . '/api/sms/v2/test/text/single';
            
            $data = [
                'from' => $this->senderId,
                'to' => $this->formatPhoneNumber($to),
                'text' => $message
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->timeout(30)->post($url, $data);

            if ($response->successful()) {
                return $response->json();
            } else {
                throw new Exception("Test SMS failed: " . $response->status());
            }
        } catch (Exception $e) {
            Log::error('Test SMS failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
