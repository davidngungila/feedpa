<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClickPesaAPIService
{
    protected array $config;
    protected ?string $token = null;
    protected ?int $tokenExpiry = null;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Generate JWT Authorization Token
     */
    public function generateToken(): string
    {
        $url = $this->config['api_base_url'] . '/generate-token';

        $response = Http::withHeaders([
            'api-key' => $this->config['api_key'],
            'client-id' => $this->config['client_id']
        ])->timeout(30)->post($url);

        if ($response->successful() && $response->json('success')) {
            $rawToken = $response->json('token');
            if (strpos($rawToken, 'Bearer ') === 0) {
                $this->token = substr($rawToken, 7);
            } else {
                $this->token = $rawToken;
            }
            $this->tokenExpiry = time() + 3600;
            return $rawToken;
        }

        throw new Exception('Failed to generate token: ' . ($response->json('message') ?? 'Unknown error'));
    }

    /**
     * Get valid token (generate new one if expired)
     */
    protected function getValidToken(): string
    {
        if (!$this->token || $this->tokenExpiry <= time()) {
            $this->generateToken();
        }
        return $this->token;
    }

    /**
     * Preview USSD-PUSH request
     */
    public function previewUSSDPush(float $amount, string $orderReference, string $phoneNumber, bool $fetchSenderDetails = false, ?string $checksum = null): array
    {
        $url = $this->config['api_base_url'] . '/payments/preview-ussd-push-request';

        $data = [
            'amount' => $amount,
            'currency' => $this->config['currency'],
            'orderReference' => $orderReference,
            'phoneNumber' => $phoneNumber,
            'fetchSenderDetails' => $fetchSenderDetails
        ];

        if ($checksum) {
            $data['checksum'] = $checksum;
        }

        return $this->makeRequest('POST', $url, $data);
    }

    /**
     * Initiate USSD-PUSH request
     */
    public function initiateUSSDPush(float $amount, string $orderReference, string $phoneNumber, ?string $checksum = null, array $customerDetails = []): array
    {
        $url = $this->config['api_base_url'] . '/payments/initiate-ussd-push-request';

        $data = [
            'amount' => $amount,
            'currency' => $this->config['currency'],
            'orderReference' => $orderReference,
            'phoneNumber' => $phoneNumber
        ];

        // Add customer details if provided
        if (!empty($customerDetails)) {
            if (isset($customerDetails['customerName'])) {
                $data['customerName'] = $customerDetails['customerName'];
            }
            if (isset($customerDetails['description'])) {
                $data['description'] = $customerDetails['description'];
            }
            if (isset($customerDetails['email'])) {
                $data['customerEmail'] = $customerDetails['email'];
            }
        }

        if ($checksum) {
            $data['checksum'] = $checksum;
        }

        return $this->makeRequest('POST', $url, $data);
    }

    /**
     * Create USSD Push Payment (alias for initiateUSSDPush)
     */
    public function createUssdPushPayment(array $paymentData): array
    {
        $amount = $paymentData['amount'] ?? 0;
        $orderReference = $paymentData['order_reference'] ?? '';
        $phoneNumber = $paymentData['customer_phone'] ?? '';
        $customerName = $paymentData['customer_name'] ?? null;
        $description = $paymentData['description'] ?? null;
        $email = $paymentData['email'] ?? null;
        
        $customerDetails = [];
        if ($customerName) $customerDetails['customerName'] = $customerName;
        if ($description) $customerDetails['description'] = $description;
        if ($email) $customerDetails['email'] = $email;
        
        return $this->initiateUSSDPush($amount, $orderReference, $phoneNumber, null, $customerDetails);
    }

    /**
     * Query Payment Status by Order Reference
     */
    public function queryPaymentStatus(string $orderReference): array
    {
        $url = 'https://api.clickpesa.com/third-parties/payments/' . urlencode($orderReference);
        return $this->makeRequest('GET', $url);
    }

    /**
     * Get Payment Status by Order Reference (alias for queryPaymentStatus)
     */
    public function getPaymentStatus(string $orderReference): array
    {
        return $this->queryPaymentStatus($orderReference);
    }

    /**
     * Query All Payments with filtering and pagination
     */
    public function queryAllPayments(array $params = []): array
    {
        $url = $this->config['api_base_url'] . '/payments/all';
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $this->makeRequest('GET', $url);
    }

    /**
     * Preview Mobile Money Payout
     */
    public function previewMobileMoneyPayout(float $amount, string $phoneNumber, string $currency = 'TZS', ?string $orderReference = null, ?string $checksum = null): array
    {
        $url = 'https://api.clickpesa.com/third-parties/payouts/preview-mobile-money-payout';

        $data = [
            'amount' => $amount,
            'phoneNumber' => $phoneNumber,
            'currency' => $currency
        ];

        if ($orderReference) $data['orderReference'] = $orderReference;
        if ($checksum) $data['checksum'] = $checksum;

        return $this->makeRequest('POST', $url, $data);
    }

    /**
     * Create Mobile Money Payout
     */
    public function createMobileMoneyPayout(float $amount, string $phoneNumber, string $currency = 'TZS', ?string $orderReference = null, ?string $checksum = null): array
    {
        $url = 'https://api.clickpesa.com/third-parties/payouts/create-mobile-money-payout';

        $data = [
            'amount' => $amount,
            'phoneNumber' => $phoneNumber,
            'currency' => $currency
        ];

        if ($orderReference) $data['orderReference'] = $orderReference;
        if ($checksum) $data['checksum'] = $checksum;

        return $this->makeRequest('POST', $url, $data);
    }

    /**
     * Preview Bank Payout
     */
    public function previewBankPayout(float $amount, string $currency, string $accountNumber, string $accountName, string $bic, string $transferType = 'ACH', ?string $orderReference = null, ?string $checksum = null): array
    {
        $url = 'https://api.clickpesa.com/third-parties/payouts/preview-bank-payout';

        $data = [
            'amount' => $amount,
            'currency' => $currency,
            'accountNumber' => $accountNumber,
            'accountName' => $accountName,
            'bic' => $bic,
            'transferType' => $transferType,
            'accountCurrency' => $currency
        ];

        if ($orderReference) $data['orderReference'] = $orderReference;
        if ($checksum) $data['checksum'] = $checksum;

        return $this->makeRequest('POST', $url, $data);
    }

    /**
     * Create Bank Payout
     */
    public function createBankPayout(float $amount, string $currency, string $accountNumber, string $accountName, string $bic, string $transferType = 'ACH', ?string $orderReference = null, ?string $checksum = null): array
    {
        $url = 'https://api.clickpesa.com/third-parties/payouts/create-bank-payout';

        $data = [
            'amount' => $amount,
            'currency' => $currency,
            'accountNumber' => $accountNumber,
            'accountName' => $accountName,
            'bic' => $bic,
            'transferType' => $transferType,
            'accountCurrency' => $currency
        ];

        if ($orderReference) $data['orderReference'] = $orderReference;
        if ($checksum) $data['checksum'] = $checksum;

        return $this->makeRequest('POST', $url, $data);
    }

    /**
     * Query Payout Status
     */
    public function queryPayoutStatus(string $orderReference): array
    {
        $url = 'https://api.clickpesa.com/third-parties/payouts/' . urlencode($orderReference);
        return $this->makeRequest('GET', $url);
    }

    /**
     * Query All Payouts with filtering and pagination
     */
    public function queryAllPayouts(array $params = []): array
    {
        $url = 'https://api.clickpesa.com/third-parties/payouts/all';
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $this->makeRequest('GET', $url);
    }

    /**
     * Retrieve Banks List
     */
    public function getBanksList(): array
    {
        $url = 'https://api.clickpesa.com/third-parties/list/banks';
        return $this->makeRequest('GET', $url);
    }

    /**
     * Get Account Balance
     */
    public function getAccountBalance(string $currency = 'TZS'): array
    {
        $url = 'https://api.clickpesa.com/third-parties/account/balance';
        
        // Ensure currency is valid - add as query parameter
        $currency = in_array($currency, ['TZS', 'USD']) ? $currency : 'TZS';
        $url .= '?currency=' . urlencode($currency);
        
        return $this->makeRequest('GET', $url);
    }

    /**
     * Get Account Statement
     */
    public function getAccountStatement(string $currency = 'TZS', ?string $startDate = null, ?string $endDate = null): array
    {
        $url = 'https://api.clickpesa.com/third-parties/account/statement';
        
        // Ensure currency is valid and format correctly for API
        $currency = in_array($currency, ['TZS', 'USD']) ? $currency : 'TZS';
        
        $params = [];
        if ($startDate) $params['startDate'] = $startDate;
        if ($endDate) $params['endDate'] = $endDate;
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        // Debug: Log the final URL
        Log::info('Account Statement API Call', [
            'url' => $url,
            'params' => $params,
            'currency' => $currency
        ]);
        
        // Try currency as query parameter first, then as header
        try {
            // Add currency as query parameter
            if (!empty($params)) {
                $params['currency'] = $currency;
            } else {
                // If no other params, add currency as query param
                $url .= '?currency=' . urlencode($currency);
                return $this->makeRequest('GET', $url, null);
            }
            
            return $this->makeRequestWithCurrency('GET', $url, $currency, null);
        } catch (Exception $e) {
            Log::error('Account Statement API Error', [
                'url' => $url,
                'currency' => $currency,
                'params' => $params,
                'error' => $e->getMessage()
            ]);
            
            // Return error response that matches expected format
            return [
                'error' => 'API Error: ' . $e->getMessage(),
                'currency' => $currency,
                'message' => 'Failed to retrieve account statement. Please try again.'
            ];
        }
    }

    // ==================== BILLPAY METHODS ====================

    /**
     * Create Order Control Number
     */
    public function createOrderControlNumber(string $billDescription, ?float $billAmount = null, ?string $billReference = null, string $billPaymentMode = 'ALLOW_PARTIAL_AND_OVER_PAYMENT'): array
    {
        $url = $this->config['api_base_url'] . '/billpay/create-order-control-number';

        $data = [
            'billDescription' => $billDescription,
            'billPaymentMode' => $billPaymentMode
        ];

        if ($billAmount !== null) $data['billAmount'] = $billAmount;
        if ($billReference) $data['billReference'] = $billReference;

        return $this->makeRequest('POST', $url, $data);
    }

    /**
     * Create Customer Control Number
     */
    public function createCustomerControlNumber(string $customerName, ?string $customerEmail = null, ?string $customerPhone = null, ?string $billDescription = null, ?float $billAmount = null, ?string $billReference = null, string $billPaymentMode = 'ALLOW_PARTIAL_AND_OVER_PAYMENT'): array
    {
        $url = $this->config['api_base_url'] . '/billpay/create-customer-control-number';

        $data = [
            'customerName' => $customerName,
            'billPaymentMode' => $billPaymentMode
        ];

        if ($customerEmail) $data['customerEmail'] = $customerEmail;
        if ($customerPhone) $data['customerPhone'] = $customerPhone;
        if ($billDescription) $data['billDescription'] = $billDescription;
        if ($billAmount !== null) $data['billAmount'] = $billAmount;
        if ($billReference) $data['billReference'] = $billReference;

        return $this->makeRequest('POST', $url, $data);
    }

    /**
     * Bulk Create Order Control Numbers
     */
    public function bulkCreateOrderControlNumbers(array $controlNumbers): array
    {
        $url = $this->config['api_base_url'] . '/billpay/bulk-create-order-control-numbers';
        return $this->makeRequest('POST', $url, ['controlNumbers' => $controlNumbers]);
    }

    /**
     * Bulk Create Customer Control Numbers
     */
    public function bulkCreateCustomerControlNumbers(array $controlNumbers): array
    {
        $url = $this->config['api_base_url'] . '/billpay/bulk-create-customer-control-numbers';
        return $this->makeRequest('POST', $url, ['controlNumbers' => $controlNumbers]);
    }

    /**
     * Query BillPay Number Details
     */
    public function queryBillPayNumber(string $billPayNumber): array
    {
        $url = $this->config['api_base_url'] . '/billpay/' . $billPayNumber;
        return $this->makeRequest('GET', $url);
    }

    /**
     * Update BillPay Reference
     */
    public function updateBillPayReference(string $billPayNumber, ?float $billAmount = null, ?string $billDescription = null, ?string $billPaymentMode = null, ?string $billStatus = null): array
    {
        $url = $this->config['api_base_url'] . '/billpay/' . $billPayNumber;

        $data = [];
        if ($billAmount !== null) $data['billAmount'] = $billAmount;
        if ($billDescription) $data['billDescription'] = $billDescription;
        if ($billPaymentMode) $data['billPaymentMode'] = $billPaymentMode;
        if ($billStatus) $data['billStatus'] = $billStatus;

        return $this->makeRequest('PATCH', $url, $data);
    }

    // ==================== UTILITY METHODS ====================

    /**
     * Generate unique order reference (max 20 characters)
     */
    public function generateOrderReference(string $prefix = 'FEEDTAN'): string
    {
        $uniqueId = strtoupper(uniqid());
        $timestamp = time();
        $reference = $prefix . substr($uniqueId, -8) . substr($timestamp, -6);
        
        if (strlen($reference) > 20) {
            $reference = substr($reference, 0, 20);
        }
        
        return $reference;
    }

    /**
     * Generate FEEDTAN control number with alphanumeric suffix
     */
    public function generateFeedtanPayControlNumber(?int $suffix = null): string
    {
        if ($suffix === null) {
            $suffix = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } else {
            $suffix = str_pad($suffix, 4, '0', STR_PAD_LEFT);
            $suffix = substr($suffix, -4);
        }
        
        return 'FEEDTAN' . $suffix;
    }

    /**
     * Format amount for API
     */
    public function formatAmount(float $amount): float
    {
        return (float) number_format($amount, 0, '.', '');
    }

    /**
     * Validate phone number for Tanzania
     */
    public function validatePhoneNumber(string $phoneNumber): ?string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        if (preg_match('/^255[67]\d{8}$/', $cleaned)) {
            return $cleaned;
        }
        
        return null;
    }

    /**
     * Get payment status description
     */
    public function getStatusDescription(string $status): string
    {
        return match($status) {
            'SUCCESS' => 'Payment completed successfully',
            'SETTLED' => 'Payment has been settled',
            'PROCESSING' => 'Payment is being processed',
            'PENDING' => 'Payment is pending',
            'FAILED' => 'Payment failed',
            default => 'Unknown payment status'
        };
    }

    /**
     * Convert API error messages to user-friendly messages
     */
    private function getUserFriendlyErrorMessage(string $errorMessage, int $statusCode): string
    {
        // Insufficient funds errors
        if (stripos($errorMessage, 'insufficient funds') !== false || 
            stripos($errorMessage, 'balance') !== false ||
            stripos($errorMessage, 'top up') !== false) {
            return "Insufficient Funds: Your account balance is too low to complete this transaction. Please top up your Halopesa account and try again. For assistance, contact your Halopesa agent.";
        }

        // Invalid currency errors
        if (stripos($errorMessage, 'invalid currency') !== false) {
            return "Currency Error: The selected currency is not supported. Please use TZS (Tanzanian Shilling) for transactions.";
        }

        // Invalid phone number errors
        if (stripos($errorMessage, 'invalid phone') !== false || 
            stripos($errorMessage, 'phone number') !== false) {
            return "Phone Number Error: The phone number format is invalid. Please use the format: 255712345678 (starting with 255 followed by 9 digits).";
        }

        // Bill reference errors
        if (stripos($errorMessage, 'bill reference') !== false) {
            return "Control Number Error: The control number format is invalid. Please use only alphanumeric characters (letters and numbers) without spaces or special characters.";
        }

        // Authentication errors
        if ($statusCode === 401) {
            return "Authentication Error: System authentication failed. Please try again in a few moments. If the problem persists, contact system administrator.";
        }

        // Rate limiting errors
        if ($statusCode === 429) {
            return "Too Many Requests: Please wait a few moments before trying again. The system is processing many requests.";
        }

        // Server errors
        if ($statusCode >= 500) {
            return "System Error: The payment system is temporarily unavailable. Please try again in a few minutes. If the problem continues, contact support.";
        }

        // Validation errors
        if ($statusCode === 400) {
            return "Validation Error: Please check all form fields and ensure all required information is provided correctly.";
        }

        // Generic error with original message
        return "Payment Error: " . $errorMessage . " Please check your details and try again.";
    }

    /**
     * Make HTTP request with error handling
     */
    private function makeRequest(string $method, string $url, ?array $data = null, array $headers = []): array
    {
        try {
            $response = Http::withHeaders($this->getHeaders($headers))
                ->timeout(30)
                ->$method($url, $data);

            if ($response->successful()) {
                return $response->json();
            } else {
                $errorMessage = $response->json('message') ?? $response->body() ?? 'Unknown API error';
                $userFriendlyMessage = $this->getUserFriendlyErrorMessage($errorMessage, $response->status());
                throw new Exception($userFriendlyMessage);
            }
        } catch (Exception $e) {
            Log::error('API request failed', [
                'method' => $method,
                'url' => $url,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get headers for HTTP request
     */
    private function getHeaders(array $headers = []): array
    {
        $token = $this->getValidToken();

        $defaultHeaders = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json'
        ];

        return array_merge($defaultHeaders, $headers);
    }

    /**
     * Make HTTP request with currency header
     */
    protected function makeRequestWithCurrency(string $method, string $url, string $currency, ?array $data = null): array
    {
        $token = $this->getValidToken();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
            'Currency' => $currency
        ])->timeout(30)->$method($url, $data ?? []);

        if ($response->failed()) {
            Log::error('ClickPesa API Error (with currency)', [
                'url' => $url,
                'method' => $method,
                'currency' => $currency,
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            
            throw new Exception('API Error: ' . ($response->json('message') ?? $response->body()));
        }

        return $response->json();
    }
}
