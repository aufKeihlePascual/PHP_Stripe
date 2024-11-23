<?php
// Start the session
session_start();

// Clear the session and destroy the cart
session_unset();  // Remove all session variables
session_destroy();  // Destroy the session

// Optionally, reset the cart array
$_SESSION['cart'] = [];

// Send a success response
echo json_encode(['status' => 'success', 'message' => 'Cart cleared']);
?>
