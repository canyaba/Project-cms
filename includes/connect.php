<?php
/**
 * Database Connection Module
 *
 * Establishes PDO connection to MySQL database using environment-based configuration
 * Supports both local development and production deployments
 */

require_once __DIR__ . '/config.php';

// Get database configuration from environment variables
$dbHost = \ConfigLoader::get('DB_HOST', '127.0.0.1');
$dbPort = \ConfigLoader::get('DB_PORT', 3306);
$dbName = \ConfigLoader::get('DB_NAME', 'serverside');
$dbUser = \ConfigLoader::get('DB_USER', 'serveuser');
$dbPass = \ConfigLoader::get('DB_PASSWORD', 'password');
$dbCharset = \ConfigLoader::get('DB_CHARSET', 'utf8mb4');

// Build DSN (Data Source Name)
$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset={$dbCharset}";

try {
    // Create PDO connection with production-ready settings
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false, // Use prepared statements correctly
    ];

    // Add MySQL-specific options if available
    if (defined('PDO::MYSQL_ATTR_FOUND_ROWS')) {
        $options[PDO::MYSQL_ATTR_FOUND_ROWS] = true;
    }

    $db = new PDO($dsn, $dbUser, $dbPass, $options);

    // Set session SQL mode for consistency
    $db->exec("SET SESSION sql_mode='STRICT_TRANS_TABLES'");

} catch (PDOException $e) {
    // Handle connection errors gracefully
    if (\ConfigLoader::isProduction()) {
        // In production, don't expose database details
        error_log('Database connection failed: ' . $e->getMessage());
        http_response_code(503);
        die('Service temporarily unavailable. Please try again later.');
    } else {
        // In development, show the error for debugging
        echo '<div style="background:#fee; padding:20px; border:1px solid #f00; margin:20px;">';
        echo '<h2>Database Connection Error</h2>';
        echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
        echo '<p><strong>Check your .env file configuration:</strong></p>';
        echo '<ul>';
        echo '<li>DB_HOST: ' . $dbHost . '</li>';
        echo '<li>DB_PORT: ' . $dbPort . '</li>';
        echo '<li>DB_NAME: ' . $dbName . '</li>';
        echo '<li>DB_USER: ' . $dbUser . '</li>';
        echo '</ul>';
        echo '</div>';
        die();
    }
}
?>