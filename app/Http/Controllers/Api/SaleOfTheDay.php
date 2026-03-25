<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SaleOfTheDay extends Model
{
    use HasUuids;

    protected $fillable = [
        'product_variant_id',
        'sale_date',
        'special_price'
    ];

    protected $casts = [
        'sale_date' => 'date',
        'special_price' => 'decimal:2',
    ];

    /**
     * Get the product variant
     */
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * Get today's sale
     */
    public static function today()
    {
        return self::where('sale_date', now()->toDateString())->first();
    }

    /**
     * Check if product is on sale today
     */
    public static function isOnSale($variantId)
    {
        return self::where('product_variant_id', $variantId)
            ->where('sale_date', now()->toDateString())
            ->exists();
    }
}