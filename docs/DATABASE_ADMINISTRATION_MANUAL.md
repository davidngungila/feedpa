# FeedTan Payment System - Database Administration Manual

Complete database administration guide for FeedTan payment system.

---

## Table of Contents
1. [Database Overview](#database-overview)
2. [Schema Design](#schema-design)
3. [Migration Management](#migration-management)
4. [Backup & Restore](#backup--restore)
5. [Performance Optimization](#performance-optimization)
6. [Security & Access Control](#security--access-control)
7. [Monitoring & Maintenance](#monitoring--maintenance)
8. [Troubleshooting](#troubleshooting)

---

## Database Overview

### Database Information

**Database Name**: feedtanclickpesa  
**Database Engine**: MySQL 8.0+  
**Character Set**: utf8mb4  
**Collation**: utf8mb4_unicode_ci

### Connection Details

#### Production
```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=feedtanclickpesa
DB_USERNAME=feedtan_user
DB_PASSWORD=secure_password
```

#### Staging
```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=feedtanclickpesa_staging
DB_USERNAME=feedtan_staging
DB_PASSWORD=staging_password
```

---

## Schema Design

### Tables Overview

#### users
Stores user authentication and role information.

```sql
CREATE TABLE users (
    id CHAR(36) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### transactions
Stores payment transaction records.

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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_order_reference (order_reference),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_phone (phone),
    INDEX idx_status_created (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Relationships

#### Entity Relationship Diagram

```
users (1) ----< (N) transactions
```

#### Foreign Key Relationships

Currently, the system does not enforce foreign key constraints for flexibility. Relationships are handled at the application level via Eloquent ORM.

---

## Migration Management

### Creating Migrations

#### Generate Migration File
```bash
php artisan make:migration create_transactions_table
```

#### Migration File Structure
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
            $table->text('description')->nullable();
            $table->string('type', 50)->default('payment');
            $table->string('payment_method')->nullable();
            $table->json('callback_data')->nullable();
            $table->timestamp('callback_received_at')->nullable();
            $table->boolean('sms_sent')->default(false);
            $table->text('sms_message')->nullable();
            $table->timestamp('sms_sent_at')->nullable();
            $table->text('sms_error')->nullable();
            $table->timestamps();
            
            $table->index('order_reference');
            $table->index('status');
            $table->index('created_at');
            $table->index('phone');
            $table->index(['status', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
```

### Running Migrations

#### Run All Migrations
```bash
php artisan migrate
```

#### Run Migrations in Production
```bash
php artisan migrate --force
```

#### Run Specific Migration
```bash
php artisan migrate --path=database/migrations/2024_01_01_000000_create_transactions_table.php
```

### Rolling Back Migrations

#### Rollback Last Migration
```bash
php artisan migrate:rollback
```

#### Rollback Multiple Steps
```bash
php artisan migrate:rollback --step=3
```

#### Rollback All Migrations
```bash
php artisan migrate:reset
```

#### Rollback and Migrate Fresh
```bash
php artisan migrate:fresh
```

#### Rollback, Migrate Fresh, and Seed
```bash
php artisan migrate:fresh --seed
```

### Migration Status

#### Check Migration Status
```bash
php artisan migrate:status
```

Output:
```
Migration name ............................................................................ Batch / Status
2014_10_12_000000_create_users_table .......................................................... [1] Ran
2014_10_12_100000_create_password_resets_table .............................................. [1] Ran
2024_01_01_000000_create_transactions_table .................................................. [1] Ran
```

---

## Backup & Restore

### Backup Strategies

#### Full Database Backup
```bash
mysqldump -u feedtan_user -p feedtanclickpesa > backup-$(date +%Y%m%d-%H%M%S).sql
```

#### Compressed Backup
```bash
mysqldump -u feedtan_user -p feedtanclickpesa | gzip > backup-$(date +%Y%m%d-%H%M%S).sql.gz
```

#### Backup Specific Tables
```bash
mysqldump -u feedtan_user -p feedtanclickpesa transactions > transactions-backup.sql
```

#### Backup with Laravel Command
```bash
php artisan db:backup --file="backup-$(date +%Y%m%d-%H%M%S).sql"
```

### Automated Backups

#### Cron Job for Daily Backups
```bash
# Add to crontab
0 3 * * * mysqldump -u feedtan_user -p'password' feedtanclickpesa | gzip > /backups/feedtan-$(date +\%Y\%m\%d).sql.gz
```

#### Keep Last 7 Days
```bash
# Add to crontab
0 4 * * * find /backups -name "feedtan-*.sql.gz" -mtime +7 -delete
```

### Restore Procedures

#### Restore from SQL File
```bash
mysql -u feedtan_user -p feedtanclickpesa < backup-20240101-030000.sql
```

#### Restore from Compressed File
```bash
gunzip < backup-20240101-030000.sql.gz | mysql -u feedtan_user -p feedtanclickpesa
```

#### Restore with Laravel Command
```bash
php artisan db:restore --file="backup-20240101-030000.sql"
```

### Backup Verification

#### Verify Backup Integrity
```bash
# Check SQL file
head -n 20 backup-20240101-030000.sql

# Check compressed file
gunzip -t backup-20240101-030000.sql.gz
```

#### Test Restore (Staging)
```bash
# Restore to staging database
mysql -u feedtan_staging -p feedtanclickpesa_staging < backup-20240101-030000.sql

# Verify data
mysql -u feedtan_staging -p feedtanclickpesa_staging -e "SELECT COUNT(*) FROM transactions;"
```

---

## Performance Optimization

### Query Optimization

#### Analyze Slow Queries
```sql
-- Enable slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;

-- View slow queries
SELECT * FROM mysql.slow_log ORDER BY query_time DESC LIMIT 10;
```

#### Explain Query Execution
```sql
EXPLAIN SELECT * FROM transactions WHERE status = 'SUCCESS' AND created_at >= '2024-01-01';
```

#### Optimize Common Queries

**Before:**
```sql
SELECT * FROM transactions WHERE phone = '255622239304';
```

**After (with index):**
```sql
-- Ensure phone index exists
CREATE INDEX idx_phone ON transactions(phone);

-- Query will use index
SELECT * FROM transactions WHERE phone = '255622239304';
```

### Indexing Strategy

#### Analyze Index Usage
```sql
-- Check index usage
SELECT * FROM sys.schema_unused_indexes;

-- Check index cardinality
SHOW INDEX FROM transactions;
```

#### Add Composite Indexes
```sql
-- For status + date range queries
CREATE INDEX idx_status_created ON transactions(status, created_at);

-- For phone + status queries
CREATE INDEX idx_phone_status ON transactions(phone, status);
```

#### Remove Unused Indexes
```sql
-- Drop unused index
DROP INDEX idx_unused ON transactions;
```

### Table Optimization

#### Optimize Tables
```sql
OPTIMIZE TABLE transactions;
OPTIMIZE TABLE users;
```

#### Analyze Tables
```sql
ANALYZE TABLE transactions;
ANALYZE TABLE users;
```

#### Check Table Size
```sql
SELECT 
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
FROM information_schema.tables
WHERE table_schema = 'feedtanclickpesa'
ORDER BY size_mb DESC;
```

### Connection Pooling

#### Configure Connection Pool
Edit `config/database.php`:

```php
'mysql' => [
    'driver' => 'mysql',
    'url' => env('DATABASE_URL'),
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'forge'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'unix_socket' => env('DB_SOCKET', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'prefix_indexes' => true,
    'strict' => true,
    'engine' => null,
    'options' => extension_loaded('pdo_mysql') ? array_filter([
        PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
    ]) : [],
    'pool' => [
        'max_connections' => 100,
        'min_connections' => 10,
    ],
],
```

---

## Security & Access Control

### User Management

#### Create Database User
```sql
CREATE USER 'feedtan_user'@'localhost' IDENTIFIED BY 'secure_password';
```

#### Grant Privileges
```sql
-- Application user (limited privileges)
GRANT SELECT, INSERT, UPDATE, DELETE ON feedtanclickpesa.* TO 'feedtan_user'@'localhost';
FLUSH PRIVILEGES;

-- Admin user (full privileges)
GRANT ALL PRIVILEGES ON feedtanclickpesa.* TO 'feedtan_admin'@'localhost';
FLUSH PRIVILEGES;
```

#### Revoke Privileges
```sql
REVOKE ALL PRIVILEGES ON feedtanclickpesa.* FROM 'feedtan_user'@'localhost';
FLUSH PRIVILEGES;
```

#### Drop User
```sql
DROP USER 'feedtan_user'@'localhost';
```

### Password Management

#### Change User Password
```sql
ALTER USER 'feedtan_user'@'localhost' IDENTIFIED BY 'new_secure_password';
FLUSH PRIVILEGES;
```

#### Password Requirements
- Minimum 12 characters
- Mix of uppercase, lowercase, numbers, and special characters
- Rotate passwords every 90 days
- Never use default passwords

### Access Control

#### Restrict Remote Access
```sql
-- Only allow localhost access
GRANT ALL PRIVILEGES ON feedtanclickpesa.* TO 'feedtan_user'@'localhost';

-- Allow specific IP
GRANT ALL PRIVILEGES ON feedtanclickpesa.* TO 'feedtan_user'@'192.168.1.100';
```

#### View User Privileges
```sql
SHOW GRANTS FOR 'feedtan_user'@'localhost';
```

### Data Encryption

#### Encrypt Sensitive Data at Rest
```php
// Use Laravel encryption
$encrypted = Crypt::encryptString($sensitiveData);
$decrypted = Crypt::decryptString($encrypted);
```

#### Encrypt Backups
```bash
# Encrypt backup
gpg --symmetric --cipher-algo AES256 backup-20240101.sql

# Decrypt backup
gpg --decrypt backup-20240101.sql.gpg > backup-20240101.sql
```

---

## Monitoring & Maintenance

### Monitoring Queries

#### Monitor Database Size
```sql
SELECT 
    table_schema AS 'Database',
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)'
FROM information_schema.tables
WHERE table_schema = 'feedtanclickpesa'
GROUP BY table_schema;
```

#### Monitor Table Row Counts
```sql
SELECT 
    table_name,
    table_rows
FROM information_schema.tables
WHERE table_schema = 'feedtanclickpesa'
ORDER BY table_rows DESC;
```

#### Monitor Connection Count
```sql
SHOW STATUS LIKE 'Threads_connected';
```

#### Monitor Query Performance
```sql
-- Show running queries
SHOW PROCESSLIST;

-- Kill long-running query
KILL <process_id>;
```

### Maintenance Tasks

#### Daily Tasks
- [ ] Check disk space
- [ ] Review slow query log
- [ ] Verify backup completion
- [ ] Monitor connection count

#### Weekly Tasks
- [ ] Analyze table statistics
- [ ] Review index usage
- [ ] Check for table fragmentation
- [ ] Review user access logs

#### Monthly Tasks
- [ ] Optimize tables
- [ ] Review and update statistics
- [ ] Audit user permissions
- [ ] Test backup restoration

### Data Retention

#### Archive Old Transactions
```sql
-- Create archive table
CREATE TABLE transactions_archive LIKE transactions;

-- Move old transactions
INSERT INTO transactions_archive
SELECT * FROM transactions
WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);

-- Delete from main table
DELETE FROM transactions
WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);
```

#### Purge Old Logs
```sql
-- Delete old records (if using log tables)
DELETE FROM payment_logs
WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAYS);
```

---

## Troubleshooting

### Common Issues

#### Connection Refused
**Symptoms**: Unable to connect to database

**Solutions**:
1. Check MySQL service status
```bash
sudo systemctl status mysql
```

2. Restart MySQL service
```bash
sudo systemctl restart mysql
```

3. Check connection credentials in `.env`

#### Slow Queries
**Symptoms**: Queries taking too long

**Solutions**:
1. Identify slow queries
```sql
SHOW PROCESSLIST;
```

2. Analyze query with EXPLAIN
```sql
EXPLAIN SELECT * FROM transactions WHERE status = 'SUCCESS';
```

3. Add appropriate indexes
```sql
CREATE INDEX idx_status ON transactions(status);
```

#### Lock Issues
**Symptoms**: Queries waiting for locks

**Solutions**:
1. Check for locks
```sql
SHOW OPEN TABLES WHERE In_use > 0;
```

2. Kill locking process
```sql
KILL <process_id>;
```

#### Disk Space Issues
**Symptoms**: Disk full, unable to write

**Solutions**:
1. Check disk usage
```bash
df -h
```

2. Clean old backups
```bash
find /backups -name "*.sql.gz" -mtime +30 -delete
```

3. Archive old data
```sql
-- See Data Retention section
```

### Emergency Procedures

#### Database Corruption
**Symptoms**: Table corruption, unable to query

**Solutions**:
1. Check table status
```sql
CHECK TABLE transactions;
```

2. Repair table
```sql
REPAIR TABLE transactions;
```

3. Restore from backup if repair fails

#### Data Loss
**Symptoms**: Accidental data deletion

**Solutions**:
1. Stop application immediately
```bash
php artisan down
```

2. Restore from most recent backup
```bash
mysql -u feedtan_user -p feedtanclickpesa < backup-latest.sql
```

3. Verify data integrity
```sql
SELECT COUNT(*) FROM transactions;
```

4. Bring application back online
```bash
php artisan up
```

---

## Appendix

### A. Useful SQL Queries

#### Transaction Statistics
```sql
SELECT 
    status,
    COUNT(*) as count,
    SUM(amount) as total_amount,
    AVG(amount) as avg_amount
FROM transactions
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY status;
```

#### Daily Revenue
```sql
SELECT 
    DATE(created_at) as date,
    COUNT(*) as transactions,
    SUM(amount) as revenue
FROM transactions
WHERE status = 'SUCCESS'
    AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY DATE(created_at)
ORDER BY date DESC;
```

#### Payment Method Breakdown
```sql
SELECT 
    payment_method,
    COUNT(*) as count,
    SUM(amount) as total
FROM transactions
WHERE status = 'SUCCESS'
GROUP BY payment_method;
```

### B. Configuration Files

#### MySQL Configuration (my.cnf)
```ini
[mysqld]
# Connection Settings
max_connections = 200
max_connect_errors = 100

# Buffer Settings
innodb_buffer_pool_size = 2G
innodb_log_file_size = 512M
innodb_log_buffer_size = 16M

# Query Cache
query_cache_size = 64M
query_cache_limit = 2M

# Logging
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow-query.log
long_query_time = 2

# Character Set
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci
```

### C. Contact Information

| Role | Name | Email | Phone |
|------|------|-------|-------|
| Database Admin | - | dba@feedtancmg.org | - |
| System Admin | - | admin@feedtancmg.org | +255 622 239 304 |
| DevOps Engineer | - | devops@feedtancmg.org | - |

---

© 2026 FeedTan. All rights reserved.
