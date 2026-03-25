<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PromoCodeUsage extends Model
{
    use HasUuids;

    protected $fillable = [
        'promo_code_id',
        'user_id',
        'order_id',
        'discount_amount',
        'metadata'
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    /**
     * Get the promo code
     */
    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }

    /**
     * Get the user who used the promo code
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order where promo code was used
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}