<?php
// Start the session
session_start();

// Get the cart data from the POST request
$cart = json_decode(file_get_contents('php://input'), true);

// Update the session cart
$_SESSION['cart'] = $cart;

// Send a success response
echo json_encode(['status' => 'success', 'message' => 'Cart updated']);
?>
