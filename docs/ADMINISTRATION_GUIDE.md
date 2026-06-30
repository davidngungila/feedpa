# FeedTan Payment System - Administration Guide

Complete guide for system administrators managing the FeedTan payment platform.

---

## Table of Contents
1. [User Management](#user-management)
2. [Role Permissions](#role-permissions)
3. [System Configuration](#system-configuration)
4. [Monitoring & Analytics](#monitoring--analytics)
5. [Data Management](#data-management)
6. [Security Settings](#security-settings)
7. [Backup & Recovery](#backup--recovery)
8. [System Maintenance](#system-maintenance)

---

## User Management

### Adding New Users

#### Via Database
```sql
INSERT INTO users (name, email, password, role, created_at, updated_at)
VALUES (
    'John Admin',
    'admin@feedtancmg.org',
    '$2y$10$hashedpassword',
    'admin',
    NOW(),
    NOW()
);
```

#### Via Tinker
```bash
php artisan tinker
>>> $user = new App\Models\User();
>>> $user->name = 'John Admin';
>>> $user->email = 'admin@feedtancmg.org';
>>> $user->password = bcrypt('securepassword');
>>> $user->role = 'admin';
>>> $user->save();
```

### User Roles

#### Admin
- Full system access
- User management
- System configuration
- View all transactions
- Generate reports

#### Manager
- Dashboard access
- Transaction monitoring
- Manual SMS sending
- Export data
- View receipts

#### Customer
- Make payments
- View payment status
- Download receipts
- Receive notifications

### Modifying User Roles

```bash
php artisan tinker
>>> $user = App\Models\User::find(1);
>>> $user->role = 'manager';
>>> $user->save();
```

### Deleting Users

**Warning**: This action is irreversible.

```bash
php artisan tinker
>>> $user = App\Models\User::find(1);
>>> $user->delete();
```

---

## Role Permissions

### Permission Matrix

| Feature | Admin | Manager | Customer |
|---------|-------|---------|----------|
| Dashboard | ✓ | ✓ | ✗ |
| Payments | ✓ | ✓ | ✓ |
| User Management | ✓ | ✗ | ✗ |
| System Settings | ✓ | ✗ | ✗ |
| Manual SMS | ✓ | ✓ | ✗ |
| Export Data | ✓ | ✓ | ✗ |
| API Access | ✓ | ✗ | ✗ |
| View All Transactions | ✓ | ✓ | Own only |

### Custom Permissions

To add custom permissions, modify `app/Models/User.php`:

```php
public function hasPermission($permission)
{
    $permissions = [
        'admin' => ['*'],
        'manager' => ['dashboard', 'payments', 'sms', 'export'],
        'customer' => ['payments', 'receipts']
    ];
    
    $userPermissions = $permissions[$this->role] ?? [];
    return in_array('*', $userPermissions) || in_array($permission, $userPermissions);
}
```

---

## System Configuration

### Environment Variables

#### Payment Gateway Settings
```env
CLICKPESA_API_KEY=your-api-key
CLICKPESA_SECRET_KEY=your-secret-key
CLICKPESA_MERCHANT_CODE=your-merchant-code
CLICKPESA_BASE_URL=https://api.clickpesa.co.tz
```

#### SMS Service Settings
```env
MESSAGING_TOKEN=your-messaging-token
MESSAGING_SENDER_ID=FEEDTAN
```

#### Email Settings
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

#### Database Settings
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=feedtanclickpesa
DB_USERNAME=your-username
DB_PASSWORD=your-password
```

### Configuration Commands

#### Clear Configuration Cache
```bash
php artisan config:clear
```

#### Cache Configuration
```bash
php artisan config:cache
```

#### View Current Configuration
```bash
php artisan config:show app.name
```

---

## Monitoring & Analytics

### Dashboard Metrics

#### Key Performance Indicators
- **Total Transactions**: Daily transaction volume
- **Success Rate**: Percentage of successful payments
- **Revenue**: Total revenue collected
- **Average Transaction Value**: Mean payment amount
- **Payment Methods**: Breakdown by payment type

#### Real-Time Monitoring
Access dashboard at: `https://pay.feedtancmg.org/dashboard`

### Log Monitoring

#### Laravel Logs
```bash
tail -f storage/logs/laravel.log
```

#### Payment Logs
```bash
tail -f storage/logs/payments.log
```

#### SMS Logs
```bash
tail -f storage/logs/sms.log
```

#### Email Logs
```bash
tail -f storage/logs/email.log
```

### Performance Monitoring

#### Check Application Status
```bash
php artisan up
```

#### Check Queue Status
```bash
php artisan queue:work --status
```

#### Monitor Memory Usage
```bash
php artisan tinker
>>> memory_get_usage(true);
```

### Alerting

Set up alerts for:
- Payment failure rate > 10%
- API response time > 5 seconds
- Database connection failures
- SMS delivery failures
- Email sending failures

---

## Data Management

### Exporting Transaction Data

#### Via Dashboard
1. Navigate to Dashboard
2. Apply filters (date range, status, payment method)
3. Click "Export CSV" or "Export Excel"
4. Download file

#### Via Command Line
```bash
php artisan transactions:export --format=csv --from=2026-01-01 --to=2026-12-31
```

### Data Retention Policy

#### Recommended Retention Periods
- **Transaction Records**: 7 years (legal requirement)
- **SMS Logs**: 1 year
- **Email Logs**: 1 year
- **System Logs**: 90 days
- **Error Logs**: 180 days

#### Automated Cleanup
```bash
php artisan logs:cleanup --days=90
```

### Database Maintenance

#### Optimize Tables
```bash
php artisan db:optimize
```

#### Backup Database
```bash
php artisan db:backup
```

#### Restore Database
```bash
php artisan db:restore --file=backup-2026-05-11.sql
```

### Data Archiving

Archive old transactions to improve performance:

```bash
php artisan transactions:archive --before=2025-01-01
```

---

## Security Settings

### Authentication

#### Password Policy
- Minimum 8 characters
- At least 1 uppercase letter
- At least 1 number
- At least 1 special character

#### Session Management
```env
SESSION_LIFETIME=120
SESSION_DRIVER=file
```

#### Two-Factor Authentication (Optional)
Enable 2FA for admin accounts:

```bash
php artisan 2fa:enable --user=admin@feedtancmg.org
```

### API Security

#### Rate Limiting
Configure in `app/Providers/RouteServiceProvider.php`:

```php
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60);
});
```

#### IP Whitelisting
Add trusted IPs to `.env`:

```env
TRUSTED_PROXIES=192.168.1.1,10.0.0.1
```

### CSRF Protection

CSRF is enabled by default. To exempt specific routes:

```php
// app/Http/Middleware/VerifyCsrfToken.php
protected $except = [
    'webhooks/*',
    'api/payment-callback'
];
```

### SSL Configuration

#### Force HTTPS
Add to `app/Providers/AppServiceProvider.php`:

```php
public function boot()
{
    if (app()->environment('production')) {
        URL::forceScheme('https');
    }
}
```

#### SSL Certificate Renewal
```bash
certbot renew
```

---

## Backup & Recovery

### Backup Strategy

#### Daily Backups
- Database dump
- Configuration files
- Uploaded files
- Log files

#### Backup Location
- Local storage: `storage/backups/`
- Remote storage: AWS S3 or Google Cloud Storage

#### Backup Script
```bash
#!/bin/bash
DATE=$(date +%Y-%m-%d)
php artisan db:backup --file="backup-$DATE.sql"
tar -czf "backup-$DATE.tar.gz" storage/backups/ .env
aws s3 cp "backup-$DATE.tar.gz" s3://feedtan-backups/
```

### Recovery Procedures

#### Database Recovery
```bash
php artisan db:restore --file=backup-2026-05-11.sql
```

#### Configuration Recovery
```bash
tar -xzf backup-2026-05-11.tar.gz
php artisan config:clear
```

#### Disaster Recovery
1. Restore from latest backup
2. Verify database integrity
3. Test payment processing
4. Monitor system logs
5. Notify users of downtime

---

## System Maintenance

### Scheduled Tasks

#### Clear Cache Daily
```bash
0 2 * * * php /path/to/feedtan/artisan cache:clear >> /dev/null 2>&1
```

#### Backup Database Daily
```bash
0 3 * * * php /path/to/feedtan/artisan db:backup >> /dev/null 2>&1
```

#### Clean Logs Weekly
```bash
0 4 * * 0 php /path/to/feedtan/artisan logs:cleanup --days=90 >> /dev/null 2>&1
```

#### Update Dependencies Monthly
```bash
0 5 1 * * cd /path/to/feedtan && composer update >> /dev/null 2>&1
```

### Software Updates

#### Check for Updates
```bash
composer outdated
npm outdated
```

#### Update Dependencies
```bash
composer update
npm update
```

#### Run Migrations
```bash
php artisan migrate
```

#### Rebuild Assets
```bash
npm run build
```

### Performance Tuning

#### Enable Query Cache
```env
DB_CACHE=true
DB_CACHE_TTL=3600
```

#### Enable Redis Cache
```env
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

#### Optimize Images
```bash
php artisan images:optimize
```

---

## Troubleshooting

### Common Admin Issues

#### Users Cannot Login
1. Check database connection
2. Verify user exists in database
3. Reset user password
4. Clear session cache

#### Dashboard Not Loading
1. Clear browser cache
2. Clear Laravel cache: `php artisan cache:clear`
3. Clear view cache: `php artisan view:clear`
4. Rebuild assets: `npm run build`

#### Payments Not Processing
1. Verify ClickPesa API credentials
2. Check API endpoint accessibility
3. Review payment logs
4. Test API connection

#### SMS Not Sending
1. Verify messaging service token
2. Check SMS credit balance
3. Review SMS logs
4. Test SMS service

### Emergency Procedures

#### System Down
1. Check server status
2. Review error logs
3. Restart services
4. Notify users
5. Escalate if needed

#### Data Breach
1. Immediately change all API keys
2. Reset all user passwords
3. Review access logs
4. Notify affected users
5. Implement additional security measures

#### Payment Gateway Issues
1. Switch to backup gateway (if available)
2. Notify ClickPesa support
3. Monitor transaction queue
4. Process pending transactions manually

---

## Support Resources

**Technical Support**: support@feedtancmg.org  
**Emergency Contact**: +255 622 239 304  
**Documentation**: https://docs.feedtancmg.org  
**GitHub Issues**: https://github.com/davidngungila/feedpa/issues

---

## Appendix

### A. Admin Checklist

#### Daily
- [ ] Check dashboard for errors
- [ ] Review failed transactions
- [ ] Monitor SMS delivery
- [ ] Check system logs

#### Weekly
- [ ] Review user activity
- [ ] Analyze payment trends
- [ ] Check API performance
- [ ] Verify backup completion

#### Monthly
- [ ] Update dependencies
- [ ] Review security logs
- [ ] Audit user access
- [ ] Test disaster recovery

### B. Contact Information

| Role | Name | Email | Phone |
|------|------|-------|-------|
| System Admin | - | admin@feedtancmg.org | +255 622 239 304 |
| Database Admin | - | dba@feedtancmg.org | - |
| Security Officer | - | security@feedtancmg.org | - |
| Support Team | - | support@feedtancmg.org | +255 622 239 304 |

---

© 2026 FeedTan. All rights reserved.
