<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttributeGroup;
use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttributeGroupController extends Controller
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
                ->paginate(20);  // Changed from get() to paginate()

            return view('admin.attributes.index', compact('attributes'));
        }

    public function create()
    {
        $attributes = Attribute::all();
        return view('admin.attribute-groups.create', compact('attributes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:attribute_groups',
            'description' => 'nullable|string',
            'sort_order' => 'integer',
            'attributes' => 'nullable|array',
        ]);

        $group = AttributeGroup::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => true,
        ]);

        if ($request->has('attributes')) {
            foreach ($request->attributes as $index => $attributeId) {
                $group->attributes()->attach($attributeId, ['sort_order' => $index]);
            }
        }

        return redirect()->route('admin.attribute-groups.index')->with('success', 'Group created successfully.');
    }

    public function edit(AttributeGroup $attributeGroup)
    {
        $attributes = Attribute::all();
        $assignedAttributes = $attributeGroup->attributes->pluck('id')->toArray();
        return view('admin.attribute-groups.edit', compact('attributeGroup', 'attributes', 'assignedAttributes'));
    }

    public function update(Request $request, AttributeGroup $attributeGroup)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:attribute_groups,name,' . $attributeGroup->id,
            'description' => 'nullable|string',
            'sort_order' => 'integer',
            'attributes' => 'nullable|array',
        ]);

        $attributeGroup->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        if ($request->has('attributes')) {
            $syncData = [];
            foreach ($request->attributes as $index => $attributeId) {
                $syncData[$attributeId] = ['sort_order' => $index];
            }
            $attributeGroup->attributes()->sync($syncData);
        } else {
            $attributeGroup->attributes()->sync([]);
        }

        return redirect()->route('admin.attribute-groups.index')->with('success', 'Group updated successfully.');
    }

    public function destroy(AttributeGroup $attributeGroup)
    {
        $attributeGroup->attributes()->detach();
        $attributeGroup->delete();
        return redirect()->route('admin.attribute-groups.index')->with('success', 'Group deleted successfully.');
    }
}