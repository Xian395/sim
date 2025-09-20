<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Brand;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryReportsController extends Controller
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
        return Inertia::render('Admin/InventoryReports/Index');
    }

    public function inventoryReport(Request $request)
    {
        $products = Product::with('brand')
            ->select('id', 'name', 'stock_quantity', 'price', 'brand_id')
            ->orderBy('stock_quantity', 'asc')
            ->get();

        $totalProducts = $products->count();
        $totalValue = $products->sum(function ($product) {
            return $product->stock_quantity * $product->price;
        });

        $lowStockProducts = $products->where('stock_quantity', '<=', 10);
        $outOfStockProducts = $products->where('stock_quantity', '<=', 0);

        return response()->json([
            'products' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'brand' => $product->brand ? $product->brand->name : 'No Brand',
                    'stock_quantity' => $product->stock_quantity,
                    'price' => $product->price,
                    'value' => $product->stock_quantity * $product->price,
                    'status' => $product->stock_quantity <= 0 ? 'Out of Stock' :
                               ($product->stock_quantity <= 10 ? 'Low Stock' : 'In Stock'),
                ];
            }),
            'summary' => [
                'totalProducts' => $totalProducts,
                'totalValue' => $totalValue,
                'lowStockCount' => $lowStockProducts->count(),
                'outOfStockCount' => $outOfStockProducts->count(),
            ]
        ]);
    }
}