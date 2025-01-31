<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;

class OrderController extends Controller
{
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
            'discount' => 'nullable|numeric|min:0', // Validate discount
        ]);

        $user = $request->user();
        $customer = Customer::findOrFail($validated['customer_id']);
        $totalPrice = 0;

        foreach ($validated['products'] as $productData) {
            $product = Product::findOrFail($productData['id']);

            // Ensure that the product has enough quantity before placing the order
            if ($product->quantity < $productData['quantity']) {
                return response()->json([
                    'message' => 'Not enough stock for product: ' . $product->name
                ], 400);
            }

            $totalPrice += $product->price * $productData['quantity'];

            // Reduce the quantity of the product
            $product->decrement('quantity', $productData['quantity']);
        }

        // Apply discount if provided
        $discount = $validated['discount'] ?? 0;
        $finalPrice = $totalPrice - $discount;

        $orderNumber = 'ORD-' . strtoupper(uniqid());

        $order = Order::create([
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'total_price' => $finalPrice,
            'order_number' => $orderNumber,
            'discount' => $discount,
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
            'order' => $order->load('customer', 'products'),
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
            'discount' => 'nullable|numeric|min:0', // Validate discount
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        $totalPrice = 0;

        // Detach old products first (reset quantities)
        foreach ($order->products as $product) {
            $orderProductData = $order->products->find($product->id);
            if ($orderProductData) {
                $product->increment('quantity', $orderProductData->pivot->quantity);
            }
        }

        $order->products()->detach();

        foreach ($validated['products'] as $productData) {
            $product = Product::findOrFail($productData['id']);

            // Ensure there is enough stock to update
            if ($product->quantity < $productData['quantity']) {
                return response()->json([
                    'message' => 'Not enough stock for product: ' . $product->name
                ], 400);
            }

            $totalPrice += $product->price * $productData['quantity'];

            // Decrement the product quantity
            $product->decrement('quantity', $productData['quantity']);
            $order->products()->attach($product->id, [
                'quantity' => $productData['quantity'],
                'price' => $product->price,
            ]);
        }

        $discount = $validated['discount'] ?? $order->discount;
        $finalPrice = $totalPrice - $discount;

        $order->update([
            'customer_id' => $customer->id,
            'total_price' => $finalPrice,
            'discount' => $discount,
        ]);

        return response()->json($order->load('customer', 'products'));
    }

    public function destroy(Order $order)
    {
        $order->products()->detach();
        $order->delete();

        return response()->json(null, 204);
    }
}
