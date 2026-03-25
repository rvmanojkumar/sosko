<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'product_id',
        'supplier_id',
        'sku',
        'price',
        'sale_price',
        'discount_percent',
        'stock_quantity',
        'low_stock_threshold',
        'weight',
        'images',
        'is_default',
        'is_active'
    ];

    protected $casts = [
        'images' => 'array',
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'weight' => 'decimal:2',
        'stock_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => true,
        'discount_percent' => 0,
        'stock_quantity' => 0,
        'low_stock_threshold' => 5,
    ];

    /**
     * Get the product that owns the variant
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the supplier for this variant
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the attribute values for this variant
     */
    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'product_variant_attribute_values');
    }

    /**
     * Get the images for this variant
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_variant_id');
    }

    /**
     * Get the cart items for this variant
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the order items for this variant
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the current price (considering sale price, flash sales, etc.)
     */
    public function getCurrentPriceAttribute()
    {
        // Check for sale price
        if ($this->sale_price && $this->sale_price < $this->price) {
            return $this->sale_price;
        }
        
        // Check for flash sale
        $flashSale = $this->flashSales()
            ->where('flash_sales.start_time', '<=', now())
            ->where('flash_sales.end_time', '>=', now())
            ->first();
            
        if ($flashSale && $flashSale->pivot->flash_price) {
            return $flashSale->pivot->flash_price;
        }
        
        // Check for sale of the day
        $saleOfDay = SaleOfTheDay::where('product_variant_id', $this->id)
            ->where('sale_date', now()->toDateString())
            ->first();
            
        if ($saleOfDay && $saleOfDay->special_price) {
            return $saleOfDay->special_price;
        }
        
        return $this->price;
    }

    /**
     * Get the discount percentage
     */
    public function getDiscountPercentageAttribute()
    {
        if ($this->sale_price && $this->sale_price < $this->price) {
            return round((($this->price - $this->sale_price) / $this->price) * 100, 2);
        }
        
        return $this->discount_percent;
    }

    /**
     * Check if variant is in stock
     */
    public function isInStock()
    {
        return $this->stock_quantity > 0;
    }

    /**
     * Check if variant is low on stock
     */
    public function isLowStock()
    {
        return $this->stock_quantity <= $this->low_stock_threshold && $this->stock_quantity > 0;
    }

    /**
     * Check if variant is out of stock
     */
    public function isOutOfStock()
    {
        return $this->stock_quantity <= 0;
    }

    /**
     * Decrease stock quantity
     */
    public function decreaseStock($quantity)
    {
        $this->decrement('stock_quantity', $quantity);
        
        // Log low stock if applicable
        if ($this->isLowStock()) {
            // Trigger low stock notification
            event(new \App\Events\LowStock($this));
        }
    }

    /**
     * Increase stock quantity
     */
    public function increaseStock($quantity)
    {
        $this->increment('stock_quantity', $quantity);
    }

    /**
     * Get formatted attributes as string
     */
    public function getFormattedAttributesAttribute()
    {
        return $this->attributeValues->map(function($attributeValue) {
            return $attributeValue->attribute->name . ': ' . $attributeValue->value;
        })->implode(', ');
    }

    /**
     * Get all flash sales for this variant
     */
    public function flashSales()
    {
        return $this->belongsToMany(FlashSale::class, 'flash_sale_products')
                    ->withPivot('flash_price', 'quantity_limit', 'sold_count')
                    ->withTimestamps();
    }

    /**
     * Scope for active variants
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for variants in stock
     */
    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    /**
     * Scope for default variant
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope for variants with sale
     */
    public function scopeOnSale($query)
    {
        return $query->where(function($q) {
            $q->whereNotNull('sale_price')
              ->where('sale_price', '<', 'price')
              ->orWhere('discount_percent', '>', 0);
        });
    }

    /**
     * Scope by price range
     */
    public function scopePriceBetween($query, $min, $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }
}