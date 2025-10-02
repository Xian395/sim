<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Log;
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

    public function stockHistory($productId)
    {
        $product = Product::findOrFail($productId);

        // Get stock in/out logs for this product
        $logs = Log::with('user')
            ->whereIn('action', ['stock_in', 'stock_out'])
            ->where('details', 'like', '%' . $product->name . '%')
            ->orderBy('created_at', 'desc')
            ->get();

        $stockHistory = $logs->map(function ($log) {
            // Extract quantity from details
            $quantity = $this->extractQuantityFromDetails($log->details);

            return [
                'id' => $log->id,
                'action' => $log->action,
                'quantity' => $quantity,
                'user' => $log->user ? $log->user->name : 'Unknown User',
                'details' => $log->details,
                'created_at' => $log->created_at,
            ];
        });

        return response()->json($stockHistory);
    }

    private function extractQuantityFromDetails($details)
    {
        // Pattern to match "Quantity: 10" or "Qty: 10" or similar
        if (preg_match('/(?:quantity|qty):\s*(\d+)/i', $details, $matches)) {
            return (int) $matches[1];
        }

        // Pattern to match "10 units" or "10 items"
        if (preg_match('/(\d+)\s*(?:units|items)/i', $details, $matches)) {
            return (int) $matches[1];
        }

        // Pattern to match numbers after product name
        if (preg_match('/\[Qty:\s*(\d+)\]/i', $details, $matches)) {
            return (int) $matches[1];
        }

        return 0;
    }
}