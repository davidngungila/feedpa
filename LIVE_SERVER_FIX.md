# Live Server Fix - Model::__callStatic() Error

## Issue Analysis
The error `Model::__callStatic()` at PaymentController.php(172) indicates that the live server is trying to call a static method that doesn't exist or the model isn't properly configured.

## Immediate Fix Steps

### 1. Pull Latest Code
```bash
cd /var/www/repositories/feedpa
git pull origin master
```

### 2. Clear All Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 3. Check Database Migration
```bash
php artisan migrate:status
```

### 4. If Migration Missing
```bash
php artisan migrate
```

### 5. Enable Debug Mode (Temporarily)
```bash
nano .env
```
Change:
```env
APP_DEBUG=true
```

### 6. Restart Services
```bash
systemctl restart php8.3-fpm
systemctl reload nginx
```

## Verify Model Configuration

The Transaction model should have:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_reference',
        'transaction_id',
        'status',
        'amount',
        'currency',
        'phone_number',  // This must match database column
        'payer_name',
        'email',
        'description',
        'type',
        'payment_method',
        'callback_data',
        'callback_received_at',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'callback_data' => 'array',
        'callback_received_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}
```

## Test the Fix

After applying fixes:
1. Test payment form: https://pay.feedtancmg.org/payment
2. Check Laravel logs: `tail -f storage/logs/laravel.log`
3. Verify transaction creation in database

## Common Issues & Solutions

### Issue 1: Old Code on Server
**Solution**: Ensure `git pull` succeeded and files are updated

### Issue 2: Missing Database Column
**Solution**: Run `php artisan migrate` to update schema

### Issue 3: Cache Issues
**Solution**: Clear all Laravel caches

### Issue 4: Permissions
**Solution**: Ensure storage directory is writable
```bash
chmod -R 775 storage/
chown -R www-data:www-data storage/
```

## Debug Mode Instructions

If still failing after fixes:

1. Enable debug mode in `.env`
2. Reload page to see exact error
3. Check full error message
4. Send complete error log for analysis

## Quick Commands

```bash
# All-in-one fix
cd /var/www/repositories/feedpa
git pull origin master
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan migrate
systemctl restart php8.3-fpm
systemctl reload nginx
```
