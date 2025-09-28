<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StockOutController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth()->user()->role !== 'admin') {
                return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
            }
            return $next($request);
        });
        $this->middleware('log:stock_out')->only('store');
    }

    public function index()
    {
        $products = Product::with('category')
            ->select('id', 'name', 'barcode', 'stock_quantity', 'item_code', 'image_path')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'barcode' => $product->barcode,
                    'stock_quantity' => $product->stock_quantity,
                    'item_code' => $product->item_code,
                    'image_path' => $product->image_path,
                    'image_url' => Product::getImageUrl($product->image_path),
                ];
            });

        return Inertia::render('Admin/StockOut', [
            'products' => $products,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'transaction_date' => 'required|date',
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($product->stock_quantity < $validated['quantity']) {
            return redirect()->back()->with('error', 'Insufficient stock. Available: ' . $product->stock_quantity);
        }

        $product->decrement('stock_quantity', $validated['quantity']);

        StockTransaction::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'type' => 'out',
            'quantity' => $validated['quantity'],
            'reason' => $validated['reason'],
            'notes' => $validated['notes'] ?? null,
            'transaction_date' => $validated['transaction_date'],
        ]);

        $request->session()->flash('log_details', "Removed {$validated['quantity']} units of [{$product->barcode}] {$product->name} (Reason: {$validated['reason']}).");

        return redirect()->back()->with('success', 'Stock removed successfully.');
    }
}