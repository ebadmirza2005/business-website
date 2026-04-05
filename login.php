<?php

include "db.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method"
    ]);
    exit;
}

$email = isset($_POST["email"]) ? trim($_POST["email"]) : "";
$password = isset($_POST["password"]) ? $_POST["password"] : "";

if ($email === "" || $password === "") {
    echo json_encode([
        "success" => false,
        "message" => "Email and password are required"
    ]);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT fullname AS username, password FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode([
            "success" => false,
            "message" => "User not found"
        ]);
        $stmt->close();
        exit;
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    if (!password_verify($password, $user["password"])) {
        echo json_encode([
            "success" => false,
            "message" => "Wrong password"
        ]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "message" => "Login successful",
        "username" => $user["username"]
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Login failed",
        "error" => $e->getMessage()
    ]);
}

?>