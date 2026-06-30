# FeedTan Payment System - API Reference Manual

Complete API documentation for developers integrating with FeedTan payment services.

---

## Table of Contents
1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Base URLs](#base-urls)
4. [Payment Endpoints](#payment-endpoints)
5. [Status Endpoints](#status-endpoints)
6. [Webhook Endpoints](#webhook-endpoints)
7. [Error Codes](#error-codes)
8. [Rate Limiting](#rate-limiting)
9. [SDK Integration](#sdk-integration)
10. [Testing](#testing)

---

## Overview

The FeedTan API provides RESTful endpoints for payment processing, status checking, and webhook handling. The API uses JSON for request/response bodies and standard HTTP status codes.

### API Version
- **Current Version**: v1
- **Base Path**: `/api/v1`

### Supported Methods
- **GET**: Retrieve data
- **POST**: Create resources
- **PUT**: Update resources
- **DELETE**: Remove resources

### Data Format
- **Request**: JSON
- **Response**: JSON
- **Character Encoding**: UTF-8

---

## Authentication

### API Key Authentication

All API requests require authentication using API keys.

#### Headers
```http
Authorization: Bearer YOUR_API_KEY
Content-Type: application/json
Accept: application/json
```

#### Environment Variables
```env
CLICKPESA_API_KEY=your-api-key
CLICKPESA_SECRET_KEY=your-secret-key
CLICKPESA_MERCHANT_CODE=your-merchant-code
```

### Obtaining API Credentials

1. Contact FeedTan support: support@feedtancmg.org
2. Provide your business details
3. Receive API key and secret key
4. Configure in your application

### Security Best Practices

- Never expose API keys in client-side code
- Use environment variables for credentials
- Rotate API keys regularly
- Implement IP whitelisting
- Monitor API usage for anomalies

---

## Base URLs

### Production
```
https://pay.feedtancmg.org/api/v1
```

### Staging (if available)
```
https://staging.feedtancmg.org/api/v1
```

### Local Development
```
http://localhost:8000/api/v1
```

---

## Payment Endpoints

### Create Payment

Initiates a new payment transaction.

#### Endpoint
```
POST /payments/store
```

#### Request Headers
```http
Authorization: Bearer YOUR_API_KEY
Content-Type: application/json
```

#### Request Body
```json
{
    "amount": 10000,
    "currency": "TZS",
    "phone": "255622239304",
    "payer_name": "John Doe",
    "email": "john@example.com",
    "description": "Service payment",
    "reference": "FEEDTAN123456"
}
```

#### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| amount | decimal | Yes | Payment amount |
| currency | string | Yes | Currency code (TZS, USD, EUR) |
| phone | string | Yes | Customer phone number (format: 255XXXXXXXXX) |
| payer_name | string | Yes | Customer full name |
| email | string | No | Customer email address |
| description | string | No | Payment description |
| reference | string | No | Custom reference number |

#### Response (Success - 200 OK)
```json
{
    "success": true,
    "message": "Payment initiated successfully",
    "data": {
        "reference": "FEEDTAN123456",
        "status": "PENDING",
        "amount": 10000,
        "currency": "TZS",
        "phone": "255622239304",
        "created_at": "2026-05-11T12:00:00Z",
        "payment_url": "https://pay.feedtancmg.org/payment/FEEDTAN123456"
    }
}
```

#### Response (Error - 400 Bad Request)
```json
{
    "success": false,
    "message": "Invalid phone number format",
    "errors": {
        "phone": ["Phone number must be in format 255XXXXXXXXX"]
    }
}
```

#### Response (Error - 401 Unauthorized)
```json
{
    "success": false,
    "message": "Invalid API credentials"
}
```

#### cURL Example
```bash
curl -X POST https://pay.feedtancmg.org/api/v1/payments/store \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 10000,
    "currency": "TZS",
    "phone": "255622239304",
    "payer_name": "John Doe",
    "email": "john@example.com",
    "description": "Service payment"
  }'
```

#### PHP Example
```php
use Illuminate\Support\Facades\Http;

$response = Http::withHeaders([
    'Authorization' => 'Bearer YOUR_API_KEY',
    'Content-Type' => 'application/json'
])->post('https://pay.feedtancmg.org/api/v1/payments/store', [
    'amount' => 10000,
    'currency' => 'TZS',
    'phone' => '255622239304',
    'payer_name' => 'John Doe',
    'email' => 'john@example.com',
    'description' => 'Service payment'
]);

$data = $response->json();
```

#### JavaScript Example
```javascript
fetch('https://pay.feedtancmg.org/api/v1/payments/store', {
    method: 'POST',
    headers: {
        'Authorization': 'Bearer YOUR_API_KEY',
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        amount: 10000,
        currency: 'TZS',
        phone: '255622239304',
        payer_name: 'John Doe',
        email: 'john@example.com',
        description: 'Service payment'
    })
})
.then(response => response.json())
.then(data => console.log(data));
```

---

### Check Payment Status

Retrieves the current status of a payment.

#### Endpoint
```
GET /payments/status?reference=FEEDTAN123456
```

#### Request Headers
```http
Authorization: Bearer YOUR_API_KEY
```

#### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| reference | string | Yes | Payment reference number |

#### Response (Success - 200 OK)
```json
{
    "success": true,
    "data": {
        "reference": "FEEDTAN123456",
        "status": "SUCCESS",
        "amount": 10000,
        "currency": "TZS",
        "phone": "255622239304",
        "payer_name": "John Doe",
        "payment_method": "Mobile Money",
        "created_at": "2026-05-11T12:00:00Z",
        "updated_at": "2026-05-11T12:01:30Z",
        "transaction_id": "clickpesa-transaction-id"
    }
}
```

#### Payment Status Values
- `PENDING`: Payment initiated, awaiting confirmation
- `PROCESSING`: Payment being processed
- `SUCCESS`: Payment completed successfully
- `SETTLED`: Payment settled in merchant account
- `FAILED`: Payment failed
- `ERROR`: System error occurred

#### cURL Example
```bash
curl -X GET "https://pay.feedtancmg.org/api/v1/payments/status?reference=FEEDTAN123456" \
  -H "Authorization: Bearer YOUR_API_KEY"
```

---

### Payment History

Retrieves a list of payments with optional filters.

#### Endpoint
```
GET /payments/history
```

#### Request Headers
```http
Authorization: Bearer YOUR_API_KEY
```

#### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| limit | integer | No | Number of results (default: 50, max: 100) |
| offset | integer | No | Pagination offset (default: 0) |
| status | string | No | Filter by status (SUCCESS, FAILED, PENDING) |
| from_date | string | No | Start date (YYYY-MM-DD) |
| to_date | string | No | End date (YYYY-MM-DD) |

#### Response (Success - 200 OK)
```json
{
    "success": true,
    "data": [
        {
            "reference": "FEEDTAN123456",
            "status": "SUCCESS",
            "amount": 10000,
            "currency": "TZS",
            "phone": "255622239304",
            "payer_name": "John Doe",
            "created_at": "2026-05-11T12:00:00Z"
        }
    ],
    "pagination": {
        "total": 150,
        "limit": 50,
        "offset": 0,
        "has_more": true
    }
}
```

#### cURL Example
```bash
curl -X GET "https://pay.feedtancmg.org/api/v1/payments/history?limit=10&status=SUCCESS" \
  -H "Authorization: Bearer YOUR_API_KEY"
```

---

## Status Endpoints

### API Status Check

Checks the API service status and ClickPesa gateway connectivity.

#### Endpoint
```
POST /payments/api/status
```

#### Request Headers
```http
Authorization: Bearer YOUR_API_KEY
Content-Type: application/json
```

#### Request Body
```json
{
    "check_gateway": true
}
```

#### Response (Success - 200 OK)
```json
{
    "success": true,
    "status": "operational",
    "services": {
        "api": "operational",
        "database": "operational",
        "clickpesa_gateway": "operational",
        "sms_service": "operational",
        "email_service": "operational"
    },
    "timestamp": "2026-05-11T12:00:00Z"
}
```

#### Service Status Values
- `operational`: Service is running normally
- `degraded`: Service is running with reduced performance
- `down`: Service is unavailable

---

## Webhook Endpoints

### Payment Callback

Receives payment status updates from ClickPesa.

#### Endpoint
```
POST /webhooks/clickpesa
```

#### Request Headers
```http
Content-Type: application/json
```

#### Request Body
```json
{
    "reference": "FEEDTAN123456",
    "status": "SUCCESS",
    "amount": 10000,
    "currency": "TZS",
    "phone": "255622239304",
    "payer_name": "John Doe",
    "transaction_id": "clickpesa-transaction-id",
    "timestamp": "2026-05-11T12:00:00Z",
    "signature": "generated_signature"
}
```

#### Response (Success - 200 OK)
```json
{
    "success": true,
    "message": "Webhook received successfully"
}
```

#### Webhook Security

Verify webhook signature:

```php
public function verifyWebhookSignature($payload, $signature)
{
    $secret = config('clickpesa.secret_key');
    $expectedSignature = hash_hmac('sha256', $payload, $secret);
    return hash_equals($expectedSignature, $signature);
}
```

#### Handling Duplicate Webhooks

Check for existing transactions before processing:

```php
$existingTransaction = Transaction::where('reference', $reference)->first();
if ($existingTransaction && $existingTransaction->status === 'SUCCESS') {
    return response()->json(['success' => true, 'message' => 'Already processed']);
}
```

---

## Error Codes

### HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 429 | Too Many Requests |
| 500 | Internal Server Error |
| 502 | Bad Gateway |
| 503 | Service Unavailable |

### Application Error Codes

| Code | Description | Solution |
|------|-------------|----------|
| ERR_001 | Invalid API credentials | Check API key and secret |
| ERR_002 | Invalid phone number format | Use format 255XXXXXXXXX |
| ERR_003 | Invalid amount | Amount must be positive |
| ERR_004 | Invalid currency | Use supported currency codes |
| ERR_005 | Payment not found | Check reference number |
| ERR_006 | Payment already processed | Reference already used |
| ERR_007 | Gateway timeout | Retry payment |
| ERR_008 | Insufficient funds | Customer needs to top up |
| ERR_009 | Network error | Check network connectivity |
| ERR_010 | Database error | Contact support |

### Error Response Format

```json
{
    "success": false,
    "message": "Error description",
    "error_code": "ERR_001",
    "errors": {
        "field": ["Error message"]
    }
}
```

---

## Rate Limiting

### Rate Limits

- **Standard**: 100 requests per minute
- **Premium**: 1000 requests per minute
- **Enterprise**: Custom limits

### Rate Limit Headers

```http
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1620720000
```

### Handling Rate Limits

When rate limit is exceeded (HTTP 429):

```json
{
    "success": false,
    "message": "Rate limit exceeded",
    "retry_after": 60
}
```

Implement exponential backoff:

```javascript
async function makeRequest(url, options, retries = 3) {
    try {
        const response = await fetch(url, options);
        if (response.status === 429 && retries > 0) {
            const retryAfter = response.headers.get('Retry-After') || 60;
            await new Promise(resolve => setTimeout(resolve, retryAfter * 1000));
            return makeRequest(url, options, retries - 1);
        }
        return response;
    } catch (error) {
        if (retries > 0) {
            await new Promise(resolve => setTimeout(resolve, 1000));
            return makeRequest(url, options, retries - 1);
        }
        throw error;
    }
}
```

---

## SDK Integration

### PHP SDK

#### Installation
```bash
composer require feedtan/sdk
```

#### Usage
```php
use FeedTan\FeedTanSDK;

$sdk = new FeedTanSDK([
    'api_key' => 'YOUR_API_KEY',
    'secret_key' => 'YOUR_SECRET_KEY',
    'merchant_code' => 'YOUR_MERCHANT_CODE',
    'environment' => 'production'
]);

// Create payment
$payment = $sdk->payments->create([
    'amount' => 10000,
    'currency' => 'TZS',
    'phone' => '255622239304',
    'payer_name' => 'John Doe'
]);

// Check status
$status = $sdk->payments->status('FEEDTAN123456');
```

### JavaScript SDK

#### Installation
```bash
npm install feedtan-sdk
```

#### Usage
```javascript
import FeedTan from 'feedtan-sdk';

const sdk = new FeedTan({
    apiKey: 'YOUR_API_KEY',
    secretKey: 'YOUR_SECRET_KEY',
    merchantCode: 'YOUR_MERCHANT_CODE',
    environment: 'production'
});

// Create payment
const payment = await sdk.payments.create({
    amount: 10000,
    currency: 'TZS',
    phone: '255622239304',
    payerName: 'John Doe'
});

// Check status
const status = await sdk.payments.status('FEEDTAN123456');
```

### Python SDK

#### Installation
```bash
pip install feedtan-sdk
```

#### Usage
```python
from feedtan import FeedTanSDK

sdk = FeedTanSDK(
    api_key='YOUR_API_KEY',
    secret_key='YOUR_SECRET_KEY',
    merchant_code='YOUR_MERCHANT_CODE',
    environment='production'
)

# Create payment
payment = sdk.payments.create({
    'amount': 10000,
    'currency': 'TZS',
    'phone': '255622239304',
    'payer_name': 'John Doe'
})

# Check status
status = sdk.payments.status('FEEDTAN123456')
```

---

## Testing

### Sandbox Environment

Use the sandbox environment for testing:

```env
CLICKPESA_BASE_URL=https://sandbox.clickpesa.co.tz
CLICKPESA_API_KEY=sandbox-api-key
CLICKPESA_SECRET_KEY=sandbox-secret-key
```

### Test Phone Numbers

Use test phone numbers for sandbox testing:

- `255700000001` - Always successful
- `255700000002` - Always fails (insufficient funds)
- `255700000003` - Timeout
- `255700000004` - Network error

### Test Cases

#### Successful Payment
```bash
curl -X POST https://sandbox.feedtancmg.org/api/v1/payments/store \
  -H "Authorization: Bearer SANDBOX_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 10000,
    "currency": "TZS",
    "phone": "255700000001",
    "payer_name": "Test User"
  }'
```

#### Failed Payment
```bash
curl -X POST https://sandbox.feedtancmg.org/api/v1/payments/store \
  -H "Authorization: Bearer SANDBOX_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 10000,
    "currency": "TZS",
    "phone": "255700000002",
    "payer_name": "Test User"
  }'
```

### Postman Collection

Import the Postman collection from:
`docs/postman/FeedTan_API.postman_collection.json`

---

## Support

### API Support
- **Email**: api-support@feedtancmg.org
- **Documentation**: https://docs.feedtancmg.org/api
- **GitHub**: https://github.com/davidngungila/feedpa/issues

### Status Page
- **Status**: https://status.feedtancmg.org
- **Incidents**: https://status.feedtancmg.org/incidents

---

## Changelog

### Version 1.0.0 (2026-05-11)
- Initial API release
- Payment creation endpoint
- Payment status endpoint
- Payment history endpoint
- Webhook support
- SDKs for PHP, JavaScript, Python

---

© 2026 FeedTan. All rights reserved.
