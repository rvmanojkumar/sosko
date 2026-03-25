<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductReview extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'product_id',
        'product_variant_id',
        'user_id',
        'order_id',
        'rating',
        'review',
        'is_verified_purchase',
        'is_approved',
        'helpful_count'
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_verified_purchase' => 'boolean',
        'is_approved' => 'boolean',
        'helpful_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'is_approved' => false,
        'helpful_count' => 0,
        'is_verified_purchase' => false,
    ];

    /**
     * Get the product that this review belongs to
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the product variant that this review belongs to
     */
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * Get the user who wrote the review
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order associated with this review
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the media (images) for this review
     */
    public function media()
    {
        return $this->hasMany(ReviewMedia::class, 'product_review_id');
    }

    /**
     * Get the helpful votes for this review
     */
    public function helpfulVotes()
    {
        return $this->hasMany(ReviewHelpfulVote::class, 'product_review_id');
    }

    /**
     * Check if review is approved
     */
    public function isApproved()
    {
        return $this->is_approved;
    }

    /**
     * Check if review is verified purchase
     */
    public function isVerified()
    {
        return $this->is_verified_purchase;
    }

    /**
     * Approve the review
     */
    public function approve()
    {
        $this->update(['is_approved' => true]);
        
        // Update product rating
        $this->product->updateRating();
        
        return $this;
    }

    /**
     * Reject the review
     */
    public function reject()
    {
        $this->delete();
        
        // Update product rating
        $this->product->updateRating();
        
        return $this;
    }

    /**
     * Mark as helpful by a user
     */
    public function markHelpful($userId)
    {
        if (!$this->helpfulVotes()->where('user_id', $userId)->exists()) {
            $this->helpfulVotes()->create(['user_id' => $userId]);
            $this->increment('helpful_count');
            return true;
        }
        
        return false;
    }

    /**
     * Unmark helpful
     */
    public function unmarkHelpful($userId)
    {
        $deleted = $this->helpfulVotes()->where('user_id', $userId)->delete();
        
        if ($deleted) {
            $this->decrement('helpful_count');
            return true;
        }
        
        return false;
    }

    /**
     * Check if user marked this review as helpful
     */
    public function isHelpfulByUser($userId)
    {
        return $this->helpfulVotes()->where('user_id', $userId)->exists();
    }

    /**
     * Get formatted rating stars
     */
    public function getStarsAttribute()
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }

    /**
     * Get rating as percentage
     */
    public function getRatingPercentageAttribute()
    {
        return ($this->rating / 5) * 100;
    }

    /**
     * Get formatted review with user info
     */
    public function getFormattedReviewAttribute()
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'stars' => $this->stars,
            'review' => $this->review,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'avatar' => $this->user->profile_photo_url,
            ],
            'is_verified_purchase' => $this->is_verified_purchase,
            'is_approved' => $this->is_approved,
            'helpful_count' => $this->helpful_count,
            'images' => $this->media->map(function($media) {
                return [
                    'id' => $media->id,
                    'url' => $media->media_url,
                ];
            }),
            'created_at' => $this->created_at->diffForHumans(),
            'created_at_raw' => $this->created_at->toISOString(),
        ];
    }

    /**
     * Scope for approved reviews
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope for pending reviews
     */
    public function scopePending($query)
    {
        return $query->where('is_approved', false);
    }

    /**
     * Scope for verified purchases
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified_purchase', true);
    }

    /**
     * Scope for specific rating
     */
    public function scopeRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    /**
     * Scope for reviews of a specific product
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope for reviews by a specific user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for reviews with images
     */
    public function scopeWithImages($query)
    {
        return $query->has('media');
    }
}