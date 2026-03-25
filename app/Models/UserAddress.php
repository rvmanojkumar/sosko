<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id', 'address_line1', 'address_line2', 'city', 'state',
        'country', 'postal_code', 'phone', 'address_type', 'is_default',
        'latitude', 'longitude'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}