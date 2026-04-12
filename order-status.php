<?php
require_once 'db.php';

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (empty($input['sessionId'])) {
        throw new Exception('Session ID is required');
    }

    $sessionId = htmlspecialchars($input['sessionId']);

    $conn = new mysqli(
        $_ENV['DB_HOST'] ?? 'localhost',
        $_ENV['DB_USER'] ?? 'root',
        $_ENV['DB_PASS'] ?? '',
        $_ENV['DB_NAME'] ?? 'business_website'
    );

    if ($conn->connect_error) {
        throw new Exception('Database error');
    }

    $stmt = $conn->prepare("SELECT id, email, package_name, package_type, amount, status, error_message, created_at FROM orders WHERE session_id = ? LIMIT 1");
    if (!$stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }

    $stmt->bind_param('s', $sessionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();
    $conn->close();

    if (!$order) {
        throw new Exception('Order not found');
    }

    echo json_encode([
        'success' => true,
        'order' => $order
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
