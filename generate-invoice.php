<?php
require 'init.php';
require 'vendor/autoload.php';

session_start();

global $stripe;
$errorMessage = '';
$paymentLinkUrl = '';
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

// Fetch customers from Stripe
$customers = [];
try {
    $customers = $stripe->customers->all(['limit' => 10]); // Adjust the limit as needed
} catch (\Stripe\Exception\ApiErrorException $e) {
    $errorMessage = "Error fetching customers: " . htmlspecialchars($e->getMessage());
}

if (!empty($cart)) {
    $line_items = [];
    foreach ($cart as $item) {
        try {
            $product = $stripe->products->retrieve($item['productId']);
            $price = $stripe->prices->retrieve($product->default_price);

            $item['price_id'] = $price->id;  
            $item['quantity'] = isset($item['quantity']) ? $item['quantity'] : 1; 

            $line_items[] = [
                'price' => $item['price_id'],
                'quantity' => $item['quantity'],
            ];

        } catch (\Stripe\Exception\ApiErrorException $e) {
            $errorMessage = "Error retrieving product or price: " . htmlspecialchars($e->getMessage());
            break;
        }
    }

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
            <li class="navbar-item"><a href="create-customer.php" class="navbar-link">Register</a></li>
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

        <form action="download-invoice.php" method="POST" class="download-invoice-form">
            <label for="customer_id">Select Customer:</label>
            <select name="customer_id" id="customer_id" class="dropdown-menu">
                <?php
                try {
                    $customers = $stripe->customers->all();

                    foreach ($customers as $customer) {
                        echo "<option value='" . htmlspecialchars($customer->id) . "'>" . htmlspecialchars($customer->name) . "</option>";
                    }
                } catch (\Stripe\Exception\ApiErrorException $e) {
                    echo "<option disabled>Error fetching customers</option>";
                }
                ?>
            </select>
            <button type="submit" class="btn">Download PDF Invoice</button>
        </form>

    </div>

    <script src="cart.js"></script>
</body>
</html>
