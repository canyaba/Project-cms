# Security & Hardening Guide

## ProjectCMS Security Best Practices

This guide covers security hardening and best practices for ProjectCMS in production.

---

## 1. Secure Environment Setup

### 1.1 Environment Variables

Never hardcode sensitive data. Use environment variables instead.

**✓ Good:**
```php
$password = getenv('DB_PASSWORD') ?? $_ENV['DB_PASSWORD'];
```

**✗ Bad:**
```php
$password = 'gorgonzola7!'; // Hardcoded credentials
```

### 1.2 .env File Management

- [ ] Store `.env` outside web root or protect with .htaccess
- [ ] Add to `.gitignore` - never commit
- [ ] Use strong, unique passwords
- [ ] Change default credentials immediately
- [ ] Rotate credentials periodically (quarterly minimum)

### 1.3 File & Directory Permissions

```bash
# Correct permissions
chmod 755 ./uploads      # Directory - readable/executable
chmod 755 ./logs         # Directory - readable/executable
chmod 755 ./sessions     # Directory - readable/executable
chmod 644 *.php          # Files - readable only
chmod 644 .env           # Config - readable only
chmod 600 .env           # More restrictive on sensitive server
```

---

## 2. Database Security

### 2.1 SQL Injection Prevention

**✓ Always use prepared statements:**
```php
$stmt = $db->prepare('SELECT * FROM users WHERE id = ? AND status = ?');
$stmt->execute([$userId, 'active']);
```

**✗ Never concatenate SQL:**
```php
$query = "SELECT * FROM users WHERE id = $userId"; // VULNERABLE
```

### 2.2 Database User Permissions

Create dedicated database user with minimal permissions:

```sql
-- Create application-specific user
CREATE USER 'app_user'@'localhost' IDENTIFIED BY 'strong_password';

-- Grant only necessary permissions
GRANT SELECT, INSERT, UPDATE, DELETE ON projectcms.* TO 'app_user'@'localhost';

-- NOT recommended (too permissive):
-- GRANT ALL PRIVILEGES ON projectcms.* TO 'app_user'@'localhost';

-- Flush privileges
FLUSH PRIVILEGES;
```

### 2.3 Connection Security

```
# In .env for production
DB_HOST=127.0.0.1  # Use IP, not localhost
DB_SSL=true        # Use SSL for connections
```

---

## 3. Authentication & Sessions

### 3.1 Password Security

**Use bcrypt with proper cost:**
```php
// Hashing (registration/password change)
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

// Verification (login)
if (password_verify($userInput, $hash)) {
    // Correct password
}
```

**Enforce password requirements:**
- Minimum 10 characters
- Mix of uppercase, lowercase, numbers, special characters
- No dictionary words

### 3.2 Session Security

```php
// In production-config.php / auth.php
session_set_cookie_params([
    'lifetime' => 3600,           // 1 hour
    'path' => '/',
    'secure' => true,             // HTTPS only
    'httponly' => true,           // No JavaScript access
    'samesite' => 'Lax',          // CSRF protection
]);

// Always regenerate ID on login
session_regenerate_id(true);

// Logout properly
session_destroy();
```

### 3.3 Rate Limiting

Limit login attempts to prevent brute-force attacks:

```php
// Track login attempts
$attempts = 0;
if (isset($_SESSION['login_attempts'])) {
    $attempts = $_SESSION['login_attempts'];
}

if ($attempts >= 5) {
    // Block for 15 minutes
    $lockTime = $_SESSION['login_locktime'] ?? 0;
    if (time() - $lockTime < 900) {
        die('Too many login attempts. Try again later.');
    }
}

// Increment on failed login
$_SESSION['login_attempts']++;
```

---

## 4. Input Validation & Output Encoding

### 4.1 Input Validation

Always validate on server-side:

```php
// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    throw new Exception('Invalid email');
}

// Validate integer
$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
if ($id === false) {
    throw new Exception('Invalid ID');
}

// Sanitize string
$name = filter_var($name, FILTER_SANITIZE_STRING);
```

### 4.2 Output Encoding

**Escape HTML output:**
```php
// ✓ Safe
<h1><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></h1>

// ✗ Vulnerable to XSS
<h1><?php echo $name; ?></h1>
```

**Use the helper function:**
```php
// In production-config.php
function hs($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Usage
<h1><?php echo hs($name); ?></h1>
```

### 4.3 CSRF Protection

**Use CSRF tokens on all forms:**
```php
// Generate token
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// In form
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <!-- form fields -->
</form>

// Verify on submission
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    die('CSRF token validation failed');
}
```

---

## 5. File Upload Security

### 5.1 Validate Uploads

```php
// Whitelist allowed types
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$maxSize = 5 * 1024 * 1024; // 5MB

// Check MIME type
if (!in_array($_FILES['image']['type'], $allowedTypes)) {
    die('Invalid file type');
}

// Check size
if ($_FILES['image']['size'] > $maxSize) {
    die('File too large');
}

// Verify actual MIME type (not just extension)
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, $allowedTypes)) {
    die('File content does not match file type');
}
```

