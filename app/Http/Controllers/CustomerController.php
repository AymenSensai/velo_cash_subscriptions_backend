<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
class CustomerController extends Controller
{
    public function index(Request $request)
    {
        // Get the authenticated user
        $user = $request->user();

        // Fetch customers that belong to the user
        $customers = Customer::where('user_id', $user->id)
                             ->orderBy('created_at', 'desc')
                             ->get();

        return response()->json($customers);
    }

    public function store(Request $request)
    {
        // Capture the authenticated user's id
        $user = $request->user();
        $userId = $user->id;

        // Validate incoming data
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:customers,name',
            'phone_number' => 'required|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'vat_number' => 'nullable|string|max:50',
            'company_name' => 'nullable|string',
            'mobile_number' => 'nullable|string',
            'responsible_name' => 'nullable|string',
        ]);

        // Add the user_id to the validated data
        $validated['user_id'] = $userId;

        // Create the customer
        $customer = Customer::create($validated);

        // Return the created customer data
        return response()->json([
            'id' => $customer->id,
            'name' => $customer->name,
            'phone_number' => $customer->phone_number,
            'email' => $customer->email,
            'address' => $customer->address,
            'vat_number' => $customer->vat_number,
            'company_name' => $customer->company_name,
            'mobile_number' => $customer->mobile_number,
            'responsible_name' => $customer->responsible_name,
        ], 201);
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
        ]);

        $customer->update($validated);

        return response()->json([
            'id' => $customer->id,
            'name' => $customer->name,
            'phone_number' => $customer->phone_number,
            'email' => $customer->email,
            'address' => $customer->address,
            'vat_number' => $customer->vat_number,
            'company_name' => $customer->company_name,
            'mobile_number' => $customer->mobile_number,
            'responsible_name' => $customer->responsible_name,
        ]);
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
