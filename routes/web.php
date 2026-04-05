<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Vendor\DashboardController as VendorDashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\PromoCodeController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\VendorEarningController;
use App\Http\Controllers\Admin\AttributeController;

// ==================== PUBLIC ROUTES ====================
Route::get('/', function () {
    return view('welcome');
});
Route::get('/storage/{filename}', function ($filename) {
    $path = storage_path('/storage/' . $filename);

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path, [
        'Access-Control-Allow-Origin' => '*'
    ]);
});

Route::get('/clear-session', function () {
    session()->flush();
    auth()->logout();
    return 'Session cleared. Now close this window and try /admin/login again';
});

Route::get('/force-logout', function () {
    auth()->logout();
    session()->flush();
    return 'Logged out. Now try /admin/login';
});

Route::get('/session-check', function () {
    return [
        'session_id' => session()->getId(),
        'auth' => auth()->check(),
        'user' => auth()->user() ? auth()->user()->email : null,
    ];
});

Route::get('/debug-session', function () {
    return [
        'session_id' => session()->getId(),
        'session_exists' => session()->has('_token'),
        'auth_check' => auth()->check(),
        'session_driver' => config('session.driver'),
        'storage_writable' => is_writable(storage_path('framework/sessions')),
    ];
});

// ==================== AUTHENTICATION ROUTES ====================
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Convenience aliases
Route::get('/admin/login', [LoginController::class, 'showLoginForm']);
Route::get('/vendor/login', [LoginController::class, 'showLoginForm']);

// ==================== PROTECTED ADMIN ROUTES ====================
Route::middleware(['auth', 'role:admin|super-admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard - use the imported AdminDashboardController
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Products
    Route::resource('products', ProductController::class);
    Route::post('products/bulk-delete', [ProductController::class, 'bulkDelete'])->name('products.bulk-delete');
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::put('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::delete('orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
    Route::delete('products/{product}/images/{image}', [ProductController::class, 'deleteImage'])->name('products.delete-image');

    Route::resource('attributes', AttributeController::class);

    // Orders
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::put('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::delete('orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
    
    // Vendors - Add these routes
    Route::get('vendors', [VendorController::class, 'index'])->name('vendors.index');
    Route::get('vendors/{vendor}', [VendorController::class, 'show'])->name('vendors.show');
    Route::put('vendors/{vendor}/approve', [VendorController::class, 'approve'])->name('vendors.approve');
    Route::put('vendors/{vendor}/reject', [VendorController::class, 'reject'])->name('vendors.reject');
    Route::put('vendors/{vendor}/suspend', [VendorController::class, 'suspend'])->name('vendors.suspend');
    Route::put('vendors/{vendor}/activate', [VendorController::class, 'activate'])->name('vendors.activate');
    Route::delete('vendors/{vendor}', [VendorController::class, 'destroy'])->name('vendors.destroy');

    // Vendor Earnings
    Route::get('vendor-earnings', [VendorEarningController::class, 'index'])->name('vendor-earnings.index');
    Route::get('vendor-earnings/export', [VendorEarningController::class, 'export'])->name('vendor-earnings.export');
    Route::post('vendor-earnings/bulk-process', [VendorEarningController::class, 'bulkProcess'])->name('vendor-earnings.bulk-process');
    Route::get('vendor-earnings/statistics', [VendorEarningController::class, 'statistics'])->name('vendor-earnings.statistics');
    Route::get('vendor-earnings/{earning}', [VendorEarningController::class, 'show'])->name('vendor-earnings.show');
    Route::put('vendor-earnings/{earning}/process', [VendorEarningController::class, 'process'])->name('vendor-earnings.process');
    Route::get('vendors/{vendor}/earnings', [VendorEarningController::class, 'vendorEarnings'])->name('vendors.earnings');

    // Users
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::put('users/{user}/status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Categories
    Route::resource('categories', CategoryController::class);
    
    // Promo Codes
    Route::resource('promo-codes', PromoCodeController::class);
    
    // Banners
    Route::resource('banners', BannerController::class);
    
    // Reviews
    Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::put('reviews/{review}/approve', [ReviewController::class, 'approve'])->name('reviews.approve');
    Route::delete('reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    
    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('reports/export', [ReportController::class, 'export'])->name('reports.export');
});

// ==================== PROTECTED VENDOR ROUTES ====================
Route::middleware(['auth', 'role:vendor'])->prefix('vendor')->name('vendor.')->group(function () {
    Route::get('/', [VendorDashboardController::class, 'index'])->name('dashboard');
    // Add other vendor routes here
});