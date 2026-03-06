<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * GET /api/products
     * Supports: ?search=, ?category=, ?sort_by=name|price|stock, ?sort_dir=asc|desc
     */
    public function index(Request $request)
    {
        $query = Product::query();

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by category
        if ($request->filled('category') && $request->category !== 'All') {
            $query->where('category', $request->category);
        }

        // Sorting
        $sortBy  = in_array($request->sort_by, ['name', 'price', 'stock', 'category', 'created_at'])
            ? $request->sort_by : 'created_at';
        $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortDir);

        $products = $query->get();

        return response()->json(['products' => $products], 200);
    }

    /**
     * POST /api/products
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'stock'    => 'required|integer|min:0',
            'price'    => 'required|numeric|min:0',
            'img'      => 'nullable|string|max:10',
        ]);

        $product = Product::create([
            'name'     => $request->name,
            'category' => $request->category,
            'stock'    => $request->stock,
            'price'    => $request->price,
            'img'      => $request->img ?? '📦',
            'trend'    => '+0%',
        ]);

        return response()->json(['message' => 'Product added!', 'product' => $product], 201);
    }

    /**
     * GET /api/products/{id}
     */
    public function show($id)
    {
        $product = Product::findOrFail($id);
        return response()->json(['product' => $product], 200);
    }

    /**
     * PUT /api/products/{id}
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name'     => 'sometimes|string|max:255',
            'category' => 'sometimes|string|max:100',
            'stock'    => 'sometimes|integer|min:0',
            'price'    => 'sometimes|numeric|min:0',
            'img'      => 'nullable|string|max:10',
            'trend'    => 'nullable|string|max:20',
        ]);

        $product->update($request->only(['name', 'category', 'stock', 'price', 'img', 'trend']));

        return response()->json(['message' => 'Product updated!', 'product' => $product], 200);
    }

    /**
     * DELETE /api/products/{id}
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Product deleted.'], 200);
    }
}
