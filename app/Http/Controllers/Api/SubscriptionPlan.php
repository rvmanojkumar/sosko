<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlan extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'billing_period',
        'max_products',
        'max_images_per_product',
        'featured_listing',
        'priority_support',
        'commission_rate',
        'features',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'max_products' => 'integer',
        'max_images_per_product' => 'integer',
        'featured_listing' => 'boolean',
        'priority_support' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $attributes = [
        'is_active' => true,
        'commission_rate' => 10.00,
    ];

    /**
     * Get all vendor subscriptions for this plan
     */
    public function subscriptions()
    {
        return $this->hasMany(VendorSubscription::class);
    }

    /**
     * Get active subscriptions
     */
    public function activeSubscriptions()
    {
        return $this->subscriptions()->where('status', 'active')
            ->where('end_date', '>=', now());
    }

    /**
     * Calculate monthly price
     */
    public function monthlyPrice()
    {
        if ($this->billing_period === 'yearly') {
            return $this->price / 12;
        }
        
        return $this->price;
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute()
    {
        return '₹' . number_format($this->price, 2);
    }

    /**
     * Scope for active plans
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for free plans
     */
    public function scopeFree($query)
    {
        return $query->where('price', 0);
    }

    /**
     * Scope for paid plans
     */
    public function scopePaid($query)
    {
        return $query->where('price', '>', 0);
    }
}