<?php
// Configuration file - loads from .env
require_once 'config-env.php';
require_once 'vendor/autoload.php';

// Get Stripe public key from environment
$stripePublicKey = env('STRIPE_PUBLIC_KEY', 'pk_test_your_public_key');

header('Content-Type: application/json');
echo json_encode([
    'stripePublicKey' => $stripePublicKey
]);
?>
