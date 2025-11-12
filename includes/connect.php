<?php
// Admin / write-capable DB connection
// Central config is in includes/config.php
require_once __DIR__ . '/config.php';

try {
    $db = new PDO(DB_DSN, DB_USER, DB_PASS, DB_PDO_OPTIONS);
} catch (PDOException $e) {
    // Log and show friendly message
    error_log('DB connect failed: ' . $e->getMessage());
    // In development you may want to display the message; in production show a 503.
    header('HTTP/1.1 503 Service Unavailable');
    echo 'Service temporarily unavailable. Please try again later.';
    exit();
}
?>