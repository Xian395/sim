<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Application;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Staff\PosController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\StockInController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\StockOutController;
use App\Http\Controllers\Admin\InventoryReportsController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\StaffDashboardController;

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });

// Route::get('/', function () {
//     return redirect()->route('login');
// });


Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            default => redirect()->route('staff.pos'),
        };
    }

    return Inertia::render('Auth/Login', [
        'canResetPassword' => true,
        'status' => session('status'),
    ]);
});




Route::middleware(['auth', 'verified'])->group(function () {
    // Route::get('/dashboard', function () {
    //     return inertia('Dashboard');
    // })->name('dashboard');

    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/staff/dashboard', [StaffDashboardController::class, 'index'])->name('staff.dashboard');

    // API endpoints for staff management
    Route::get('/api/staff-transactions/{userId}', [LogController::class, 'getStaffTransactions'])->name('api.staff-transactions');
    Route::get('/api/stock-transaction-logs', [LogController::class, 'getStockTransactionLogs'])->name('api.stock-transaction-logs');
    Route::post('/api/activity-logs/delete-multiple', [LogController::class, 'deleteMultipleLogs'])->name('api.activity-logs.delete-multiple');



      Route::prefix('admin')->name('admin.')->group(function () {

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::get('/users/{user}/transactions', [UserController::class, 'transactions'])->name('users.transactions');




        Route::get('/stock-in', [StockInController::class, 'index'])->name('stock-in.index');
        Route::post('/stock-in', [StockInController::class, 'store'])->name('stock-in.store');
        Route::get('/stock-out', [StockOutController::class, 'index'])->name('stock-out.index');
        Route::post('/stock-out', [StockOutController::class, 'store'])->name('stock-out.store');



      Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        


        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        Route::patch('/categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');

        Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
        Route::get('/brands/create', [BrandController::class, 'create'])->name('brands.create');
        Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
        Route::get('/brands/{brand}/edit', [BrandController::class, 'edit'])->name('brands.edit');
        Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
        Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy');
        Route::get('/brands/{brand}/products', [BrandController::class, 'products'])->name('brands.products');



         Route::get('/reports', [LogController::class, 'index'])->name('logs.index');
         Route::get('/activity-logs', [LogController::class, 'activityLogs'])->name('activity-logs.index');

         // API endpoints for reports
         Route::get('/api/sales-report', [LogController::class, 'salesReport'])->name('api.sales-report');
         Route::get('/api/brand-sales-report', [LogController::class, 'brandSalesReport'])->name('api.brand-sales-report');
         Route::get('/api/income-report', [LogController::class, 'incomeReport'])->name('api.income-report');
         Route::get('/api/staff-sales-report', [LogController::class, 'staffSalesReport'])->name('api.staff-sales-report');
         Route::get('/api/products-list', [LogController::class, 'getProductsList'])->name('api.products-list');

         Route::get('/inventory-reports', [InventoryReportsController::class, 'index'])->name('inventory-reports.index');
         Route::get('/inventory-reports/report', [InventoryReportsController::class, 'inventoryReport'])->name('inventory-reports.report');
         Route::get('/inventory-reports/stock-history/{productId}', [InventoryReportsController::class, 'stockHistory'])->name('inventory-reports.stock-history');
         Route::get('/inventory-reports/sale-history/{productId}', [InventoryReportsController::class, 'productSaleHistory'])->name('inventory-reports.sale-history');

        Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
        Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');

    });

        Route::prefix('staff')->middleware('pos')->name('staff.')->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('pos');
        Route::post('/pos/store', [PosController::class, 'store'])->middleware('log:sale')->name('pos.store');

         Route::get('/pos/suggestions', [PosController::class, 'suggestions'])->name('pos.suggestions');
        Route::post('/pos/quick-add', [PosController::class, 'quickAdd'])->name('pos.quick-add');
    
        // Route::post('/pos/scan', [PosController::class, 'scanBarcode'])->name('pos.scan');
    });


});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
