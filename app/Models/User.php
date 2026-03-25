<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\Auditable;
use Illuminate\Support\Facades\Storage;
use App\Models\UserWallet;
use App\Models\WalletTransaction;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids, SoftDeletes, HasRoles, Auditable;
    protected $guard_name = 'api'; // 
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'profile_photo',
        'dob',
        'firebase_uid',
        'is_active',
        'email_verified_at',
        'phone_verified_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'dob' => 'date',
        'is_active' => 'boolean',
        'id' => 'string',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * Get the user's addresses
     */
    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    /**
     * Get the default address
     */
    public function defaultAddress()
    {
        return $this->hasOne(UserAddress::class)->where('is_default', true);
    }

    /**
     * Get the user's devices
     */
    public function devices()
    {
        return $this->hasMany(UserDevice::class);
    }

    
    /**
     * Get wallet balance
     */
    public function walletBalance()
    {
        return $this->wallet ? $this->wallet->balance : 0;
    }

    /**
     * Get the user's wishlist items
     */
    public function wishlist()
    {
        return $this->belongsToMany(Product::class, 'wishlists')
                    ->withTimestamps();
    }

    /**
     * Check if product is in wishlist
     */
    public function isInWishlist($productId)
    {
        return $this->wishlist()->where('product_id', $productId)->exists();
    }

    /**
     * Get the user's cart
     */
    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    /**
     * Get cart items count
     */
    public function cartItemsCount()
    {
        return $this->cart ? $this->cart->items()->count() : 0;
    }

    /**
     * Get the user's orders
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get pending orders
     */
    public function pendingOrders()
    {
        return $this->orders()->whereIn('order_status', ['placed', 'confirmed', 'processing']);
    }

    /**
     * Get completed orders
     */
    public function completedOrders()
    {
        return $this->orders()->where('order_status', 'delivered');
    }

    /**
     * Get the user's reviews
     */
    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    /**
     * Get the user's product questions
     */
    public function questions()
    {
        return $this->hasMany(ProductQuestion::class);
    }

    /**
     * Get the user's notification preferences
     */
    public function notificationPreferences()
    {
        return $this->hasOne(NotificationPreference::class);
    }

    /**
     * Get or create notification preferences
     */
    public function getNotificationPreferences()
    {
        return $this->notificationPreferences()->firstOrCreate([]);
    }

    /**
     * Get the user's vendor profile
     */
    public function vendorProfile()
    {
        return $this->hasOne(VendorProfile::class);
    }

    /**
     * Check if user is a vendor
     */
    public function isVendor()
    {
        return $this->vendorProfile !== null;
    }

    /**
     * Check if user is an approved vendor
     */
    public function isApprovedVendor()
    {
        return $this->vendorProfile && $this->vendorProfile->status === 'approved';
    }

    /**
     * Check if user is active
     */
    public function isActive()
    {
        return $this->is_active;
    }

    /**
     * Get the user's products (if vendor)
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'vendor_id');
    }

    /**
     * Get active products (if vendor)
     */
    public function activeProducts()
    {
        return $this->products()->where('is_active', true);
    }

    /**
     * Get total spent by user
     */
    public function totalSpent()
    {
        return $this->orders()->where('payment_status', 'paid')->sum('total_amount');
    }

    /**
     * Get total orders count
     */
    public function totalOrders()
    {
        return $this->orders()->count();
    }

    /**
     * Get recent orders
     */
    public function recentOrders($limit = 5)
    {
        return $this->orders()->with(['items'])->latest()->limit($limit)->get();
    }

    /**
     * Get referral earnings
     */
    public function referralEarnings()
    {
        return $this->walletTransactions()
            ->where('source', 'referral')
            ->where('type', 'credit')
            ->sum('amount');
    }



    /**
     * Add to wallet balance
     */
    public function addToWallet($amount, $source, $description = null, $orderId = null)
    {
        $wallet = $this->wallet;
        
        if (!$wallet) {
            $wallet = $this->wallet()->create([
                'balance' => 0,
                'total_earned' => 0,
                'total_redeemed' => 0,
            ]);
        }
        
        $wallet->increment('balance', $amount);
        $wallet->increment('total_earned', $amount);
        
        return $this->walletTransactions()->create([
            'order_id' => $orderId,
            'amount' => $amount,
            'type' => 'credit',
            'source' => $source,
            'description' => $description,
            'metadata' => ['balance_after' => $wallet->balance],
        ]);
    }

    /**
     * Deduct from wallet balance
     */
    public function deductFromWallet($amount, $source, $description = null, $orderId = null)
    {
        $wallet = $this->wallet;
        
        if (!$wallet || $wallet->balance < $amount) {
            throw new \Exception('Insufficient wallet balance');
        }
        
        $wallet->decrement('balance', $amount);
        $wallet->increment('total_redeemed', $amount);
        
        return $this->walletTransactions()->create([
            'order_id' => $orderId,
            'amount' => $amount,
            'type' => 'debit',
            'source' => $source,
            'description' => $description,
            'metadata' => ['balance_after' => $wallet->balance],
        ]);
    }

    /**
     * Get FCM tokens for user
     */
    public function getFcmTokens()
    {
        return $this->devices()
            ->where('is_active', true)
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->toArray();
    }

    /**
     * Update user's last active
     */
    public function updateLastActive()
    {
        $this->update(['last_active_at' => now()]);
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for inactive users
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope for users with verified phone
     */
    public function scopeVerifiedPhone($query)
    {
        return $query->whereNotNull('phone_verified_at');
    }

    /**
     * Scope for users with verified email
     */
    public function scopeVerifiedEmail($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * Scope for vendors
     */
    public function scopeVendors($query)
    {
        return $query->whereHas('roles', function ($q) {
            $q->where('name', 'vendor');
        });
    }

    /**
     * Scope for customers
     */
    public function scopeCustomers($query)
    {
        return $query->whereHas('roles', function ($q) {
            $q->where('name', 'customer');
        })->orWhereDoesntHave('roles');
    }

    /**
     * Get formatted name
     */
    public function getFormattedNameAttribute()
    {
        return $this->name ?? explode('@', $this->email)[0];
    }

    /**
     * Get profile photo URL
     */
    

    /**
     * Get user statistics
     */
    
    public function getStatisticsAttribute()
{
    return [
        'total_orders' => $this->orders()->count(),
        'total_spent' => (float) $this->orders()->where('payment_status', 'paid')->sum('total_amount'),
        'wallet_balance' => (float) ($this->wallet ? $this->wallet->balance : 0),
        'wishlist_count' => $this->wishlist()->count(),
        'reviews_count' => $this->reviews()->count(),
        'questions_count' => $this->questions()->count(),
    ];
}

/**
 * Get profile photo URL
 */
    public function getProfilePhotoUrlAttribute()
    {
        if ($this->profile_photo) {
            return Storage::disk('public')->url($this->profile_photo);
        }
        
        // Use UI Avatars as fallback
            $name = urlencode($this->name ?? $this->email ?? 'User');
            return "https://ui-avatars.com/api/?background=2980B9&color=fff&name={$name}";
    }
    public function wallet()
    {
        return $this->hasOne(UserWallet::class);
    }

public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }
}