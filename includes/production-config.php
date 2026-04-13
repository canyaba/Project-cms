<?php
/**
 * Production Security Configuration
 *
 * This file contains security settings and best practices for production deployment
 * Apply these PHP configuration directives to your php.ini or .htaccess
 */

// ============================================================================
// ERROR HANDLING & LOGGING
// ============================================================================

// Don't display errors to users (prevents information disclosure)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

// Log errors to file instead
ini_set('log_errors', 1);
ini_set('error_log', dirname(__DIR__) . '/logs/php-errors.log');

// Production environment
define('APP_ENV', 'production');
define('APP_DEBUG', false);

// ============================================================================
// SESSION SECURITY
// ============================================================================

// Session configuration for security
ini_set('session.name', 'PROJECTCMS');
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // HTTPS only
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', 3600); // 1 hour

// ============================================================================
// INPUT & DATA HANDLING
// ============================================================================

// Disable magic quotes if still enabled (legacy)
ini_set('magic_quotes_gpc', 0);

// Expose PHP version? No, hide it
header_remove('X-Powered-By');

// ============================================================================
// FILE UPLOAD SECURITY
// ============================================================================

// Maximum upload size (5 MB)
ini_set('upload_max_filesize', '5M');
ini_set('post_max_size', '5M');

// Disable file uploads to web-accessible directories
define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/');
define('MAX_FILE_SIZE', 5242880); // 5 MB in bytes

// ============================================================================
// SECURITY HEADERS
// ============================================================================

// Content Security Policy - Restrict resource loading
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; font-src 'self' data:;");

// Prevent clickjacking
header("X-Frame-Options: SAMEORIGIN");

// Prevent MIME type sniffing
header("X-Content-Type-Options: nosniff");

// Enable XSS protection
header("X-XSS-Protection: 1; mode=block");

// Referrer policy
header("Referrer-Policy: strict-origin-when-cross-origin");

// ============================================================================
// SECURITY CONFIGURATION
// ============================================================================

// Prevent SQL Injection - Always use prepared statements (enforced in connect.php)
define('USE_PREPARED_STATEMENTS', true);

// CSRF token configuration
define('CSRF_TOKEN_LENGTH', 32);
define('CSRF_TOKEN_NAME', '_csrf_token');

// Password configuration
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_HASH_OPTIONS', [
    'cost' => 12,
]);

// ============================================================================
// DATABASE CONFIGURATION
// ============================================================================

// These are loaded from environment variables via config.php
// Never hardcode database credentials in code

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Generate CSRF token
 */
function generateCsrfToken(): string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
    }

    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verify CSRF token
 */
function verifyCsrfToken(string $token): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    return hash_equals($_SESSION[CSRF_TOKEN_NAME] ?? '', $token);
}

/**
 * Hash password securely
 */
function hashPassword(string $password): string {
    return password_hash($password, PASSWORD_HASH_ALGO, PASSWORD_HASH_OPTIONS);
}

/**
 * Verify password
 */
function verifyPassword(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

/**
 * Sanitize output for HTML context
 */
function hs(mixed $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize output for JavaScript context
 */
function jsEscape(mixed $value): string {
    return json_encode($value);
}

// ============================================================================
// DIRECTORY SETUP
// ============================================================================

// Create required directories if they don't exist
$directories = [
    dirname(__DIR__) . '/logs',
    dirname(__DIR__) . '/uploads',
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// ============================================================================
// ERROR HANDLING
// ============================================================================

// Set up error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (APP_DEBUG) {
        echo "Error [$errno]: $errstr in $errfile on line $errline\n";
    } else {
        error_log("[$errno] $errstr in $errfile on line $errline");
    }
    return true;
});

// Set up exception handler
set_exception_handler(function($exception) {
    if (APP_DEBUG) {
        echo '<pre>' . $exception . '</pre>';
    } else {
        error_log('Exception: ' . $exception->getMessage());
        http_response_code(500);
        die('An error occurred. Please try again later.');
    }
});
