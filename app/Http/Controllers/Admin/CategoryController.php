<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller; 
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
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
        $categories = Category::all();
        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Categories/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        Category::create($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        return Inertia::render('Admin/Categories/Edit', [
            'category' => $category,
        ]);
    }

 public function update(Request $request, Category $category)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
    ]);

    $category->update($validated);

    if ($request->expectsJson()) {
        return response()->json([
            'message' => 'Category updated successfully.',
            'category' => $category
        ]);
    }

    return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
}

 public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            throw ValidationException::withMessages([
                'category' => 'Cannot delete category with associated products. Please remove all products from this category first.'
            ]);
        }

        $category->delete();
        
        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}