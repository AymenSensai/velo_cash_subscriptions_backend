<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    // Get subscriptions for the authenticated user
    public function index()
    {
        $user = auth()->user();

        $subscriptions = Subscription::get();

        return response()->json($subscriptions);
    }

    // Store a new subscription for the authenticated user
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:subscriptions,name',
            'price' => 'required|numeric|min:0',
        ]);

        $subscription = Subscription::create($validated);

        return response()->json($subscription, 201);
    }

    // Show a specific subscription if it belongs to the user
    public function show(Subscription $subscription, Request $request)
    {
        if ($subscription->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($subscription);
    }

    // Update a subscription if it belongs to the user
    public function update(Request $request, Subscription $subscription)
    {
        if ($subscription->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|unique:subscriptions,name,' . $subscription->id . ',id,user_id,' . $subscription->user_id . '|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        $subscription->update($validated);

        return response()->json($subscription);
    }

    // Delete a subscription if it belongs to the user
    public function destroy(Subscription $subscription, Request $request)
    {
        if ($subscription->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $subscription->delete();

        return response()->json(null, 204);
    }

    public function subscriptionDetails(Customer $customer)
    {
        // If subscriptions are paid, redirect to success page
        if ($customer->payed_subscriptions) {
            return redirect()->route('subscription.success');
        }

        $subscriptions = $customer->subscriptions;

        if ($subscriptions->isEmpty()) {
            return response()->json(['message' => 'No subscriptions found'], 404);
        }

        $reference = $this->generateReference();

        return view('subscriptions.details', compact('subscriptions', 'customer', 'reference'));
    }

    public function pay(Request $request, Customer $customer)
    {
        try {
            $authorizationCode = $request->input('authorization_code');

            // Get the subscriptions that belong to the customer
            $subscriptions = $customer->subscriptions;

            if ($subscriptions->isEmpty()) {
                // Mark as false since no valid subscriptions found
                $customer->update(['payed_subscriptions' => false]);
                return response()->json(['error' => 'No valid subscriptions found'], 404);
            }

            // Save authorization code in customer record
            $customer->update([
                'authorization_code' => $authorizationCode,
                'payed_subscriptions' => true  // Mark as paid
            ]);

            return response()->json([
                'message' => 'Subscriptions marked as paid',
                'paid_subscriptions' => $subscriptions
            ]);

        } catch (\Exception $e) {
            // If an error occurs, mark as false
            $customer->update(['payed_subscriptions' => false]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // In your controller or a helper function
    function generateReference() {
        $timestamp = time(); // Current timestamp
        $randomString = bin2hex(random_bytes(4)); // Random string
        return "sub_{$timestamp}_{$randomString}";
    }

    public function getCustomersWithSubscriptions()
    {
        // Retrieve customers who have subscriptions and has_subscription is true
        $user = auth()->user();

        $customers = Customer::with('subscriptions')
            ->where('user_id', $user->id)
            ->where('has_subscription', true)
            ->get();

        // Return the customers with their subscriptions
        return response()->json($customers);
    }
}
