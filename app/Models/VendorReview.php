<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorReview extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'vendor_profile_id',
        'user_id',
        'order_id',
        'rating',
        'review',
        'is_verified_purchase',
        'is_approved'
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_verified_purchase' => 'boolean',
        'is_approved' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'is_approved' => false,
        'is_verified_purchase' => false,
    ];

    /**
     * Get the vendor profile
     */
    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class);
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
     * Approve the review
     */
    public function approve()
    {
        $this->update(['is_approved' => true]);
        
        // Update vendor rating
        $this->vendorProfile->updateRating();
        
        return $this;
    }

    /**
     * Reject the review
     */
    public function reject()
    {
        $this->delete();
        
        // Update vendor rating
        $this->vendorProfile->updateRating();
        
        return $this;
    }

    /**
     * Get formatted rating stars
     */
    public function getStarsAttribute()
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }

    /**
     * Get formatted review
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
     * Scope for reviews of a specific vendor
     */
    public function scopeForVendor($query, $vendorId)
    {
        return $query->where('vendor_profile_id', $vendorId);
    }

    /**
     * Scope for reviews by a specific user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}