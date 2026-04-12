<?php
require_once 'config-env.php';
require_once 'db.php';
require_once 'vendor/autoload.php';

\Stripe\Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || empty($input['sessionId'])) {
        throw new Exception('Missing session ID');
    }

    $sessionId = htmlspecialchars($input['sessionId']);

    // Verify session ID format
    if (!preg_match('/^cs_test_|^cs_live_/', $sessionId)) {
        throw new Exception('Invalid session ID format');
    }

    // Get from database first
    $conn = new mysqli(
        $_ENV['DB_HOST'] ?? 'localhost',
        $_ENV['DB_USER'] ?? 'root',
        $_ENV['DB_PASS'] ?? '',
        $_ENV['DB_NAME'] ?? 'business_website'
    );

    if ($conn->connect_error) {
        throw new Exception('Database error');
    }

    $stmt = $conn->prepare("SELECT * FROM orders WHERE session_id = ?");
    if (!$stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }

    $stmt->bind_param('s', $sessionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();

    if (!$order) {
        throw new Exception('Order not found');
    }

    // Retrieve from Stripe to confirm
    try {
        $session = \Stripe\Checkout\Session::retrieve($sessionId);
    } catch (\Stripe\Exception\ApiErrorException $e) {
        throw new Exception('Unable to verify payment: ' . $e->getMessage());
    }

    // Verify payment status
    $isPaid = $session->payment_status === 'paid';

    // Update order status if payment is confirmed
    if ($isPaid && $order['status'] !== 'completed') {
        $updateStmt = $conn->prepare("UPDATE orders SET status = 'completed', payment_intent = ? WHERE session_id = ?");
        if ($updateStmt) {
            $payment_intent = $session->payment_intent ?? '';
            $updateStmt->bind_param('ss', $payment_intent, $sessionId);
            $updateStmt->execute();
            $updateStmt->close();
        }
    }

    $conn->close();

    // Determine status message
    $statusMessage = '';
    switch ($session->payment_status) {
        case 'paid':
            $statusMessage = 'Payment completed successfully';
            break;
        case 'unpaid':
            $statusMessage = 'Payment is pending';
            break;
        case 'no_payment_required':
            $statusMessage = 'No payment required';
            break;
        default:
            $statusMessage = 'Unknown payment status';
    }

    echo json_encode([
        'success' => true,
        'status' => $session->payment_status,
        'isPaid' => $isPaid,
        'statusMessage' => $statusMessage,
        'customer_email' => $session->customer_email,
        'amount' => $session->amount_total,
        'payment_intent' => $session->payment_intent,
        'orderStatus' => $order['status'],
        'packageName' => $order['package_name'],
        'packageType' => $order['package_type'],
    ]);

} catch (\Stripe\Exception\ApiErrorException $e) {
    http_response_code(400);
    error_log('Stripe Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Payment verification failed',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(400);
    error_log('Verification Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