### 5.2 Store Outside Web Root

```php
// Store in non-web-accessible directory
define('UPLOAD_DIR', '/var/data/uploads/'); // Outside HTTP root

// Generate random filename to prevent conflicts
$filename = bin2hex(random_bytes(16)) . '.jpg';
$filepath = UPLOAD_DIR . $filename;

move_uploaded_file($_FILES['image']['tmp_name'], $filepath);
```

### 5.3 Prevent Execution

Use `.htaccess` in uploads directory:
```apache
<FilesMatch "\.php$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

php_flag engine off
```

---

## 6. HTTPS & SSL/TLS

### 6.1 Enable HTTPS

- [ ] Obtain SSL certificate (Let's Encrypt is free)
- [ ] Configure web server for HTTPS
- [ ] Redirect HTTP to HTTPS

**Apache:**
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>
```

**Nginx:**
```nginx
server {
    listen 80;
    return 301 https://$server_name$request_uri;
}
```

### 6.2 HSTS (HTTP Strict Transport Security)

```apache
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
```

---

## 7. Security Headers

### 7.1 Content Security Policy

```php
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';");
```

### 7.2 Other Security Headers

```php
// Prevent clickjacking
header("X-Frame-Options: SAMEORIGIN");

// Prevent MIME type sniffing
header("X-Content-Type-Options: nosniff");

// Enable XSS protection
header("X-XSS-Protection: 1; mode=block");

// Referrer policy
header("Referrer-Policy: strict-origin-when-cross-origin");

// Permissions policy
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
```

---

## 8. Error Handling & Logging

### 8.1 Error Configuration

```php
// Production
ini_set('display_errors', 0);     // Don't show errors to users
ini_set('log_errors', 1);         // Log errors
ini_set('error_log', '/var/log/php-errors.log');
```

### 8.2 Log Sensitive Information

```php
error_log('User ' . $userId . ' login attempt failed');
error_log('Database query error: ' . $e->getMessage());
```

### 8.3 Monitor Logs

```bash
# Check for suspicious patterns
tail -f /var/log/php-errors.log | grep -E "SQL|injection|exploit"
```

---

## 9. Dependency Management

### 9.1 Use Composer

```json
{
    "require": {
        "php": ">=8.2"
    }
}
```

### 9.2 Keep Dependencies Updated

```bash
# Check for updates
composer outdated

# Update dependencies
composer update

# Security vulnerabilities
composer audit
```

---

## 10. Regular Security Maintenance

### 10.1 Daily
- [ ] Review error logs for suspicious activity
- [ ] Check for failed login attempts
- [ ] Monitor disk usage

### 10.2 Weekly
- [ ] Review access logs for anomalies
- [ ] Check for new security vulnerabilities
- [ ] Test backup restoration

### 10.3 Monthly
- [ ] Update PHP and operating system
- [ ] Review and rotate credentials (quarterly)
- [ ] Run security audit: `php security-audit.php`
- [ ] Database optimization: `OPTIMIZE TABLE`

### 10.4 Quarterly
- [ ] Full security assessment
- [ ] Penetration testing (hire professional)
- [ ] Update security policies
- [ ] Rotate database passwords

---

## 11. Incident Response

### 11.1 Detect Compromise

Signs of compromise:
- Unauthorized admin accounts
- Unexpected file modifications
- Database changes
- Email sending to external addresses
- Performance degradation

### 11.2 Respond to Breach

1. **Isolate** - Take system offline
2. **Assess** - Determine what happened
3. **Contain** - Prevent further damage
4. **Eradicate** - Remove malware
5. **Recover** - Restore from clean backup
6. **Document** - Record incident details
7. **Improve** - Prevent recurrence

---

## 12. Security Tools

### Use These Tools

- **SSL Labs** - https://www.ssllabs.com/ssltest/ - Test HTTPS config
- **OWASP ZAP** - Free penetration testing tool
- **Burp Suite Community** - Web vulnerability scanner
- **Composer Audit** - Check for known vulnerabilities
- **SNYK** - Dependency vulnerability database

---

## Security Checklist

- [ ] No hardcoded passwords in code
- [ ] .env file in .gitignore
- [ ] HTTPS enabled
- [ ] SQL injection prevention (prepared statements)
- [ ] XSS prevention (output encoding)
- [ ] CSRF tokens implemented
- [ ] Sessions configured securely
- [ ] Passwords hashed with bcrypt
- [ ] File uploads validated
- [ ] Sensitive files protected from web access
- [ ] Error logging configured
- [ ] Rate limiting implemented
- [ ] Security headers configured
- [ ] Backups automated
- [ ] Monitoring configured

---

## Resources

- OWASP Top 10: https://owasp.org/www-project-top-ten/
- PHP Security: https://www.php.net/manual/en/security.php
- Web Security: https://portswigger.net/web-security
- Security Headers: https://securityheaders.com/

---

For more information, see:
- DEPLOYMENT.md - Deployment instructions
- DEPLOYMENT_CHECKLIST.md - Pre-deployment verification
- security-audit.php - Run security checks
