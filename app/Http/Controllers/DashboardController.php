<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $orders_number = Order::where('user_id', $user->id)->count();
        $products_number = Product::where('user_id', $user->id)->count();
        $customers_number = Customer::where('user_id', $user->id)->count();

        return response()->json([
            'orders_number' => $orders_number,
            'products_number' => $products_number,
            'customers_number' => $customers_number,
        ]);
    }
}
