# Deployment Guide - ProjectCMS

## Prerequisites

- **PHP**: 8.2 or later (must include MySQLi/PDO extensions)
- **MySQL/MariaDB**: 10.4 or later
- **Composer**: For dependency management (optional but recommended)

## Critical Note: Vercel Deployment

**Vercel is a serverless platform optimized for Node.js, Python, and static sites. Traditional PHP applications are NOT natively supported on Vercel.**

### Vercel Alternatives:
For this PHP+MySQL application, consider these deployment platforms instead:

- **Heroku** (if still available) - Has PHP buildpack support
- **PlanetScale** - MySQL database as a service
- **Railway.app** - Excellent PHP/MySQL support
- **Fly.io** - Full containerization support
- **Render** - Native PHP/MySQL support
- **Traditional Shared Hosting** - GoDaddy, Bluehost, etc.
- **Docker + Cloud Platforms** - AWS ECS, Google Cloud Run, DigitalOcean App Platform

If you must use Vercel, you would need to:
1. Rewrite the application as a Node.js API
2. Use serverless PHP runtime with significant architectural changes
3. Host your database separately (PlanetScale, AWS RDS)

---

## Local Development Setup

### 1. Clone and Install

```bash
git clone <your-repository-url>
cd ProjectCms
composer install  # If using Composer
```

### 2. Configure Environment

```bash
cp .env.example .env
```

Edit `.env` with your local database credentials:

```
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=serverside
DB_USER=serveruser
DB_PASSWORD=your_password
APP_ENV=development
APP_DEBUG=true
```

### 3. Database Setup

```bash
mysql -u root -p < sql/serverside.sql
```

### 4. Start Development Server

```bash
php -S localhost:8000
```

Visit: http://localhost:8000

---

## Recommended Deployment Platforms (PHP/MySQL)

### Option 1: Railway.app (Recommended)

1. Push code to GitHub
2. Connect Railway to your GitHub repo
3. Add MySQL plugin
4. Set environment variables in Railway dashboard
5. Deploy with one click

**Advantages**: Simple, managed MySQL, automatic deployments

### Option 2: Render.com

1. Create account and connect GitHub
2. Create new "Web Service" from repository
3. Set build command: `composer install` (if using)
4. Set start command: `php -S 0.0.0.0:8080`
5. Connect PostgreSQL/MySQL database
6. Deploy

### Option 3: Docker + Cloud Platform (AWS, Google Cloud, DigitalOcean)

**Dockerfile**:
```dockerfile
FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql mysqli

COPY . /var/www/html/

RUN chmod -R 755 /var/www/html/uploads
RUN chmod -R 755 /var/www/html/logs

EXPOSE 80
```

---

## Pre-Deployment Checklist

- [ ] All hardcoded credentials moved to `.env`
- [ ] `.env` file added to `.gitignore`
- [ ] `.env.example` committed with template values
- [ ] Database backups created
- [ ] SSL/HTTPS certificate configured on server
- [ ] Session storage directory writable
- [ ] Upload directory writable and outside web root
- [ ] Error logging configured
- [ ] Security headers set (Content-Security-Policy, etc.)
- [ ] File permissions configured (755 for dirs, 644 for files)
- [ ] Database migration script tested
- [ ] Uploads folder permissions configured

## Production Configuration

### 1. Environment Variables

Set these in your hosting platform's dashboard:

```
APP_ENV=production
APP_DEBUG=false
DB_HOST=your-db-host
DB_NAME=your-db-name
DB_USER=your-db-user
DB_PASSWORD=your-secure-password
SESSION_SECURE=true
SESSION_HTTPONLY=true
```

### 2. PHP Configuration

In your hosting control panel or `php.ini`:

```ini
display_errors = Off
display_startup_errors = Off
log_errors = On
error_log = /var/log/php-errors.log
session.cookie_secure = 1
session.cookie_httponly = 1
upload_max_filesize = 5M
post_max_size = 5M
```

### 3. Web Server Configuration

#### Apache (.htaccess)

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /

    # Block access to sensitive files
    RewriteRule ^\.env$ - [F]
    RewriteRule ^\.git$ - [F]
    RewriteRule ^sql/ - [F]

    # Security headers
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>
```

#### Nginx

```nginx
# Block access to sensitive files
location ~ /\. {
    deny all;
}

location ~ ^/sql/ {
    deny all;
}

# Security headers
add_header X-Content-Type-Options "nosniff";
add_header X-Frame-Options "SAMEORIGIN";
add_header X-XSS-Protection "1; mode=block";
add_header Referrer-Policy "strict-origin-when-cross-origin";

