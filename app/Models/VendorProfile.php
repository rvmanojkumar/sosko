<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class VendorProfile extends Model
{
    use HasFactory, HasUuids, SoftDeletes, Auditable;

    protected $fillable = [
        'user_id',
        'store_name',
        'store_slug',
        'description',
        'logo',
        'banner',
        'contact_email',
        'contact_phone',
        'address',
        'latitude',
        'longitude',
        'status',
        'rejection_reason',
        'rating',
        'follower_count',
        'settings'
    ];

    protected $casts = [
        'settings' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'rating' => 'decimal:2',
        'follower_count' => 'integer',
    ];

    protected $attributes = [
        'status' => 'pending',
        'rating' => 0,
        'follower_count' => 0,
    ];

    /**
     * Get the user associated with the vendor profile
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all products for this vendor
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'vendor_id', 'user_id');
    }

    /**
     * Get all documents for this vendor
     */
    public function documents()
    {
        return $this->hasMany(VendorDocument::class);
    }

    /**
     * Get all subscriptions for this vendor
     */
    public function subscriptions()
    {
        return $this->hasMany(VendorSubscription::class);
    }

    /**
     * Get the current active subscription
     */
    public function currentSubscription()
    {
        return $this->hasOne(VendorSubscription::class)
            ->where('status', 'active')
            ->where('end_date', '>=', now())
            ->latest();
    }

    /**
     * Get the subscription plan for current subscription
     */
    public function subscription()
    {
        return $this->hasOneThrough(
            SubscriptionPlan::class,
            VendorSubscription::class,
            'vendor_profile_id',
            'id',
            'id',
            'subscription_plan_id'
        )->where('vendor_subscriptions.status', 'active')
         ->where('vendor_subscriptions.end_date', '>=', now());
    }


    /**
     * Get all earnings for this vendor
     */
    



    /**
     * Get all orders for this vendor
     */
    public function orders()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get followers count (users who follow this vendor)
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'vendor_followers', 'vendor_profile_id', 'user_id')
            ->withTimestamps();
    }

    public function getFollowerCountAttribute()
    {
        return $this->followers()->count();
    }
    /**
 * Check if a user follows this vendor
 */
    public function isFollowedBy($userId)
    {
        return $this->followers()->where('user_id', $userId)->exists();
    }

    /**
 * Toggle follow status for a user
 */
public function toggleFollow($userId)
    {
        if ($this->isFollowedBy($userId)) {
            $this->followers()->detach($userId);
            return false; // Unfollowed
        } else {
            $this->followers()->attach($userId);
            return true; // Followed
        }
    }
    /**
     * Check if vendor has reached product limit
     */
    public function hasReachedProductLimit()
    {
        $subscription = $this->currentSubscription;
        
        if (!$subscription) {
            return true;
        }
        
        $plan = $subscription->plan;
        $productCount = $this->products()->count();
        
        return $productCount >= $plan->max_products;
    }

    /**
     * Get remaining product slots
     */
    public function remainingProductSlots()
    {
        $subscription = $this->currentSubscription;
        
        if (!$subscription) {
            return 0;
        }
        
        $plan = $subscription->plan;
        $productCount = $this->products()->count();
        
        return max(0, $plan->max_products - $productCount);
    }
    public function getCurrentPlanNameAttribute()
{
    $subscription = $this->currentSubscription;
    
    if (!$subscription || !$subscription->plan) {
        return 'No Active Plan';
    }
    
    return $subscription->plan->name;
    }
    public function getSubscriptionStatusAttribute()
{
    $subscription = $this->currentSubscription;
    
    if (!$subscription) {
        return 'No Active Subscription';
    }
    
    if ($subscription->isExpired()) {
        return 'Expired';
    }
    
    return ucfirst($subscription->status);
}
    /**
     * Calculate total earnings
     */
    public function totalEarnings()
    {
        return $this->earnings()->where('status', 'processed')->sum('vendor_amount');
    }

    /**
     * Calculate pending earnings
     */
    public function pendingEarnings()
    {
        return $this->earnings()->where('status', 'pending')->sum('vendor_amount');
    }

    /**
     * Calculate total payout
     */
    public function totalPayouts()
    {
        return $this->payouts()->where('status', 'completed')->sum('amount');
    }

    /**
     * Get monthly sales data
     */
    public function monthlySales($year = null, $month = null)
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;
        
        return $this->earnings()
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->sum('vendor_amount');
    }

    /**
     * Update vendor rating based on reviews
     */
    

    /**
     * Increment follower count
     */
    public function incrementFollowerCount()
    {
        $this->increment('follower_count');
    }

    /**
     * Decrement follower count
     */
    public function decrementFollowerCount()
    {
        $this->decrement('follower_count');
    }

    /**
     * Scope for approved vendors
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for pending vendors
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for active vendors (approved and not suspended)
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for top vendors by rating
     */
    public function scopeTopRated($query, $limit = 10)
    {
        return $query->where('status', 'approved')
            ->orderBy('rating', 'desc')
            ->limit($limit);
    }

    /**
     * Scope for top vendors by earnings
     */
    public function scopeTopEarning($query, $limit = 10)
    {
        return $query->withSum('earnings', 'vendor_amount')
            ->orderBy('earnings_sum_vendor_amount', 'desc')
            ->limit($limit);
    }
    // app/Models/VendorProfile.php - Add these relationships

