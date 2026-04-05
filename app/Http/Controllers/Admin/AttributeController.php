<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AttributeController extends Controller
{
    public function index(Request $request)
    {
        $attributes = Attribute::with('values')
            ->when($request->search, function($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->type, function($query, $type) {
                $query->where('type', $type);
            })
            ->orderBy('sort_order')
            ->paginate(20);

        return view('admin.attributes.index', compact('attributes'));
    }

    public function create()
    {
        return view('admin.attributes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:attributes',
            'type' => 'required|in:select,color,size,radio,checkbox',
            'display_type' => 'required|in:dropdown,button,swatch',
            'is_required' => 'boolean',
            'is_filterable' => 'boolean',
            'is_global' => 'boolean',
            'sort_order' => 'integer',
            'description' => 'nullable|string',
            'values' => 'nullable|array',
            'values.*.value' => 'required|string',
            'values.*.color_code' => 'nullable|string',
            'values.*.sort_order' => 'integer',
        ]);

        $attribute = Attribute::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'type' => $request->type,
            'display_type' => $request->display_type,
            'is_required' => $request->is_required ?? false,
            'is_filterable' => $request->is_filterable ?? true,
            'is_global' => $request->is_global ?? true,
            'sort_order' => $request->sort_order ?? 0,
            'description' => $request->description,
        ]);

        // Create attribute values
        if ($request->has('values')) {
            foreach ($request->values as $index => $valueData) {
                AttributeValue::create([
                    'attribute_id' => $attribute->id,
                    'value' => $valueData['value'],
                    'color_code' => $valueData['color_code'] ?? null,
                    'sort_order' => $valueData['sort_order'] ?? $index,
                ]);
            }
        }

        return redirect()->route('admin.attributes.index')
            ->with('success', 'Attribute created successfully.');
    }

    public function edit(Attribute $attribute)
    {
        $attribute->load('values');
        return view('admin.attributes.edit', compact('attribute'));
    }

    public function update(Request $request, Attribute $attribute)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:attributes,name,' . $attribute->id,
            'type' => 'required|in:select,color,size,radio,checkbox',
            'display_type' => 'required|in:dropdown,button,swatch',
            'is_required' => 'boolean',
            'is_filterable' => 'boolean',
            'is_global' => 'boolean',
            'sort_order' => 'integer',
            'description' => 'nullable|string',
            'values' => 'nullable|array',
            'values.*.id' => 'nullable|exists:attribute_values,id',
            'values.*.value' => 'required|string',
            'values.*.color_code' => 'nullable|string',
            'values.*.sort_order' => 'integer',
        ]);

        $attribute->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'type' => $request->type,
            'display_type' => $request->display_type,
            'is_required' => $request->is_required ?? false,
            'is_filterable' => $request->is_filterable ?? true,
            'is_global' => $request->is_global ?? true,
            'sort_order' => $request->sort_order ?? 0,
            'description' => $request->description,
        ]);

        // Handle values
        $existingValueIds = [];
        
        if ($request->has('values')) {
            foreach ($request->values as $valueData) {
                if (isset($valueData['id'])) {
                    // Update existing value
                    $value = AttributeValue::find($valueData['id']);
                    if ($value) {
                        $value->update([
                            'value' => $valueData['value'],
                            'color_code' => $valueData['color_code'] ?? null,
                            'sort_order' => $valueData['sort_order'] ?? 0,
                        ]);
                        $existingValueIds[] = $value->id;
                    }
                } else {
                    // Create new value
                    $newValue = AttributeValue::create([
                        'attribute_id' => $attribute->id,
                        'value' => $valueData['value'],
                        'color_code' => $valueData['color_code'] ?? null,
                        'sort_order' => $valueData['sort_order'] ?? 0,
                    ]);
                    $existingValueIds[] = $newValue->id;
                }
            }
        }

        // Delete values that were removed
        AttributeValue::where('attribute_id', $attribute->id)
            ->whereNotIn('id', $existingValueIds)
            ->delete();

        return redirect()->route('admin.attributes.index')
            ->with('success', 'Attribute updated successfully.');
    }

    public function destroy(Attribute $attribute)
    {
        // Check if attribute is used in any category
        if ($attribute->categories()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete attribute that is assigned to categories.');
        }

        // Delete all values first
        $attribute->values()->delete();
        $attribute->delete();

        return redirect()->route('admin.attributes.index')
            ->with('success', 'Attribute deleted successfully.');
    }

    // API endpoint for getting all attributes (for AJAX)
    public function getAllAttributes()
    {
        $attributes = Attribute::with('values')->orderBy('sort_order')->get();
        return response()->json($attributes);
    }
}