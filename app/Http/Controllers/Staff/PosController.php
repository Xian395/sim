<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
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
        ->select('id', 'barcode', 'name', 'stock_quantity', 'price', 'item_code', 'image_path');

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
            $productsQuery->leftJoin('category_product', 'products.id', '=', 'category_product.product_id')
                         ->leftJoin('categories', 'category_product.category_id', '=', 'categories.id')
                         ->orderBy('categories.name', $sortOrder)
                         ->orderBy('products.name', 'asc')
                         ->groupBy('products.id'); 
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

                $product->decrement('stock_quantity', $item['quantity']);

                $sale_items[] = [
                    'id' => $product->id,
                    'display_name' => "[{$product->barcode}] {$product->name}",
                    'item_code' => $product->item_code,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $subtotal,
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
}