# PHP processing
location ~ \.php$ {
    fastcgi_pass unix:/var/run/php-fpm.sock;
    fastcgi_index index.php;
    include fastcgi_params;
}
```

### 4. File Permissions

```bash
# Set correct directory permissions
chmod 755 ./uploads
chmod 755 ./logs
chmod 755 ./sessions

# Restrict file permissions
chmod 644 .env
chmod 644 index.php
chmod 644 *.php
chmod 755 includes
chmod 644 includes/*.php
```

### 5. Database Optimization

Run these queries on your production database:

```sql
-- Create indexes for better performance
CREATE INDEX idx_equipment_created ON equipment(created_at);
CREATE INDEX idx_reviews_equipment ON reviews(equipment_id);
CREATE INDEX idx_reviews_rating ON reviews(rating);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_categories_name ON categories(name);

-- Analyze tables
ANALYZE TABLE equipment;
ANALYZE TABLE reviews;
ANALYZE TABLE users;
ANALYZE TABLE categories;
```

---

## Deployment Process

### Step 1: Prepare Code

```bash
# Run tests/validation
composer test

# Create production .env
cp .env.example .env.production
# Edit with production values
```

### Step 2: Database Migration

```bash
# Backup existing database
mysqldump -u user -p database > backup.sql

# Apply migrations
mysql -u user -p database < sql/serverside.sql
```

### Step 3: Upload Files

Via FTP/SFTP or Git push:

```bash
git add .
git commit -m "Prepare for production deployment"
git push origin main
```

### Step 4: Verify Installation

1. Visit your domain
2. Test login functionality
3. Test file uploads
4. Check error logs for issues
5. Run security scan

---

## Monitoring & Maintenance

### Log Files to Monitor

```bash
# PHP errors
tail -f /var/log/php-errors.log

# Web server access
tail -f /var/log/apache2/access.log

# Application logs
tail -f logs/app.log
```

### Regular Maintenance

- **Weekly**: Check error logs
- **Monthly**: Database optimization (OPTIMIZE TABLE)
- **Quarterly**: Security updates, dependency updates
- **Annually**: Full security audit

### Backup Strategy

```bash
# Database backup
mysqldump -u user -p database | gzip > database-$(date +%Y%m%d).sql.gz

# File backup
tar -czf application-$(date +%Y%m%d).tar.gz .

# Schedule via cron (daily at 2 AM)
0 2 * * * /usr/local/bin/backup.sh
```

---

## Troubleshooting

### Issue: Database Connection Failed

**Solution**:
1. Check `.env` file exists and has correct credentials
2. Verify MySQL service is running
3. Check database user permissions
4. Review error logs: `error_log` setting in production-config.php

### Issue: File Upload Not Working

**Solution**:
1. Check `uploads/` directory permissions (should be 755)
2. Verify PHP `upload_max_filesize` setting
3. Check disk space available
4. Review PHP error logs

### Issue: Session Not Persisting

**Solution**:
1. Check session storage directory exists and is writable
2. Verify `session.save_path` in php.ini
3. Check browser cookie settings
4. Ensure HTTPS is enabled for `session.cookie_secure`

### Issue: 500 Internal Server Error

**Solution**:
1. Enable debugging temporarily (set APP_DEBUG=true)
2. Check PHP error log
3. Verify all required PHP extensions are installed
4. Check file permissions
5. Test with simpler PHP script

---

## Security Hardening Checklist

- [ ] All database credentials in environment variables only
- [ ] `.env` file never committed to repository
- [ ] Upload directory outside web root or protected
- [ ] File upload validation (type, size, MIME)
- [ ] Input validation and sanitization on all forms
- [ ] Output escaping for all user-generated content
- [ ] HTTPS enabled with valid SSL certificate
- [ ] Security headers configured
- [ ] Database user has minimal required permissions
- [ ] Admin panel protected additional authentication
- [ ] Regular security updates for PHP and dependencies
- [ ] Database backups automated and encrypted
- [ ] Error messages don't expose system details
- [ ] Rate limiting on login and uploads
- [ ] CSRF tokens on all state-changing forms

---

## Support & Additional Resources

- [OWASP Security Guidelines](https://owasp.org/)
- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)
- [MySQL Security](https://dev.mysql.com/doc/refman/8.0/en/security.html)
- [Your Hosting Provider's Documentation]

## Questions?

Review the README.md for project overview and the code comments for implementation details.
