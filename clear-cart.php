<?php
session_start();

session_unset();  
session_destroy();  

$_SESSION['cart'] = [];

echo json_encode(['status' => 'success', 'message' => 'Cart cleared']);
?>
