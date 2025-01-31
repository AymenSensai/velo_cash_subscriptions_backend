<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $categories = $user->categories;

        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Get the authenticated user
        $user = $request->user();

        // Create the category and associate it with the user
        $category = $user->categories()->create([
            'name' => $validatedData['name'],
        ]);

        // Return a response with the created category
        return response()->json($category, 201);
    }

    public function show(Request $request, Category $category)
    {
        // Ensure the category belongs to the user's account
        $user = $request->user();
        if ($category->account_id !== $user->account_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($category);
    }

    public function update(Request $request, Category $category)
    {
        $user = $request->user();

        if ($category->account_id !== $user->account_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ]);

        $category->update($validated);

        return response()->json($category);
    }

    public function destroy(Request $request, Category $category)
    {
        $user = $request->user();

        if ($category->account_id !== $user->account_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $category->delete();

        return response()->json(null, 204);
    }
}
