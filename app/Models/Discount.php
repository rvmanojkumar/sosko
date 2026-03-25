<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'type',
        'value',
        'min_order_value',
        'buy_quantity',
        'get_quantity',
        'start_date',
        'end_date',
        'usage_limit',
        'used_count',
        'is_active'
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_value' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the products this discount applies to
     */
    public function products()
    {
        return $this->belongsToMany(ProductVariant::class, 'discount_products');
    }

    /**
     * Get the categories this discount applies to
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'discount_categories');
    }

    /**
     * Check if discount is active
     */
    public function isActive()
    {
        return $this->is_active && 
               $this->start_date <= now() && 
               $this->end_date >= now();
    }

    /**
     * Calculate discount amount
     */
    public function calculateDiscount($subtotal, $quantity = 1)
    {
        if ($this->type === 'flat') {
            return min($this->value, $subtotal);
        } elseif ($this->type === 'percentage') {
            $discount = $subtotal * ($this->value / 100);
            return min($discount, $subtotal);
        } elseif ($this->type === 'buy_x_get_y') {
            if ($quantity >= $this->buy_quantity) {
                $freeItems = floor($quantity / $this->buy_quantity) * $this->get_quantity;
                $pricePerItem = $subtotal / $quantity;
                return $freeItems * $pricePerItem;
            }
        }
        
        return 0;
    }
}