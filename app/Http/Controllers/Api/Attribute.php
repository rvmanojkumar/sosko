<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attribute extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'display_type',
        'is_required',
        'is_filterable',
        'is_global',
        'sort_order',
        'description',
        'validation_rules'
    ];

    protected $casts = [
        'validation_rules' => 'array',
        'is_required' => 'boolean',
        'is_filterable' => 'boolean',
        'is_global' => 'boolean',
    ];

    /**
     * Get the values for this attribute
     */
    public function values()
    {
        return $this->hasMany(AttributeValue::class)->orderBy('sort_order');
    }

    /**
     * Get categories that have this attribute
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_attribute_mappings')
                    ->withPivot('is_required', 'is_filterable', 'sort_order')
                    ->withTimestamps();
    }

    /**
     * Scope for global attributes
     */
    public function scopeGlobal($query)
    {
        return $query->where('is_global', true);
    }

    /**
     * Scope for filterable attributes
     */
    public function scopeFilterable($query)
    {
        return $query->where('is_filterable', true);
    }
}