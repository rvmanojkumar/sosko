<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorSubscription extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'vendor_profile_id',
        'subscription_plan_id',
        'start_date',
        'end_date',
        'status',
        'razorpay_subscription_id',
        'payment_data',
        'auto_renew'
    ];

    protected $casts = [
        'payment_data' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'auto_renew' => 'boolean',
    ];

    protected $attributes = [
        'status' => 'active',
        'auto_renew' => false,
    ];

    /**
     * Get the vendor profile
     */
    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class);
    }

    /**
     * Get the subscription plan
     */
    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    /**
     * Check if subscription is active
     */
    public function isActive()
    {
        return $this->status === 'active' && $this->end_date >= now();
    }

    /**
     * Check if subscription is expired
     */
    public function isExpired()
    {
        return $this->end_date < now();
    }

    /**
     * Get days remaining
     */
    public function daysRemaining()
    {
        if ($this->isExpired()) {
            return 0;
        }
        
        return now()->diffInDays($this->end_date);
    }

    /**
     * Cancel subscription
     */
    public function cancel()
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Renew subscription
     */
    public function renew($newEndDate = null)
    {
        $this->update([
            'start_date' => now(),
            'end_date' => $newEndDate ?? now()->addMonth(),
            'status' => 'active',
        ]);
    }

    /**
     * Scope for active subscriptions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('end_date', '>=', now());
    }

    /**
     * Scope for expired subscriptions
     */
    public function scopeExpired($query)
    {
        return $query->where('end_date', '<', now())
            ->orWhere('status', '!=', 'active');
    }

    /**
     * Scope for cancelled subscriptions
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }
}