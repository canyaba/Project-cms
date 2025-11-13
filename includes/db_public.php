<?php
// Public DB connection helper.
// Keeps the setup simple by reusing the main PDO connection from connect.php.

require_once __DIR__ . '/connect.php';

if (!isset($db_public) || !($db_public instanceof PDO)) {
    $db_public = $db;
}
