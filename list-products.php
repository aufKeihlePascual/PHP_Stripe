<?php
require "init.php";

$products = $stripe->products->all();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1 class="page-title">Our Products</h1>
        <div class="product-list">
            <?php foreach ($products->data as $product): ?>
                <div class="product-item">

                    <div class="product-image">
                        <?php if (!empty($product->images)): ?>
                            <img src="<?php echo htmlspecialchars($product->images[0]); ?>" alt="<?php echo htmlspecialchars($product->name); ?>" class="product-img">
                        <?php else: ?>
                            <p>No image available</p>
                        <?php endif; ?>
                    </div>

                    <div class="product-details">
                        <h2 class="product-name"><?php echo htmlspecialchars($product->name); ?></h2>
                        <p class="product-description"><?php echo htmlspecialchars($product->description); ?></p>

                        <p class="product-price">
                            <?php
                            try {
                                $prices = $stripe->prices->all(['product' => $product->id]);
                                if (count($prices->data) > 0) {
                                    $price = $prices->data[0];  
                                    echo strtoupper($price->currency) . ' ' . number_format($price->unit_amount / 100, 2);  
                                } else {
                                    echo 'Price not available';
                                }
                            } catch (\Stripe\Exception\ApiErrorException $e) {
                                echo 'Error fetching price: ' . $e->getMessage();
                            }
                            ?>
                        </p>

                        <a href="#" class="buy-button">Buy Now</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
