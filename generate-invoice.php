<?php
require 'init.php';

// Start the session to retrieve the cart
session_start();

$errorMessage = '';
$paymentLinkUrl = '';

// Retrieve the cart from session
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

if (!empty($cart)) {
    // Prepare line items based on cart data
    $line_items = [];
    foreach ($cart as $item) {
        try {
            // Retrieve the product and price info from Stripe
            $product = $stripe->products->retrieve($item['productId']);
            $price = $stripe->prices->retrieve($product->default_price);

            // Ensure price_id and quantity are properly set
            $item['price_id'] = $price->id;  // Use price_id from Stripe
            $item['quantity'] = isset($item['quantity']) ? $item['quantity'] : 1; // Ensure quantity is set

            // Add to line items
            $line_items[] = [
                'price' => $item['price_id'],
                'quantity' => $item['quantity'],
            ];

        } catch (\Stripe\Exception\ApiErrorException $e) {
            $errorMessage = "Error retrieving product or price: " . htmlspecialchars($e->getMessage());
            break;
        }
    }

    // If no error, generate the payment link
    if (empty($errorMessage)) {
        try {
            $payment_link = $stripe->paymentLinks->create([
                'line_items' => $line_items,
            ]);
            $paymentLinkUrl = $payment_link->url;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $errorMessage = "Error creating payment link: " . htmlspecialchars($e->getMessage());
        }
    }
} else {
    $errorMessage = "No products in cart.";
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Payment Link</title>
    <link rel="stylesheet" href="invoice.css">
</head>
<body>
    <nav class="navbar">
        <ul class="navbar-menu">
            <li class="navbar-item"><a href="create-customer.php" class="navbar-link">Home</a></li>
            <li class="navbar-item"><a href="list-products.php" class="navbar-link">Products</a></li>
            <li class="navbar-item"><a href="generate-invoice.php" class="navbar-link">Generate Invoice</a></li>
        </ul>
    </nav>

    <div class="container">
        <h1>Generate Payment Link</h1>

        <?php if ($paymentLinkUrl): ?>
            <div class="modal-overlay" id="paymentLinkModal">
                <div class="modal">
                    <h2>Payment link created successfully!</h2>
                    <p>Your payment link is ready. Use the button below to proceed.</p>
                    <div class="modal-buttons">
                        <a href="<?= htmlspecialchars($paymentLinkUrl) ?>" target="_blank">Click here to pay</a>
                        <button onclick="closeModal()">Close</button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="message error"><?= $errorMessage ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <h2>Your Cart</h2>

            <?php if (empty($cart)): ?>
                <p>No products in cart</p>
            <?php else: ?>
                <div class="cart-items">
                    <?php
                    $totalPrice = 0;
                    foreach ($cart as $item):
                        $totalPrice += $item['price'] * $item['quantity'];
                    ?>
                        <div class="cart-item">
                            <div class="cart-product">
                                <img src="<?= htmlspecialchars($item['imageUrl'] ?? 'product_image_placeholder.jpg') ?>" alt="<?= htmlspecialchars($item['productName']) ?>" class="cart-item-image">
                                
                                <div class="cart-item-details">
                                    <h3><?= htmlspecialchars($item['productName']) ?></h3>
                                    
                                    <div class="cart-item-price">
                                        <div class="price-quantity">
                                            <div class="price-quantity-group">
                                                <span class="price">$<?= number_format($item['price'], 2) ?></span>
                                                <span class="x">x</span>
                                            </div>
                                            <div class="quantity-controls">
                                                <button class="quantity-btn1" onclick="updateQuantity('<?= $item['productId'] ?>', -1)">-</button>
                                                <span class="quantity"><?= $item['quantity'] ?></span>
                                                <button class="quantity-btn" onclick="updateQuantity('<?= $item['productId'] ?>', 1)">+</button>
                                                <p>Total: $<?= number_format($item['price'] * $item['quantity'], 2) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p class="total-price">Total Price: $<?= number_format($totalPrice, 2) ?></p>
                <?php
                    if ($paymentLinkUrl) {
                        echo "<div class=\"center-button\"><a href=\"$paymentLinkUrl\" target=\"_blank\" class=\"checkout-button\">Generate Payment Link</a></div>";
                    }
                endif;
                ?>
        </form>

        <form action="" method="POST" class="clear-cart-form" onsubmit="event.preventDefault(); clearCart();">
            <button type="submit" name="clear_cart" class="clear-cart-btn">Clear Cart</button>
        </form>

        <form action="list-products.php" method="GET" class="go-back-form">
            <button type="submit" class="go-back-btn">Go Back to Shopping</button>
        </form>
    </div>

    <script src="cart.js"></script>
</body>
</html>
