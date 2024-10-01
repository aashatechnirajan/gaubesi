@extends('frontend.layouts.master')

@section('content')
<section class="container">
    <div class="itemsdetailsection py-4 row d-flex justify-content-center align-items-center">
        <div class="itemsdetailsection_imagecollection col-md-4 col-10 align-items-center order-md-1">
            <div class="itemsdetailsection_image">
                <img src="{{ asset($product->product_image) }}" alt="{{ $product->product_name }}" id="mainproductimage" class="img-fluid" />
            </div>
            <div class="itemsdetailsection_hoverimage d-flex justify-content-center mt-2">
                @if($product->other_images)
                    @foreach (json_decode($product->other_images) as $image)
                        <img src="{{ asset($image) }}" alt="{{ $product->product_name }}" onclick="moreimageFunc(this)" class="img-thumbnail mx-1" style="width: 60px; height: 60px; cursor: pointer;" />
                    @endforeach
                @endif
            </div>         
        </div>
        <div class="description col-md-5 col-12 align-items-center order-md-1 order-3">
            <h2>Description about Product</h2>
            <div class="productdescription">
                <p>
                    <i class="fa-regular fa-hand-point-right"></i>
                    <span>{{ $product->description }}</span>
                </p>
            </div>
        </div>
        <div class="addtocartandbuysection col-md-3 col-11 py-2 order-md-1 order-2 my-3">
            <h5 class="py-2">{{ $product->product_name }}</h5>
            <div class="pricecollection d-flex flex-column py-1">
                <span class="newprice">${{ $product->selling_price }}</span>
                <div class="incres_dec py-2">
                    <span class="qty">Quantity</span>
                    <i class="fa-solid fa-plus" onclick="increaseQuantity()"></i>
                    <span id="quantity">1</span>
                    <i class="fa-solid fa-minus" onclick="decreaseQuantity()"></i>
                </div>
                <div class="totalprice py-2">
                    <span>Total Price: $<span id="totalPrice">{{ $product->selling_price }}</span></span>
                </div>
            </div>
            <div class="buy_addbuttom py-2">
                @if(auth()->check())
                    <button onclick="proceedToPayment('{{ $product->id }}', null, '{{ route('payment', ['id' => $product->id]) }}', false)" class="buynow btn btn-primary">Buy Now</button>
                @else
                    <button onclick="showPaymentModal()" class="buynow btn btn-primary">Buy Now</button>
                @endif
            </div>
        </div>
    </div>
</section>

<!-- Modal for Payment Options -->
@if(!auth()->check())
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Payment Options</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Would you like to pay with login or without login?</p>
                <div class="d-flex justify-content-between">
                    <button class="btn btn-primary" onclick="proceedToPayment('{{ $product->id }}', '{{ route('login') }}', '{{ route('payment', ['id' => $product->id]) }}', true)">Pay with Login</button>
                    <button class="btn btn-warning" onclick="proceedToPayment('{{ $product->id }}', null, '{{ route('newpayment', ['id' => $product->id]) }}', false)">Pay without Login</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<script>
    const productPrice = {{ $product->selling_price }};
    
    function updateTotalPrice(quantity) {
        const totalPriceElement = document.getElementById('totalPrice');
        totalPriceElement.innerText = quantity * productPrice;
    }

    function moreimageFunc(element) {
        const mainproductimage = document.getElementById("mainproductimage");
        const newimage = element.src;
        mainproductimage.src = newimage;
    }

    function increaseQuantity() {
        let quantityElement = document.getElementById('quantity');
        let quantity = parseInt(quantityElement.innerText);
        quantity++;
        quantityElement.innerText = quantity;
        updateTotalPrice(quantity);
    }

    function decreaseQuantity() {
        let quantityElement = document.getElementById('quantity');
        let quantity = parseInt(quantityElement.innerText);
        if (quantity > 1) {
            quantity--;
            quantityElement.innerText = quantity;
            updateTotalPrice(quantity);
        }
    }

    function showPaymentModal() {
        const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
        paymentModal.show();
    }

    function proceedToPayment(productId, loginRoute, paymentRoute, isLoginRequired) {
        const quantity = document.getElementById('quantity').innerText;
        const totalPrice = document.getElementById('totalPrice').innerText;

        if (isLoginRequired) {
            // Redirect to login route and store payment info in sessionStorage
            sessionStorage.setItem('intendedPaymentRoute', paymentRoute);
            sessionStorage.setItem('quantity', quantity);
            sessionStorage.setItem('totalPrice', totalPrice);
            window.location.href = loginRoute;
        } else {
            // Redirect directly to payment route with query parameters
            window.location.href = `${paymentRoute}?quantity=${quantity}&totalPrice=${totalPrice}`;
        }
    }
</script>
@endsection
