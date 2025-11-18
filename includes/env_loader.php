<?php
/**
 * Simple .env file loader
 * Loads environment variables from .env file into $_ENV superglobal
 */

function loadEnv($path) {
    if (!file_exists($path)) {
        throw new Exception(".env file not found at: {$path}");
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parse line
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);

            $name = trim($name);
            $value = trim($value);

            // Remove quotes if present
            if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                $value = $matches[2];
            }

            // Set environment variable
            if (!array_key_exists($name, $_ENV)) {
                $_ENV[$name] = $value;
                putenv("{$name}={$value}");
            }
        }
    }
}

// Load .env file from project root
// Using DOCUMENT_ROOT for absolute path (works from any include location)
$envPath = $_SERVER['DOCUMENT_ROOT'] . '/.env';
loadEnv($envPath);

/**
 * Helper function to get environment variable with fallback
 */
function env($key, $default = null) {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}
?>
