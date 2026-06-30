# FeedTan Payment System - Complete User Manual

## Table of Contents
1. [System Overview](#system-overview)
2. [Installation & Setup](#installation--setup)
3. [User Roles & Permissions](#user-roles--permissions)
4. [Getting Started](#getting-started)
5. [Payment Processing](#payment-processing)
6. [Dashboard Features](#dashboard-features)
7. [SMS Notifications](#sms-notifications)
8. [Email Notifications](#email-notifications)
9. [API Integration](#api-integration)
10. [Troubleshooting](#troubleshooting)
11. [FAQ](#faq)
12. [Support & Contact](#support--contact)

---

## System Overview

FeedTan is a comprehensive payment processing system built on Laravel framework with ClickPesa payment gateway integration. The system enables businesses to:

- Accept mobile money payments from customers
- Process real-time payment callbacks
- Send automated SMS notifications
- Send email confirmations
- Monitor transactions via an advanced dashboard
- Generate payment receipts
- Export transaction data

### Key Features
- **Multi-Currency Support**: Process payments in TZS and other currencies
- **Real-time Processing**: Instant payment status updates via webhooks
- **Automated Notifications**: SMS and email confirmations for all transactions
- **Advanced Dashboard**: Real-time analytics, revenue tracking, and transaction monitoring
- **Secure Transactions**: CSRF protection, encrypted data, and secure API integration
- **Mobile Responsive**: Works seamlessly on desktop and mobile devices

### System Architecture
- **Backend**: Laravel 13.5.0 (PHP 8.4.12)
- **Frontend**: Blade templates with Bootstrap 5
- **Database**: MySQL
- **Payment Gateway**: ClickPesa API
- **SMS Service**: Messaging Service API (messaging-service.co.tz)
- **Email Service**: SMTP/PHPMailer integration

---

## Installation & Setup

### Prerequisites
- PHP 8.4 or higher
- MySQL 5.7 or higher
- Composer
- Node.js and NPM
- Web server (Apache/Nginx)
- SSL certificate (recommended for production)

### Installation Steps

#### 1. Clone the Repository
```bash
git clone https://github.com/davidngungila/feedpa.git
cd feedpa
```

#### 2. Install Dependencies
```bash
composer install
npm install
```

#### 3. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

#### 4. Configure Environment Variables
Edit `.env` file with your settings:

```env
APP_NAME=FeedTan
APP_ENV=production
APP_KEY=your-app-key
APP_DEBUG=false
APP_URL=https://pay.feedtancmg.org

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=feedtanclickpesa
DB_USERNAME=your-username
DB_PASSWORD=your-password

CLICKPESA_API_KEY=your-clickpesa-api-key
CLICKPESA_SECRET_KEY=your-clickpesa-secret-key
CLICKPESA_MERCHANT_CODE=your-merchant-code
CLICKPESA_BASE_URL=https://api.clickpesa.co.tz

MESSAGING_TOKEN=your-messaging-service-token
MESSAGING_SENDER_ID=FEEDTAN

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@feedtancmg.org
MAIL_FROM_NAME="${APP_NAME}"
```

#### 5. Database Setup
```bash
php artisan migrate
php artisan db:seed
```

#### 6. Build Frontend Assets
```bash
npm run build
```

#### 7. Set Permissions
```bash
chmod -R 755 storage bootstrap/cache
```

#### 8. Configure Web Server
**Apache Configuration:**
```apache
<VirtualHost *:80>
    ServerName pay.feedtancmg.org
    DocumentRoot /path/to/feedpa/public
    
    <Directory /path/to/feedpa/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Nginx Configuration:**
```nginx
server {
    listen 80;
    server_name pay.feedtancmg.org;
    root /path/to/feedpa/public;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

#### 9. SSL Configuration (Recommended)
```bash
certbot --apache -d pay.feedtancmg.org
```

---

## User Roles & Permissions

### Admin Role
- Full access to all system features
- Manage users and permissions
- View all transactions
- Configure system settings
- Generate reports
- Manual SMS sending

### Manager Role
- View dashboard analytics
- Monitor transactions
- Send manual SMS notifications
- Export transaction data
- View payment receipts

### Customer Role
- Make payments
- View payment status
- Download receipts
- Receive SMS/email notifications

---

## Getting Started

### First-Time Login

1. **Access the Dashboard**
   - Navigate to: `https://pay.feedtancmg.org/dashboard`
   - Enter your credentials
   - Click "Login"

2. **Dashboard Overview**
   - Total transactions count
   - Revenue statistics
   - Success/failure rates
   - Recent transactions list
   - Payment method breakdown

### Navigation Menu

- **Dashboard**: Main analytics and overview
- **Payments**: Payment processing and history
- **Account**: Account balance and statements
- **Settings**: System configuration

---

## Payment Processing

### Creating a Payment

#### Via Public Payment Page

1. Navigate to `https://pay.feedtancmg.org/payment`
2. Fill in the payment details:
   - **Amount**: Enter payment amount in TZS
   - **Phone Number**: Customer's mobile number (format: 255XXXXXXXXX)
   - **Customer Name**: Customer's full name
   - **Email**: Customer's email address (optional)
   - **Description**: Payment description (optional)
3. Click "Process Payment"
4. Wait for USSD prompt on customer's phone
5. Customer enters PIN to confirm payment
6. System processes payment and redirects to status page

#### Via API Integration

```php
use App\Services\ClickPesaAPIService;

$api = new ClickPesaAPIService();

$paymentData = [
    'amount' => 10000,
    'currency' => 'TZS',
    'phone' => '255622239304',
    'payer_name' => 'John Doe',
    'email' => 'john@example.com',
    'description' => 'Service payment'
];

$result = $api->createPayment($paymentData);
```

### Payment Status Check

#### Via Web Interface
1. Go to Payments → Status
2. Enter payment reference number
3. Click "Check Status"
4. View current payment status and details

#### Via API
```php
$status = $api->checkPaymentStatus($reference);
```

### Payment Statuses

- **PENDING**: Payment initiated, awaiting customer confirmation
- **PROCESSING**: Payment being processed by payment gateway
- **SUCCESS**: Payment completed successfully
- **SETTLED**: Payment settled in merchant account
- **FAILED**: Payment failed (insufficient funds, timeout, etc.)
- **ERROR**: System error occurred during processing

### Payment Receipts

#### Download Receipt
1. Navigate to Payments → History
2. Find the desired transaction
3. Click "Receipt" button
4. Download PDF receipt

#### Receipt Contents
- Transaction ID
- Payment reference
- Amount and currency
- Customer details
- Payment date and time
- Payment status
- Merchant information

---

## Dashboard Features

### Main Dashboard

#### Statistics Cards
- **Total Transactions**: Total number of payments processed
- **Successful Payments**: Count of successful transactions
- **Failed Payments**: Count of failed transactions
- **Total Revenue**: Sum of all settled payments
- **Today's Revenue**: Revenue from today's transactions
- **Success Rate**: Percentage of successful payments

#### Transaction Filters
- **Date Range**: Today, This Week, This Month, This Year, Custom
- **Status**: All, Successful, Failed, Pending
- **Payment Method**: Mobile Money, Bank Transfer, Card

#### Recent Transactions Table
- Transaction reference
- Customer name
- Amount
- Payment method
- Status
- Date/time
- Actions (View, Receipt, SMS)

### Advanced Analytics

#### Revenue Chart
- Daily revenue trends
- Weekly revenue comparison
- Monthly revenue analysis
- Yearly revenue overview

#### Payment Methods Breakdown
- Mobile Money (M-Pesa, Tigo Pesa, Airtel Money)
- Bank Transfer
- Card Payments
- Other methods

#### Customer Analytics
- Top customers by transaction volume
- Top customers by revenue
- Customer payment patterns

#### Currency Breakdown
- TZS transactions
- USD transactions
- EUR transactions
- Other currencies

### Export Functionality

#### Export to CSV
1. Click "Export CSV" button
2. Select date range and filters
3. Download CSV file with transaction data

#### Export to Excel
1. Click "Export Excel" button
2. Select date range and filters
3. Download Excel file with transaction data

#### Export to PDF
1. Click "Export PDF" button
2. Select date range and filters
3. Download PDF report with charts and tables

---

## SMS Notifications

### Automatic SMS Notifications

The system automatically sends SMS notifications for:

- **Payment Confirmation**: When payment is successful
- **Payment Failed**: When payment fails with reason
- **Payment Settled**: When payment is settled in merchant account
- **Insufficient Funds**: When customer has insufficient funds

### SMS Message Templates

#### Payment Confirmation (Swahili)
```
Malipo yamefanikiwa. Tumepokea kiasi cha TZS {amount} kutoka kwa {customer_name} tarehe {date}. Rejea: {reference}. Asante kwa kutumia huduma zetu.
```

#### Payment Confirmation (English)
```
Payment successful. We have received TZS {amount} from {customer_name} on {date}. Reference: {reference}. Thank you for using our service.
```

#### Payment Failed
```
Malipo yamekubalika. Tumekuwa na tatizo la kutumia kiasi cha TZS {amount}. Rejea: {reference}. Tafadhali jaribu tena.
```

### Manual SMS Sending

#### Via Dashboard
1. Navigate to Dashboard
2. Find the transaction in the list
3. Click "SMS" button
4. Confirm SMS sending
5. System sends SMS to customer's phone number

#### Via API
```php
$messaging = new MessagingServiceAPI();
$result = $messaging->sendPaymentConfirmation($phoneNumber, $paymentData);
```

### SMS Configuration

#### Environment Variables
```env
MESSAGING_TOKEN=your-api-token
MESSAGING_SENDER_ID=FEEDTAN
```

#### SMS Service API
- **Base URL**: https://messaging-service.co.tz
- **Endpoint**: /api/sms/v2/text/single
- **Method**: POST
- **Authentication**: Bearer token

### SMS Tracking

The system tracks:
- SMS sent status
- SMS delivery status
- SMS error messages
- SMS sent timestamp
- SMS cost per message

---

## Email Notifications

### Automatic Email Notifications

The system sends email notifications for:

- **Payment Confirmation**: Detailed payment receipt
- **Payment Failed**: Failure reason and next steps
- **Payment Settled**: Settlement confirmation
- **Daily Reports**: Daily transaction summary (if enabled)

### Email Templates

#### Payment Confirmation Email
- Subject: Payment Confirmation - {reference}
- Content: Payment details, receipt attachment

#### Payment Failed Email
- Subject: Payment Failed - {reference}
- Content: Failure reason, retry instructions

### Email Configuration

#### SMTP Settings
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@feedtancmg.org
MAIL_FROM_NAME=FeedTan
```

#### Email Service Class
The system uses `EmailNotificationService` for email operations:

```php
$emailService = new EmailNotificationService();
$emailService->sendPaymentConfirmation($customerEmail, $paymentData);
```

### Email Tracking

- Email sent status
- Email delivery status
- Email open tracking (optional)
- Email click tracking (optional)

---

## API Integration

### ClickPesa API Integration

#### API Configuration
```env
CLICKPESA_API_KEY=your-api-key
CLICKPESA_SECRET_KEY=your-secret-key
CLICKPESA_MERCHANT_CODE=your-merchant-code
CLICKPESA_BASE_URL=https://api.clickpesa.co.tz
```

#### API Endpoints

##### Create Payment
- **Endpoint**: `/api/v1/payments`
- **Method**: POST
- **Authentication**: API Key + Secret Key

```json
{
    "amount": 10000,
    "currency": "TZS",
    "phone": "255622239304",
    "payer_name": "John Doe",
    "email": "john@example.com",
    "description": "Service payment",
    "merchant_code": "YOUR_MERCHANT_CODE"
}
```

##### Check Payment Status
- **Endpoint**: `/api/v1/payments/{reference}`
- **Method**: GET
- **Authentication**: API Key + Secret Key

##### Query All Payments
- **Endpoint**: `/api/v1/payments`
- **Method**: GET
- **Parameters**: limit, offset, startDate, endDate, status

### Webhook Configuration

#### Webhook URL
- **Endpoint**: `/webhooks/clickpesa`
- **Method**: POST
- **Authentication**: Disabled (CSRF protection bypassed)

#### Webhook Payload
```json
{
    "reference": "FEEDTAN123456",
    "status": "SUCCESS",
    "amount": 10000,
    "currency": "TZS",
    "phone": "255622239304",
    "payer_name": "John Doe",
    "transaction_id": "clickpesa-transaction-id",
    "timestamp": "2026-05-11T12:00:00Z"
}
```

#### Webhook Security
- Verify webhook signature (recommended for production)
- Validate reference exists in database
- Check for duplicate webhook calls
- Log all webhook events

### Custom API Integration

#### Payment Controller API
```php
// Create payment
POST /payments/store

// Check status
GET /payments/status?reference=xxx

// Payment history
GET /payments/history

// API status check
POST /payments/api/status
```

---

## Troubleshooting

### Common Issues

#### Payment Not Processing

**Symptoms**: Payment initiates but doesn't complete

**Solutions**:
1. Check ClickPesa API credentials
2. Verify customer phone number format (255XXXXXXXXX)
3. Ensure sufficient customer account balance
4. Check network connectivity
5. Verify API endpoint accessibility

**Debug Steps**:
```bash
php artisan tinker
>>> $api = new App\Services\ClickPesaAPIService();
>>> $api->checkPaymentStatus('FEEDTAN123456');
```

#### SMS Not Sending

**Symptoms**: SMS notifications not delivered

**Solutions**:
1. Verify messaging service token
2. Check phone number format
3. Ensure sufficient SMS credit balance
4. Check messaging service API status
5. Verify sender ID configuration

**Debug Steps**:
```bash
php artisan tinker
>>> $messaging = new App\Services\MessagingServiceAPI();
>>> $messaging->sendSMS('255622239304', 'Test message');
```

#### Email Not Sending

**Symptoms**: Email notifications not delivered

**Solutions**:
1. Verify SMTP credentials
2. Check email server accessibility
3. Ensure email is not in spam folder
4. Verify email configuration
5. Check email service logs

**Debug Steps**:
```bash
php artisan tinker
>>> $email = new App\Services\EmailNotificationService();
>>> $email->sendPaymentConfirmation('test@example.com', $data);
```

#### Dashboard Not Loading

**Symptoms**: Dashboard shows errors or blank screen

**Solutions**:
1. Clear browser cache
2. Clear Laravel cache: `php artisan cache:clear`
3. Clear view cache: `php artisan view:clear`
4. Rebuild assets: `npm run build`
5. Check browser console for JavaScript errors

#### Database Connection Issues

**Symptoms**: Database connection errors

**Solutions**:
1. Verify database credentials in `.env`
2. Ensure MySQL service is running
3. Check database exists
4. Verify user has proper permissions
5. Test connection: `php artisan db:show`

### Error Codes

| Error Code | Description | Solution |
|------------|-------------|----------|
| ERR_001 | Invalid API credentials | Check ClickPesa API keys |
| ERR_002 | Invalid phone number | Use format 255XXXXXXXXX |
| ERR_003 | Insufficient funds | Customer needs to top up |
| ERR_004 | Payment timeout | Retry payment |
| ERR_005 | SMS sending failed | Check messaging service |
| ERR_006 | Email sending failed | Check SMTP configuration |
| ERR_007 | Database error | Check database connection |
| ERR_008 | Webhook verification failed | Check webhook signature |

### Log Files

#### Laravel Logs
- **Location**: `storage/logs/laravel.log`
- **View**: `tail -f storage/logs/laravel.log`

#### Payment Logs
- **Location**: `storage/logs/payments.log`
- **Contains**: Payment API calls, responses, errors

#### SMS Logs
- **Location**: `storage/logs/sms.log`
- **Contains**: SMS API calls, delivery status

#### Email Logs
- **Location**: `storage/logs/email.log`
- **Contains**: Email sending attempts, delivery status

---

## FAQ

### General Questions

**Q: What currencies does FeedTan support?**
A: FeedTan primarily supports Tanzanian Shilling (TZS), but can be configured to support other currencies including USD, EUR, and GBP.

**Q: Is FeedTan secure?**
A: Yes, FeedTan uses industry-standard security measures including SSL encryption, CSRF protection, and secure API integration.

**Q: Can I integrate FeedTan with my existing website?**
A: Yes, FeedTan provides API endpoints for seamless integration with existing websites and applications.

### Payment Questions

**Q: How long does payment processing take?**
A: Most payments are processed within 30 seconds to 2 minutes, depending on the mobile money provider.

**Q: What happens if a payment fails?**
A: The system automatically notifies the customer via SMS and email with the failure reason. The customer can retry the payment.

**Q: Can customers get refunds?**
A: Refunds must be processed manually through the ClickPesa merchant dashboard. Contact support for refund requests.

### SMS Questions

**Q: How much does SMS cost?**
A: SMS costs vary by provider and destination. Check your messaging service account for current rates.

**Q: Can I customize SMS messages?**
A: Yes, SMS templates can be customized in the `MessagingServiceAPI.php` file.

**Q: What if SMS delivery fails?**
A: The system logs failed SMS attempts and can be configured to retry automatically.

### Dashboard Questions

**Q: How often is dashboard data updated?**
A: Dashboard data is updated in real-time for new transactions. Historical data is cached for performance.

**Q: Can I export dashboard data?**
A: Yes, you can export transaction data to CSV, Excel, or PDF formats.

**Q: How do I filter transactions?**
A: Use the date range, status, and payment method filters on the dashboard to filter transactions.

### Technical Questions

**Q: What are the system requirements?**
A: PHP 8.4+, MySQL 5.7+, Composer, Node.js, and a web server (Apache/Nginx).

**Q: Can I host FeedTan on shared hosting?**
A: Yes, FeedTan can be hosted on shared hosting that meets the system requirements.

**Q: How do I update FeedTan?**
A: Pull the latest changes from the repository, run `composer install`, `npm install`, and `php artisan migrate`.

---

## Support & Contact

### Technical Support

**Email**: support@feedtancmg.org
**Phone**: +255 622 239 304
**Hours**: Monday - Friday, 8:00 AM - 5:00 PM EAT

### Documentation

**Online Documentation**: https://docs.feedtancmg.org
**GitHub Repository**: https://github.com/davidngungila/feedpa
**Issue Tracker**: https://github.com/davidngungila/feedpa/issues

### Training Resources

**Video Tutorials**: https://www.youtube.com/feedtan
**Webinars**: Monthly webinars on new features
**User Guides**: Available in the documentation portal

### Emergency Contact

For critical issues outside business hours:
**Emergency Line**: +255 622 239 304
**Email**: emergency@feedtancmg.org

---

## Appendix

### A. Configuration Reference

#### Complete .env Example
```env
APP_NAME=FeedTan
APP_ENV=production
APP_KEY=base64:your-key-here
APP_DEBUG=false
APP_URL=https://pay.feedtancmg.org

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=feedtanclickpesa
DB_USERNAME=your-username
DB_PASSWORD=your-password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

CLICKPESA_API_KEY=your-clickpesa-api-key
CLICKPESA_SECRET_KEY=your-clickpesa-secret-key
CLICKPESA_MERCHANT_CODE=your-merchant-code
CLICKPESA_BASE_URL=https://api.clickpesa.co.tz

MESSAGING_TOKEN=your-messaging-service-token
MESSAGING_SENDER_ID=FEEDTAN

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@feedtancmg.org
MAIL_FROM_NAME="${APP_NAME}"
```

### B. API Reference

#### ClickPesa API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| /api/v1/payments | POST | Create payment |
| /api/v1/payments/{ref} | GET | Check status |
| /api/v1/payments | GET | Query all payments |
| /api/v1/refunds | POST | Create refund |

#### FeedTan API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| /payments/store | POST | Create payment |
| /payments/status | GET | Check status |
| /payments/history | GET | Payment history |
| /payments/api/status | POST | API status check |
| /dashboard/send-manual-sms | POST | Send manual SMS |

### C. Database Schema

#### Transactions Table

| Column | Type | Description |
|--------|------|-------------|
| id | UUID | Primary key |
| order_reference | string | Unique reference |
| transaction_id | string | ClickPesa transaction ID |
| status | string | Payment status |
| amount | decimal | Payment amount |
| currency | string | Currency code |
| phone | string | Customer phone |
| payer_name | string | Customer name |
| email | string | Customer email |
| payment_method | string | Payment method |
| callback_data | json | Webhook data |
| sms_sent | boolean | SMS sent status |
| sms_message | text | SMS content |
| sms_sent_at | timestamp | SMS sent time |
| sms_error | text | SMS error message |
| created_at | timestamp | Creation time |
| updated_at | timestamp | Last update |

### D. Security Best Practices

1. **Always use HTTPS** in production
2. **Keep API keys secret** and rotate regularly
3. **Enable CSRF protection** for all forms
4. **Validate all user inputs**
5. **Sanitize database queries**
6. **Implement rate limiting** on API endpoints
7. **Monitor logs** for suspicious activity
8. **Keep dependencies updated**
9. **Use strong passwords** for all accounts
10. **Regular security audits** recommended

### E. Performance Optimization

1. **Enable caching** for frequently accessed data
2. **Use database indexes** on frequently queried columns
3. **Optimize images** and static assets
4. **Enable CDN** for static content
5. **Use queue workers** for background jobs
6. **Monitor database queries** for slow performance
7. **Implement pagination** for large datasets
8. **Use lazy loading** for relationships
9. **Enable HTTP/2** on web server
10. **Regular database maintenance** (optimize tables)

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0.0 | 2026-05-11 | Initial release |
| 1.1.0 | 2026-05-11 | Added SMS manual sending |
| 1.2.0 | 2026-05-11 | Fixed JavaScript errors |
| 1.3.0 | 2026-05-11 | Enhanced dashboard features |

---

## License

FeedTan Payment System is proprietary software. All rights reserved.

© 2026 FeedTan. All rights reserved.
