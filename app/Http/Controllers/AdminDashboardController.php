<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Product;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $products = Product::all()->toArray();
        $stats = [
            'total_products' => Product::count(),
            'in_stock' => Product::where('stock_quantity', '>', 5)->count(),
            'low_stock' => Product::whereBetween('stock_quantity', [1, 5])->count(),
            'out_of_stock' => Product::where('stock_quantity', 0)->count(),
        ];

        return Inertia::render('Admin/AdminDashboard', [
            'products' => $products,
            'stats' => $stats,
        ]);
    }
}