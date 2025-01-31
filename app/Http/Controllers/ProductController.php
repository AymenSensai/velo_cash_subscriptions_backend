<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $products = $user->products()
                        ->with('category')
                        ->orderBy('created_at', 'desc')
                        ->get();

        return response()->json($products);
    }

    public function store(Request $request)
    {
        // Capture the authenticated user's account_id
        $user = $request->user();

        // Validate the incoming request data
        $validated = $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'name' => 'required|string|max:255',
            'barcode' => 'required|string|unique:products',
            'sku' => 'required|string|unique:products',
            'category_id' => 'nullable|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
        ]);

        $validated['quantity'] = intval($validated['quantity']);

        $validated['user_id'] = $user->id;

        // Handle the image upload if provided
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $uploadedImage = Cloudinary::upload($image->getRealPath(), [
                'folder' => 'products',
            ]);
            $validated['image'] = $uploadedImage->getSecurePath();
        }

        // Create the new product with the validated data
        $product = Product::create($validated);

        // Eager load the category with the created product
        $product->load('category');

        // Return the created product data as a response
        return response()->json($product, 201);
    }

    public function show(Product $product)
    {
        return $product;
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'name' => 'required|string|max:255',
            'barcode' => 'required|string|unique:products,barcode,' . $product->id,
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'category_id' => 'nullable|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) {
                $publicId = basename(parse_url($product->image, PHP_URL_PATH), '.' . pathinfo($product->image, PATHINFO_EXTENSION));
                Cloudinary::destroy($publicId);
            }

            $file = $request->file('image');
            $uploadedImage = Cloudinary::upload($file->getRealPath(), [
                'folder' => 'products',
            ]);
            $validated['image'] = $uploadedImage->getSecurePath();
        }

        $product->update($validated);

        return response()->json($product);
    }

    public function destroy(Product $product)
    {
        // Optionally, delete the image from Cloudinary
        if ($product->image) {
            $publicId = basename(parse_url($product->image, PHP_URL_PATH), '.' . pathinfo($product->image, PATHINFO_EXTENSION));
            Cloudinary::destroy($publicId);
        }

        $product->delete();

        return response()->json(null, 204);
    }

    public function restock(Request $request, Product $product)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $product->increment('quantity', $validated['quantity']);

        return response()->json($product, 200);
    }
}
