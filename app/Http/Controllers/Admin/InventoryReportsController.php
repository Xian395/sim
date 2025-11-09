<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Log;
use App\Models\StockTransaction;
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
                // Get the most recent acquisition price from stock transactions
                $latestStockIn = StockTransaction::where('product_id', $product->id)
                    ->where('type', 'in')
                    ->orderBy('transaction_date', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();

                $acquisitionPrice = $latestStockIn ? $latestStockIn->acquisition_price : 0;

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'brand' => $product->brand ? $product->brand->name : 'No Brand',
                    'stock_quantity' => $product->stock_quantity,
                    'acquisition_price' => $acquisitionPrice,
                    'acquisition_total' => $product->stock_quantity * $acquisitionPrice,
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

    public function productSaleHistory($productId)
    {
        $product = Product::with('brand')->findOrFail($productId);

        // Get sales from the last 7 days only
        $oneWeekAgo = now()->subDays(7);

        // Get sale logs for this product from the last week
        $logs = Log::with('user')
            ->where('action', 'sale')
            ->where('created_at', '>=', $oneWeekAgo)
            ->orderBy('created_at', 'desc')
            ->get();

        // Filter logs that contain this product in metadata or details
        $salesHistory = $logs->filter(function ($log) use ($product) {
            // Check if product name is in details
            if (stripos($log->details, $product->name) !== false) {
                return true;
            }

            // Check if product is in metadata
            if ($log->metadata) {
                $metadata = json_decode($log->metadata, true);
                if (isset($metadata['items'])) {
                    foreach ($metadata['items'] as $item) {
                        if (isset($item['name']) && $item['name'] === $product->name) {
                            return true;
                        }
                    }
                }
            }

            return false;
        })->map(function ($log) use ($product) {
            // Extract quantity for this product
            $quantity = 0;

            if ($log->metadata) {
                $metadata = json_decode($log->metadata, true);
                if (isset($metadata['items'])) {
                    foreach ($metadata['items'] as $item) {
                        if (isset($item['name']) && $item['name'] === $product->name) {
                            $quantity = $item['quantity'] ?? 0;
                            break;
                        }
                    }
                }
            }

            // Fallback to extracting from details if not found in metadata
            if ($quantity === 0) {
                $quantity = $this->extractQuantityFromDetails($log->details);
            }

            return [
                'id' => $log->id,
                'date' => $log->created_at,
                'quantity' => $quantity,
                'brand' => $product->brand ? $product->brand->name : 'No Brand',
                'user' => $log->user ? $log->user->name : 'Unknown User',
                'details' => $log->details,
            ];
        })->values();

        return response()->json($salesHistory);
    }
}