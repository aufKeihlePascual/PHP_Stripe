<?php

require "init.php";

global $stripe;
$line_items = [];

$products = $stripe->products->all();
foreach ($products as $product)
{
    array_push($line_items,
        [
            'price' => $product->default_price,
            'quantity' => 1
        ]
    );
}
$payment_link = $stripe->paymentLinks->create([
    'line_items' => $line_items
]);

print_r($payment_link->url);