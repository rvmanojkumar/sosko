<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttributeGroup;
use App\Models\Attribute;
use App\Models\Category;
use App\Http\Resources\AttributeGroupResource;
use App\Http\Resources\AttributeGroupCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttributeGroupController extends Controller
{
    /**
     * Get all attribute groups
     */
    public function index(Request $request)
    {
        $groups = AttributeGroup::with(['attributes' => function($query) {
                $query->orderBy('pivot_sort_order');
            }])
            ->when($request->is_active !== null, function ($query) use ($request) {
                $query->where('is_active', $request->is_active);
            })
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($request->per_page ?? 20);
            
        return response()->json([
            'success' => true,
            'data' => new AttributeGroupCollection($groups)
        ]);
    }

    /**
     * Get all attribute groups for a specific category
     */
    public function getByCategory(Category $category, Request $request)
    {
        $groups = $category->attributeGroups()
            ->with(['attributes' => function($query) {
                $query->orderBy('pivot_sort_order');
            }])
            ->orderBy('pivot_sort_order')
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => AttributeGroupResource::collection($groups)
        ]);
    }

    /**
     * Create a new attribute group
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:attribute_groups,name',
            'description' => 'nullable|string',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($request->name);
        
        $group = AttributeGroup::create($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Attribute group created successfully',
            'data' => new AttributeGroupResource($group)
        ], 201);
    }

    /**
     * Get a specific attribute group
     */
    public function show(AttributeGroup $attributeGroup)
    {
        $attributeGroup->load(['attributes' => function($query) {
            $query->orderBy('pivot_sort_order')->with('values');
        }]);
        
        return response()->json([
            'success' => true,
            'data' => new AttributeGroupResource($attributeGroup)
        ]);
    }

    /**
     * Update an attribute group
     */
    public function update(Request $request, AttributeGroup $attributeGroup)
    {
        $request->validate([
            'name' => 'string|max:255|unique:attribute_groups,name,' . $attributeGroup->id,
            'description' => 'nullable|string',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        $data = $request->all();
        
        if ($request->has('name') && $request->name !== $attributeGroup->name) {
            $data['slug'] = Str::slug($request->name);
        }
        
        $attributeGroup->update($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Attribute group updated successfully',
            'data' => new AttributeGroupResource($attributeGroup)
        ]);
    }

    /**
     * Delete an attribute group
     */
    public function destroy(AttributeGroup $attributeGroup)
    {
        // Check if group has attributes
        if ($attributeGroup->attributes()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete attribute group that has attributes. Remove attributes first.'
            ], 400);
        }
        
        $attributeGroup->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Attribute group deleted successfully'
        ]);
    }

    /**
     * Add attribute to group
     */
    public function addAttribute(Request $request, AttributeGroup $attributeGroup, Attribute $attribute)
    {
        $request->validate([
            'sort_order' => 'integer',
        ]);
        
        // Check if attribute is already in group
        if ($attributeGroup->attributes()->where('attribute_id', $attribute->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Attribute already exists in this group'
            ], 400);
        }
        
        $attributeGroup->attributes()->attach($attribute->id, [
            'sort_order' => $request->sort_order ?? $attributeGroup->attributes()->count()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Attribute added to group successfully'
        ]);
    }

    /**
     * Remove attribute from group
     */
    public function removeAttribute(AttributeGroup $attributeGroup, Attribute $attribute)
    {
        $attributeGroup->attributes()->detach($attribute->id);
        
        return response()->json([
            'success' => true,
            'message' => 'Attribute removed from group successfully'
        ]);
    }

    /**
     * Update attribute sort order in group
     */
    public function updateAttributeOrder(Request $request, AttributeGroup $attributeGroup)
    {
        $request->validate([
            'attributes' => 'required|array',
            'attributes.*.id' => 'required|exists:attributes,id',
            'attributes.*.sort_order' => 'required|integer|min:0',
        ]);
        
        foreach ($request->attributes as $attributeData) {
            $attributeGroup->attributes()->updateExistingPivot($attributeData['id'], [
                'sort_order' => $attributeData['sort_order']
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Attribute order updated successfully'
        ]);
    }

    /**
     * Assign group to category
     */
    public function assignToCategory(Request $request, AttributeGroup $attributeGroup, Category $category)
    {
        $request->validate([
            'sort_order' => 'integer',
        ]);
        
        // Check if group is already assigned to category
        if ($category->attributeGroups()->where('attribute_group_id', $attributeGroup->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Group already assigned to this category'
            ], 400);
        }
        
        $category->attributeGroups()->attach($attributeGroup->id, [
            'sort_order' => $request->sort_order ?? $category->attributeGroups()->count()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Group assigned to category successfully'
        ]);
    }

    /**
     * Remove group from category
     */
    public function removeFromCategory(AttributeGroup $attributeGroup, Category $category)
    {
        $category->attributeGroups()->detach($attributeGroup->id);
        
        return response()->json([
            'success' => true,
            'message' => 'Group removed from category successfully'
        ]);
    }

    /**
     * Get group attributes with values
     */
    public function getAttributes(AttributeGroup $attributeGroup, Request $request)
    {
        $attributes = $attributeGroup->attributes()
            ->with(['values' => function($query) {
                $query->orderBy('sort_order');
            }])
            ->when($request->is_filterable !== null, function ($query) use ($request) {
                $query->where('is_filterable', $request->is_filterable);
            })
            ->orderBy('pivot_sort_order')
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => AttributeResource::collection($attributes)
        ]);
    }

    /**
     * Reorder groups
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'groups' => 'required|array',
            'groups.*.id' => 'required|exists:attribute_groups,id',
            'groups.*.sort_order' => 'required|integer|min:0',
        ]);
        
        foreach ($request->groups as $groupData) {
            AttributeGroup::where('id', $groupData['id'])->update([
                'sort_order' => $groupData['sort_order']
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Groups reordered successfully'
        ]);
    }
}