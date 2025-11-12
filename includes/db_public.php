<?php
// Public DB connection helper
// Purpose: allow public (non-logged-in) pages to use a read-only DB user when available.
// Usage: include 'includes/db_public.php'; then use $db_public for queries.

// Load central config (does not create a DB connection)
require_once __DIR__ . '/config.php';

// Determine which credentials to use for public pages. Prefer read-only constants when defined.
$user = defined('DB_RO_USER') ? DB_RO_USER : DB_USER;
$pass = defined('DB_RO_PASS') ? DB_RO_PASS : DB_PASS;
$dsn  = defined('DB_RO_DSN') ? DB_RO_DSN : DB_DSN;

// Create a separate PDO instance for public use so we don't accidentally reuse an admin connection.
try {
    $db_public = new PDO($dsn, $user, $pass, DB_PDO_OPTIONS);
} catch (PDOException $e) {
    // If the read-only connection fails, log the error and try to fall back to the primary admin connection
    // if it's available via includes/connect.php. We don't require connect.php here to keep separation;
    // only include it when necessary for a fallback.
    error_log('Public DB connection failed: ' . $e->getMessage());
    $fallbackDb = null;
    $connectPath = __DIR__ . '/connect.php';
    if (file_exists($connectPath)) {
        // include connect.php which will create $db or exit on failure
        require_once $connectPath;
        if (isset($db) && $db instanceof PDO) {
            $fallbackDb = $db;
        }
    }

    if ($fallbackDb) {
        $db_public = $fallbackDb;
    } else {
        header('HTTP/1.1 503 Service Unavailable');
        echo 'Service temporarily unavailable. Please try again later.';
        exit();
    }
}
