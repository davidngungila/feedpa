# E-commerce Payment API Integration Guide

This API allows your e-commerce system to integrate with the FeedTan payment system for mobile money payments (M-Pesa, Tigo Pesa, Airtel Money, Halopesa).

## Base URL

All API endpoints are prefixed with `/api/ecommerce`.

For example, if your site is `https://pay.feedtancmg.org`, the full base URL is:
`https://pay.feedtancmg.org/api/ecommerce`

## Endpoints

### 1. Initiate Payment

Initiates a mobile money payment request.

**Endpoint:** `POST /payments/initiate`

**Request Body:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `amount` | number | Yes | Payment amount in TZS (minimum 500) |
| `phone_number` | string | Yes | Customer's phone number (format: 255712345678) |
| `payer_name` | string | Yes | Customer's full name |
| `description` | string | Yes | Payment description (e.g., "Order #123") |
| `order_reference` | string | No | Your system's unique order reference. If not provided, one will be generated. |
| `email` | string | No | Customer's email address |
| `callback_url` | string | No | URL to receive payment status updates (not implemented yet, but reserved for future use) |
| `metadata` | object | No | Additional custom data to store with the transaction |

**Example Request:**
```json
{
    "amount": 15000,
    "phone_number": "255712345678",
    "payer_name": "John Doe",
    "description": "Order #123 - Shopping Cart",
    "order_reference": "MYSHOP-12345",
    "email": "john@example.com",
    "metadata": {
        "order_id": 12345,
        "items": [
            {"name": "Product A", "quantity": 1, "price": 10000},
            {"name": "Product B", "quantity": 1, "price": 5000}
        ]
    }
}
```

**Success Response (200 OK):**
```json
{
    "success": true,
    "message": "Payment initiated successfully. USSD push sent to 255712345678",
    "data": {
        "order_reference": "MYSHOP-12345",
        "transaction_id": "CP-1234567890",
        "amount": 15000,
        "phone_number": "255712345678",
        "status": "PENDING"
    }
}
```

**Error Response (422 Unprocessable Entity):**
```json
{
    "success": false,
    "message": "Invalid phone number. Please use format: 255712345678"
}
```

---

### 2. Check Payment Status

Check the current status of a payment.

**Endpoint:** `GET /payments/status/{orderReference}`

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `orderReference` | string | Yes | The order reference (yours or the generated one) |

**Example Request:**
```http
GET /api/ecommerce/payments/status/MYSHOP-12345
```

**Success Response (200 OK):**
```json
{
    "success": true,
    "data": {
        "order_reference": "MYSHOP-12345",
        "transaction_id": "CP-1234567890",
        "status": "SUCCESS",
        "amount": 15000,
        "currency": "TZS",
        "phone_number": "255712345678",
        "payer_name": "John Doe",
        "email": "john@example.com",
        "description": "Order #123 - Shopping Cart",
        "payment_method": "M-Pesa",
        "created_at": "2026-06-16T10:30:00+00:00",
        "updated_at": "2026-06-16T10:30:15+00:00"
    }
}
```

**Possible Status Values:**
- `PROCESSING`: Payment is being prepared
- `PENDING`: USSD push sent, waiting for customer to confirm
- `SUCCESS`: Payment completed successfully
- `SETTLED`: Payment has been settled
- `FAILED`: Payment failed
- `DECLINED`: Payment was declined
- `CANCELLED`: Payment was cancelled

---

### 3. Transaction History

Get a paginated list of e-commerce transactions.

**Endpoint:** `GET /payments/history`

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `phone_number` | string | No | Filter by customer phone number |
| `start_date` | string | No | Filter by start date (YYYY-MM-DD) |
| `end_date` | string | No | Filter by end date (YYYY-MM-DD) |
| `status` | string | No | Filter by payment status |
| `per_page` | number | No | Number of results per page (default: 20, max: 100) |

**Example Request:**
```http
GET /api/ecommerce/payments/history?status=SUCCESS&start_date=2026-06-01&per_page=10
```

**Success Response (200 OK):**
```json
{
    "success": true,
    "data": [
        {
            "id": "...",
            "order_reference": "MYSHOP-12345",
            "transaction_id": "CP-1234567890",
            "status": "SUCCESS",
            "amount": 15000,
            "currency": "TZS",
            "phone": "255712345678",
            "customer_name": "John Doe",
            "payer_name": "John Doe",
            "email": "john@example.com",
            "description": "Order #123 - Shopping Cart",
            "type": "ecommerce_payment",
            "payment_method": "M-Pesa",
            "created_at": "2026-06-16T10:30:00+00:00",
            "updated_at": "2026-06-16T10:30:15+00:00"
        }
    ],
    "pagination": {
        "current_page": 1,
        "per_page": 10,
        "total": 50,
        "last_page": 5
    }
}
```

## Integration Flow

1. **Customer checks out** on your e-commerce site
2. **Your system** calls `POST /api/ecommerce/payments/initiate` with payment details
3. **Save the `order_reference`** from the response in your order record
4. **Show payment instructions** to the customer (e.g., "Check your phone for a USSD prompt")
5. **Poll the status endpoint** (`GET /api/ecommerce/payments/status/{orderReference}`) every few seconds to check if payment is completed
6. **Update your order status** when the payment status is `SUCCESS` or `SETTLED`
