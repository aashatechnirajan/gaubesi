<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\OrderWithoutLogin;

class OrderController extends Controller
{
    /**
     * Display a listing of the orders.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Fetch orders with login and sort them by the latest order_date
        $ordersWithLogin = Order::with('customer')
            ->whereNotNull('user_id')
            ->orderBy('order_date', 'desc')
            ->get();
    
        // Fetch orders without login and sort them by the latest order_date
        $ordersWithoutLogin = OrderWithoutLogin::orderBy('order_date', 'desc')->get();
    
        // Combine both collections
        $combinedOrders = $ordersWithLogin->merge($ordersWithoutLogin);
    
        // Sort the combined collection by order_date in descending order
        $sortedOrders = $combinedOrders->sortByDesc('order_date');
    
        return view('backend.order.index', [
            'combinedOrders' => $sortedOrders,
        ]);
    }

    /**
     * Show the form for creating a new order.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $customers = Customer::all();
        return view('backend.order.create', compact('customers'));
    }

    /**
     * Store a newly created order in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->merge([
            'is_paid' => $request->has('is_paid'),
            'is_shipped' => $request->has('is_shipped'),
            'is_delivered' => $request->has('is_delivered'),
        ]);

        $validatedData = $request->validate([
            'user_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'total_amount' => 'required|numeric',
            'payment_method' => 'required|string|max:255',
            'payment_status' => 'required|string|in:pending,completed,cancelled',
            'shipping_address' => 'required|string|max:255',
            'shipping_country' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'shipping_cost' => 'required|numeric',
            'tax_amount' => 'required|numeric',
            'order_status' => 'required|string|in:pending,processing,shipped,delivered,cancelled',
            'is_paid' => 'required|boolean',
            'is_shipped' => 'required|boolean',
            'is_delivered' => 'required|boolean',
            'delivery_date' => 'nullable|date',
            'delivery_time' => 'nullable|string|max:5',
        ]);
        Order::create($validatedData);
        return redirect()->route('backend.orders.index');
    }

    /**
     * Show the form for editing the specified order.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function edit(Order $order)
    {
        $customers = Customer::all();
        return view('backend.order.update', compact('order', 'customers'));
    }

    /**
     * Update the specified order in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        $request->merge([
            'is_paid' => $request->has('is_paid'),
            'is_shipped' => $request->has('is_shipped'),
            'is_delivered' => $request->has('is_delivered'),
        ]);

        $validatedData = $request->validate([
            'user_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'total_amount' => 'required|numeric',
            'payment_method' => 'required|string|max:255',
            'payment_status' => 'required|string|in:pending,completed,cancelled',
            'shipping_address' => 'required|string|max:255',
            'shipping_country' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'shipping_cost' => 'required|numeric',
            'tax_amount' => 'required|numeric',
            'order_status' => 'required|string|in:pending,processing,shipped,delivered,cancelled',
            'is_paid' => 'required|boolean',
            'is_shipped' => 'required|boolean',
            'is_delivered' => 'required|boolean',
            'delivery_date' => 'nullable|date',
            'delivery_time' => 'nullable|string|max:5',
        ]);

        $order->update($validatedData);
        return redirect()->route('backend.orders.index');
    }

    /**
     * Remove the specified order from storage.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        Log::info('Destroy method called for order: ' . $order->id);
        try {
            if (!$order) {
                return redirect()->route('backend.orders.index')->with('error', 'Order not found.');
            }
            $order->delete();
            return redirect()->route('backend.orders.index')->with('success', 'Order deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting order: ' . $e->getMessage());
            return redirect()->route('backend.orders.index')->with('error', 'Failed to delete order.');
        }
    }
}
