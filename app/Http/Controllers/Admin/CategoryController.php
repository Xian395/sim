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

    public function index(Request $request)
    {
        $filter = $request->get('filter', 'active');

        $categories = Category::when($filter === 'active', function ($query) {
            return $query->where('status', 'active');
        })->when($filter === 'archived', function ($query) {
            return $query->where('status', 'inactive');
        })->when($filter === 'all', function ($query) {
            return $query;
        })->orderBy('name')->get();

        // Get counts for all statuses
        $activeCategoryCount = Category::where('status', 'active')->count();
        $archivedCategoryCount = Category::where('status', 'inactive')->count();
        $totalCategoryCount = Category::count();

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
            'filter' => $filter,
            'counts' => [
                'active' => $activeCategoryCount,
                'archived' => $archivedCategoryCount,
                'total' => $totalCategoryCount,
            ],
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

        $validated['status'] = 'active';
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
                'category' => 'Cannot archive category with associated products. Please remove all products from this category first.'
            ]);
        }

        $category->update(['status' => 'inactive']);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category archived successfully.');
    }

    public function toggleStatus(Category $category)
    {
        $newStatus = $category->status === 'active' ? 'inactive' : 'active';
        $category->update(['status' => $newStatus]);

        $message = $newStatus === 'active' ? 'Category activated successfully.' : 'Category deactivated successfully.';

        return redirect()->route('admin.categories.index')
            ->with('success', $message);
    }
}