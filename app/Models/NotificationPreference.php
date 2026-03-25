<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id', 'order_updates', 'promo_alerts', 'vendor_alerts',
        'flash_sales', 'newsletters'
    ];

    protected $casts = [
        'order_updates' => 'boolean',
        'promo_alerts' => 'boolean',
        'vendor_alerts' => 'boolean',
        'flash_sales' => 'boolean',
        'newsletters' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}