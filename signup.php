<?php

ob_start();
ini_set('display_errors', '0');

register_shutdown_function(static function (): void {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (!headers_sent()) {
            header('Content-Type: application/json');
            http_response_code(500);
        }

        if (ob_get_length()) {
            ob_clean();
        }

        echo json_encode([
            'success' => false,
            'message' => 'A server error occurred. Please try again.'
        ]);
    }
});

include "db.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method"
    ]);
    exit;
}

$username = isset($_POST["username"]) ? trim($_POST["username"]) : "";
$email = isset($_POST["email"]) ? trim($_POST["email"]) : "";
$password = isset($_POST["password"]) ? $_POST["password"] : "";

if ($username === "" || $email === "" || strlen($password) < 6) {
    echo json_encode([
        "success" => false,
        "message" => "Please provide valid name, email and password"
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "success" => false,
        "message" => "Please provide a valid email address"
    ]);
    exit;
}

try {
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    if ($checkResult->num_rows > 0) {
        echo json_encode([
            "success" => false,
            "message" => "This email is already registered"
        ]);
        $checkStmt->close();
        exit;
    }
    $checkStmt->close();

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $insertStmt = $conn->prepare("INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)");
    $insertStmt->bind_param("sss", $username, $email, $hashedPassword);
    $insertStmt->execute();
    $insertStmt->close();

    echo json_encode([
        "success" => true,
        "message" => "Signup successful",
        "username" => $username
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Signup failed",
        "error" => $e->getMessage()
    ]);
}

?>