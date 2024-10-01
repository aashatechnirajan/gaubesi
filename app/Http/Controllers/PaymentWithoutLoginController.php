<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\PaymentWithoutLogin;
use Stripe\Stripe;
use Stripe\Charge;
use App\Models\OrderWithoutLogin;
use Illuminate\Support\Facades\Session;

class PaymentWithoutLoginController extends Controller
{
    public function processPayment(Request $request)
{
    Stripe::setApiKey(env('STRIPE_SECRET'));

    try {
        $charge = Charge::create([
            'amount' => $request->amount * 100,
            'currency' => 'usd',
            'source' => $request->stripeToken,
            'description' => 'Payment for order',
        ]);

        $order = OrderWithoutLogin::create([
            'user_name' => $request->name,
            'user_email' => $request->email,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'total_amount' => $request->amount,
            'payment_method' => 'stripe',
            'payment_status' => 'completed',
            'shipping_address' => $request->address,
            'shipping_country' => $request->country,
            'postal_code' => $request->postal_code,
            'order_status' => 'pending',
            'is_paid' => true,
        ]);

        PaymentWithoutLogin::create([
            'order_id' => $order->id,
            'product_id' => $request->product_id,
            'amount' => $request->amount,
            'payment_method' => 'stripe',
            'payment_status' => 'completed',
        ]);

        $product = Product::findOrFail($order->product_id);

        $orderSummary = [
            'order' => $order,
            'product' => $product,
            'totalPrice' => $order->total_amount,
        ];

        session()->put('order_summary', $orderSummary);
        session()->forget('cart');
        session()->flash('payment_success', true);
        return redirect()->route('payment.bill', $order->id)->with('success_message', 'Payment successful! Your cart has been cleared.');
    } catch (\Exception $e) {
        return back()->with('error_message', 'Error! ' . $e->getMessage());
    }
}

    public function showNewPaymentPage($productId, Request $request)
    {
        $product = Product::findOrFail($productId);
        $quantity = $request->query('quantity', 1);
        $totalPrice = $request->query('totalPrice', $product->selling_price * $quantity);

        return view('frontend.newpayment', [
            'product' => $product,
            'quantity' => $quantity,
            'totalPrice' => $totalPrice
        ]);
    }

    public function showBill($orderId)
    {
        $order = OrderWithoutLogin::with('product')->findOrFail($orderId);
        $totalPrice = $order->quantity * $order->product->selling_price;
        $products = [$order->product];
        $quantities = [$order->quantity];
    
        return view('frontend.bill', compact('order', 'totalPrice', 'products', 'quantities'));
    }
    
}
