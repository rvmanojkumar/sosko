<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Attribute;
use Illuminate\Http\Request;

class CategoryAttributeController extends Controller
{
    public function index(Category $category)
    {
        $attributes = $category->attributes()
            ->with(['values', 'attributeGroup'])
            ->get();
            
        return response()->json([
            'attributes' => $attributes,
            'filterable_attributes' => $category->getFilterableAttributes(),
            'required_attributes' => $category->getRequiredAttributes(),
        ]);
    }

    public function attach(Request $request, Category $category)
    {
        $request->validate([
            'attribute_id' => 'required|exists:attributes,id',
            'is_required' => 'boolean',
            'is_filterable' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $category->attributes()->syncWithoutDetaching([
            $request->attribute_id => [
                'is_required' => $request->is_required ?? false,
                'is_filterable' => $request->is_filterable ?? true,
                'sort_order' => $request->sort_order ?? 0,
            ]
        ]);

        return response()->json(['message' => 'Attribute attached successfully']);
    }

    public function detach(Category $category, Attribute $attribute)
    {
        $category->attributes()->detach($attribute->id);
        
        return response()->json(['message' => 'Attribute detached successfully']);
    }

    public function updatePivot(Request $request, Category $category, Attribute $attribute)
    {
        $request->validate([
            'is_required' => 'boolean',
            'is_filterable' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $category->attributes()->updateExistingPivot($attribute->id, [
            'is_required' => $request->is_required ?? false,
            'is_filterable' => $request->is_filterable ?? true,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return response()->json(['message' => 'Attribute settings updated']);
    }
}