/**
 * Get all earnings for this vendor
 */
public function earnings()
{
    return $this->hasMany(VendorEarning::class);
}

/**
 * Get all payouts for this vendor
 */
public function payouts()
{
    return $this->hasMany(VendorPayout::class);
}

/**
 * Get all withdrawal requests
 */
public function withdrawals()
{
    return $this->hasMany(VendorWithdrawal::class);
}

/**
 * Get all bank accounts
 */
public function bankAccounts()
{
    return $this->hasMany(VendorBankAccount::class);
}

/**
 * Get the default bank account
 */
public function defaultBankAccount()
{
    return $this->hasOne(VendorBankAccount::class)->where('is_default', true);
}

/**
 * Get pending earnings total
 */
public function getPendingEarningsAttribute()
{
    return $this->earnings()->pending()->sum('vendor_amount');
}

/**
 * Get total earnings
 */
public function getTotalEarningsAttribute()
{
    return $this->earnings()->sum('vendor_amount');
}

/**
 * Get processed earnings total
 */
public function getProcessedEarningsAttribute()
{
    return $this->earnings()->processed()->sum('vendor_amount');
}

/**
 * Create withdrawal request
 */
public function requestWithdrawal($amount, $bankAccountId = null)
{
    if ($amount > $this->pending_earnings) {
        throw new \Exception('Insufficient pending earnings');
    }
    
    $bankAccount = null;
    if ($bankAccountId) {
        $bankAccount = $this->bankAccounts()->find($bankAccountId);
    } else {
        $bankAccount = $this->defaultBankAccount;
    }
    
    if (!$bankAccount) {
        throw new \Exception('No bank account found');
    }
    
    return $this->withdrawals()->create([
        'amount' => $amount,
        'bank_account_id' => $bankAccount->id,
        'ifsc_code' => $bankAccount->ifsc_code,
        'account_holder_name' => $bankAccount->account_holder_name,
        'status' => 'pending',
    ]);
}
/**
 * Get the reviews for this vendor
 */
public function reviews()
{
    return $this->hasMany(VendorReview::class);
}

/**
 * Get approved reviews
 */
public function approvedReviews()
{
    return $this->reviews()->where('is_approved', true);
}

/**
 * Get average rating
 */
public function getAverageRatingAttribute()
{
    return $this->approvedReviews()->avg('rating') ?? 0;
}

/**
 * Get review count
 */
public function getReviewCountAttribute()
{
    return $this->approvedReviews()->count();
}

/**
 * Get rating distribution
 */
public function getRatingDistributionAttribute()
{
    $distribution = [];
    
    for ($i = 1; $i <= 5; $i++) {
        $distribution[$i] = $this->approvedReviews()->where('rating', $i)->count();
    }
    
    return $distribution;
}

/**
 * Update vendor rating based on reviews
 */
public function updateRating()
{
    $rating = $this->average_rating;
    $this->update(['rating' => $rating]);
    return $rating;
}
}