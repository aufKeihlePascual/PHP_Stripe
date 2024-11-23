// Add product to the cart (in the session)
function addToCart(productId, productName, price, imageUrl) {
    // Get the current cart from sessionStorage (through an AJAX request or form)
    let cart = JSON.parse(sessionStorage.getItem('cart')) || [];

    // Check if the product is already in the cart
    let existingProduct = cart.find(item => item.productId === productId);
    if (existingProduct) {
        // If the product exists, increase the quantity
        existingProduct.quantity += 1;
    } else {
        // If the product doesn't exist, add it to the cart with quantity 1
        cart.push({
            productId: productId,
            productName: productName,
            price: parseFloat(price),
            quantity: 1,
            imageUrl: imageUrl
        });
    }

    // Save the updated cart back to sessionStorage
    sessionStorage.setItem('cart', JSON.stringify(cart));

    // Send cart data to the server to store in PHP session
    fetch('update-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(cart)
    })
    .then(response => response.json())
    .then(data => console.log(data))
    .catch(error => console.error('Error updating cart:', error));

    // Show alert confirming the product was added to the cart
    alert(productName + ' has been added to the cart!');
}

function updateQuantity(productId, change) {
    let cart = JSON.parse(sessionStorage.getItem('cart')) || [];

    let product = cart.find(item => item.productId === productId);
    if (product) {
        product.quantity = Math.max(1, product.quantity + change);
    }

    sessionStorage.setItem('cart', JSON.stringify(cart));

    fetch('update-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(cart)
    })
    .then(response => response.json())
    .then(data => console.log('Cart updated:', data))
    .catch(error => console.error('Error updating cart:', error));

    document.querySelector(`.cart-item[data-product-id="${productId}"] .quantity`).textContent = product.quantity;
}

function clearCart() {
    sessionStorage.removeItem('cart');
    
    fetch('clear-cart.php', {
        method: 'POST',
    })
    .then(response => response.json())
    .then(data => {
        console.log('Cart cleared:', data);
        window.location.reload();
    })
    .catch(error => console.error('Error clearing cart:', error));
}
