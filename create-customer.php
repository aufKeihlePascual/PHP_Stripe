<?php
require 'init.php'; 

$successMessage = ''; 
$errorMessage = '';   

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $phone = htmlspecialchars($_POST['phone']);
    $line1 = htmlspecialchars($_POST['line1']);
    $line2 = htmlspecialchars($_POST['line2']);
    $state = htmlspecialchars($_POST['state']);
    $city = htmlspecialchars($_POST['city']);
    $country = htmlspecialchars($_POST['country']);
    $postal_code = htmlspecialchars($_POST['postal_code']);

    try {
        $customer = $stripe->customers->create([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'address' => [
                'line1' => $line1,
                'line2' => $line2,
                'state' => $state,
                'city' => $city,
                'country' => $country,
                'postal_code' => $postal_code
            ]
        ]);

        $successMessage = "Customer '{$customer->name}' created successfully!";
    } catch (\Stripe\Exception\ApiErrorException $e) {
        $errorMessage = "Error: " . htmlspecialchars($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Customer</title>
    <link rel="stylesheet" href="customer.css">
</head>
<body>
    <nav class="navbar">
        <ul class="navbar-menu">
            <li class="navbar-item"><a href="create-customer.php" class="navbar-link">Register</a></li>
            <li class="navbar-item"><a href="list-products.php" class="navbar-link">Products</a></li>
            <li class="navbar-item"><a href="generate-invoice.php" class="navbar-link">Generate Invoice</a></li>
        </ul>
    </nav>

    <div class="form-container">
        <h1>Create Customer</h1>

        <?php if ($successMessage): ?>
            <div class="message success"><?= $successMessage ?></div>
        <?php elseif ($errorMessage): ?>
            <div class="message error"><?= $errorMessage ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="Enter full name" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter email address" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="tel" id="phone" name="phone" placeholder="Enter phone number" required>
            </div>

            <div class="form-group">
                <label for="line1">Address Line 1</label>
                <input type="text" id="line1" name="line1" placeholder="Enter primary address" required>
            </div>

            <div class="form-group">
                <label for="line2">Address Line 2</label>
                <input type="text" id="line2" name="line2" placeholder="Enter secondary address (optional)">
            </div>

            <div class="form-group">
                <label for="state">State</label>
                <input type="text" id="state" name="state" placeholder="Enter state">
            </div>

            <div class="form-group">
                <label for="city">City</label>
                <input type="text" id="city" name="city" placeholder="Enter city" required>
            </div>

            <div class="form-group">
                <label for="country">Country</label>
                <input type="text" id="country" name="country" placeholder="Enter country" required>
            </div>

            <div class="form-group">
                <label for="postal_code">Postal Code</label>
                <input type="text" id="postal_code" name="postal_code" placeholder="Enter postal code" required>
            </div>

            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>
