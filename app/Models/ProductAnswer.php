<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductAnswer extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'product_question_id',
        'vendor_profile_id',
        'user_id',
        'answer'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the question that this answer belongs to
     */
    public function question()
    {
        return $this->belongsTo(ProductQuestion::class, 'product_question_id');
    }

    /**
     * Get the vendor who answered (if answered by vendor)
     */
    public function vendor()
    {
        return $this->belongsTo(VendorProfile::class, 'vendor_profile_id');
    }

    /**
     * Get the user who answered (if answered by admin or customer)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the answerer name
     */
    public function getAnswererNameAttribute()
    {
        if ($this->vendor) {
            return $this->vendor->store_name . ' (Vendor)';
        }
        
        if ($this->user) {
            if ($this->user->hasRole('admin')) {
                return $this->user->name . ' (Admin)';
            }
            return $this->user->name;
        }
        
        return 'Anonymous';
    }

    /**
     * Get the answerer avatar
     */
    public function getAnswererAvatarAttribute()
    {
        if ($this->vendor && $this->vendor->logo) {
            return $this->vendor->logo;
        }
        
        if ($this->user) {
            return $this->user->profile_photo_url;
        }
        
        return null;
    }

    /**
     * Get the answerer type
     */
    public function getAnswererTypeAttribute()
    {
        if ($this->vendor) {
            return 'vendor';
        }
        
        if ($this->user) {
            if ($this->user->hasRole('admin')) {
                return 'admin';
            }
            return 'customer';
        }
        
        return 'anonymous';
    }

    /**
     * Get formatted answer with answerer info
     */
    public function getFormattedAnswerAttribute()
    {
        return [
            'id' => $this->id,
            'answer' => $this->answer,
            'answered_by' => $this->answerer_name,
            'answered_by_avatar' => $this->answerer_avatar,
            'answerer_type' => $this->answerer_type,
            'answered_at' => $this->created_at->diffForHumans(),
            'created_at' => $this->created_at->toISOString(),
        ];
    }

    /**
     * Scope for answers by vendor
     */
    public function scopeByVendor($query, $vendorId)
    {
        return $query->where('vendor_profile_id', $vendorId);
    }

    /**
     * Scope for answers by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for recent answers
     */
    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }
}