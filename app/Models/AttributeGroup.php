<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttributeGroup extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'is_active' => true,
        'sort_order' => 0,
    ];

    /**
     * Get the attributes in this group
     */
    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'attribute_group_mappings')
                    ->withPivot('sort_order')
                    ->withTimestamps()
                    ->orderBy('pivot_sort_order');
    }

    /**
     * Get the categories that use this attribute group
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_attribute_groups')
                    ->withPivot('sort_order')
                    ->withTimestamps();
    }

    /**
     * Get active attributes in this group
     */
    public function activeAttributes()
    {
        return $this->attributes()->where('is_active', true);
    }

    /**
     * Get attribute count
     */
    public function getAttributeCountAttribute()
    {
        return $this->attributes()->count();
    }

    /**
     * Get formatted name with count
     */
    public function getFormattedNameAttribute()
    {
        return $this->name . ' (' . $this->attribute_count . ' attributes)';
    }

    /**
     * Scope for active groups
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope ordered by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Scope for groups with attributes
     */
    public function scopeWithAttributes($query)
    {
        return $query->has('attributes');
    }
}