// app/Models/VendorFollower.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorFollower extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'vendor_followers';

    protected $fillable = [
        'vendor_profile_id',
        'user_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the vendor profile that is being followed
     */
    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class);
    }

    /**
     * Get the user who is following the vendor
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for a specific vendor
     */
    public function scopeForVendor($query, $vendorId)
    {
        return $query->where('vendor_profile_id', $vendorId);
    }

    /**
     * Scope for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Check if a user is following a vendor
     */
    public static function isFollowing($vendorId, $userId)
    {
        return self::where('vendor_profile_id', $vendorId)
            ->where('user_id', $userId)
            ->exists();
    }
}