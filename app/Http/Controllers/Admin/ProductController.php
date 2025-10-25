<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProductController extends Controller
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

    public function index()
    {
        $products = Product::with(['categories', 'brand'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'item_code' => $product->item_code,
                    'name' => $product->name,
                    'barcode' => $product->barcode,
                    'categories' => $product->categories->map(function($category) {
                        return [
                            'id' => $category->id,
                            'name' => $category->name,
                            'status' => $category->status
                        ];
                    })->toArray(),
                    'category' => $product->categories->first() ? [
                        'id' => $product->categories->first()->id,
                        'name' => $product->categories->first()->name
                    ] : null,
                    'brand' => $product->brand ? [
                        'id' => $product->brand->id,
                        'name' => $product->brand->name
                    ] : null,
                    'price' => $product->price,
                    'stock_quantity' => $product->stock_quantity,
                    'description' => $product->description,
                    'image_url' => $product->image_path ? \App\Models\Product::getImageUrl($product->image_path) : null,
                    'lastUpdated' => $product->updated_at->diffForHumans(),
                ];
            });

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'categories' => Category::where('status', 'active')->orderBy('name')->get(),
            'allCategories' => Category::orderBy('name')->get(), // For display of existing inactive categories
            'brands' => Brand::all(),
        ]);
    }

    public function create()
    {
        $categories = Category::where('status', 'active')->orderBy('name')->get();
        $brands = Brand::all();
        return Inertia::render('Admin/Products/Create', [
            'categories' => $categories,
            'brands' => $brands,
        ]);
    }

  public function store(Request $request)
{
    // Get active category IDs for validation
    $activeCategoryIds = Category::where('status', 'active')->pluck('id')->toArray();

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'item_code' => 'required|string|max:20|unique:products,item_code',
        'price' => 'nullable|numeric|min:0.01',
        'category_ids' => 'required|array|min:1',
        'category_ids.*' => 'exists:categories,id',
        'brand_id' => 'nullable|exists:brands,id',
        'description' => 'nullable|string',
        'productimage' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
    ]);

    // Additional validation to ensure only active categories are selected
    $invalidCategories = array_diff($validated['category_ids'], $activeCategoryIds);
    if (!empty($invalidCategories)) {
        return back()->withErrors([
            'category_ids' => 'One or more selected categories are inactive and cannot be used.'
        ]);
    }

    $imagePath = null;
    if ($request->hasFile('productimage')) {
        $disk = config('app.env') === 'local' ? 'public' : 's3';
        $imagePath = $request->file('productimage')->store('products', $disk);
    }

    $validated['barcode'] = $this->generateBarcodeFromItemCode($validated['item_code']);
    $validated['image_path'] = $imagePath;

    $categoryIds = $validated['category_ids'];
    unset($validated['category_ids'], $validated['productimage']);

    $product = Product::create($validated);

    $product->categories()->attach($categoryIds);

    return redirect()->route('admin.products.create')->with('success', 'Product created successfully.');
}

    public function edit(Product $product)
    {
        $categories = Category::where('status', 'active')->orderBy('name')->get();
        $brands = Brand::all();
        $product->load(['categories', 'brand']);

        // Check if product has any inactive categories
        $inactiveCategories = $product->categories()->where('status', 'inactive')->get();

        // Format product data with proper image URL
        $productData = [
            'id' => $product->id,
            'item_code' => $product->item_code,
            'name' => $product->name,
            'barcode' => $product->barcode,
            'price' => $product->price,
            'stock_quantity' => $product->stock_quantity,
            'description' => $product->description,
            'image_path' => $product->image_path,
            'image_url' => $product->image_path ? \App\Models\Product::getImageUrl($product->image_path) : null,
            'brand_id' => $product->brand_id,
            'categories' => $product->categories,
            'brand' => $product->brand,
        ];

        return Inertia::render('Admin/Products/Edit', [
            'product' => $productData,
            'categories' => $categories,
            'brands' => $brands,
            'inactiveCategories' => $inactiveCategories,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        // Get active category IDs for validation
        $activeCategoryIds = Category::where('status', 'active')->pluck('id')->toArray();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'item_code' => 'required|string|max:20|unique:products,item_code,' . $product->id,
            'price' => 'nullable|numeric|min:0.01',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'description' => 'nullable|string',
            'productimage' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Additional validation to ensure only active categories are selected
        $invalidCategories = array_diff($validated['category_ids'], $activeCategoryIds);
        if (!empty($invalidCategories)) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'category_ids' => ['One or more selected categories are inactive and cannot be used.']
                ]
            ], 422);
        }

        if ($validated['item_code'] !== $product->item_code) {
            $validated['barcode'] = $this->generateBarcodeFromItemCode($validated['item_code']);
        }

        if ($request->hasFile('productimage')) {
            if ($product->image_path) {
                $disk = config('app.env') === 'local' ? 'public' : 's3';
                Storage::disk($disk)->delete($product->image_path);
            }

            $disk = config('app.env') === 'local' ? 'public' : 's3';
            $validated['image_path'] = $request->file('productimage')->store('products', $disk);
        }

        $categoryIds = $validated['category_ids'];
        unset($validated['category_ids'], $validated['productimage']);

        $product->update($validated);

        $product->categories()->sync($categoryIds);

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        // Delete associated image file
        if ($product->image_path) {
            $disk = config('app.env') === 'local' ? 'public' : 's3';
            Storage::disk($disk)->delete($product->image_path);
        }

        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }

    private function generateBarcodeFromItemCode($itemCode)
    {
        $cleanItemCode = strtoupper(str_replace(' ', '', $itemCode));

        $numericCode = $this->convertItemCodeToNumeric($cleanItemCode);

        $barcodeBase = str_pad(substr($numericCode, 0, 12), 12, '0', STR_PAD_LEFT);

        $checkDigit = $this->calculateEAN13CheckDigit($barcodeBase);

        return $barcodeBase . $checkDigit;
    }

    private function convertItemCodeToNumeric($itemCode)
    {
        $numeric = '';

        for ($i = 0; $i < strlen($itemCode); $i++) {
            $char = $itemCode[$i];

            if (is_numeric($char)) {
                $numeric .= $char;
            } else {
                $numeric .= str_pad((ord($char) - 64), 2, '0', STR_PAD_LEFT);
            }
        }

        if (strlen($numeric) > 12) {
            $hash = hash('crc32', $itemCode);
            $numeric = substr(str_pad($hash, 12, '0', STR_PAD_LEFT), 0, 12);
        }

        return $numeric;
    }

    private function calculateEAN13CheckDigit($barcode)
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $digit = (int) $barcode[$i];
            $sum += ($i % 2 === 0) ? $digit : $digit * 3;
        }

        $checkDigit = (10 - ($sum % 10)) % 10;
        return $checkDigit;
    }
}
