# FeedTan Payment System - Deployment / DevOps Runbook

Complete deployment and operations guide for FeedTan payment system.

---

## Table of Contents
1. [Deployment Overview](#deployment-overview)
2. [Environment Setup](#environment-setup)
3. [CI/CD Pipeline](#cicd-pipeline)
4. [Deployment Process](#deployment-process)
5. [Rollback Procedures](#rollback-procedures)
6. [Infrastructure as Code](#infrastructure-as-code)
7. [Monitoring & Alerting](#monitoring--alerting)
8. [Incident Response](#incident-response)
9. [Maintenance Procedures](#maintenance-procedures)

---

## Deployment Overview

### Deployment Strategy

FeedTan uses a **blue-green deployment** strategy for zero-downtime deployments.

#### Deployment Environments

| Environment | Purpose | URL | Database |
|-------------|---------|-----|----------|
| Development | Local development | localhost | Local MySQL |
| Staging | Pre-production testing | staging.feedtancmg.org | Staging MySQL |
| Production | Live production | pay.feedtancmg.org | Production MySQL |

#### Deployment Frequency
- **Hotfixes**: As needed
- **Feature releases**: Weekly
- **Major releases**: Monthly

---

## Environment Setup

### Prerequisites

#### Server Requirements

**Minimum Specifications:**
- CPU: 2 cores
- RAM: 4 GB
- Storage: 50 GB SSD
- OS: Ubuntu 22.04 LTS

**Recommended Specifications:**
- CPU: 4 cores
- RAM: 8 GB
- Storage: 100 GB SSD
- OS: Ubuntu 22.04 LTS

#### Software Requirements

```bash
# PHP 8.4
sudo apt update
sudo apt install php8.4 php8.4-fpm php8.4-mysql php8.4-xml php8.4-mbstring php8.4-curl php8.4-zip php8.4-bcmath

# Nginx
sudo apt install nginx

# MySQL
sudo apt install mysql-server

# Redis
sudo apt install redis-server

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Node.js & NPM
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

### Server Configuration

#### PHP Configuration

Edit `/etc/php/8.4/fpm/php.ini`:

```ini
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 20M
post_max_size = 20M
date.timezone = Africa/Dar_es_Salaam
```

#### Nginx Configuration

Create `/etc/nginx/sites-available/feedtan`:

```nginx
server {
    listen 80;
    server_name pay.feedtancmg.org;
    root /var/www/feedtanclickpesa/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    index index.php index.html;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site:

```bash
sudo ln -s /etc/nginx/sites-available/feedtan /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

#### SSL Configuration

```bash
sudo certbot --nginx -d pay.feedtancmg.org
```

---

## CI/CD Pipeline

### GitHub Actions Workflow

Create `.github/workflows/deploy.yml`:

```yaml
name: Deploy to Production

on:
  push:
    branches:
      - master

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - name: Checkout code
      uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.4'
        extensions: mbstring, xml, mysql, curl, zip, bcmath
    
    - name: Install dependencies
      run: composer install --no-dev --optimize-autoloader
    
    - name: Setup Node.js
      uses: actions/setup-node@v3
      with:
        node-version: '20'
    
    - name: Install frontend dependencies
      run: npm ci
    
    - name: Build frontend assets
      run: npm run build
    
    - name: Run tests
      run: vendor/bin/phpunit
    
    - name: Deploy to server
      uses: appleboy/ssh-action@master
      with:
        host: ${{ secrets.SERVER_HOST }}
        username: ${{ secrets.SERVER_USER }}
        key: ${{ secrets.SSH_PRIVATE_KEY }}
        script: |
          cd /var/www/feedtanclickpesa
          git pull origin master
          composer install --no-dev --optimize-autoloader
          npm install --production
          npm run build
          php artisan migrate --force
          php artisan cache:clear
          php artisan config:cache
          php artisan route:cache
          php artisan view:cache
          sudo systemctl reload php8.4-fpm
          sudo systemctl reload nginx
```

### Pipeline Stages

#### 1. Build Stage
- Checkout code
- Install dependencies
- Build frontend assets

#### 2. Test Stage
- Run unit tests
- Run integration tests
- Code quality checks

#### 3. Deploy Stage
- Deploy to server
- Run migrations
- Clear caches
- Reload services

---

## Deployment Process

### Pre-Deployment Checklist

- [ ] All tests passing
- [ ] Code review approved
- [ ] Database migrations prepared
- [ ] Backup created
- [ ] Rollback plan documented
- [ ] Stakeholders notified

### Deployment Steps

#### 1. Create Backup

```bash
# Database backup
php artisan db:backup --file="pre-deploy-$(date +%Y%m%d-%H%M%S).sql"

# File backup
tar -czf "pre-deploy-files-$(date +%Y%m%d-%H%M%S).tar.gz" /var/www/feedtanclickpesa
```

#### 2. Deploy Code

```bash
# SSH into server
ssh user@server

# Navigate to project directory
cd /var/www/feedtanclickpesa

# Pull latest code
git pull origin master

# Install/update dependencies
composer install --no-dev --optimize-autoloader
npm install --production

# Build frontend assets
npm run build
```

#### 3. Run Migrations

```bash
# Run database migrations
php artisan migrate --force

# Check for any issues
php artisan migrate:status
```

#### 4. Clear Caches

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Re-optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### 5. Restart Services

```bash
# Restart PHP-FPM
sudo systemctl restart php8.4-fpm

# Reload Nginx
sudo systemctl reload nginx

# Restart Redis (if needed)
sudo systemctl restart redis-server
```

#### 6. Verify Deployment

```bash
# Check application status
curl -I https://pay.feedtancmg.org

# Check logs
tail -f storage/logs/laravel.log

# Test API endpoint
curl https://pay.feedtancmg.org/api/v1/payments/api/status
```

### Post-Deployment Verification

- [ ] Application loads correctly
- [ ] API endpoints responding
- [ ] Database queries working
- [ ] SMS service functional
- [ ] Email service functional
- [ ] No errors in logs
- [ ] Performance metrics normal

---

## Rollback Procedures

### Automatic Rollback Triggers

- Deployment fails
- Critical errors in logs
- API response time > 10 seconds
- Error rate > 10%

### Manual Rollback Steps

#### 1. Stop Deployment

```bash
# If deployment is still running
git stash
```

#### 2. Restore Database

```bash
# Restore from backup
php artisan db:restore --file="pre-deploy-YYYYMMDD-HHMMSS.sql"
```

#### 3. Revert Code

```bash
# Revert to previous commit
git revert HEAD

# Or checkout previous commit
git checkout previous-commit-hash
```

#### 4. Restore Files

```bash
# Restore from backup
tar -xzf "pre-deploy-files-YYYYMMDD-HHMMSS.tar.gz" -C /
```

#### 5. Clear Caches

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

#### 6. Restart Services

```bash
sudo systemctl restart php8.4-fpm
sudo systemctl reload nginx
```

### Rollback Verification

- [ ] Application loads correctly
- [ ] Previous functionality restored
- [ ] No data loss
- [ ] Logs show normal operation

---

## Infrastructure as Code

### Terraform Configuration

#### Main Configuration

Create `terraform/main.tf`:

```hcl
provider "aws" {
  region = "us-east-1"
}

resource "aws_instance" "feedtan_server" {
  ami           = "ami-0c55b159cbfafe1f0"
  instance_type = "t3.medium"
  
  tags = {
    Name = "feedtan-production"
    Environment = "production"
  }
  
  root_block_device {
    volume_size = 100
    volume_type = "gp3"
  }
}

resource "aws_db_instance" "feedtan_database" {
  allocated_storage    = 20
  storage_type         = "gp2"
  engine               = "mysql"
  engine_version       = "8.0"
  instance_class       = "db.t3.micro"
  db_name              = "feedtanclickpesa"
  username             = "feedtan"
  password             = var.db_password
  
  tags = {
    Name = "feedtan-database"
  }
}

resource "aws_elasticache_cluster" "feedtan_cache" {
  cluster_id           = "feedtan-cache"
  engine               = "redis"
  node_type            = "cache.t3.micro"
  num_cache_nodes      = 1
  parameter_group_name  = "default.redis7"
  engine_version       = "7.0"
}
```

#### Variables

Create `terraform/variables.tf`:

```hcl
variable "db_password" {
  type        = string
  description = "Database password"
  sensitive   = true
}

variable "aws_region" {
  type        = string
  default     = "us-east-1"
  description = "AWS region"
}
```

### Docker Configuration

#### Dockerfile

Create `Dockerfile`:

```dockerfile
FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Copy frontend files
COPY public public
COPY storage storage

# Set permissions
RUN chown -R www-data:www-data /var/www

EXPOSE 9000

CMD ["php-fpm"]
```

#### Docker Compose

Create `docker-compose.yml`:

```yaml
version: '3.8'

services:
  app:
    build: .
    container_name: feedtan-app
    restart: unless-stopped
    working_dir: /var/www
    volumes:
      - ./:/var/www
    networks:
      - feedtan-network

  nginx:
    image: nginx:alpine
    container_name: feedtan-nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./:/var/www
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
    networks:
      - feedtan-network

  mysql:
    image: mysql:8.0
    container_name: feedtan-mysql
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: feedtanclickpesa
      MYSQL_ROOT_PASSWORD: root_password
      MYSQL_USER: feedtan
      MYSQL_PASSWORD: feedtan_password
    volumes:
      - mysql_data:/var/lib/mysql
    networks:
      - feedtan-network

  redis:
    image: redis:alpine
    container_name: feedtan-redis
    restart: unless-stopped
    networks:
      - feedtan-network

networks:
  feedtan-network:
    driver: bridge

volumes:
  mysql_data:
```

---

## Monitoring & Alerting

### Application Monitoring

#### Laravel Telescope

Install and configure Telescope:

```bash
composer require laravel/telescope
php artisan telescope:install
php artisan migrate
```

Configure in `config/telescope.php`:

```php
'watchers' => [
    Watchers\RequestWatcher::class => true,
    Watchers\QueryWatcher::class => true,
    Watchers\MailWatcher::class => true,
    Watchers\JobWatcher::class => true,
],
```

#### Logging

Configure logging channels in `config/logging.php`:

```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'slack'],
    ],
    'payments' => [
        'driver' => 'daily',
        'path' => storage_path('logs/payments.log'),
        'level' => 'info',
        'days' => 30,
    ],
],
```

### Server Monitoring

#### Uptime Monitoring

Use UptimeRobot or similar service:
- Monitor HTTPS endpoint
- Check response time
- Alert on downtime

#### Resource Monitoring

Install monitoring agents:
- CPU usage
- Memory usage
- Disk usage
- Network traffic

### Alerting

#### Alert Channels

- **Email**: ops@feedtancmg.org
- **SMS**: +255 622 239 304
- **Slack**: #ops-alerts

#### Alert Rules

| Metric | Threshold | Severity |
|--------|-----------|----------|
| Error Rate | > 5% | Warning |
| Error Rate | > 10% | Critical |
| Response Time | > 5s | Warning |
| Response Time | > 10s | Critical |
| Disk Usage | > 80% | Warning |
| Disk Usage | > 90% | Critical |

---

## Incident Response

### Incident Severity Levels

#### P1 - Critical
- System down
- Payment processing failure
- Data breach
- Response time: 15 minutes

#### P2 - High
- Degraded performance
- Partial service outage
- Security vulnerability
- Response time: 1 hour

#### P3 - Medium
- Minor performance issues
- Non-critical bugs
- Response time: 4 hours

#### P4 - Low
- Cosmetic issues
- Documentation errors
- Response time: 24 hours

### Incident Response Process

#### 1. Detection
- Automated alert received
- User report
- Monitoring dashboard

#### 2. Triage
- Assess severity
- Determine impact
- Assign owner

#### 3. Investigation
- Check logs
- Reproduce issue
- Identify root cause

#### 4. Resolution
- Implement fix
- Test solution
- Deploy to production

#### 5. Recovery
- Verify fix
- Monitor system
- Clear alerts

#### 6. Post-Incident
- Document incident
- Create post-mortem
- Implement improvements

### Communication Plan

#### Internal Communication
- **P1**: Immediate notification to all ops team
- **P2**: Notification to ops team within 15 minutes
- **P3**: Notification to ops team within 1 hour
- **P4**: Notification to ops team within 4 hours

#### External Communication
- **P1**: Public announcement within 1 hour
- **P2**: Public announcement within 4 hours
- **P3/P4**: No public announcement required

---

## Maintenance Procedures

### Daily Maintenance

#### Automated Tasks
```bash
# Clear cache (2:00 AM)
0 2 * * * cd /var/www/feedtanclickpesa && php artisan cache:clear

# Backup database (3:00 AM)
0 3 * * * cd /var/www/feedtanclickpesa && php artisan db:backup

# Clean old logs (4:00 AM)
0 4 * * * find /var/www/feedtanclickpesa/storage/logs -name "*.log" -mtime +30 -delete
```

#### Manual Checks
- [ ] Review error logs
- [ ] Check disk space
- [ ] Monitor API response times
- [ ] Verify SMS delivery rate
- [ ] Check email delivery rate

### Weekly Maintenance

#### Tasks
- [ ] Review security logs
- [ ] Check for software updates
- [ ] Analyze performance metrics
- [ ] Review failed transactions
- [ ] Test backup restoration

### Monthly Maintenance

#### Tasks
- [ ] Update dependencies
- [ ] Review and rotate API keys
- [ ] Audit user access
- [ ] Test disaster recovery
- [ ] Review and optimize database
- [ ] Update documentation

### Security Maintenance

#### Security Updates
```bash
# Update OS packages
sudo apt update && sudo apt upgrade

# Update PHP packages
composer update

# Update NPM packages
npm update
```

#### Security Audits
- Run vulnerability scans
- Review access logs
- Audit user permissions
- Check for exposed credentials

---

## Appendix

### A. Deployment Checklist

#### Pre-Deployment
- [ ] Code reviewed and approved
- [ ] All tests passing
- [ ] Database migrations ready
- [ ] Backup created
- [ ] Rollback plan documented
- [ ] Stakeholders notified

#### During Deployment
- [ ] Backup created
- [ ] Code deployed
- [ ] Migrations run
- [ ] Caches cleared
- [ ] Services restarted

#### Post-Deployment
- [ ] Application verified
- [ ] API tested
- [ ] Logs checked
- [ ] Performance monitored
- [ ] Rollback available

### B. Emergency Contacts

| Role | Name | Email | Phone |
|------|------|-------|-------|
| DevOps Lead | - | devops@feedtancmg.org | +255 622 239 304 |
| Database Admin | - | dba@feedtancmg.org | - |
| Security Officer | - | security@feedtancmg.org | - |
| System Admin | - | admin@feedtancmg.org | - |

### C. Useful Commands

#### Application Commands
```bash
# Check application status
php artisan up

# Put application in maintenance mode
php artisan down

# Clear all caches
php artisan cache:clear

# View routes
php artisan route:list

# View configuration
php artisan config:show
```

#### Database Commands
```bash
# Backup database
php artisan db:backup

# Restore database
php artisan db:restore

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback
```

#### Queue Commands
```bash
# Start queue worker
php artisan queue:work

# Restart queue workers
php artisan queue:restart

# View failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

---

© 2026 FeedTan. All rights reserved.
