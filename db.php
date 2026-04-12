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

function isJsonRequest(): bool
{
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $xrw = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
    return stripos($accept, 'application/json') !== false || strcasecmp($xrw, 'XMLHttpRequest') === 0;
}

function failDbConnection(string $message, bool $isLocal): void
{
    http_response_code(500);

    if (isJsonRequest()) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $isLocal ? $message : 'Database connection failed. Please contact support.'
        ]);
        exit;
    }

    die($isLocal ? ('ERROR: ' . $message) : 'Database connection failed. Please contact support.');
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
        failDbConnection('Database configuration is missing. Set DB_HOST, DB_USER, DB_PASS, DB_NAME.', $isLocal);
    }
}

$conn = @new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    failDbConnection($conn->connect_error, $isLocal);
}

$conn->set_charset('utf8mb4');

?>