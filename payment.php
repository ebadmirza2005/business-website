<?php
require_once 'db.php';
require_once 'vendor/autoload.php';

\Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY'] ?? 'sk_test_your_secret_key');

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate input
    if (!$input) {
        throw new Exception('Invalid request data');
    }

    if (empty($input['packageName']) || empty($input['amount']) || empty($input['email'])) {
        throw new Exception('Missing required fields: packageName, amount, email');
    }

    $packageName = htmlspecialchars($input['packageName']);
    $amount = intval($input['amount']);
    $email = filter_var($input['email'], FILTER_VALIDATE_EMAIL);
    $packageType = htmlspecialchars($input['packageType'] ?? 'unknown');

    // Validate amount
    if ($amount <= 0 || $amount > 999999) {
        throw new Exception('Invalid amount');
    }

    // Validate email
    if (!$email) {
        throw new Exception('Invalid email address');
    }

    // Database connection
    $conn = new mysqli(
        $_ENV['DB_HOST'] ?? 'localhost',
        $_ENV['DB_USER'] ?? 'root',
        $_ENV['DB_PASS'] ?? '',
        $_ENV['DB_NAME'] ?? 'business_website'
    );

    if ($conn->connect_error) {
        throw new Exception('Database error: Unable to connect');
    }

    // Check if user already has a pending order
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE email = ? AND status = 'pending' AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    if ($stmt) {
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if ($row['count'] > 0) {
            throw new Exception('You already have a pending payment. Please complete it first.');
        }
        $stmt->close();
    }

    // Create Stripe Checkout Session with proper error handling
    try {
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $packageName,
                            'description' => ucfirst(str_replace('-', ' ', $packageType)) . ' Development Package',
                            'images' => [getenv('APP_URL') . '/assets/logo.png'] ?? [],
                        ],
                        'unit_amount' => $amount,
                    ],
                    'quantity' => 1,
                ]
            ],
            'mode' => 'payment',
            'success_url' => (getenv('APP_URL') ?? 'http://localhost') . '/payment-success.html?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => (getenv('APP_URL') ?? 'http://localhost') . '/index.php#packages',
            'customer_email' => $email,
            'payment_intent_data' => [
                'metadata' => [
                    'package_name' => $packageName,
                    'package_type' => $packageType,
                    'order_email' => $email,
                ]
            ],
            'metadata' => [
                'package_name' => $packageName,
                'package_type' => $packageType,
            ]
        ]);
    } catch (\Stripe\Exception\InvalidRequestException $e) {
        throw new Exception('Payment configuration error: ' . $e->getMessage());
    }

    // Save order to database
    $stmt = $conn->prepare("
        INSERT INTO orders (email, package_name, package_type, amount, session_id, status, created_at)
        VALUES (?, ?, ?, ?, ?, 'pending', NOW())
    ");

    if (!$stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }

    $stmt->bind_param('sssds', $email, $packageName, $packageType, $amount, $session->id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to create order: ' . $stmt->error);
    }

    $stmt->close();
    $conn->close();

    echo json_encode([
        'success' => true,
        'sessionId' => $session->id,
        'sessionUrl' => $session->url,
        'message' => 'Checkout session created successfully'
    ]);

} catch (\Stripe\Exception\ApiErrorException $e) {
    http_response_code(400);
    error_log('Stripe API Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Payment service error: ' . $e->getMessage(),
        'type' => 'stripe_error'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    error_log('Payment Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'type' => 'validation_error'
    ]);
}
?>
