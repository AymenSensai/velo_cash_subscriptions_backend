<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function updateBillingDay(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'billing_day' => 'required|integer|min:1|max:31',
        ]);

        $user->update(['billing_day' => $validated['billing_day']]);

        return response()->json([
            'message' => 'Billing day updated successfully!',
            'user' => $user,
        ]);
    }

    public function profile(Request $request)
    {
        $user = $request->user();

        // Return the user's profile details including billing day

        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'billing_day' => $user->billing_day,
        ]);
    }
}
