<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $customers = Customer::where('user_id', $user->id)
                             ->with(['subscriptions' => function ($query) {
                                 $query->withPivot('is_paused');
                             }])
                             ->orderBy('created_at', 'desc')
                             ->get();

        return response()->json($customers);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:customers,name',
            'phone_number' => 'required|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'vat_number' => 'nullable|string|max:50',
            'company_name' => 'nullable|string',
            'mobile_number' => 'nullable|string',
            'responsible_name' => 'nullable|string',
            'has_subscription' => 'nullable|boolean',
            'subscription_ids' => 'nullable|array',
            'subscription_ids.*' => 'exists:subscriptions,id',
        ]);

        $validated['user_id'] = $user->id;

        // Create the customer first
        $customer = Customer::create($validated);

        // Attach selected subscriptions to the customer
        if (!empty($validated['subscription_ids'])) {
            foreach ($validated['subscription_ids'] as $subscriptionId) {
                $customer->subscriptions()->attach($subscriptionId, ['is_paused' => false]);
            }
        }

        return response()->json($customer->load('subscriptions'), 201);
    }

    public function show(Customer $customer)
    {
        return $customer;
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|unique:customers,phone_number,' . $customer->id,
            'email' => 'nullable|email|unique:customers,email,' . $customer->id,
            'address' => 'nullable|string',
            'vat_number' => 'nullable|string|max:50',
            'company_name' => 'nullable|string',
            'mobile_number' => 'nullable|string',
            'responsible_name' => 'nullable|string',
            'subscription_id' => 'nullable|exists:subscriptions,id,user_id,'
        ]);

        $customer->update($validated);

        return response()->json($customer);
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return response()->json(null, 204);
    }

    public function orders(Customer $customer)
    {
        $orders = $customer->orders()
            ->with('products.category')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    public function addSubscription(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'subscription_id' => 'required|exists:subscriptions,id',
        ]);

        $subscriptionId = $validated['subscription_id'];

        if ($customer->subscriptions()->where('subscription_id', $subscriptionId)->exists()) {
            return response()->json(['message' => 'Subscription already assigned to this customer.'], 409);
        }

        $customer->subscriptions()->attach($subscriptionId, ['is_paused' => false]);

        return response()->json(['message' => 'Subscription added successfully.', 'customer' => $customer->load('subscriptions')], 201);
    }

    public function toggleSubscriptionPause(Request $request, Customer $customer, $subscriptionId)
    {
        if (!$customer->subscriptions()->where('subscription_id', $subscriptionId)->exists()) {
            return response()->json(['message' => 'Subscription not found for this customer.'], 404);
        }

        $currentStatus = $customer->subscriptions()->where('subscription_id', $subscriptionId)->first()->pivot->is_paused;

        $customer->subscriptions()->updateExistingPivot($subscriptionId, [
            'is_paused' => !$currentStatus,
        ]);

        return response()->json([
            'message' => 'Subscription status updated successfully.',
            'is_paused' => !$currentStatus,
        ]);
    }

    public function toggleHasSubscription(Customer $customer)
    {
        if ($customer->has_subscription) {
            // Set to false and clear subscriptions
            $customer->subscriptions()->detach();
            $customer->update(['has_subscription' => false]);
        } else {
            // Set to true but keep subscriptions empty
            $customer->update(['has_subscription' => true]);
        }

        return response()->json([
            'message' => 'Subscription status toggled successfully.',
            'has_subscription' => $customer->has_subscription,
            'subscriptions' => $customer->subscriptions()->get(),
        ]);
    }
}
