<?php
// Database configuration for the CMS
// Edit these values once and they will be used by both admin and public DB helpers.

// Primary (write-capable) DB credentials
define('DB_DSN', 'mysql:host=localhost;dbname=serverside;charset=utf8');
define('DB_USER', 'serveruser');
define('DB_PASS', 'gorgonzola7!');

// Optional read-only credentials for public pages. If not set, the public helper will
// fall back to the primary credentials.
// define('DB_RO_USER', 'webreader');
// define('DB_RO_PASS', 'read_password');
// define('DB_RO_DSN', 'mysql:host=localhost;dbname=serverside;charset=utf8');

// PDO options used by connections
if (!defined('DB_PDO_OPTIONS')) {
    define('DB_PDO_OPTIONS', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

?>
