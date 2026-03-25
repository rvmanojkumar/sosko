<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class VendorReview extends Model
{
    use HasUuids;

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
    ];

    protected $attributes = [
        'is_approved' => false,
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
     * Get the order
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
    }

    /**
     * Reject the review
     */
    public function reject()
    {
        $this->delete();
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
}