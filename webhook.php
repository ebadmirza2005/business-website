<?php
require_once 'db.php';
require_once 'vendor/autoload.php';
require_once 'smtp_config.php';

\Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY'] ?? 'sk_test_your_secret_key');

header('Content-Type: application/json');

try {
    // Verify webhook signature
    $endpoint_secret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '';
    $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
    $body = file_get_contents('php://input');

    if (!$endpoint_secret) {
        throw new Exception('Webhook secret not configured');
    }

    $event = \Stripe\Webhook::constructEvent($body, $sig_header, $endpoint_secret);

    // Database connection
    $conn = new mysqli(
        $_ENV['DB_HOST'] ?? 'localhost',
        $_ENV['DB_USER'] ?? 'root',
        $_ENV['DB_PASS'] ?? '',
        $_ENV['DB_NAME'] ?? 'business_website'
    );

    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    switch ($event->type) {
        case 'checkout.session.completed':
            $session = $event->data->object;
            
            // Update order status to completed
            $stmt = $conn->prepare("UPDATE orders SET status = 'completed', payment_intent = ? WHERE session_id = ?");
            if ($stmt) {
                $stmt->bind_param('ss', $session->payment_intent, $session->id);
                $stmt->execute();
                $stmt->close();
            }

            // Get order details
            $stmt = $conn->prepare("SELECT email, package_name, package_type, amount FROM orders WHERE session_id = ?");
            if ($stmt) {
                $stmt->bind_param('s', $session->id);
                $stmt->execute();
                $result = $stmt->get_result();
                $order = $result->fetch_assoc();
                $stmt->close();

                if ($order) {
                    sendPaymentSuccessEmail($order);
                }
            }
            break;

        case 'charge.failed':
            $charge = $event->data->object;
            
            // Update order status to failed
            $stmt = $conn->prepare("UPDATE orders SET status = 'failed', error_message = ? WHERE session_id = ?");
            if ($stmt) {
                $error_msg = $charge->failure_message ?? 'Payment declined';
                $stmt->bind_param('ss', $error_msg, $charge->metadata->session_id ?? '');
                $stmt->execute();
                $stmt->close();
            }
            break;

        case 'payment_intent.payment_failed':
            $intent = $event->data->object;
            
            // Update order status to failed
            $error_msg = $intent->last_payment_error->message ?? 'Payment failed';
            $stmt = $conn->prepare("UPDATE orders SET status = 'failed', error_message = ? WHERE payment_intent = ?");
            if ($stmt) {
                $stmt->bind_param('ss', $error_msg, $intent->id);
                $stmt->execute();
                $stmt->close();
            }
            break;
    }

    $conn->close();

    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Webhook processed']);

} catch (\Stripe\Exception\SignatureVerificationException $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid signature']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function sendPaymentSuccessEmail($order) {
    try {
        $mail = getMailer();
        $mail->addAddress($order['email']);
        $mail->Subject = 'Payment Successful - Faaz Pro Tech';
        $mail->isHTML(true);
        
        $amount = number_format($order['amount'], 2);
        $packageType = ucfirst(str_replace('-', ' ', $order['package_type']));
        
        $mail->Body = "
        <html>
            <body style='font-family: Arial, sans-serif; color: #333;'>
                <h2 style='color: #21c6ff;'>Payment Successful!</h2>
                <p>Thank you for choosing Faaz Pro Tech.</p>
                
                <h3>Order Details:</h3>
                <table style='border-collapse: collapse; width: 100%;'>
                    <tr style='background: #f5f5f5;'>
                        <td style='padding: 10px; border: 1px solid #ddd;'><strong>Package:</strong></td>
                        <td style='padding: 10px; border: 1px solid #ddd;'>{$order['package_name']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px; border: 1px solid #ddd;'><strong>Service:</strong></td>
                        <td style='padding: 10px; border: 1px solid #ddd;'>{$packageType}</td>
                    </tr>
                    <tr style='background: #f5f5f5;'>
                        <td style='padding: 10px; border: 1px solid #ddd;'><strong>Amount:</strong></td>
                        <td style='padding: 10px; border: 1px solid #ddd;'>\${$amount}</td>
                    </tr>
                </table>
                
                <p style='margin-top: 20px;'>Our team will contact you shortly to discuss your project details and timeline.</p>
                <p>Best regards,<br/><strong>Faaz Pro Tech Team</strong></p>
            </body>
        </html>";
        
        $mail->send();
    } catch (Exception $e) {
        error_log('Failed to send email: ' . $e->getMessage());
    }
}
?>
