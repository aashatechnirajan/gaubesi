@extends('frontend.layouts.master')

@section('content')
<section class="container">
    @if(session('success_message'))
    <div class="alert alert-success">{{ session('success_message') }}</div>
@endif
    <div class="cart-section">
        <div class="cart-container">
            <div class="cart-items" id="cartItems">
                <!-- Cart items will be dynamically inserted here -->
            </div>
            <div class="order-summary">
                <h4 style="color:#8B4513 ">Order Summary</h4>
                <span class="summary-line"></span>
                <div id="orderSummary">
                    <!-- Order summary will be dynamically inserted here -->
                </div>
                <button type="button" class="buy-button" onclick="handleBuyNow()">Buy now</button>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let cart = JSON.parse(localStorage.getItem('cart') || '{}');
    const cartItemsContainer = document.getElementById('cartItems');
    const orderSummaryContainer = document.getElementById('orderSummary');

    function updateCart() {
        cartItemsContainer.innerHTML = '';
        let total = 0;
        let totalItems = 0;

        for (let productId in cart) {
            let product = cart[productId];
            total += product.price * product.quantity;
            totalItems += product.quantity;

            let itemHtml = `
                <div class="cart-item">
                    <h5>${product.name}</h5>
                    <p>Price: $. ${product.price}</p>
                    <div class="quantity-control">
                        <button onclick="changeQuantity('${productId}', -1)">-</button>
                        <span id="quantity-${productId}">${product.quantity}</span>
                        <button onclick="changeQuantity('${productId}', 1)">+</button>
                    </div>
                    <p>Total: $. ${product.price * product.quantity}</p>
                    <button class="remove-btn" onclick="removeItem('${productId}')">
                        <i class="fa fa-trash"></i> Remove
                    </button>
                </div>
            `;
            cartItemsContainer.innerHTML += itemHtml;
        }

        orderSummaryContainer.innerHTML = `
            <div class="summary-item">
                <h5>Total Items</h5>
                <h5>${totalItems}</h5>
            </div>
            <div class="summary-item">
                <h5>Total Price</h5>
                <h5>$. ${total}</h5>
            </div>
        `;

        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartCount();
    }

    window.changeQuantity = function(productId, change) {
    cart[productId].quantity += change;
    if (cart[productId].quantity < 1) { 
        cart[productId].quantity = 1; 
    }
    updateCart();
}
    window.removeItem = function(productId) {
        delete cart[productId];
        updateCart();
    }

    window.updateCartCount = function() {
        let cartCount = Object.values(cart).reduce((total, item) => total + item.quantity, 0);
        let cartCountElement = document.getElementById('cartCount');
        if (cartCountElement) {
            cartCountElement.textContent = cartCount;
        }
    }

    updateCart();

    // Check if we need to redirect to bulk payment after login
    @auth
        if (localStorage.getItem('redirectToBulkPayment') === 'true') {
            localStorage.removeItem('redirectToBulkPayment');
            redirectToBulkPayment();
        }
    @endauth
});

function handleBuyNow() {
    @auth
        redirectToBulkPayment();
    @else
        let cart = JSON.parse(localStorage.getItem('cart') || '{}');
        let products = Object.values(cart);
        let totalQuantity = products.reduce((sum, product) => sum + product.quantity, 0);
        let totalPrice = products.reduce((sum, product) => sum + (product.price * product.quantity), 0);

        // Encode the cart data in the URL
        let cartData = encodeURIComponent(JSON.stringify({
            products: products,
            totalQuantity: totalQuantity,
            totalPrice: totalPrice
        }));

        // Store intended URL in session storage
        sessionStorage.setItem('intendedPaymentRoute', "{{ route('bulk.payment') }}?products=" + cartData);
        window.location.href = "{{ route('login') }}?cart_data=" + cartData;
    @endauth
}

function redirectToBulkPayment() {
    let cart = JSON.parse(localStorage.getItem('cart') || '{}');
    let products = Object.values(cart);
    let totalQuantity = products.reduce((sum, product) => sum + product.quantity, 0);
    let totalPrice = products.reduce((sum, product) => sum + (product.price * product.quantity), 0);

    if (totalQuantity > 0) {
        window.location.href = `{{ route('bulk.payment') }}?products=${encodeURIComponent(JSON.stringify(products))}&totalQuantity=${totalQuantity}&totalPrice=${totalPrice}`;
    } else {
        alert('Your cart is empty. Please add products to the cart before proceeding with the bulk payment.');
    }
}
</script>
@endsection