<?php
require 'init.php';

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$endpoint_secret = 'your_webhook_secret'; // Replace with your webhook secret

try {
    $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);

    if ($event->type === 'payment_intent.succeeded') {
        $paymentIntent = $event->data->object;
        // Handle successful payment (e.g., mark invoice as paid in your database)
    }

    http_response_code(200); // Acknowledge receipt of the event
} catch (\UnexpectedValueException $e) {
    // Invalid payload
    http_response_code(400);
    exit();
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    // Invalid signature
    http_response_code(400);
    exit();
}
?>
