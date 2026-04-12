<?php
// START: Ensure clean JSON output
ini_set('display_errors', '0');
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Create response function
function sendJSON($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

try {
    // Load environment
    if (!file_exists('config-env.php')) {
        sendJSON(['success' => false, 'message' => 'Config file not found'], 500);
    }
    require_once 'config-env.php';
    
    // Get raw input
    $rawInput = file_get_contents('php://input');
    if (empty($rawInput)) {
        sendJSON(['success' => false, 'message' => 'Empty request body'], 400);
    }
    
    $input = json_decode($rawInput, true);
    if (!is_array($input)) {
        sendJSON(['success' => false, 'message' => 'Invalid JSON: ' . $rawInput], 400);
    }

    // Validate required fields
    $packageName = $input['packageName'] ?? null;
    $amount = $input['amount'] ?? null;
    $email = $input['email'] ?? null;
    $packageType = $input['packageType'] ?? 'unknown';

    if (!$packageName || !$amount || !$email) {
        sendJSON(['success' => false, 'message' => 'Missing fields: packageName=' . ($packageName ? 'OK' : 'MISSING') . ', amount=' . ($amount ? $amount : 'MISSING') . ', email=' . ($email ? 'OK' : 'MISSING')], 400);
    }

    $packageName = htmlspecialchars($packageName);
    $amount = intval($amount);
    $email = filter_var($email, FILTER_VALIDATE_EMAIL);
    $packageType = htmlspecialchars($packageType);

    if (!$email) {
        sendJSON(['success' => false, 'message' => 'Invalid email'], 400);
    }

    if ($amount <= 0 || $amount > 999999) {
        sendJSON(['success' => false, 'message' => 'Invalid amount: ' . $amount], 400);
    }

    // Load Stripe
    if (!file_exists('vendor/autoload.php')) {
        sendJSON(['success' => false, 'message' => 'Stripe library not found'], 500);
    }
    require_once 'vendor/autoload.php';
    
    // Setup Stripe
    $stripe_key = env('STRIPE_SECRET_KEY');
    if (!$stripe_key) {
        sendJSON(['success' => false, 'message' => 'Stripe key not configured'], 500);
    }
    \Stripe\Stripe::setApiKey($stripe_key);

    // Database connection
    $conn = new mysqli(
        env('DB_HOST', 'localhost'),
        env('DB_USER', 'root'),
        env('DB_PASS', ''),
        env('DB_NAME', 'business_website')
    );

    if ($conn->connect_error) {
        sendJSON(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error], 500);
    }

    // Create Stripe session
    $stripeSession = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [
            [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $packageName,
                        'description' => ucfirst(str_replace('-', ' ', $packageType)) . ' Package',
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
    ]);

    // Save order to database
    $stmt = $conn->prepare("INSERT INTO orders (email, package_name, package_type, amount, session_id, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    if (!$stmt) {
        sendJSON(['success' => false, 'message' => 'Database error: ' . $conn->error], 500);
    }

    $stmt->bind_param('sssds', $email, $packageName, $packageType, $amount, $stripeSession->id);
    if (!$stmt->execute()) {
        sendJSON(['success' => false, 'message' => 'Failed to save order: ' . $stmt->error], 500);
    }

    $stmt->close();
    $conn->close();

    // Success response
    sendJSON([
        'success' => true,
        'sessionId' => $stripeSession->id,
        'sessionUrl' => $stripeSession->url,
        'message' => 'Checkout session created'
    ]);

} catch (\Stripe\Exception\ApiErrorException $e) {
    sendJSON(['success' => false, 'message' => 'Stripe Error: ' . $e->getMessage()], 400);
} catch (Exception $e) {
    sendJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 400);
}
