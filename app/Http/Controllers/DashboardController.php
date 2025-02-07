<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get total number of customers for the authenticated user
        $customers_number = Customer::where('user_id', $user->id)->count();

        // Get the number of customers with at least one active subscription
        $subscribed_customers_number = Customer::where('user_id', $user->id)
            ->whereHas('subscriptions', function ($query) {
                $query->where('is_paused', false);
            })
            ->count();

        // Calculate the expected revenue from active subscriptions
        $expected_revenue = Subscription::whereHas('customers', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->sum('price');

        return response()->json([
            'customers_number' => $customers_number,
            'subscribed_customers_number' => $subscribed_customers_number,
            'expected_revenue' => $expected_revenue,
        ]);
    }
}
