# FeedTan Payment System - Technical Design Document

Complete technical architecture and design documentation for developers.

---

## Table of Contents
1. [System Overview](#system-overview)
2. [Architecture](#architecture)
3. [Technology Stack](#technology-stack)
4. [Database Design](#database-design)
5. [API Design](#api-design)
6. [Security Architecture](#security-architecture)
7. [Payment Flow](#payment-flow)
8. [Notification System](#notification-system)
9. [Scalability Considerations](#scalability-considerations)
10. [Monitoring & Logging](#monitoring--logging)

---

## System Overview

FeedTan is a Laravel-based payment processing system that integrates with ClickPesa payment gateway to process mobile money payments in Tanzania.

### Core Objectives
- Process mobile money payments securely
- Provide real-time payment status updates
- Send automated SMS and email notifications
- Offer comprehensive dashboard analytics
- Enable API integration for third-party services

### System Boundaries
- **Inbound**: Payment requests from users and API clients
- **Outbound**: Payment gateway calls, SMS service calls, email service calls
- **Internal**: Database, cache, queue, file storage

---

## Architecture

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                         Client Layer                         │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐   │
│  │  Web UI  │  │  Mobile  │  │  API     │  │  Webhook │   │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘   │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                      Application Layer                       │
│  ┌──────────────────────────────────────────────────────┐  │
│  │              Laravel Application                        │  │
│  │  ┌──────────┐  ┌──────────┐  ┌──────────┐           │  │
│  │  │ Routes   │  │ Controllers│  │ Services │           │  │
│  │  └──────────┘  └──────────┘  └──────────┘           │  │
│  │  ┌──────────┐  ┌──────────┐  ┌──────────┐           │  │
│  │  │ Models   │  │ Views    │  │ Middleware│          │  │
│  │  └──────────┘  └──────────┘  └──────────┘           │  │
│  └──────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                        Service Layer                         │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐   │
│  │ClickPesa │  │  SMS     │  │  Email   │  │  Queue   │   │
│  │   API    │  │ Service  │  │ Service  │  │  Worker  │   │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘   │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                        Data Layer                            │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐   │
│  │  MySQL   │  │  Redis   │  │  File    │  │  Cache   │   │
│  │ Database │  │  Cache   │  │ Storage  │  │  Store   │   │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘   │
└─────────────────────────────────────────────────────────────┘
```

### Component Diagram

```
┌─────────────────┐
│   PaymentController   │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  ClickPesaAPIService  │
└────────┬────────┘
         │
         ├──────────────┬──────────────┐
         ▼              ▼              ▼
┌─────────────┐  ┌─────────────┐  ┌─────────────┐
│  MessagingServiceAPI  │  │  EmailNotificationService  │  │  Transaction Model  │
└─────────────┘  └─────────────┘  └─────────────┘
```

### Data Flow

1. **Payment Request**: Client → PaymentController → ClickPesaAPIService
2. **Payment Processing**: ClickPesaAPIService → ClickPesa API
3. **Webhook Callback**: ClickPesa → CallbackController → Transaction Model
4. **Notification**: CallbackController → MessagingServiceAPI/EmailNotificationService
5. **Status Check**: Client → PaymentController → Transaction Model

---

## Technology Stack

### Backend Framework
- **Framework**: Laravel 13.5.0
- **PHP Version**: 8.4.12
- **Web Server**: Apache/Nginx

### Database
- **Database**: MySQL 5.7+
- **ORM**: Eloquent ORM
- **Migrations**: Laravel Migrations
- **Seeding**: Laravel Seeders

### Frontend
- **Template Engine**: Blade
- **CSS Framework**: Bootstrap 5
- **JavaScript**: Vanilla JS + Alpine.js
- **Build Tool**: Vite

### Caching
- **Cache Driver**: Redis (production), File (development)
- **Session Driver**: Redis (production), File (development)
- **Queue Driver**: Redis (production), Sync (development)

### Payment Gateway
- **Provider**: ClickPesa
- **API Version**: v1
- **Authentication**: API Key + Secret Key

### SMS Service
- **Provider**: Messaging Service (messaging-service.co.tz)
- **API Version**: v2
- **Authentication**: Bearer Token

### Email Service
- **Protocol**: SMTP
- **Library**: PHPMailer
- **Encryption**: TLS

### Development Tools
- **Package Manager**: Composer, NPM
- **Version Control**: Git
- **Code Quality**: PHPStan, ESLint
- **Testing**: PHPUnit, Pest

---

## Database Design

### Entity Relationship Diagram

```
┌─────────────────┐       ┌─────────────────┐
│     users       │       │  transactions  │
├─────────────────┤       ├─────────────────┤
│ id (UUID)       │◄──────│ id (UUID)       │
│ name            │       │ order_reference │
│ email           │       │ transaction_id  │
│ password        │       │ status          │
│ role            │       │ amount          │
│ created_at      │       │ currency        │
│ updated_at      │       │ phone           │
└─────────────────┘       │ payer_name      │
                          │ email           │
                          │ payment_method  │
                          │ callback_data   │
                          │ sms_sent        │
                          │ sms_message     │
                          │ sms_sent_at     │
                          │ sms_error       │
                          │ created_at      │
                          │ updated_at      │
                          └─────────────────┘
```

### Tables

#### users
```sql
CREATE TABLE users (
    id CHAR(36) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'customer') DEFAULT 'customer',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
);
```

#### transactions
```sql
CREATE TABLE transactions (
    id CHAR(36) PRIMARY KEY,
    order_reference VARCHAR(255) UNIQUE NOT NULL,
    transaction_id VARCHAR(255),
    status ENUM('PENDING', 'PROCESSING', 'SUCCESS', 'SETTLED', 'FAILED', 'ERROR') DEFAULT 'PENDING',
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'TZS',
    phone VARCHAR(20),
    payer_name VARCHAR(255),
    email VARCHAR(255),
    description TEXT,
    type VARCHAR(50) DEFAULT 'payment',
    payment_method VARCHAR(50),
    callback_data JSON,
    callback_received_at TIMESTAMP,
    sms_sent BOOLEAN DEFAULT FALSE,
    sms_message TEXT,
    sms_sent_at TIMESTAMP,
    sms_error TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_order_reference (order_reference),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_phone (phone)
);
```

### Database Indexes

#### Performance Indexes
- `transactions.order_reference` - Unique index for payment lookups
- `transactions.status` - Filter by payment status
- `transactions.created_at` - Date range queries
- `transactions.phone` - Customer payment history
- `users.email` - User authentication

#### Composite Indexes (if needed)
```sql
CREATE INDEX idx_status_date ON transactions(status, created_at);
CREATE INDEX idx_phone_status ON transactions(phone, status);
```

### Database Relationships

#### User to Transactions
```php
// User Model
public function transactions()
{
    return $this->hasMany(Transaction::class);
}

// Transaction Model
public function user()
{
    return $this->belongsTo(User::class);
}
```

---

## API Design

### RESTful Principles

#### Resource Naming
- Use nouns, not verbs
- Use plural nouns for collections
- Use kebab-case for URLs

#### HTTP Methods
- `GET` - Retrieve resources
- `POST` - Create resources
- `PUT` - Update resources (full update)
- `PATCH` - Update resources (partial update)
- `DELETE` - Remove resources

#### Status Codes
- `2xx` - Success
- `4xx` - Client errors
- `5xx` - Server errors

### API Endpoints

#### Payment Endpoints
```
POST   /api/v1/payments/store          - Create payment
GET    /api/v1/payments/status         - Check payment status
GET    /api/v1/payments/history        - Get payment history
POST   /api/v1/payments/api/status     - API status check
```

#### Webhook Endpoints
```
POST   /webhooks/clickpesa             - ClickPesa callback
```

### Request/Response Format

#### Request Format
```json
{
    "amount": 10000,
    "currency": "TZS",
    "phone": "255622239304",
    "payer_name": "John Doe"
}
```

#### Response Format
```json
{
    "success": true,
    "message": "Payment created successfully",
    "data": {
        "reference": "FEEDTAN123456",
        "status": "PENDING"
    }
}
```

#### Error Response Format
```json
{
    "success": false,
    "message": "Validation error",
    "errors": {
        "phone": ["Invalid phone number format"]
    }
}
```

### API Versioning

#### URL Versioning
```
/api/v1/payments/store
/api/v2/payments/store
```

#### Version Strategy
- Maintain backward compatibility
- Deprecate old versions with notice
- Document version changes in release notes

---

## Security Architecture

### Authentication

#### API Authentication
- API Key + Secret Key pair
- Bearer token in Authorization header
- Token rotation every 90 days

#### User Authentication
- Laravel Authentication system
- Bcrypt password hashing
- Session-based authentication
- CSRF protection for web forms

### Authorization

#### Role-Based Access Control (RBAC)
```php
// Middleware
public function handle($request, Closure $next, $role)
{
    if (!$request->user()->hasRole($role)) {
        abort(403, 'Unauthorized');
    }
    return $next($request);
}
```

#### Permission Matrix
| Role | Dashboard | Payments | Users | Settings |
|------|-----------|----------|-------|----------|
| Admin | ✓ | ✓ | ✓ | ✓ |
| Manager | ✓ | ✓ | ✗ | ✗ |
| Customer | ✗ | ✓ | ✗ | ✗ |

### Data Protection

#### Encryption
- Passwords: Bcrypt
- API Keys: Encrypted at rest
- Sensitive data: AES-256

#### HTTPS
- Force HTTPS in production
- SSL/TLS encryption
- HSTS headers

### Input Validation

#### Server-Side Validation
```php
$request->validate([
    'amount' => 'required|numeric|min:100',
    'phone' => 'required|regex:/^255[0-9]{9}$/',
    'payer_name' => 'required|string|max:255'
]);
```

#### SQL Injection Prevention
- Use Eloquent ORM
- Use parameterized queries
- Never concatenate user input

### CSRF Protection

#### CSRF Tokens
- Automatically included in forms
- Verified on POST requests
- Exempted for webhooks

### Rate Limiting

#### API Rate Limiting
```php
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(100);
});
```

#### IP-Based Limiting
```php
RateLimiter::for('ip', function (Request $request) {
    return Limit::perMinute(20)->by($request->ip());
});
```

---

## Payment Flow

### Payment Creation Flow

```
1. User submits payment form
   ↓
2. PaymentController validates input
   ↓
3. ClickPesaAPIService creates payment
   ↓
4. ClickPesa API returns reference
   ↓
5. Transaction saved to database (status: PENDING)
   ↓
6. User redirected to payment page
   ↓
7. Customer confirms via USSD
   ↓
8. ClickPesa processes payment
   ↓
9. ClickPesa sends webhook callback
   ↓
10. CallbackController processes webhook
   ↓
11. Transaction status updated
   ↓
12. SMS notification sent
   ↓
13. Email notification sent
   ↓
14. User notified of result
```

### Payment State Machine

```
┌─────────┐
│ PENDING │
└────┬────┘
     │
     ├─────────────┐
     │             │
     ▼             ▼
┌─────────┐   ┌─────────┐
│PROCESSING│   │ FAILED  │
└────┬────┘   └─────────┘
     │
     ▼
┌─────────┐
│ SUCCESS │
└────┬────┘
     │
     ▼
┌─────────┐
│ SETTLED │
└─────────┘
```

### Error Handling

#### Payment Errors
- Network timeout → Retry (3 attempts)
- Invalid input → Return validation error
- Gateway error → Log and notify admin
- Insufficient funds → Notify customer

#### Webhook Errors
- Invalid signature → Return 401
- Duplicate webhook → Return 200 (idempotent)
- Processing error → Retry via queue

---

## Notification System

### SMS Notification Flow

```
1. Payment status changes
   ↓
2. CallbackController triggers notification
   ↓
3. MessagingServiceAPI formats message
   ↓
4. SMS sent via messaging-service.co.tz
   ↓
5. Delivery status logged
   ↓
6. Transaction updated with SMS status
```

### Email Notification Flow

```
1. Payment status changes
   ↓
2. CallbackController triggers notification
   ↓
3. EmailNotificationService formats email
   ↓
4. Email sent via SMTP
   ↓
5. Delivery status logged
   ↓
6. Transaction updated with email status
```

### Notification Templates

#### SMS Templates
- Payment confirmation
- Payment failed
- Payment settled
- Insufficient funds

#### Email Templates
- Payment receipt
- Payment failure notice
- Daily summary (optional)

### Queue System

#### Queue Configuration
```env
QUEUE_CONNECTION=redis
QUEUE_DRIVER=redis
```

#### Queue Workers
```bash
php artisan queue:work --tries=3 --timeout=120
```

#### Failed Jobs
```bash
php artisan queue:failed
php artisan queue:retry all
```

---

## Scalability Considerations

### Horizontal Scaling

#### Load Balancing
- Use Nginx as load balancer
- Round-robin distribution
- Health checks

#### Session Management
- Redis for session storage
- Sticky sessions (if needed)
- Session affinity

### Vertical Scaling

#### Database Optimization
- Read replicas for read-heavy operations
- Database indexing
- Query optimization

#### Caching Strategy
- Redis for frequently accessed data
- Cache invalidation on updates
- Cache warming for critical data

### Performance Optimization

#### Database
- Connection pooling
- Query optimization
- Index optimization

#### Application
- Opcode caching (OPcache)
- HTTP/2 support
- Gzip compression

#### CDN
- Static assets via CDN
- Image optimization
- Lazy loading

### Monitoring

#### Application Performance Monitoring (APM)
- Response time tracking
- Error rate monitoring
- Database query performance

#### Infrastructure Monitoring
- CPU usage
- Memory usage
- Disk I/O
- Network I/O

---

## Monitoring & Logging

### Logging Strategy

#### Log Levels
- **DEBUG**: Detailed debugging information
- **INFO**: General informational messages
- **WARNING**: Warning messages
- **ERROR**: Error messages
- **CRITICAL**: Critical errors

#### Log Channels
- **Stack**: Default channel
- **Single**: Single file
- **Daily**: Daily rotated files
- **Syslog**: System logger
- **Errorlog**: PHP error log

#### Log Files
- `storage/logs/laravel.log` - General application logs
- `storage/logs/payments.log` - Payment-specific logs
- `storage/logs/sms.log` - SMS service logs
- `storage/logs/email.log` - Email service logs

### Log Format

#### Standard Format
```
[2026-05-11 12:00:00] local.INFO: Payment created {"reference":"FEEDTAN123456","amount":10000}
```

#### Structured Logging
```php
Log::info('Payment created', [
    'reference' => $reference,
    'amount' => $amount,
    'phone' => $phone
]);
```

### Monitoring Metrics

#### Application Metrics
- Request rate
- Response time
- Error rate
- Payment success rate
- SMS delivery rate

#### Business Metrics
- Total transactions
- Total revenue
- Average transaction value
- Payment method distribution

### Alerting

#### Alert Conditions
- Error rate > 5%
- Response time > 5 seconds
- Payment failure rate > 10%
- SMS delivery failure rate > 5%
- Database connection failures

#### Alert Channels
- Email
- SMS
- Slack (optional)
- PagerDuty (optional)

---

## Appendix

### A. Configuration Files

#### .env.example
```env
APP_NAME=FeedTan
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://pay.feedtancmg.org

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=feedtanclickpesa
DB_USERNAME=
DB_PASSWORD=

CLICKPESA_API_KEY=
CLICKPESA_SECRET_KEY=
CLICKPESA_MERCHANT_CODE=
CLICKPESA_BASE_URL=https://api.clickpesa.co.tz

MESSAGING_TOKEN=
MESSAGING_SENDER_ID=FEEDTAN

MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

### B. Directory Structure

```
feedtanclickpesa/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   ├── Services/
│   └── Providers/
├── config/
├── database/
│   ├── migrations/
│   └── seeders/
├── public/
│   ├── assets/
│   └── build/
├── resources/
│   ├── views/
│   └── js/
├── routes/
│   ├── web.php
│   └── api.php
├── storage/
│   ├── app/
│   ├── framework/
│   └── logs/
├── tests/
├── vendor/
├── .env
├── .env.example
├── artisan
├── composer.json
└── package.json
```

### C. Service Dependencies

#### External Services
- ClickPesa API: https://api.clickpesa.co.tz
- Messaging Service: https://messaging-service.co.tz
- SMTP Server: Configured in .env

#### Internal Services
- MySQL Database: 127.0.0.1:3306
- Redis Cache: 127.0.0.1:6379
- Queue Worker: php artisan queue:work

---

© 2026 FeedTan. All rights reserved.
