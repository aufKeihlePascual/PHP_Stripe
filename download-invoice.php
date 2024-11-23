<?php

use Fpdf\Fpdf;

require 'init.php';
require 'vendor/autoload.php';

session_start();

$cart = $_SESSION['cart'] ?? [];
$customerId = $_POST['customer_id'] ?? null; // Fetch customer_id from the form

if (empty($cart)) {
    die('No products in the cart to generate an invoice.');
}

$customerName = 'Unknown Customer';

// Fetch customer details from Stripe if customer_id is provided
if ($customerId) {
    try {
        $customer = $stripe->customers->retrieve($customerId, []);
        $customerName = $customer->name ?? 'Unknown Customer';
    } catch (\Stripe\Exception\ApiErrorException $e) {
        error_log('Stripe API Error: ' . $e->getMessage());
        $customerName = 'Error fetching customer';
    }
}

// Ensure FPDF is available (from Composer)
$pdf = new Fpdf();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Invoice', 0, 1, 'C');
$pdf->Ln(10);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, "Customer: $customerName", 0, 1);
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(80, 10, 'Product Name', 1);
$pdf->Cell(30, 10, 'Price', 1);
$pdf->Cell(30, 10, 'Quantity', 1);
$pdf->Cell(30, 10, 'Total', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 12);
$totalPrice = 0;

foreach ($cart as $item) {
    $productTotal = $item['price'] * $item['quantity'];
    $totalPrice += $productTotal;

    $pdf->Cell(80, 10, $item['productName'], 1);
    $pdf->Cell(30, 10, "$" . number_format($item['price'], 2), 1);
    $pdf->Cell(30, 10, $item['quantity'], 1);
    $pdf->Cell(30, 10, "$" . number_format($productTotal, 2), 1);
    $pdf->Ln();
}

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(110, 10, 'Total Price', 1);
$pdf->Cell(30, 10, "$" . number_format($totalPrice, 2), 1);

$pdf->Output('D', 'invoice.pdf');
exit;