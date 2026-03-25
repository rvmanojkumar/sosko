<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attribute extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'type', 'display_type', 'is_required', 
        'is_filterable', 'is_global', 'sort_order', 'description', 
        'validation_rules'
    ];

    protected $casts = [
        'validation_rules' => 'array',
        'is_required' => 'boolean',
        'is_filterable' => 'boolean',
        'is_global' => 'boolean',
    ];

    public function values()
    {
        return $this->hasMany(AttributeValue::class)->orderBy('sort_order');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_attribute_mappings')
                    ->withPivot('is_required', 'is_filterable', 'sort_order')
                    ->withTimestamps();
    }

    public function attributeGroups()
    {
        return $this->belongsToMany(AttributeGroup::class, 'attribute_group_mappings')
                    ->withPivot('sort_order')
                    ->withTimestamps();
    }

    public function productVariants()
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_attribute_values', 
                                    'attribute_id', 'product_variant_id')
                    ->through('attributeValue')
                    ->withPivot('attribute_value_id');
    }

    public function scopeGlobal($query)
    {
        return $query->where('is_global', true);
    }

    public function scopeFilterable($query)
    {
        return $query->where('is_filterable', true);
    }
}