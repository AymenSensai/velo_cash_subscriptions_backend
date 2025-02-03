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
                             ->with('subscription')
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
            'subscription_id' => 'nullable|integer|exists:subscriptions,id,user_id,' . $user->id, // Ensure it’s an integer
        ]);

        // Convert empty string to null to avoid the PostgreSQL error
        $validated['subscription_id'] = $validated['subscription_id'] ?? null;

        $validated['user_id'] = $user->id;

        $customer = Customer::create($validated);

        return response()->json($customer, 201);
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
}
