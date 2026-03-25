<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Vendor\DashboardController as VendorDashboardController;
use Illuminate\Support\Facades\Auth;
// ... other imports

// ==================== PUBLIC ROUTES ====================
Route::get('/', function () {
    return view('welcome');
});
Route::get('/clear-session', function () {
    session()->flush();
    auth()->logout();
    return 'Session cleared. Now close this window and try /admin/login again';
});

// ==================== AUTHENTICATION ROUTES ====================
// Single login page (used by both admin and vendor)
// Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
// Optional aliases for convenience
// Route::get('/admin/login', [LoginController::class, 'showLoginForm']);
// Route::get('/vendor/login', [LoginController::class, 'showLoginForm']);
// Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
// Route::post('/login', [LoginController::class, 'login']);
Route::get('/admin/login', [LoginController::class, 'showLoginForm']);
Route::get('/vendor/login', [LoginController::class, 'showLoginForm']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// POST login submission
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ==================== PROTECTED ADMIN ROUTES ====================
Route::middleware(['auth', 'role:admin|super-admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    // ... other admin routes
});

// ==================== PROTECTED VENDOR ROUTES ====================
Route::middleware(['auth', 'role:vendor'])->prefix('vendor')->name('vendor.')->group(function () {
    Route::get('/', [VendorDashboardController::class, 'index'])->name('dashboard');
    // ... other vendor routes
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
Route::get('/force-logout', function () {
    auth()->logout();
    session()->flush();
    return 'Logged out. Now try /admin/login';
});