<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function getAllOrders()
    {
        $user = auth()->user();

        $orders = Order::with(['customer', 'products.category'])
                    ->where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->get();

        return response()->json($orders);
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $orders = Order::where('user_id', $user->id)
                       ->with(['customer', 'products.category'])
                       ->orderBy('created_at', 'desc')
                       ->get();

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0', // Add tax validation
        ]);

        $user = $request->user();
        $customer = Customer::findOrFail($validated['customer_id']);
        $totalPrice = 0;

        foreach ($validated['products'] as $productData) {
            $product = Product::findOrFail($productData['id']);

            if ($product->quantity < $productData['quantity']) {
                return response()->json([
                    'message' => 'Not enough stock for product: ' . $product->name
                ], 400);
            }

            $totalPrice += $product->price * $productData['quantity'];
            $product->decrement('quantity', $productData['quantity']);
        }

        $discount = $validated['discount'] ?? 0;
        $tax = $validated['tax'] ?? 0;

        $finalPrice = round(($totalPrice - $discount) * (1 + $tax), 2);

        $orderNumber = 'ORD-' . strtoupper(uniqid());

        $order = Order::create([
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'total_price' => $finalPrice,
            'order_number' => $orderNumber,
            'discount' => $discount,
            'tax' => $tax,
            'is_paid' => false,
        ]);

        foreach ($validated['products'] as $productData) {
            $product = Product::findOrFail($productData['id']);
            $order->products()->attach($product->id, [
                'quantity' => $productData['quantity'],
                'price' => $product->price,
            ]);
        }

        return response()->json([
            'order_number' => $order->order_number,
            'order' => $order->load('customer', 'products.category'),
        ], 201);
    }

    public function show(Order $order)
    {
        return $order->load('customer', 'products');
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0', // Add tax validation
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        $totalPrice = 0;

        foreach ($order->products as $product) {
            $orderProductData = $order->products->find($product->id);
            if ($orderProductData) {
                $product->increment('quantity', $orderProductData->pivot->quantity);
            }
        }

        $order->products()->detach();

        foreach ($validated['products'] as $productData) {
            $product = Product::findOrFail($productData['id']);

            if ($product->quantity < $productData['quantity']) {
                return response()->json([
                    'message' => 'Not enough stock for product: ' . $product->name
                ], 400);
            }

            $totalPrice += $product->price * $productData['quantity'];
            $product->decrement('quantity', $productData['quantity']);
            $order->products()->attach($product->id, [
                'quantity' => $productData['quantity'],
                'price' => $product->price,
            ]);
        }

        $discount = $validated['discount'] ?? $order->discount;
        $tax = $validated['tax'] ?? $order->tax;

        $finalPrice = ($totalPrice - $discount) * (1 + $tax);

        $order->update([
            'customer_id' => $customer->id,
            'total_price' => $finalPrice,
            'discount' => $discount,
            'tax' => $tax,
        ]);

        return response()->json($order->load('customer', 'products'));
    }

    public function destroy(Order $order)
    {
        $order->products()->detach();
        $order->delete();

        return response()->json(null, 204);
    }

    public function orderDetails(Order $order)
    {
        if ($order->is_paid) {
            return redirect('/payment-success');
        }

        // Eager load the products with pivot data
        $order->load('products');

        // Pass the products as $items to the view
        return view('orders.details', [
            'order' => $order,
            'items' => $order->products
        ]);
    }
    public function pay(Request $request, Order $order)
    {
        // Ensure that the order is unpaid before updating its status
        if ($order->is_paid) {
            return response()->json(['message' => 'Order already paid'], 400);
        }

        // Update the order status to paid
        $order->update(['is_paid' => true]);

        return response()->json(['message' => 'Order marked as paid']);
    }
}
