<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AttributeValue extends Model
{
    use HasUuids;

    protected $fillable = [
        'attribute_id',
        'value',
        'color_code',
        'image',
        'swatch_image',
        'sort_order',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the attribute that owns this value
     */
    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    /**
     * Get the product variants that have this attribute value
     */
    public function productVariants()
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_attribute_values');
    }

    /**
     * Get formatted value with color if applicable
     */
    public function getFormattedValueAttribute()
    {
        if ($this->color_code) {
            return [
                'value' => $this->value,
                'color_code' => $this->color_code,
            ];
        }
        
        return $this->value;
    }
}