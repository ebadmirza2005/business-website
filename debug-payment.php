<?php
header('Content-Type: application/json');

$debug = [
    'timestamp' => date('Y-m-d H:i:s'),
    'request_method' => $_SERVER['REQUEST_METHOD'],
    'php_version' => phpversion(),
    'files_exist' => [
        'config-env.php' => file_exists('config-env.php'),
        '.env' => file_exists('.env'),
        'vendor/autoload.php' => file_exists('vendor/autoload.php'),
    ],
];

try {
    // Test loading config-env.php
    require_once 'config-env.php';
    $debug['config_loaded'] = true;
    $debug['env_vars'] = [
        'STRIPE_SECRET_KEY' => substr(env('STRIPE_SECRET_KEY', 'NOT SET'), 0, 10) . '...',
        'DB_HOST' => env('DB_HOST', 'NOT SET'),
        'DB_USER' => env('DB_USER', 'NOT SET'),
    ];
} catch (Throwable $e) {
    $debug['config_loaded'] = false;
    $debug['config_error'] = $e->getMessage();
}

echo json_encode($debug, JSON_PRETTY_PRINT);
?>
