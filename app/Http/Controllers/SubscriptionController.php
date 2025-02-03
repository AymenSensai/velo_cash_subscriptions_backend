<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    // Get subscriptions for the authenticated user
    public function index(Request $request)
    {
        $user = $request->user();
        $subscriptions = Subscription::where('user_id', $user->id)->get();

        return response()->json($subscriptions);
    }

    // Store a new subscription for the authenticated user
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|unique:subscriptions,name,NULL,id,user_id,' . $user->id . '|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        $validated['user_id'] = $user->id;

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
        $subscription = $customer->subscription;

        if (!$subscription) {
            return response()->json(['message' => 'No subscription found'], 404);
        }

        $reference = $this->generateReference($subscription->id);

        return view('subscriptions.details', compact('subscription', 'customer', 'reference'));
    }

    public function pay(Request $request, Customer $customer)
    {
        $subscription = $customer->subscription;

        if (!$subscription) {
            return response()->json(['message' => 'No subscription found'], 404);
        }

        $authorizationCode = $request->input('authorization_code');
        $reference = $request->input('reference');

        // Update the customer's authorization code
        $customer->update([
            'authorization_code' => $authorizationCode,
            'reference' => $reference
        ]);

        // Mark the subscription as paid
        $subscription->update(['is_paid' => true]);

        return response()->json(['message' => 'Subscription marked as paid']);
    }

    // In your controller or a helper function
    function generateReference($subscriptionId) {
        $timestamp = time(); // Current timestamp
        $randomString = bin2hex(random_bytes(4)); // Random string
        return "sub_{$subscriptionId}_{$timestamp}_{$randomString}";
    }

    public function getCustomersWithSubscriptions()
    {
        // Retrieve customers with their subscriptions
        $customers = Customer::with('subscription')->whereHas('subscription')->get();

        // Return the customers with their subscriptions
        return response()->json($customers);
    }
}
