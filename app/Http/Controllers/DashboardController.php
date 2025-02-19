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

        // Calculate expected revenue by iterating over customers and summing their active subscriptions
        $expected_revenue = Customer::where('user_id', $user->id)
            ->with(['subscriptions' => function ($query) {
                $query->where('is_paused', false);
            }])
            ->get()
            ->sum(function ($customer) {
                return $customer->subscriptions->sum('price');
            });

        return response()->json([
            'customers_number' => $customers_number,
            'subscribed_customers_number' => $subscribed_customers_number,
            'expected_revenue' => number_format($expected_revenue, 2, '.', ''),
        ]);
    }
}
