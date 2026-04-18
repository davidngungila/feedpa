# Live Server HTTP 500 Error Debugging Guide

## Issue: HTTP 500 Error on Live Server
**URL**: https://pay.feedtancmg.org/payment
**Error**: HTTP error! status: 500

## Immediate Debugging Steps

### 1. Check Laravel Logs
First, check the Laravel error logs on the live server:

```bash
# SSH into your live server
ssh your-server

# Navigate to Laravel project
cd /path/to/your/project

# Check the latest error logs
tail -50 storage/logs/laravel.log

# Or check today's log
tail -50 storage/logs/laravel-$(date +%Y-%m-%d).log
```

### 2. Check Web Server Error Logs
Check your web server (Apache/Nginx) error logs:

```bash
# For Apache
tail -50 /var/log/apache2/error.log

# For Nginx
tail -50 /var/log/nginx/error.log

# Or check site-specific logs
tail -50 /var/log/apache2/sites-available/your-site-error.log
```

### 3. Verify Environment Configuration
Ensure the live server has the correct environment variables:

```bash
# Check if .env file exists
ls -la .env

# Verify key environment variables
cat .env | grep -E "(APP_ENV|APP_DEBUG|DB_|CLICKPESA_)"

# Check if .env is readable by web server
ls -la .env
```

## Common Causes & Solutions

### 1. Missing Environment Variables
**Problem**: Live server missing required environment variables.

**Solution**: Add these to your live server's `.env` file:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://pay.feedtancmg.org

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

# ClickPesa API (if configured)
CLICKPESA_API_KEY=your_api_key
CLICKPESA_CLIENT_ID=your_client_id
CLICKPESA_API_BASE_URL=https://api.clickpesa.com/v2
```

### 2. Database Connection Issues
**Problem**: Database connection failing on live server.

**Solution**: Test database connection:

```bash
# Test database connection
php artisan tinker
> DB::connection()->getPdo();
> exit;

# Or run migration check
php artisan migrate:status
```

### 3. File Permissions
**Problem**: Incorrect file permissions on live server.

**Solution**: Set correct permissions:

```bash
# Storage permissions
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# Ownership (adjust user/group as needed)
chown -R www-data:www-data storage/
chown -R www-data:www-data bootstrap/cache/

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 4. Missing Database Tables
**Problem**: Database tables not created on live server.

**Solution**: Run migrations:

```bash
# Run all migrations
php artisan migrate

# Or run specific migration
php artisan migrate --path=database/migrations/2026_04_18_094040_add_payer_name_to_transactions_table.php
```

### 5. PHP Version/Extensions
**Problem**: PHP version or missing extensions.

**Solution**: Check PHP configuration:

```bash
# Check PHP version
php -v

# Check required extensions
php -m | grep -E "(pdo|mbstring|openssl|tokenizer|xml)"

# Check Laravel requirements
php artisan about
```

### 6. Web Server Configuration
**Problem**: Web server not configured correctly.

**Solution**: Verify web server config:

```bash
# For Apache, ensure .htaccess is working
cat .htaccess

# For Nginx, ensure site config points to public/
# Check nginx site config
cat /etc/nginx/sites-available/your-site
```

## Specific Debugging for Payment Error

### 1. Check API Configuration
If using ClickPesa API, verify credentials:

```bash
# Test API configuration
php artisan tinker
> config('clickpesa.api_key');
> config('clickpesa.client_id');
> exit;
```

### 2. Test Payment Endpoint Directly
Test the payment endpoint with curl:

```bash
curl -X POST https://pay.feedtancmg.org/payment/store \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "payer_name": "Test User",
    "amount": 1000,
    "phone_number": "255712345678",
    "description": "Test payment"
  }'
```

### 3. Check Database Schema
Verify the transactions table structure:

```bash
php artisan tinker
> Schema::getColumnListing('transactions');
> exit;
```

## Quick Fix Checklist

### Must-Have Environment Variables:
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false` (set to true temporarily for debugging)
- [ ] `DB_CONNECTION=mysql`
- [ ] `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- [ ] `APP_URL=https://pay.feedtancmg.org`

### Database Setup:
- [ ] Database created
- [ ] Migrations run: `php artisan migrate`
- [ ] Tables exist: `transactions`, `users`, etc.

### File Permissions:
- [ ] Storage directory writable
- [ ] Bootstrap cache writable
- [ ] .env file readable by web server

### Caches Cleared:
- [ ] `php artisan cache:clear`
- [ ] `php artisan config:clear`
- [ ] `php artisan route:clear`
- [ ] `php artisan view:clear`

## Emergency Debug Mode

Temporarily enable debug mode to see detailed errors:

```env
# In .env file
APP_DEBUG=true
```

Then clear config cache:

```bash
php artisan config:clear
```

**Remember to disable debug mode after fixing the issue!**

## Next Steps

1. **Check Laravel logs first** - this will show the exact error
2. **Verify environment variables** - ensure all required variables are set
3. **Test database connection** - ensure database is accessible
4. **Run migrations** - ensure database schema is up to date
5. **Check file permissions** - ensure web server can write to required directories
6. **Clear all caches** - ensure latest configuration is loaded

## Contact Support

If you continue experiencing issues, provide:
- Laravel error log output
- Web server error log output
- Environment variables (without sensitive data)
- Database connection test results
