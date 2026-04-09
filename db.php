<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function connectWithBootstrap(string $dbHost, string $dbUser, string $dbPass, string $dbName): mysqli
{
    try {
        $connection = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
        $connection->set_charset("utf8mb4");

        return $connection;
    } catch (Throwable $initialError) {
        $adminConnection = new mysqli($dbHost, $dbUser, $dbPass);
        $adminConnection->set_charset("utf8mb4");
        $escapedDbName = $adminConnection->real_escape_string($dbName);
        $adminConnection->query(
            "CREATE DATABASE IF NOT EXISTS `{$escapedDbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
        );
        $adminConnection->close();

        $connection = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
        $connection->set_charset("utf8mb4");

        return $connection;
    }
}

try {
    $dbHost = getenv("DB_HOST") ?: "localhost";
    $dbName = getenv("DB_NAME") ?: "bussiness_website";

    $candidates = [
        [
            "user" => getenv("DB_USER") ?: "root",
            "pass" => getenv("DB_PASS") ?: "",
        ],
        [
            "user" => "appuser",
            "pass" => "AppUser@123",
        ],
    ];

    $conn = null;
    $lastError = null;

    foreach ($candidates as $candidate) {
        try {
            $conn = connectWithBootstrap($dbHost, $candidate["user"], $candidate["pass"], $dbName);
            break;
        } catch (Throwable $e) {
            $lastError = $e;
        }
    }

    if (!$conn instanceof mysqli) {
        throw $lastError ?? new RuntimeException("Unable to initialize the database connection.");
    }

    $conn->query(
        "CREATE TABLE IF NOT EXISTS users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            fullname VARCHAR(100) NOT NULL,
            email VARCHAR(191) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
} catch (Throwable $e) {
    http_response_code(500);
    header("Content-Type: application/json");
    echo json_encode([
        "success" => false,
        "message" => "Database connection/setup failed",
        "error" => $e->getMessage(),
        "hint" => "Use XAMPP MySQL root with empty password, or set DB_HOST / DB_USER / DB_PASS / DB_NAME.",
    ]);
    exit;
}

?>