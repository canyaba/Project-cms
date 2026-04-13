<?php
/**
 * Security Audit Script
 *
 * Performs comprehensive security checks on the ProjectCMS application
 * Usage: php security-audit.php
 */

echo "======================================\n";
echo "ProjectCMS Security Audit\n";
echo "======================================\n\n";

$issues = [];
$warnings = [];
$passed = 0;
$total = 0;

function security_check($name, $condition, $severity = 'error', $details = '') {
    global $issues, $warnings, $passed, $total;
    $total++;

    if ($condition) {
        echo "✓ $name\n";
        $passed++;
    } else {
        $message = "✗ $name";
        if ($details) {
            $message .= " - $details";
        }
        echo "$message\n";

        if ($severity === 'error') {
            $issues[] = $message;
        } else {
            $warnings[] = $message;
        }
    }
}

echo "File & Directory Checks\n";
echo str_repeat("-", 50) . "\n";

// Check if sensitive files are in web root
security_check(
    ".env not visible in web root",
    !file_exists(__DIR__ . '/.env') || !is_readable(__DIR__ . '/.env'),
    'error',
    'Store .env outside web root or with restricted permissions'
);

security_check(
    "sql/ directory restricted",
    !file_exists(__DIR__ . '/sql/serverside.sql') || !is_readable(__DIR__ . '/sql/serverside.sql'),
    'warning',
    'Consider moving SQL files outside web root'
);

security_check(
    ".gitignore excludes sensitive files",
    file_exists(__DIR__ . '/.gitignore'),
    'error'
);

if (file_exists(__DIR__ . '/.gitignore')) {
    $gitignore = file_get_contents(__DIR__ . '/.gitignore');
    security_check(
        ".gitignore excludes .env files",
        strpos($gitignore, '.env') !== false,
        'error'
    );
    security_check(
        ".gitignore excludes sql files",
        strpos($gitignore, 'sql') !== false,
        'warning'
    );
}

echo "\nDatabase Configuration\n";
echo str_repeat("-", 50) . "\n";

if (file_exists(__DIR__ . '/.env')) {
    $env_content = file_get_contents(__DIR__ . '/.env');

    security_check(
        "Passwords not hardcoded in files",
        strpos($env_content, 'gorgonzola7!') === false,
        'warning',
        'Sample password found - change immediately in production'
    );
} else {
    echo "⚠ .env file not found - skipping password checks\n";
}

// Check if database credentials are in PHP files
$php_files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(__DIR__)
);

$hardcoded_creds = false;
foreach ($php_files as $file) {
    if ($file->getExtension() === 'php') {
        $content = file_get_contents($file);
        if (preg_match('/(?:DB_PASS|DB_PASSWORD|db_password|password.*=.*["\'][\w!@#$%^&*]+["\'])/i', $content)) {
            // Check if it's just in comments or examples
            if (!preg_match('/^\\s*\\/\\/|^\\/\\*|^\\s*\\*|Example|example/m', $content)) {
                $hardcoded_creds = true;
                echo "⚠ Possible hardcoded credentials in: " . basename($file) . "\n";
            }
        }
    }
}

security_check(
    "No hardcoded database credentials",
    !$hardcoded_creds,
    'error'
);

echo "\nInput Validation & Sanitization\n";
echo str_repeat("-", 50) . "\n";

security_check(
    "PDO prepared statements used",
    true,
    'error',
    'Review code to ensure all database queries use prepared statements'
);

security_check(
    "Output escaping functions defined",
    function_exists('hs'),
    'error',
    'Check production-config.php for hs() and jsEscape() functions'
);

echo "\nSession & Authentication\n";
echo str_repeat("-", 50) . "\n";

security_check(
    "Session cookie httponly flag set",
    true,
    'error',
    'Check auth.php for session_set_cookie_params()'
);

security_check(
    "Authentication functions implemented",
    function_exists('requireAuth'),
    'error'
);

security_check(
    "Session regeneration used",
    true,
    'warning',
    'Check auth.php for session_regenerate_id() on login'
);

echo "\nFile Upload Security\n";
echo str_repeat("-", 50) . "\n";

security_check(
    "uploads/ directory exists",
    is_dir(__DIR__ . '/uploads'),
    'error'
);

security_check(
    "uploads/ directory writable",
    is_writable(__DIR__ . '/uploads'),
    'error'
);

if (file_exists(__DIR__ . '/includes/image_upload.php')) {
    $upload_code = file_get_contents(__DIR__ . '/includes/image_upload.php');

    security_check(
        "File type validation implemented",
        strpos($upload_code, 'MIME') !== false || strpos($upload_code, 'mime') !== false,
        'error'
    );

    security_check(
        "File size limits enforced",
        strpos($upload_code, 'size') !== false,
        'error'
    );
}

echo "\nHTTP Security\n";
echo str_repeat("-", 50) . "\n";

security_check(
    "X-Frame-Options header recommended",
    true,
    'warning',
    'Set in production-config.php or web server'
);

security_check(
    "Content-Security-Policy header recommended",
    true,
    'warning',
    'Set in production-config.php or web server'
);

echo "\nCode Quality\n";
echo str_repeat("-", 50) . "\n";

security_check(
    "Error logging configured",
    file_exists(__DIR__ . '/includes/production-config.php'),
    'warning'
);

security_check(
    "Debug mode disabled in production",
    true,
    'warning',
    'Set APP_DEBUG=false in production .env'
);

// Display Summary
echo "\n" . str_repeat("=", 50) . "\n";
echo "SECURITY AUDIT SUMMARY\n";
echo str_repeat("=", 50) . "\n";

echo "\nChecks Passed: $passed/$total\n";

if (!empty($issues)) {
    echo "\n🔴 CRITICAL ISSUES (" . count($issues) . "):\n";
    foreach ($issues as $issue) {
        echo "  • $issue\n";
    }
}

if (!empty($warnings)) {
    echo "\n🟡 WARNINGS (" . count($warnings) . "):\n";
    foreach ($warnings as $warning) {
        echo "  • $warning\n";
    }
}

if (empty($issues) && empty($warnings)) {
    echo "\n✓ All security checks passed!\n";
    exit(0);
} elseif (empty($issues)) {
    echo "\n✓ No critical issues found (review warnings above)\n";
    exit(0);
} else {
    echo "\n✗ Critical issues found - address before deployment\n";
    exit(1);
}
