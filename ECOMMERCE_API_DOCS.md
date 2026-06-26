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
| `callback_url` | string | No | URL on your commerce backend that will receive automatic payment sync updates after confirmed paid status |
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
        "status": "PENDING",
        "is_paid": false,
        "sync_ready": false,
        "payment_recorded_in_system": true,
        "amount": 15000,
        "currency": "TZS",
        "phone_number": "255712345678",
        "payer_name": "John Doe",
        "email": "john@example.com",
        "description": "Order #123 - Shopping Cart",
        "payment_method": null,
        "callback_url": "https://shop.example.com/api/payments/feedtan/callback",
        "metadata": {
            "order_id": 12345
        },
        "commerce_sync": null,
        "paid_at": null,
        "created_at": "2026-06-26T10:30:00+00:00",
        "updated_at": "2026-06-26T10:30:00+00:00"
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
        "is_paid": true,
        "sync_ready": true,
        "payment_recorded_in_system": true,
        "amount": 15000,
        "currency": "TZS",
        "phone_number": "255712345678",
        "payer_name": "John Doe",
        "email": "john@example.com",
        "description": "Order #123 - Shopping Cart",
        "payment_method": "M-Pesa",
        "callback_url": "https://shop.example.com/api/payments/feedtan/callback",
        "metadata": {
            "order_id": 12345
        },
        "commerce_sync": {
            "attempts": 1,
            "last_attempted_at": "2026-06-26T10:30:15+00:00",
            "last_attempt_status": "SUCCESS",
            "last_http_status": 200,
            "last_success_at": "2026-06-26T10:30:15+00:00",
            "last_success_status": "SUCCESS"
        },
        "paid_at": "2026-06-26T10:30:15+00:00",
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
            "order_reference": "MYSHOP-12345",
            "transaction_id": "CP-1234567890",
            "status": "SUCCESS",
            "is_paid": true,
            "sync_ready": true,
            "payment_recorded_in_system": true,
            "amount": 15000,
            "currency": "TZS",
            "phone_number": "255712345678",
            "payer_name": "John Doe",
            "email": "john@example.com",
            "description": "Order #123 - Shopping Cart",
            "payment_method": "M-Pesa",
            "callback_url": "https://shop.example.com/api/payments/feedtan/callback",
            "metadata": {
                "order_id": 12345
            },
            "commerce_sync": {
                "attempts": 1,
                "last_success_status": "SUCCESS"
            },
            "paid_at": "2026-06-16T10:30:15+00:00",
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
5. **Optionally receive automatic sync updates** on your `callback_url` after this system confirms the payment as `SUCCESS` or `SETTLED`
6. **Poll the status endpoint** (`GET /api/ecommerce/payments/status/{orderReference}`) every few seconds if you want pull-based verification or webhook fallback
7. **Update your order status** only when this system returns `is_paid: true` or the payment status is `SUCCESS` or `SETTLED`

## Automatic Callback Sync

If you provide `callback_url` during payment initiation, this system will send an HTTP `POST` request to your commerce backend when the payment becomes `SUCCESS` or `SETTLED`.

### Callback Payload Example

```json
{
    "success": true,
    "event": "payment.updated",
    "sync_ready": true,
    "payment_recorded_in_system": true,
    "data": {
        "order_reference": "MYSHOP-12345",
        "transaction_id": "CP-1234567890",
        "status": "SUCCESS",
        "is_paid": true,
        "amount": 15000,
        "currency": "TZS",
        "phone_number": "255712345678",
        "payer_name": "John Doe",
        "email": "john@example.com",
        "description": "Order #123 - Shopping Cart",
        "payment_method": "M-Pesa",
        "metadata": {
            "order_id": 12345
        },
        "paid_at": "2026-06-26T10:30:15+00:00",
        "created_at": "2026-06-26T10:30:00+00:00",
        "updated_at": "2026-06-26T10:30:15+00:00"
    }
}
```

Your commerce backend should verify the `order_reference`, confirm `is_paid` is `true`, and then mark the corresponding order as paid in its own database.

## Recommended Integration Modality

Your e-commerce platform can start the transaction, but the payment must still be recorded in this system first. This system should be treated as the **payment source of truth**, while your commerce platform should consume the payment result from here and then synchronize it into its own database.

### Operational Model

1. **Create the order in your e-commerce platform**
   Save your cart, customer, amount, and internal order ID in your commerce database with a temporary payment state such as `UNPAID` or `PENDING_PAYMENT`.

2. **Initiate the payment from your e-commerce platform**
   Call `POST /api/ecommerce/payments/initiate` from your commerce backend when the customer confirms checkout.

3. **Record the payment reference returned by this system**
   Save the returned `order_reference` and `transaction_id` in your e-commerce database. These values are the link between your commerce order and the payment record stored in this system.

4. **Treat this system as the payment ledger**
   Do not mark the order as paid only because initiation succeeded. A successful initiation only means the payment request was created and sent for customer confirmation.

5. **Read payment status from this system**
   Your commerce platform should call `GET /api/ecommerce/payments/status/{orderReference}` to confirm the real payment outcome.

6. **Synchronize only paid transactions back to your commerce database**
   When this system reports `SUCCESS` or `SETTLED`, your commerce platform should update its own order/payment tables, mark the order as paid, store the final payment status, and save the reconciliation fields returned by this API.

7. **Keep non-paid transactions pending or failed**
   If this system reports `PENDING`, `FAILED`, `DECLINED`, or `CANCELLED`, your commerce platform should keep the order in the corresponding unpaid state and not release goods/services.

### Recommended Data Mapping

Your commerce system should store at least the following fields locally after calling this API:

| Commerce Field | Source From This System | Purpose |
|---------------|-------------------------|---------|
| `order_id` | Your commerce database | Your internal order identity |
| `order_reference` | Initiate response / status response | Shared reconciliation key |
| `transaction_id` | Initiate response / status response | Payment transaction identity in this system |
| `amount` | Initiate response / status response | Amount verification |
| `phone_number` | Initiate request / status response | Customer payment number |
| `status` | Status response | Final or current payment state |
| `paid_at` | Derived from successful status/update time | Internal payment completion time |
| `payment_method` | Status response | Channel used for payment |
| `metadata` | Initiate request | Order payload traceability |

### Synchronization Rule

Use the following rule in your commerce platform:

- **Step 1:** Start the payment from the e-commerce checkout
- **Step 2:** Save the transaction identifiers returned by this system
- **Step 3:** Query this system until the payment becomes `SUCCESS` or `SETTLED`
- **Step 4:** Only then mark the order as paid in your commerce database
- **Step 5:** Store the same payment reference values in both systems for reconciliation and reporting

### Important Note

If the same order exists in both platforms, the payment status in your commerce database must always be synchronized from this system, not manually assumed from the checkout action. This avoids cases where an order is created in the shop but the mobile money payment was never actually completed.
