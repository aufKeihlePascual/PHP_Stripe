<?php
require 'init.php';

session_start();
global $stripe;

try {
    $products = $stripe->products->all(['limit' => 10]);

    echo '<!DOCTYPE html>';
    echo '<html lang="en">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>Product List</title>';
    echo '<link rel="stylesheet" href="products.css">';
    echo '</head>';
    echo '<body class="list-products">';

    echo '<nav class="navbar">';
    echo '<ul class="navbar-menu">';
    echo '<li class="navbar-item"><a href="create-customer.php" class="navbar-link">Register</a></li>';
    echo '<li class="navbar-item"><a href="list-products.php" class="navbar-link">Products</a></li>';
    echo '<li class="navbar-item"><a href="generate-invoice.php" class="navbar-link">Generate Invoice</a></li>';
    echo '</ul>';
    echo '</nav>';

    echo '<div class="container">';
    echo '<h1 class="page-title">Our Products</h1>';
    echo '<div class="product-list">';

    foreach ($products->data as $product) {
        $prices = $stripe->prices->all(['product' => $product->id]);
        $price = isset($prices->data[0]) ? $prices->data[0]->unit_amount / 100 : 'N/A'; 

        echo '<div class="product-item">';
        
        echo '<div class="product-image">';
        if (isset($product->images[0])) {
            echo '<img src="' . htmlspecialchars($product->images[0]) . '" alt="' . htmlspecialchars($product->name) . '" class="product-img">';
        } else {
            echo '<p>No image available</p>';
        }
        echo '</div>';

        echo '<div class="product-details">';
        echo '<h2 class="product-name">' . htmlspecialchars($product->name) . '</h2>';
        echo '<p class="product-description">' . htmlspecialchars($product->description) . '</p>';
        echo '<p class="product-price">';
        if ($price === 'N/A') {
            echo 'Price not available';
        } else {
            echo '$' . number_format($price, 2);  
        }
        echo '</p>';

        echo '<button class="buy-button" onclick="addToCart(\'' . addslashes(htmlspecialchars($product->id)) . '\', \'' . addslashes(htmlspecialchars($product->name)) . '\', \'' . number_format($price, 2) . '\', \'' . addslashes(htmlspecialchars($product->images[0])) . '\')">Buy Now</button>';
        echo '</div>';  
        echo '</div>';  
    }

    echo '</div>';  
    echo '<button class="checkout-button" onclick="window.location=\'generate-invoice.php\'">Checkout</button>';
    echo '</div>';  
    echo '</body>';
    echo '<script src="cart.js"></script>';
    echo '</html>';

} catch (\Stripe\Exception\ApiErrorException $e) {
    echo 'Error: ' . htmlspecialchars($e->getMessage());
}
?>