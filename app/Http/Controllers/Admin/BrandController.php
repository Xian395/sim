<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BrandController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth()->user()->role !== 'admin') {
                return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $perPage = intval($request->input('per_page')) ?: 10;
        $page = intval($request->input('page')) ?: 1;

        // Build the base query with product count
        $query = Brand::withCount('products')->orderBy('name');

        // Get total count before pagination
        $total = $query->count();

        // Apply pagination manually
        $brands = $query->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        // Calculate pagination metadata
        $lastPage = ceil($total / $perPage);
        $from = $total === 0 ? 0 : (($page - 1) * $perPage) + 1;
        $to = min($page * $perPage, $total);

        return Inertia::render('Admin/Brands/Index', [
            'brands' => $brands,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => $lastPage,
                'from' => $from,
                'to' => $to,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Brands/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:brands,name',
            'description' => 'nullable|string',
        ]);

        $brand = Brand::create($validated);

        // Check if this is an AJAX request (for inline brand creation)
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Brand created successfully.',
                'brand' => $brand
            ]);
        }

        return redirect()->route('admin.brands.create')->with('success', 'Brand created successfully.');
    }

    public function edit(Brand $brand)
    {
        return Inertia::render('Admin/Brands/Edit', [
            'brand' => $brand,
        ]);
    }

    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,' . $brand->id,
            'description' => 'nullable|string',
        ]);

        $brand->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Brand updated successfully.',
            'brand' => $brand->fresh(),
            'redirect' => route('admin.brands.index')
        ]);
    }

    public function destroy(Brand $brand)
    {
        if ($brand->products()->count() > 0) {
            return redirect()->route('admin.brands.index')
                ->with('error', 'Cannot delete brand. It has associated products.');
        }

        $brand->delete();
        return redirect()->route('admin.brands.index')->with('success', 'Brand deleted successfully.');
    }

    public function products(Brand $brand)
    {
        $products = $brand->products()
            ->with('categories')
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'item_code' => $product->item_code,
                    'price' => $product->price,
                    'stock_quantity' => $product->stock_quantity,
                    'description' => $product->description,
                    'image_path' => $product->image_path,
                    'image_url' => $product->image_path ? \App\Models\Product::getImageUrl($product->image_path) : null,
                    'categories' => $product->categories,
                ];
            });

        return response()->json([
            'products' => $products
        ]);
    }
}
