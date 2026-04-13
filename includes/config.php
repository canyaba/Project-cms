<?php
/**
 * Environment Configuration Loader
 *
 * Safely loads .env file or uses environment variables set by the hosting platform
 * This approach supports both local development and cloud deployment platforms
 */

class ConfigLoader
{
    private static array $config = [];
    private static bool $loaded = false;

    /**
     * Load environment configuration
     */
    public static function load(): void
    {
        if (self::$loaded) {
            return;
        }

        self::loadEnvFile();
        self::$loaded = true;
    }

    /**
     * Load .env file if it exists
     */
    private static function loadEnvFile(): void
    {
        $envFile = dirname(__DIR__) . '/.env';

        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $line) {
                // Skip comments
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }

                // Parse KEY=VALUE
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value, " \t\"'");

                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }
    }

    /**
     * Get configuration value with fallback
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::load();
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }

    /**
     * Get all configuration
     */
    public static function all(): array
    {
        self::load();
        return $_ENV;
    }

    /**
     * Check if in production environment
     */
    public static function isProduction(): bool
    {
        return strtolower(self::get('APP_ENV', 'development')) === 'production';
    }

    /**
     * Check if debug mode is enabled
     */
    public static function isDebug(): bool
    {
        $debug = self::get('APP_DEBUG', 'false');
        return strtolower($debug) === 'true';
    }
}

// Load configuration on include
ConfigLoader::load();
