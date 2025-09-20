<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\Log;
use Illuminate\Support\Facades\DB;

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

        // Get product sales data for chart from logs table
        $productSalesData = $this->getProductSalesFromLogs();

        // Debug: Log the sales data
        \Log::info('Product Sales Data:', $productSalesData);

        return Inertia::render('Admin/AdminDashboard', [
            'products' => $products,
            'stats' => $stats,
            'productSalesData' => $productSalesData,
        ]);
    }

    private function getProductSalesFromLogs()
    {
        // Get all sales logs
        $salesLogs = Log::where('action', 'sale')->get();

        // Get all products to match against
        $products = Product::all();
        $productSales = [];

        // Initialize product sales counters
        foreach ($products as $product) {
            $productSales[$product->name] = [
                'id' => $product->id,
                'name' => $product->name,
                'total_sales' => 0
            ];
        }

        // Parse sales logs to extract product quantities
        foreach ($salesLogs as $log) {
            $details = $log->details;

            // Parse the sale details format: "Processed sale: Test [Qty: 1], SkyFlakes Craker [Qty: 1]"
            // or similar patterns
            foreach ($products as $product) {
                $productName = $product->name;

                // Look for patterns like "ProductName [Qty: X]" or "ProductName (Qty: X)"
                $patterns = [
                    "/{$productName}\s*\[Qty:\s*(\d+)\]/i",
                    "/{$productName}\s*\(Qty:\s*(\d+)\)/i",
                    "/{$productName}\s*-\s*(\d+)/i",
                    "/{$productName}\s*:\s*(\d+)/i"
                ];

                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $details, $matches)) {
                        $quantity = (int) $matches[1];
                        $productSales[$productName]['total_sales'] += $quantity;
                        break; // Found a match for this product, move to next
                    }
                }
            }
        }

        // Filter out products with zero sales and sort by total sales
        $filteredSales = array_filter($productSales, function($item) {
            return $item['total_sales'] > 0;
        });

        // Sort by total sales descending and limit to top 10
        usort($filteredSales, function($a, $b) {
            return $b['total_sales'] - $a['total_sales'];
        });

        return array_slice($filteredSales, 0, 10);
    }
}