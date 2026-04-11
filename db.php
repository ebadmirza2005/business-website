<?php

function getEnvValue(string $key, array $envFileData): string
{
    $value = getenv($key);
    if ($value !== false && $value !== '') {
        return $value;
    }

    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return (string) $_ENV[$key];
    }

    if (isset($envFileData[$key]) && $envFileData[$key] !== '') {
        return (string) $envFileData[$key];
    }

    return '';
}

$serverName = $_SERVER['SERVER_NAME'] ?? '';
$httpHost = $_SERVER['HTTP_HOST'] ?? '';
$isLocal = in_array($serverName, ['localhost', '127.0.0.1'], true)
    || in_array($httpHost, ['localhost', '127.0.0.1'], true);

$envFilePath = __DIR__ . '/.env';
$envFileData = file_exists($envFilePath) ? (parse_ini_file($envFilePath, false, INI_SCANNER_RAW) ?: []) : [];

ini_set('display_errors', $isLocal ? '1' : '0');
error_reporting(E_ALL);

if ($isLocal) {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $db = 'bussiness_website';
} else {
    $host = getEnvValue('DB_HOST', $envFileData) ?: 'localhost';
    $user = getEnvValue('DB_USER', $envFileData);
    $pass = getEnvValue('DB_PASS', $envFileData);
    $db = getEnvValue('DB_NAME', $envFileData);

    if ($user === '' || $db === '') {
        die('Database configuration is missing. Set DB_HOST, DB_USER, DB_PASS, DB_NAME.');
    }
}

$conn = @new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    if ($isLocal) {
        die('ERROR: ' . $conn->connect_error);
    }
    die('Database connection failed. Please contact support.');
}

$conn->set_charset('utf8mb4');

?>