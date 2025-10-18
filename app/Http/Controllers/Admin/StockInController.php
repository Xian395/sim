<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StockInController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth()->user()->role !== 'admin') {
                return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
            }
            return $next($request);
        });
        $this->middleware('log:stock_in')->only('store');
    }

    public function index()
    {
        $products = Product::with('category')
            ->select('id', 'name', 'barcode', 'price', 'stock_quantity', 'item_code', 'image_path')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'barcode' => $product->barcode,
                    'price' => $product->price,
                    'stock_quantity' => $product->stock_quantity,
                    'item_code' => $product->item_code,
                    'image_path' => $product->image_path,
                    'image_url' => Product::getImageUrl($product->image_path),
                ];
            });

        return Inertia::render('Admin/StockIn', [
            'products' => $products,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'acquisition_price' => 'required|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0.01',
            'transaction_date' => 'required|date',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // Update selling price if provided
        if (isset($validated['selling_price']) && $validated['selling_price'] > 0) {
            $product->price = $validated['selling_price'];
        }

        $product->increment('stock_quantity', $validated['quantity']);
        $product->save();

        // Create stock transaction with FIFO tracking
        StockTransaction::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'type' => 'in',
            'quantity' => $validated['quantity'],
            'acquisition_price' => $validated['acquisition_price'],
            'unit_cost' => $validated['acquisition_price'], // Store unit cost for reference
            'remaining_quantity' => $validated['quantity'], // Initialize with full quantity for FIFO
            'transaction_date' => $validated['transaction_date'],
        ]);

        $logDetails = "Added {$validated['quantity']} units of [{$product->item_code}] {$product->name} at acquisition price: ₱" . number_format($validated['acquisition_price'], 2) . "/unit.";

        if (isset($validated['selling_price']) && $validated['selling_price'] > 0) {
            $logDetails .= " Selling price set to: ₱" . number_format($validated['selling_price'], 2);
        }

        $request->session()->flash('log_details', $logDetails);

        return redirect()->back()->with('success', 'Stock added successfully.');
    }
}