<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'parent_id', 'name', 'slug', 'description', 'icon', 
        'banner_image', 'sort_order', 'is_active', 'meta_data', 'seo_data'
    ];

    protected $casts = [
        'meta_data' => 'array',
        'seo_data' => 'array',
        'is_active' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'category_attribute_mappings')
                    ->withPivot('is_required', 'is_filterable', 'sort_order')
                    ->withTimestamps()
                    ->orderBy('pivot_sort_order');
    }

    public function getFilterableAttributes()
    {
        return $this->attributes()->wherePivot('is_filterable', true)->get();
    }

    public function getRequiredAttributes()
    {
        return $this->attributes()->wherePivot('is_required', true)->get();
    }

    public function getAllChildrenIds()
    {
        $ids = [$this->id];
        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->getAllChildrenIds());
        }
        return $ids;
    }

    public function attributeGroups()
{
    return $this->belongsToMany(AttributeGroup::class, 'category_attribute_groups')
                ->withPivot('sort_order')
                ->withTimestamps()
                ->orderBy('pivot_sort_order');
}

/**
 * Get all attributes (organized by groups) for this category
 */
public function getOrganizedAttributesAttribute()
{
    $groups = $this->attributeGroups()->with(['attributes' => function($query) {
        $query->orderBy('pivot_sort_order')->with('values');
    }])->get();
    
    return $groups->map(function($group) {
        return [
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'description' => $group->description,
            ],
            'attributes' => $group->attributes->map(function($attribute) {
                return [
                    'id' => $attribute->id,
                    'name' => $attribute->name,
                    'type' => $attribute->type,
                    'display_type' => $attribute->display_type,
                    'is_required' => $attribute->is_required,
                    'is_filterable' => $attribute->is_filterable,
                    'values' => $attribute->values->map(function($value) {
                        return [
                            'id' => $value->id,
                            'value' => $value->value,
                            'color_code' => $value->color_code,
                            'image' => $value->image,
                        ];
                    }),
                ];
            }),
        ];
    });
}
}