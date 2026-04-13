<?php
/**
 * Deployment Verification Script
 *
 * Run this script to verify your environment is ready for deployment
 * Usage: php deployment-check.php
 */

echo "======================================\n";
echo "ProjectCMS Deployment Verification\n";
echo "======================================\n\n";

// Track results
$checks = [];
$passed = 0;
$total = 0;

function check_requirement($name, $condition, $details = '') {
    global $checks, $passed, $total;
    $total++;

    if ($condition) {
        $checks[] = "✓ PASS: $name" . ($details ? " ($details)" : "");
        $passed++;
    } else {
        $checks[] = "✗ FAIL: $name" . ($details ? " ($details)" : "");
    }
}

// PHP Version
check_requirement(
    "PHP Version",
    version_compare(PHP_VERSION, '8.2.0', '>='),
    PHP_VERSION
);

// Required Extensions
$required_extensions = ['pdo', 'pdo_mysql', 'json', 'hash', 'filter', 'session'];
foreach ($required_extensions as $ext) {
    check_requirement(
        "PHP Extension: $ext",
        extension_loaded($ext)
    );
}

// File Permissions
check_requirement(
    "uploads/ Directory Writable",
    is_writable(__DIR__ . '/uploads'),
    __DIR__ . '/uploads'
);

check_requirement(
    "logs/ Directory Exists",
    is_dir(__DIR__ . '/logs') || is_writable(__DIR__),
    __DIR__ . '/logs'
);

// Configuration Files
check_requirement(
    ".env.example Template Exists",
    file_exists(__DIR__ . '/.env.example')
);

check_requirement(
    "includes/config.php Exists",
    file_exists(__DIR__ . '/includes/config.php')
);

check_requirement(
    "includes/production-config.php Exists",
    file_exists(__DIR__ . '/includes/production-config.php')
);

check_requirement(
    "SQL Schema File Exists",
    file_exists(__DIR__ . '/sql/serverside.sql')
);

// Version Control
check_requirement(
    ".gitignore Configured",
    file_exists(__DIR__ . '/.gitignore')
);

// Database Connection (if .env exists)
if (file_exists(__DIR__ . '/.env')) {
    require __DIR__ . '/includes/config.php';

    $dbHost = ConfigLoader::get('DB_HOST', 'not-set');
    $dbName = ConfigLoader::get('DB_NAME', 'not-set');
    $dbUser = ConfigLoader::get('DB_USER', 'not-set');

    check_requirement(
        "Database Configuration Set",
        $dbHost !== 'not-set' && $dbName !== 'not-set' && $dbUser !== 'not-set',
        "Host: $dbHost, DB: $dbName, User: $dbUser"
    );

    // Try connection (only if MySQL driver available)
    if (extension_loaded('pdo_mysql') || extension_loaded('mysqli')) {
        try {
            require __DIR__ . '/includes/connect.php';
            if (isset($db) && $db instanceof PDO) {
                check_requirement("Database Connection", true, "Successfully connected");
            }
        } catch (Exception $e) {
            check_requirement("Database Connection", false, $e->getMessage());
        }
    } else {
        check_requirement(
            "Database Connection",
            false,
            "MySQL driver (pdo_mysql) not available - will work on production server"
        );
    }
} else {
    check_requirement(
        ".env File Must Be Created",
        false,
        "Copy from .env.example and configure"
    );
}

// Display Results
echo "\nChecks Results:\n";
echo str_repeat("-", 50) . "\n";
foreach ($checks as $check) {
    echo $check . "\n";
}

echo "\n" . str_repeat("-", 50) . "\n";
echo "Summary: $passed/$total checks passed\n";

if ($passed === $total) {
    echo "\n✓ All checks passed! Ready for deployment.\n";
    exit(0);
} else {
    echo "\n✗ Some checks failed. Please review above.\n";
    exit(1);
}
