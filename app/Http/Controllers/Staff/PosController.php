<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\StockTransaction;
use App\Services\FifoService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PosController extends Controller
{
 public function index(Request $request)
{
    $query = $request->input('query', '');
    $sortBy = $request->input('sort', 'name'); 
    $sortOrder = $request->input('order', 'asc'); 
    $categoryFilter = $request->input('category', ''); 
    $stockFilter = $request->input('stock', 'all'); 

    $productsQuery = Product::with('categories')
        ->select('products.id', 'products.barcode', 'products.name', 'products.stock_quantity', 'products.price', 'products.item_code', 'products.image_path');

    if (!empty($query)) {
        $productsQuery->where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('barcode', 'like', "%{$query}%")
              ->orWhere('item_code', 'like', "%{$query}%")
              ->orWhereHas('categories', function ($categoryQuery) use ($query) {
                  $categoryQuery->where('name', 'like', "%{$query}%");
              });
        });
    }

    if (!empty($categoryFilter) && $categoryFilter !== 'all') {
        $productsQuery->whereHas('categories', function ($q) use ($categoryFilter) {
            $q->where('categories.id', $categoryFilter);
        });
    }

    switch ($stockFilter) {
        case 'in_stock':
            $productsQuery->where('stock_quantity', '>', 5);
            break;
        case 'low_stock':
            $productsQuery->whereBetween('stock_quantity', [1, 5]);
            break;
        case 'out_of_stock':
            $productsQuery->where('stock_quantity', 0);
            break;
        case 'available':
            $productsQuery->where('stock_quantity', '>', 0);
            break;
        default:
            break;
    }

    switch ($sortBy) {
        case 'name':
            $productsQuery->orderBy('name', $sortOrder);
            break;
        case 'price':
            $productsQuery->orderBy('price', $sortOrder);
            break;
        case 'stock':
            $productsQuery->orderBy('stock_quantity', $sortOrder);
            break;
        case 'category':
            $productsQuery->leftJoin('product_categories', 'products.id', '=', 'product_categories.product_id')
                         ->leftJoin('categories', 'product_categories.category_id', '=', 'categories.id')
                         ->groupBy('products.id')
                         ->orderBy(DB::raw('MIN(categories.name)'), $sortOrder)
                         ->orderBy('products.name', 'asc');
            break;
        case 'item_code':
            $productsQuery->orderBy('item_code', $sortOrder);
            break;
        case 'barcode':
            $productsQuery->orderBy('barcode', $sortOrder);
            break;
        default:
            $productsQuery->orderBy('name', 'asc');
            break;
    }

    $products = $productsQuery->get()->map(function ($product) {
        $product->display_name = "[{$product->barcode}] {$product->name}";

        if ($product->stock_quantity == 0) {
            $product->stock_status = 'out_of_stock';
        } elseif ($product->stock_quantity <= 5) {
            $product->stock_status = 'low_stock';
        } else {
            $product->stock_status = 'in_stock';
        }

        $product->category_names = $product->categories->pluck('name')->join(', ');
        $product->image_url = $product->image_path ? \App\Models\Product::getImageUrl($product->image_path) : null;

        return $product;
    });

    $categories = Category::select('id', 'name')
                         ->orderBy('name')
                         ->get();

    $stats = [
        'total_products' => Product::count(),
        'in_stock' => Product::where('stock_quantity', '>', 5)->count(),
        'low_stock' => Product::whereBetween('stock_quantity', [1, 5])->count(),
        'out_of_stock' => Product::where('stock_quantity', 0)->count(),
    ];

    return Inertia::render('Staff/Sales/Pos', [
        'products' => $products,
        'categories' => $categories,
        'stats' => $stats,
        'filters' => [
            'query' => $query,
            'sort' => $sortBy,
            'order' => $sortOrder,
            'category' => $categoryFilter,
            'stock' => $stockFilter,
        ],
    ]);
}



    public function store(Request $request)
    {
        $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'payment_amount' => ['sometimes', 'numeric', 'min:0'],
            'change_amount' => ['sometimes', 'numeric'],
        ]);

        $items = $request->input('items');
        $paymentAmount = $request->input('payment_amount');
        $changeAmount = $request->input('change_amount');
        $total = 0;
        $sale_items = [];
        $product_names = []; 

        DB::beginTransaction();
        try {
            foreach ($items as $item) {
                $product = Product::findOrFail($item['id']);
                if ($product->stock_quantity < $item['quantity']) {
                    return back()->with('error', "Insufficient stock for [{$product->barcode}] {$product->name}. Available: {$product->stock_quantity}");
                }

                $subtotal = $product->price * $item['quantity'];
                $total += $subtotal;

                // Apply FIFO stock deduction
                $fifoResult = FifoService::deductStock($product->id, $item['quantity']);

                // Decrement the product's stock quantity
                $product->decrement('stock_quantity', $item['quantity']);

                // Create stock-out transaction with FIFO batch allocations
                StockTransaction::create([
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'type' => 'out',
                    'quantity' => $item['quantity'],
                    'unit_cost' => $fifoResult['average_cost'],
                    'batch_allocations' => $fifoResult['batch_allocations'],
                    'reason' => 'Sale',
                    'transaction_date' => now()->toDateString(),
                ]);

                $sale_items[] = [
                    'id' => $product->id,
                    'display_name' => "[{$product->barcode}] {$product->name}",
                    'item_code' => $product->item_code,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $subtotal,
                    'cost' => $fifoResult['total_cost'],
                    'profit' => $subtotal - $fifoResult['total_cost'],
                ];

                $product_names[] = "{$product->name} [Qty: {$item['quantity']}]";
            }

            if ($paymentAmount !== null && $paymentAmount < $total) {
                return back()->with('error', 'Payment amount is insufficient. Required: PHP ' . number_format($total, 2) . ', Received: ₱' . number_format($paymentAmount, 2));
            }

            $products_log = implode(', ', $product_names);
            $payment_log = $paymentAmount ? " - Payment: PHP " . number_format($paymentAmount, 2) : "";
            $change_log = ($changeAmount && $changeAmount > 0) ? " - Change: PHP " . number_format($changeAmount, 2) : "";
            
            $request->session()->put('log_details', "Processed sale: {$products_log} - Total: PHP " . number_format($total, 2) . $payment_log . $change_log);

            $receipt_id = 'RCPT-' . strtoupper(uniqid());
            $sale_date = Carbon::now()->format('Y-m-d H:i:s');
            $staff_name = auth()->user()->name;

            DB::commit();

            $receiptData = [
                'receipt_id' => $receipt_id,
                'sale_date' => $sale_date,
                'staff_name' => $staff_name,
                'items' => $sale_items,
                'total' => $total,
            ];

            if ($paymentAmount !== null) {
                $receiptData['payment_amount'] = $paymentAmount;
                if ($changeAmount !== null) {
                    $receiptData['change_amount'] = $changeAmount;
                }
            }

            $successMessage = 'Sale processed successfully. Total: PHP ' . number_format($total, 2);
            if ($paymentAmount !== null) {
                $successMessage .= ' | Payment: PHP ' . number_format($paymentAmount, 2);
                if ($changeAmount && $changeAmount > 0) {
                    $successMessage .= ' | Change: PHP ' . number_format($changeAmount, 2);
                }
            }

            return back()->with([
                'success' => $successMessage,
                'receipt_data' => $receiptData,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to process sale: ' . $e->getMessage());
        }
    }

    public function suggestions(Request $request)
    {
        $query = $request->input('query', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $suggestions = Product::with('categories')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('barcode', 'like', "%{$query}%")
                  ->orWhere('item_code', 'like', "%{$query}%")
                  ->orWhereHas('categories', function ($categoryQuery) use ($query) {
                      $categoryQuery->where('name', 'like', "%{$query}%");
                  });
            })
            ->select('id', 'name', 'barcode', 'item_code', 'price', 'stock_quantity')
            ->where('stock_quantity', '>', 0)
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'barcode' => $product->barcode,
                    'item_code' => $product->item_code,
                    'display_name' => "[{$product->barcode}] {$product->name}",
                    'price' => $product->price,
                    'stock_quantity' => $product->stock_quantity,
                    'categories' => $product->categories->pluck('name')->toArray(),
                ];
            });

        return response()->json($suggestions);
    }

    public function quickAdd(Request $request)
    {
        $request->validate([
            'barcode' => ['required', 'string'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
        ]);

        $barcode = $request->input('barcode');
        $quantity = $request->input('quantity', 1);

        $product = Product::with('categories')
            ->where('barcode', $barcode)
            ->orWhere('item_code', $barcode)
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found with barcode: ' . $barcode
            ], 404);
        }

        if ($product->stock_quantity < $quantity) {
            return response()->json([
                'success' => false,
                'message' => "Insufficient stock for {$product->name}. Available: {$product->stock_quantity}"
            ], 400);
        }

        $product->display_name = "[{$product->barcode}] {$product->name}";

        return response()->json([
            'success' => true,
            'product' => $product,
            'message' => "Added {$product->name} to cart"
        ]);
    }

    public function transactions(Request $request)
    {
        $userId = auth()->id();
        $page = $request->input('page', 1);
        $perPage = intval($request->input('per_page')) ?: 10;
        $search = $request->input('search', '');
        $period = $request->input('period', 'all');

        // Build date range based on period
        $dateRange = $this->getDateRange($period);

        $query = \App\Models\Log::where('action', 'sale')
            ->where('user_id', $userId)
            ->when($search, function ($q) use ($search) {
                return $q->where('details', 'like', "%{$search}%");
            });

        // Apply date filter if not 'all'
        if ($period !== 'all') {
            $query->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
        }

        $query->orderBy('created_at', 'desc');
        $total = $query->count();

        // Get all sales for summary calculation (not paginated)
        $allSalesQuery = \App\Models\Log::where('action', 'sale')
            ->where('user_id', $userId);

        if ($period !== 'all') {
            $allSalesQuery->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
        }

        $allSalesQuery->orderBy('created_at', 'desc');
        $allSalesLogs = $allSalesQuery->get();

        // Calculate summary
        $summary = $this->calculateSalesSummary($allSalesLogs);

        $logs = $query->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        $transactions = $logs->map(function ($sale) {
            $details = $sale->details;
            $products = [];
            $totalAmount = 0;
            $paymentAmount = 0;
            $changeAmount = 0;

            // Extract products with quantities using regex
            if (preg_match_all('/(.+?)\s*\[Qty:\s*(\d+)\]/', $details, $matches)) {
                for ($i = 0; $i < count($matches[0]); $i++) {
                    $products[] = [
                        'name' => trim($matches[1][$i]),
                        'qty' => (int)$matches[2][$i],
                    ];
                }
            }

            // Extract Total amount
            if (preg_match('/Total:\s*PHP\s*([\d,]+(?:\.\d{2})?)/i', $details, $match)) {
                $totalAmount = (float)str_replace(',', '', $match[1]);
            }

            // Extract Payment amount
            if (preg_match('/Payment:\s*PHP\s*([\d,]+(?:\.\d{2})?)/i', $details, $match)) {
                $paymentAmount = (float)str_replace(',', '', $match[1]);
            } else {
                $paymentAmount = $totalAmount;
            }

            // Extract Change amount
            if (preg_match('/Change:\s*PHP\s*([\d,]+(?:\.\d{2})?)/i', $details, $match)) {
                $changeAmount = (float)str_replace(',', '', $match[1]);
            }

            return [
                'id' => $sale->id,
                'products' => $products,
                'total_amount' => $totalAmount,
                'payment_amount' => $paymentAmount,
                'change_amount' => $changeAmount,
                'created_at' => $sale->created_at,
            ];
        });

        $lastPage = ceil($total / $perPage);
        $from = $total === 0 ? 0 : (($page - 1) * $perPage) + 1;
        $to = min($page * $perPage, $total);

        return Inertia::render('Staff/Transactions/Index', [
            'transactions' => $transactions,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => $lastPage,
                'from' => $from,
                'to' => $to,
            ],
            'search' => $search,
            'summary' => $summary,
            'period' => $period,
        ]);
    }

    private function getDateRange($period)
    {
        $now = now();

        switch ($period) {
            case 'daily':
                return [
                    'start' => $now->clone()->startOfDay(),
                    'end' => $now->clone()->endOfDay(),
                ];
            case 'weekly':
                return [
                    'start' => $now->clone()->startOfWeek(),
                    'end' => $now->clone()->endOfWeek(),
                ];
            case 'monthly':
                return [
                    'start' => $now->clone()->startOfMonth(),
                    'end' => $now->clone()->endOfMonth(),
                ];
            default:
                return [
                    'start' => null,
                    'end' => null,
                ];
        }
    }

    private function calculateSalesSummary($logs)
    {
        $totalSales = 0;
        $totalTransactions = 0;
        $totalItemsSold = 0;

        foreach ($logs as $sale) {
            $details = $sale->details;

            // Extract and sum total amounts
            if (preg_match('/Total:\s*PHP\s*([\d,]+(?:\.\d{2})?)/i', $details, $match)) {
                $totalSales += (float)str_replace(',', '', $match[1]);
                $totalTransactions++;
            }

            // Count total items sold
            if (preg_match_all('/\[Qty:\s*(\d+)\]/', $details, $matches)) {
                foreach ($matches[1] as $qty) {
                    $totalItemsSold += (int)$qty;
                }
            }
        }

        $averagePerTransaction = $totalTransactions > 0 ? $totalSales / $totalTransactions : 0;

        return [
            'totalSales' => $totalSales,
            'totalTransactions' => $totalTransactions,
            'totalItemsSold' => $totalItemsSold,
            'averagePerTransaction' => $averagePerTransaction,
        ];
    }
}