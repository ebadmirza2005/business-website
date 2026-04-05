<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $dbHost = getenv("DB_HOST") ?: "localhost";
    $dbUser = getenv("DB_USER") ?: "appuser";
    $dbPass = getenv("DB_PASS") ?: "AppUser@123";
    $dbName = getenv("DB_NAME") ?: "myuser";

    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    $conn->set_charset("utf8mb4");

    // Ensure users table exists so signup works on fresh setup.
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
        "hint" => "Update DB_USER/DB_PASS (env vars) or edit db.php credentials.",
    ]);
    exit;
}

?>