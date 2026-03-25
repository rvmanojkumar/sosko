<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class PromoCode extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'min_order_value',
        'max_discount_amount',
        'usage_type',
        'usage_limit',
        'used_count',
        'per_user_limit',
        'user_id',
        'vendor_id',
        'applicable_products',
        'applicable_categories',
        'excluded_products',
        'excluded_categories',
        'start_date',
        'end_date',
        'is_active',
        'is_first_order_only',
        'stackable',
        'metadata'
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_value' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'per_user_limit' => 'integer',
        'applicable_products' => 'array',
        'applicable_categories' => 'array',
        'excluded_products' => 'array',
        'excluded_categories' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'is_first_order_only' => 'boolean',
        'stackable' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the user that owns the promo code (if user-specific)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the vendor that owns the promo code (if vendor-specific)
     */
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * Get all usages of this promo code
     */
    public function usages()
    {
        return $this->hasMany(PromoCodeUsage::class);
    }

    /**
     * Check if promo code is valid
     */
    public function isValid($userId = null, $subtotal = null, $products = [])
    {
        // Check if active
        if (!$this->is_active) {
            return ['valid' => false, 'message' => 'Promo code is inactive'];
        }
        
        // Check date range
        $now = Carbon::now();
        if ($now < $this->start_date) {
            return ['valid' => false, 'message' => 'Promo code not yet active'];
        }
        
        if ($now > $this->end_date) {
            return ['valid' => false, 'message' => 'Promo code has expired'];
        }
        
        // Check usage limit
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return ['valid' => false, 'message' => 'Promo code usage limit exceeded'];
        }
        
        // Check user-specific
        if ($this->user_id && $userId && $this->user_id != $userId) {
            return ['valid' => false, 'message' => 'Promo code is not valid for this user'];
        }
        
        // Check per-user limit
        if ($userId && $this->per_user_limit) {
            $userUsageCount = $this->usages()->where('user_id', $userId)->count();
            if ($userUsageCount >= $this->per_user_limit) {
                return ['valid' => false, 'message' => 'You have reached the usage limit for this promo code'];
            }
        }
        
        // Check first order only
        if ($this->is_first_order_only && $userId) {
            $orderCount = Order::where('user_id', $userId)->count();
            if ($orderCount > 0) {
                return ['valid' => false, 'message' => 'This promo code is only valid for first orders'];
            }
        }
        
        // Check minimum order value
        if ($subtotal && $this->min_order_value && $subtotal < $this->min_order_value) {
            return ['valid' => false, 'message' => "Minimum order value of ₹{$this->min_order_value} required"];
        }
        
        // Check product applicability
        if (!empty($products) && $this->applicable_products) {
            $productIds = collect($products)->pluck('id')->toArray();
            $applicable = array_intersect($productIds, $this->applicable_products);
            if (empty($applicable)) {
                return ['valid' => false, 'message' => 'No applicable products in cart'];
            }
        }
        
        return ['valid' => true, 'message' => 'Valid promo code'];
    }
    
    /**
     * Calculate discount amount
     */
    public function calculateDiscount($subtotal, $products = [])
    {
        $discount = 0;
        
        // If product-specific, calculate only on applicable products
        if (!empty($products) && $this->applicable_products) {
            $applicableSubtotal = 0;
            foreach ($products as $product) {
                if (in_array($product['id'], $this->applicable_products)) {
                    $applicableSubtotal += $product['total'] ?? ($product['price'] * $product['quantity']);
                }
            }
            $subtotal = $applicableSubtotal;
        }
        
        // Calculate discount based on type
        if ($this->type === 'percentage') {
            $discount = $subtotal * ($this->value / 100);
        } else {
            $discount = $this->value;
        }
        
        // Apply max discount limit
        if ($this->max_discount_amount && $discount > $this->max_discount_amount) {
            $discount = $this->max_discount_amount;
        }
        
        // Don't exceed subtotal
        $discount = min($discount, $subtotal);
        
        return round($discount, 2);
    }
    
    /**
     * Record usage of promo code
     */
    public function recordUsage($userId, $orderId, $discountAmount, $metadata = [])
    {
        $this->increment('used_count');
        
        return $this->usages()->create([
            'user_id' => $userId,
            'order_id' => $orderId,
            'discount_amount' => $discountAmount,
            'metadata' => $metadata,
        ]);
    }
    
    /**
     * Check if user has used this promo code
     */
    public function isUsedByUser($userId)
    {
        return $this->usages()->where('user_id', $userId)->exists();
    }
    
    /**
     * Get user usage count
     */
    public function getUserUsageCount($userId)
    {
        return $this->usages()->where('user_id', $userId)->count();
    }
    
    /**
     * Scope for active promo codes
     */
   
    
    /**
     * Scope for valid promo codes
     */
    public function scopeValid($query)
    {
        return $query->active()
            ->where(function($q) {
                $q->whereNull('usage_limit')
                  ->orWhereRaw('used_count < usage_limit');
            });
    }
    
    /**
     * Scope for user-specific promo codes
     */
   
    
    /**
     * Scope for vendor-specific promo codes
     */



/**
 * Scope for user-specific promo codes
 */
public function scopeForUser($query, $userId)
{
    return $query->where(function($q) use ($userId) {
        $q->whereNull('user_id')
          ->orWhere('user_id', $userId);
    });
}

/**
 * Scope for vendor-specific promo codes
 */
public function scopeForVendor($query, $vendorId)
{
    return $query->where(function($q) use ($vendorId) {
        $q->whereNull('vendor_id')
          ->orWhere('vendor_id', $vendorId);
    });
}

/**
 * Scope for active promo codes
 */
public function scopeActive($query)
{
    return $query->where('is_active', true)
        ->where('start_date', '<=', now())
        ->where('end_date', '>=', now());
}
}