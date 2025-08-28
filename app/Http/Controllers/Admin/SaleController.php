<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SaleController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth()->user()->role !== 'admin') {
                return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
            }
            return $next($request);
        });
        $this->middleware('log:sale')->only('store');
    }

    public function index()
    {
        return Inertia::render('Admin/Sales/Index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string',
            'quantity' => 'required|integer|min:1',
        ]);

        $request->session()->flash('log_details', "Sold {$validated['quantity']} units of {$validated['product_name']}.");

        return redirect()->back()->with('success', 'Sale recorded successfully.');
    }
}