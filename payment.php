<?php
// Payment Processing Handler
header('Content-Type: application/json; charset=utf-8');

$response = [
    'success' => false,
    'debug' => [],
    'message' => 'Payment processing failed'
];

try {
    // Step 1: Load environment
    $response['debug']['step1'] = 'Loading environment...';
    require_once 'config-env.php';
    $response['debug']['step1'] = 'Environment loaded OK';
    
    // Step 2: Get input
    $response['debug']['step2'] = 'Reading input...';
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    
    if (!$input) {
        throw new Exception('No valid JSON input received');
    }
    $response['debug']['step2'] = 'Input parsed OK: packageName=' . ($input['packageName'] ?? 'MISSING');
    
    // Step 3: Validate fields
    $response['debug']['step3'] = 'Validating...';
    if (empty($input['packageName']) || empty($input['amount']) || empty($input['email'])) {
        throw new Exception('Missing required fields');
    }
    $response['debug']['step3'] = 'Validation OK';
    
    // Step 4: Load Stripe
    $response['debug']['step4'] = 'Loading Stripe...';
    require_once 'vendor/autoload.php';
    $stripe_key = env('STRIPE_SECRET_KEY');
    if (!$stripe_key) {
        throw new Exception('Stripe key not found in environment');
    }
    \Stripe\Stripe::setApiKey($stripe_key);
    $response['debug']['step4'] = 'Stripe loaded OK';
    
    // Step 5: Parse data
    $response['debug']['step5'] = 'Parsing data...';
    $packageName = htmlspecialchars($input['packageName']);
    $amount = intval($input['amount']);
    $email = filter_var($input['email'], FILTER_VALIDATE_EMAIL);
    $packageType = htmlspecialchars($input['packageType'] ?? 'unknown');
    
    if (!$email) {
        throw new Exception('Invalid email: ' . $input['email']);
    }
    $response['debug']['step5'] = "Data: name=$packageName, amount=$amount, email=$email";
    
    // Step 6: Create Stripe session
    $response['debug']['step6'] = 'Creating Stripe session...';
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'usd',
                'product_data' => ['name' => $packageName],
                'unit_amount' => $amount,
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => 'http://localhost/payment-success.html?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => 'http://localhost/index.php#packages',
        'customer_email' => $email,
    ]);
    $response['debug']['step6'] = 'Stripe session created: ' . $session->id;
    
    // Step 7: Save to database
    $response['debug']['step7'] = 'Saving to database...';
    $conn = new mysqli(
        env('DB_HOST', 'localhost'),
        env('DB_USER', 'root'),
        env('DB_PASS', ''),
        env('DB_NAME', 'business_website')
    );
    
    if ($conn->connect_error) {
        throw new Exception('DB connection error: ' . $conn->connect_error);
    }
    
    $stmt = $conn->prepare("INSERT INTO orders (email, package_name, package_type, amount, session_id, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    if (!$stmt) {
        throw new Exception('DB prepare error: ' . $conn->error);
    }
    
    $stmt->bind_param('sssds', $email, $packageName, $packageType, $amount, $session->id);
    if (!$stmt->execute()) {
        throw new Exception('DB execute error: ' . $stmt->error);
    }
    
    $stmt->close();
    $conn->close();
    $response['debug']['step7'] = 'Database saved OK';
    
    // Success!
    $response['success'] = true;
    $response['message'] = 'Payment session created successfully';
    $response['sessionUrl'] = $session->url;
    $response['sessionId'] = $session->id;
    
} catch (\Stripe\Exception\ApiErrorException $e) {
    $response['message'] = 'Stripe error: ' . $e->getMessage();
    $response['stripeError'] = true;
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    $response['error'] = true;
}

// Always send JSON response  
http_response_code($response['success'] ? 200 : 400);
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
