<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CategoryAttributeController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\PromoCodeController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SubscriptionPlanController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\RazorpayWebhookController;
use App\Http\Controllers\Api\VendorEarningController;
use App\Http\Controllers\Api\AttributeGroupController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Razorpay webhook - Public (outside v1 prefix)
Route::post('razorpay/webhook', [RazorpayWebhookController::class, 'handle'])->name('razorpay.webhook');

Route::prefix('v1')->group(function () {
    
    // ==================== PUBLIC ROUTES ====================
    
    // Auth routes
    Route::post('auth/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('auth/verify-otp', [AuthController::class, 'verifyOtp']);
    
    // Products
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{product}', [ProductController::class, 'show']);
    Route::get('products/{product}/variants', [ProductController::class, 'variants']);
    
    // Categories
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{category}', [CategoryController::class, 'show']);
    Route::get('categories/{category}/products', [CategoryController::class, 'products']);
    Route::get('categories/{category}/attributes', [CategoryAttributeController::class, 'index']);
    Route::get('categories/{category}/attribute-groups', [AttributeGroupController::class, 'getByCategory']);
    
    // Banners
    Route::get('banners', [BannerController::class, 'index']);
    
    // Attribute groups - Public
    Route::get('attribute-groups', [AttributeGroupController::class, 'index']);
    Route::get('attribute-groups/{attributeGroup}', [AttributeGroupController::class, 'show']);
    
    // Vendors - Public
    Route::get('vendors', [VendorController::class, 'index']);
    Route::get('vendors/{vendor}', [VendorController::class, 'show']);
    Route::get('vendors/{vendor}/page', [VendorController::class, 'storePage']);
    
    // Promo code validation - Public
    Route::post('promo-codes/validate', [PromoCodeController::class, 'validatePromoCode']);
    
    // Questions - Public
    Route::get('products/{product}/questions', [QuestionController::class, 'index']);
    Route::get('questions/{question}', [QuestionController::class, 'show']);
    
    // ==================== AUTHENTICATED ROUTES ====================
    Route::middleware('auth:sanctum')->group(function () {
        
        // User profile
        Route::get('user', [AuthController::class, 'me']);
        Route::put('user/profile', [AuthController::class, 'updateProfile']);
        Route::post('user/logout', [AuthController::class, 'logout']);
        
        // Addresses
        Route::apiResource('addresses', AddressController::class);
        
        // Cart
        Route::get('cart', [CartController::class, 'index']);
        Route::post('cart/add', [CartController::class, 'add']);
        Route::put('cart/update/{cartItem}', [CartController::class, 'update']);
        Route::delete('cart/remove/{cartItem}', [CartController::class, 'remove']);
        Route::delete('cart/clear', [CartController::class, 'clear']);
        
        // Orders
        Route::post('checkout', [OrderController::class, 'checkout']);
        Route::post('verify-payment', [OrderController::class, 'verifyPayment']);
        Route::get('orders', [OrderController::class, 'myOrders']);
        Route::get('orders/{order}', [OrderController::class, 'show']);
        Route::post('orders/{order}/cancel', [OrderController::class, 'cancel']);
        
        // Wishlist
        Route::get('wishlist', [WishlistController::class, 'index']);
        Route::post('wishlist/add/{product}', [WishlistController::class, 'add']);
        Route::delete('wishlist/remove/{product}', [WishlistController::class, 'remove']);
        
        // Reviews
        Route::post('products/{product}/reviews', [ReviewController::class, 'store']);
        Route::put('reviews/{review}', [ReviewController::class, 'update']);
        Route::delete('reviews/{review}', [ReviewController::class, 'destroy']);
        Route::post('reviews/{review}/helpful', [ReviewController::class, 'helpful']);
        
        // Questions
        Route::post('products/{product}/questions', [QuestionController::class, 'store']);
        Route::put('questions/{question}', [QuestionController::class, 'update']);
        Route::delete('questions/{question}', [QuestionController::class, 'destroy']);
        Route::post('questions/{question}/answers', [QuestionController::class, 'answer']);
        Route::put('answers/{answer}', [QuestionController::class, 'updateAnswer']);
        Route::delete('answers/{answer}', [QuestionController::class, 'deleteAnswer']);
        Route::get('user/questions', [QuestionController::class, 'myQuestions']);
        
        // Notification preferences
        Route::get('notification-preferences', [NotificationController::class, 'preferences']);
        Route::put('notification-preferences', [NotificationController::class, 'updatePreferences']);
        
        // Wallet
        Route::get('wallet', [WalletController::class, 'show']);
        Route::get('wallet/transactions', [WalletController::class, 'transactions']);
        Route::get('wallet/transactions/{id}', [WalletController::class, 'transactionDetails']);
        Route::get('wallet/summary', [WalletController::class, 'summary']);
        Route::get('wallet/referral-earnings', [WalletController::class, 'referralEarnings']);
        Route::get('wallet/cashback-earnings', [WalletController::class, 'cashbackEarnings']);
        Route::post('wallet/add-funds', [WalletController::class, 'addFunds']);
        Route::post('wallet/verify-add-funds', [WalletController::class, 'verifyAddFunds']);
        Route::post('wallet/redeem', [WalletController::class, 'redeem']);
        
        // Promo codes for users
        Route::get('user/promo-codes', [PromoCodeController::class, 'getUserPromoCodes']);
        Route::post('orders/{order}/apply-promo', [PromoCodeController::class, 'apply']);
        
        // Vendor follow/unfollow
        Route::post('vendors/{vendor}/follow', [VendorController::class, 'toggleFollow']);
        Route::get('vendors/{vendor}/check-follow', [VendorController::class, 'checkFollow']);
        
        // Vendor registration and management
        Route::post('vendor/register', [VendorController::class, 'store']);
        Route::get('vendor/dashboard', [VendorController::class, 'dashboard']);
        Route::get('vendor/profile', [VendorController::class, 'profile']);
        Route::put('vendor/profile', [VendorController::class, 'updateProfile']);
        Route::get('vendor/followers', [VendorController::class, 'followers']);
        Route::get('vendor/subscription', [VendorController::class, 'subscriptionStatus']);
        Route::post('vendor/subscription/upgrade', [VendorController::class, 'upgradeSubscription']);
        
        // Vendor documents
        Route::get('vendor/documents', [VendorController::class, 'documents']);
        Route::post('vendor/documents', [VendorController::class, 'uploadDocument']);
        Route::delete('vendor/documents/{document}', [VendorController::class, 'deleteDocument']);
        
        // Vendor products
        Route::get('vendor/products', [VendorController::class, 'products']);
        Route::post('vendor/products', [ProductController::class, 'store']);
        Route::put('vendor/products/{product}', [ProductController::class, 'update']);
        Route::delete('vendor/products/{product}', [ProductController::class, 'destroy']);
        
        // Vendor orders
        Route::get('vendor/orders', [VendorController::class, 'orders']);
        Route::put('vendor/orders/{orderItem}/status', [VendorController::class, 'updateOrderStatus']);
        
        // Vendor earnings
        Route::get('vendor/earnings', [VendorEarningController::class, 'index']);
        Route::get('vendor/earnings/statistics', [VendorEarningController::class, 'statistics']);
        
        // Vendor payouts
        Route::get('vendor/payouts', [VendorEarningController::class, 'payouts']);
        
        // Vendor withdrawals
        Route::get('vendor/withdrawals', [VendorEarningController::class, 'withdrawals']);
        Route::post('vendor/withdrawals/request', [VendorEarningController::class, 'requestWithdrawal']);
        
        // Vendor bank accounts
        Route::get('vendor/bank-accounts', [VendorEarningController::class, 'bankAccounts']);
        Route::post('vendor/bank-accounts', [VendorEarningController::class, 'addBankAccount']);
        Route::delete('vendor/bank-accounts/{id}', [VendorEarningController::class, 'deleteBankAccount']);
        Route::put('vendor/bank-accounts/{id}/default', [VendorEarningController::class, 'setDefaultBankAccount']);
        
        // Vendor questions
        Route::get('vendor/questions', [QuestionController::class, 'vendorQuestions']);
        Route::get('vendor/questions/unanswered-count', [QuestionController::class, 'unansweredCount']);
        
        // ==================== ADMIN ROUTES ====================
        Route::middleware('role:admin|super-admin')->prefix('admin')->group(function () {
            
            // Dashboard
            Route::get('dashboard', [AdminController::class, 'dashboard']);
            
            // Users
            Route::get('users', [AdminController::class, 'users']);
            Route::put('users/{user}/status', [AdminController::class, 'updateUserStatus']);
            
            // Vendors
            Route::get('vendors', [AdminController::class, 'vendors']);
            Route::put('vendors/{vendor}/approve', [AdminController::class, 'approveVendor']);
            Route::put('vendors/{vendor}/reject', [AdminController::class, 'rejectVendor']);
            Route::put('vendors/{vendor}/suspend', [AdminController::class, 'suspendVendor']);
            
            // Products
            Route::get('products/pending', [AdminController::class, 'pendingProducts']);
            Route::put('products/{product}/approve', [AdminController::class, 'approveProduct']);
            
            // Orders
            Route::get('orders', [AdminController::class, 'orders']);
            Route::put('orders/{order}/status', [AdminController::class, 'updateOrderStatus']);
            
            // Categories
            Route::post('categories', [CategoryController::class, 'store']);
            Route::put('categories/{category}', [CategoryController::class, 'update']);
            Route::delete('categories/{category}', [CategoryController::class, 'destroy']);
            
            // Category attributes
            Route::post('categories/{category}/attributes', [CategoryAttributeController::class, 'attach']);
            Route::delete('categories/{category}/attributes/{attribute}', [CategoryAttributeController::class, 'detach']);
            Route::put('categories/{category}/attributes/{attribute}', [CategoryAttributeController::class, 'updatePivot']);
            
            // Subscription plans
            Route::apiResource('subscription-plans', SubscriptionPlanController::class);
            
            // Promo codes
            Route::get('promo-codes', [PromoCodeController::class, 'index']);
            Route::post('promo-codes', [PromoCodeController::class, 'store']);
            Route::get('promo-codes/{id}', [PromoCodeController::class, 'show']);
            Route::put('promo-codes/{id}', [PromoCodeController::class, 'update']);
            Route::delete('promo-codes/{id}', [PromoCodeController::class, 'destroy']);
            Route::get('promo-codes/statistics', [PromoCodeController::class, 'getStatistics']);
            
            // Banners
            Route::apiResource('banners', BannerController::class);
            
            // Reports
            Route::get('reports/sales', [ReportController::class, 'sales']);
            Route::get('reports/vendors', [ReportController::class, 'vendors']);
            Route::get('reports/products', [ReportController::class, 'products']);
            Route::post('reports/export', [ReportController::class, 'export']);
            
            // Attribute groups
            Route::post('attribute-groups', [AttributeGroupController::class, 'store']);
            Route::put('attribute-groups/{attributeGroup}', [AttributeGroupController::class, 'update']);
            Route::delete('attribute-groups/{attributeGroup}', [AttributeGroupController::class, 'destroy']);
            Route::post('attribute-groups/reorder', [AttributeGroupController::class, 'reorder']);
            
            // Attribute group attribute management
            Route::post('attribute-groups/{attributeGroup}/attributes/{attribute}', [AttributeGroupController::class, 'addAttribute']);
            Route::delete('attribute-groups/{attributeGroup}/attributes/{attribute}', [AttributeGroupController::class, 'removeAttribute']);
            Route::put('attribute-groups/{attributeGroup}/attributes/order', [AttributeGroupController::class, 'updateAttributeOrder']);
            Route::get('attribute-groups/{attributeGroup}/attributes', [AttributeGroupController::class, 'getAttributes']);
            
            // Category assignment for attribute groups
            Route::post('attribute-groups/{attributeGroup}/categories/{category}', [AttributeGroupController::class, 'assignToCategory']);
            Route::delete('attribute-groups/{attributeGroup}/categories/{category}', [AttributeGroupController::class, 'removeFromCategory']);
        });
    });
});