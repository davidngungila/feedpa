# FeedTan Payment System - Code Style & Standards Guide

Coding standards and best practices for FeedTan development team.

---

## Table of Contents
1. [PHP Standards](#php-standards)
2. [JavaScript Standards](#javascript-standards)
3. [CSS Standards](#css-standards)
4. [Blade Template Standards](#blade-template-standards)
5. [Database Standards](#database-standards)
6. [Git Workflow](#git-workflow)
7. [Code Review Process](#code-review-process)
8. [Testing Standards](#testing-standards)

---

## PHP Standards

### PSR Compliance

FeedTan follows PSR-12 coding standards for PHP.

#### Naming Conventions

**Classes**: PascalCase
```php
class PaymentController
class ClickPesaAPIService
class Transaction
```

**Methods**: camelCase
```php
public function createPayment()
public function checkPaymentStatus()
private function formatMessage()
```

**Variables**: camelCase
```php
$paymentAmount
$customerName
$transactionId
```

**Constants**: UPPER_SNAKE_CASE
```php
const MAX_RETRIES = 3;
const DEFAULT_CURRENCY = 'TZS';
```

### File Structure

#### Controllers
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ClickPesaAPIService;

class PaymentController extends Controller
{
    public function __construct(
        private ClickPesaAPIService $apiService
    ) {
    }

    public function store(Request $request)
    {
        // Implementation
    }
}
```

#### Services
```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClickPesaAPIService
{
    public function __construct(
        private string $apiKey,
        private string $secretKey
    ) {
    }

    public function createPayment(array $data): array
    {
        // Implementation
    }
}
```

#### Models
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Transaction extends Model
{
    use HasUuids;

    protected $fillable = [
        'order_reference',
        'transaction_id',
        'status',
        'amount',
        'currency',
        'phone',
        'payer_name'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'callback_data' => 'array'
    ];
}
```

### Code Formatting

#### Indentation
- Use 4 spaces for indentation
- No tabs

#### Line Length
- Maximum 120 characters per line
-Break long lines for readability

#### Spacing
- Space after comma
- Space around operators
- No space after function name

```php
// Good
$result = $this->processPayment($amount, $currency);

// Bad
$result=$this->processPayment($amount,$currency);
```

### Documentation

#### PHPDoc Blocks
```php
/**
 * Create a new payment transaction
 *
 * @param array $data Payment data including amount, currency, phone
 * @return array Payment response with reference and status
 * @throws \Exception If payment creation fails
 */
public function createPayment(array $data): array
{
    // Implementation
}
```

#### Inline Comments
```php
// Validate phone number format
if (!preg_match('/^255[0-9]{9}$/', $phone)) {
    throw new ValidationException('Invalid phone number');
}

// TODO: Implement retry logic for failed payments
// FIXME: This is a temporary fix, needs proper error handling
```

### Best Practices

#### Type Hints
```php
// Always use type hints
public function processPayment(string $reference, float $amount): bool
{
    // Implementation
}
```

#### Return Types
```php
// Always declare return types
public function getTransaction(string $reference): ?Transaction
{
    return Transaction::where('order_reference', $reference)->first();
}
```

#### Dependency Injection
```php
// Use constructor injection
public function __construct(
    private ClickPesaAPIService $apiService,
    private MessagingServiceAPI $smsService
) {
}
```

#### Exception Handling
```php
try {
    $result = $this->apiService->createPayment($data);
} catch (\Exception $e) {
    Log::error('Payment creation failed', [
        'error' => $e->getMessage(),
        'data' => $data
    ]);
    throw $e;
}
```

---

## JavaScript Standards

### Naming Conventions

**Variables**: camelCase
```javascript
const paymentAmount = 10000;
const customerName = 'John Doe';
```

**Functions**: camelCase
```javascript
function createPayment() {}
function checkStatus() {}
```

**Classes**: PascalCase
```javascript
class PaymentService {}
class TransactionModel {}
```

**Constants**: UPPER_SNAKE_CASE
```javascript
const MAX_RETRIES = 3;
const API_BASE_URL = 'https://api.example.com';
```

### Code Formatting

#### Indentation
- Use 4 spaces for indentation
- No tabs

#### Semicolons
- Always use semicolons

#### Quotes
- Use single quotes for strings
- Use double quotes for strings with embedded quotes

```javascript
// Good
const message = 'Payment successful';
const greeting = "Hello, 'world'";

// Bad
const message = "Payment successful";
```

### ES6+ Features

#### Use const/let
```javascript
// Good
const amount = 10000;
let status = 'pending';

// Bad
var amount = 10000;
var status = 'pending';
```

#### Arrow Functions
```javascript
// Good
const processPayment = (data) => {
    return api.createPayment(data);
};

// For simple functions
const formatAmount = (amount) => amount.toLocaleString();
```

#### Template Literals
```javascript
// Good
const message = `Payment of TZS ${amount} received from ${customerName}`;

// Bad
const message = 'Payment of TZS ' + amount + ' received from ' + customerName;
```

#### Destructuring
```javascript
// Good
const { amount, currency, phone } = paymentData;

// Bad
const amount = paymentData.amount;
const currency = paymentData.currency;
const phone = paymentData.phone;
```

### Async/Await

#### Prefer async/await over promises
```javascript
// Good
async function createPayment(data) {
    try {
        const response = await api.createPayment(data);
        return response;
    } catch (error) {
        console.error('Payment failed', error);
        throw error;
    }
}

// Avoid
function createPayment(data) {
    return api.createPayment(data)
        .then(response => response)
        .catch(error => {
            console.error('Payment failed', error);
            throw error;
        });
}
```

### Error Handling

#### Always handle errors
```javascript
try {
    const result = await api.createPayment(data);
    return result;
} catch (error) {
    console.error('Payment creation failed', error);
    // Handle error appropriately
    throw error;
}
```

---

## CSS Standards

### Naming Conventions

#### BEM Methodology
```css
/* Block */
.payment-form {}

/* Element */
.payment-form__input {}
.payment-form__button {}

/* Modifier */
.payment-form--large {}
.payment-form__button--disabled {}
```

#### Utility Classes
```css
/* Use utility classes for common styles */
.text-center { text-align: center; }
.mt-4 { margin-top: 1rem; }
.p-2 { padding: 0.5rem; }
```

### Code Organization

#### Group Related Styles
```css
/* Payment Form */
.payment-form {
    /* Styles */
}

.payment-form__input {
    /* Styles */
}

.payment-form__button {
    /* Styles */
}

/* Transaction List */
.transaction-list {
    /* Styles */
}
```

### Best Practices

#### Avoid !important
```css
/* Bad */
.button {
    color: red !important;
}

/* Good */
.button.primary {
    color: red;
}
```

#### Use CSS Variables
```css
:root {
    --primary-color: #007bff;
    --secondary-color: #6c757d;
    --success-color: #28a745;
}

.button {
    background-color: var(--primary-color);
}
```

#### Mobile-First Approach
```css
/* Base styles (mobile) */
.container {
    width: 100%;
    padding: 1rem;
}

/* Tablet */
@media (min-width: 768px) {
    .container {
        max-width: 720px;
    }
}

/* Desktop */
@media (min-width: 1024px) {
    .container {
        max-width: 1140px;
    }
}
```

---

## Blade Template Standards

### File Organization

#### Component Structure
```
resources/views/
├── layouts/
│   └── app.blade.php
├── components/
│   ├── payment-form.blade.php
│   └── transaction-card.blade.php
├── payments/
│   ├── create.blade.php
│   └── status.blade.php
└── dashboard/
    └── index.blade.php
```

### Template Syntax

#### Use Short Echo
```blade
{{-- Good --}}
{{ $amount }}

{{-- Bad --}}
<?php echo $amount; ?>
```

#### Use @if/@else
```blade
{{-- Good --}}
@if ($status === 'SUCCESS')
    <div class="alert alert-success">Payment successful</div>
@else
    <div class="alert alert-danger">Payment failed</div>
@endif

{{-- Bad --}}
<?php if ($status === 'SUCCESS'): ?>
    <div class="alert alert-success">Payment successful</div>
<?php else: ?>
    <div class="alert alert-danger">Payment failed</div>
<?php endif; ?>
```

#### Use @foreach
```blade
@foreach ($transactions as $transaction)
    <div>{{ $transaction->order_reference }}</div>
@endforeach
```

### Best Practices

#### Escape Output
```blade
{{-- Always escape user input --}}
{{ $userInput }}

{{-- Use {!! only for trusted HTML --}}
{!! $trustedHtml !!}
```

#### Use Components
```blade
{{-- Instead of repeating code --}}
<x-payment-form :amount="$amount" :phone="$phone" />
```

#### Use Directives
```blade
{{-- Use @auth instead of checking manually --}}
@auth
    <a href="{{ route('dashboard') }}">Dashboard</a>
@endauth

@guest
    <a href="{{ route('login') }}">Login</a>
@endguest
```

---

## Database Standards

### Naming Conventions

#### Tables
- snake_case
- Plural nouns

```sql
CREATE TABLE transactions;
CREATE TABLE users;
CREATE TABLE payment_logs;
```

#### Columns
- snake_case
- Singular nouns

```sql
order_reference
transaction_id
customer_name
created_at
updated_at
```

#### Foreign Keys
- {table}_id

```sql
user_id
transaction_id
payment_method_id
```

### Migration Standards

#### Migration File Naming
```bash
php artisan make:migration create_transactions_table
php artisan make:migration add_sms_status_to_transactions_table
```

#### Migration Structure
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('order_reference')->unique();
            $table->string('transaction_id')->nullable();
            $table->enum('status', ['PENDING', 'PROCESSING', 'SUCCESS', 'SETTLED', 'FAILED', 'ERROR'])->default('PENDING');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('TZS');
            $table->string('phone', 20)->nullable();
            $table->string('payer_name')->nullable();
            $table->string('email')->nullable();
            $table->string('payment_method')->nullable();
            $table->json('callback_data')->nullable();
            $table->boolean('sms_sent')->default(false);
            $table->timestamp('sms_sent_at')->nullable();
            $table->text('sms_error')->nullable();
            $table->timestamps();
            
            $table->index('order_reference');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
```

### Model Standards

#### Fillable Fields
```php
protected $fillable = [
    'order_reference',
    'transaction_id',
    'status',
    'amount',
    'currency',
    'phone',
    'payer_name'
];
```

#### Casts
```php
protected $casts = [
    'amount' => 'decimal:2',
    'callback_data' => 'array',
    'sms_sent' => 'boolean',
    'sms_sent_at' => 'datetime'
];
```

#### Relationships
```php
public function user()
{
    return $this->belongsTo(User::class);
}

public function notifications()
{
    return $this->hasMany(Notification::class);
}
```

---

## Git Workflow

### Branch Naming

#### Feature Branches
```bash
feature/add-sms-notification
feature/payment-dashboard
feature/api-rate-limiting
```

#### Bugfix Branches
```bash
bugfix/payment-timeout
bugfix/sms-delivery-failure
```

#### Hotfix Branches
```bash
hotfix/security-patch
hotfix-critical-bug
```

### Commit Messages

#### Format
```
<type>(<scope>): <subject>

<body>

<footer>
```

#### Types
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting)
- `refactor`: Code refactoring
- `test`: Adding tests
- `chore`: Maintenance tasks

#### Examples
```
feat(payment): add SMS notification for successful payments

- Implement SMS service integration
- Add SMS template for payment confirmation
- Update transaction model with SMS status

Closes #123
```

```
fix(api): handle timeout errors in payment processing

- Add retry logic for timeout errors
- Implement exponential backoff
- Add logging for failed attempts

Fixes #456
```

### Git Hooks

#### Pre-commit Hook
```bash
#!/bin/bash
# Run PHP CS Fixer
vendor/bin/php-cs-fixer fix --dry-run

# Run tests
vendor/bin/phpunit
```

#### Pre-push Hook
```bash
#!/bin/bash
# Run full test suite
vendor/bin/phpunit

# Run static analysis
vendor/bin/phpstan analyse
```

---

## Code Review Process

### Review Checklist

#### Functionality
- [ ] Code works as intended
- [ ] Edge cases are handled
- [ ] Error handling is appropriate
- [ ] Tests are included

#### Code Quality
- [ ] Follows coding standards
- [ ] No code duplication
- [ ] Proper documentation
- [ ] No commented-out code

#### Security
- [ ] No hardcoded credentials
- [ ] Input validation
- [ ] SQL injection prevention
- [ ] XSS prevention

#### Performance
- [ ] No unnecessary database queries
- [ ] Proper indexing
- [ ] Caching where appropriate
- [ ] No memory leaks

### Review Process

1. **Self-Review**
   - Review your own code before submitting
   - Run tests locally
   - Check for style violations

2. **Peer Review**
   - Request review from team member
   - Address review comments
   - Make necessary changes

3. **Approval**
   - Get approval from reviewer
   - Merge to main branch
   - Delete feature branch

---

## Testing Standards

### Unit Tests

#### Naming Conventions
```php
// Test class: {ClassName}Test
class PaymentControllerTest extends TestCase
{
    // Test method: test_{methodName}_{scenario}
    public function test_createPayment_withValidData_returnsSuccess()
    {
    }
}
```

#### Test Structure
```php
public function test_createPayment_withValidData_returnsSuccess()
{
    // Arrange
    $data = [
        'amount' => 10000,
        'currency' => 'TZS',
        'phone' => '255622239304',
        'payer_name' => 'John Doe'
    ];

    // Act
    $response = $this->postJson('/api/v1/payments/store', $data);

    // Assert
    $response->assertStatus(200)
             ->assertJson([
                 'success' => true,
                 'message' => 'Payment created successfully'
             ]);
}
```

### Integration Tests

#### API Testing
```php
public function test_payment_status_endpoint_returnsCorrectStatus()
{
    $transaction = Transaction::factory()->create([
        'status' => 'SUCCESS'
    ]);

    $response = $this->getJson("/api/v1/payments/status?reference={$transaction->order_reference}");

    $response->assertStatus(200)
             ->assertJson([
                 'success' => true,
                 'data' => [
                     'status' => 'SUCCESS'
                 ]
             ]);
}
```

### Test Coverage

#### Minimum Coverage
- Controllers: 80%
- Services: 90%
- Models: 85%

#### Coverage Report
```bash
vendor/bin/phpunit --coverage-html coverage
```

---

## Appendix

### A. Tools

#### PHP Tools
- **PHP CS Fixer**: Code formatting
- **PHPStan**: Static analysis
- **PHPUnit**: Unit testing
- **Pest**: Alternative testing framework

#### JavaScript Tools
- **ESLint**: Linting
- **Prettier**: Code formatting
- **Jest**: Testing

#### Git Tools
- **Husky**: Git hooks
- **Commitlint**: Commit message linting
- **Semantic Release**: Automated versioning

### B. Resources

#### Documentation
- [PSR-12 Coding Standard](https://www.php-fig.org/psr/psr-12/)
- [Laravel Documentation](https://laravel.com/docs)
- [PHP The Right Way](https://phptherightway.com/)

#### Style Guides
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices)
- [Clean Code PHP](https://github.com/jupeter/clean-code-php)

---

© 2026 FeedTan. All rights reserved.
