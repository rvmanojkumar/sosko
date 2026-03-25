<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class VendorFollower extends Model
{
    use HasUuids;

    protected $table = 'vendor_followers';

    protected $fillable = [
        'vendor_profile_id',
        'user_id'
    ];

    public $timestamps = true;

    /**
     * Get the vendor profile
     */
    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class);
    }

    /**
     * Get the user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}