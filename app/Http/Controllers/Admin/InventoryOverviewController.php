<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\Product;

class InventoryOverviewController extends Controller
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
        $products = Product::all()->toArray();
        $stats = [
            'total_products' => Product::count(),
            'in_stock' => Product::where('stock_quantity', '>', 5)->count(),
            'low_stock' => Product::whereBetween('stock_quantity', [1, 5])->count(),
            'out_of_stock' => Product::where('stock_quantity', 0)->count(),
        ];

        return Inertia::render('Admin/InventoryOverview', [
            'products' => $products,
            'stats' => $stats,
        ]);
    }
}