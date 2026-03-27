<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable; // Add this line

class Product extends Model
{
    use HasFactory, HasUuids, SoftDeletes, Auditable; // Add Auditable trait

    protected $fillable = [
        'vendor_id', 'category_id', 'supplier_id', 'name', 'slug', 'description',
        'short_description', 'brand', 'weight', 'specifications', 'is_featured',
        'is_active', 'view_count', 'sold_count', 'seo_data'
    ];

    protected $casts = [
        'specifications' => 'array',
        'seo_data' => 'array',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'weight' => 'decimal:2',
    ];

    /**
 * Get the reviews for this product
 */
public function reviews()
{
    return $this->hasMany(ProductReview::class);
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
 * Update product rating based on reviews
 */
public function updateRating()
{
    // No need to store rating separately, use average_rating accessor
    // This method exists for compatibility
    return $this->average_rating;
}

/**
 * Get reviews with pagination
 */
public function getReviews($perPage = 10, $rating = null)
{
    $query = $this->approvedReviews()->with(['user', 'media']);
    
    if ($rating) {
        $query->where('rating', $rating);
    }
    
    return $query->orderBy('created_at', 'desc')->paginate($perPage);
}

/**
 * Check if user has reviewed this product
 */
public function hasUserReviewed($userId)
{
    return $this->reviews()->where('user_id', $userId)->exists();
}

/**
 * Get user's review for this product
 */
public function getUserReview($userId)
{
    return $this->reviews()->where('user_id', $userId)->first();
}
/**
     * Get the order items for this product
     */
    public function orderItems()
    {
        return $this->hasManyThrough(
            OrderItem::class,
            ProductVariant::class,
            'product_id', // Foreign key on product_variants table
            'product_variant_id', // Foreign key on order_items table
            'id', // Local key on products table
            'id' // Local key on product_variants table
        );
    }
}