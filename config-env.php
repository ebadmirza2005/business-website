<?php

/**
 * Load environment variables from .env file
 * This file should be included at the beginning of your application
 */

function loadEnv($filePath = __DIR__ . '/.env')
{
    if (!file_exists($filePath)) {
        // If .env doesn't exist locally, try to use values already in environment
        return false;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // Skip comments and empty lines
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }

        // Parse KEY=VALUE
        if (strpos($line, '=') === false) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, '\'"');

        // Set environment variable
        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }

    return true;
}

// Load .env file (in development)
loadEnv();

/**
 * Helper function to get environment variables safely
 */
function env($key, $default = null)
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    
    if ($value === false) {
        return $default;
    }
    
    return $value;
}

// Example usage in other files:
/*
require_once __DIR__ . '/config-env.php';

$dbHost = env('DB_HOST', 'localhost');
*/
