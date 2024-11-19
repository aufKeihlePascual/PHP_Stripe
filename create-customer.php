<?php
require 'init.php';  // Ensure the Stripe client is initialized and available

// Handle payment method submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Decode the JSON payload from the frontend
        $data = json_decode(file_get_contents('php://input'), true);
        $paymentMethodId = $data['paymentMethodId'];

        // Ensure you have the correct customer ID (You should retrieve this dynamically or use an existing one)
        $customerId = 'your-customer-id-here';  // Replace with the actual customer ID

        // Attach the payment method to the customer
        $paymentMethod = $stripe->paymentMethods->attach($paymentMethodId, [
            'customer' => $customerId,
        ]);

        // Set the payment method as the default for invoices (e.g., subscriptions, one-time payments)
        $stripe->customers->update($customerId, [
            'invoice_settings' => [
                'default_payment_method' => $paymentMethod->id,
            ],
        ]);

        // Return a success response
        echo json_encode([
            'status' => 'success',
            'message' => 'Payment method successfully added.',
        ]);
    } catch (\Stripe\Exception\ApiErrorException $e) {
        // Handle error and return an error response
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage(),
        ]);
    }
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stripe Payment Method</title>
    <script src="https://js.stripe.com/v3/"></script> <!-- Include Stripe.js -->
    <style>
        /* Add some simple styling for the form */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        #payment-form {
            width: 400px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        #card-element {
            margin-bottom: 20px;
        }

        #card-errors {
            color: red;
            font-size: 14px;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

    <h1>Enter Payment Information</h1>
    <form id="payment-form">
        <div id="card-element">
            <!-- A Stripe Element will be inserted here. -->
        </div>
        <div id="card-errors" role="alert"></div>
        <button id="submit">Submit Payment</button>
    </form>

    <script>
        // Create an instance of the Stripe object with your publishable key
        const stripe = Stripe('your-publishable-key-here'); // Replace with your actual Stripe publishable key
        const elements = stripe.elements();

        // Create an instance of the card Element
        const card = elements.create('card');

        // Mount the card element into the DOM
        card.mount('#card-element');

        // Handle form submission
        const form = document.getElementById('payment-form');
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            // Create a payment method
            const {paymentMethod, error} = await stripe.createPaymentMethod('card', card);

            if (error) {
                // Show error to the user
                const errorElement = document.getElementById('card-errors');
                errorElement.textContent = error.message;
            } else {
                // Send the payment method ID to the server
                const paymentMethodId = paymentMethod.id;

                // Make an AJAX request to your backend to save the payment method
                fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ paymentMethodId: paymentMethodId }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('Payment method successfully added!');
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Something went wrong. Please try again later.');
                });
            }
        });
    </script>

</body>
</html>