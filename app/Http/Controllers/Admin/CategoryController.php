<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Resources\CategoryResource;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::with('parent', 'attributes')
            ->when($request->search, function($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->paginate(20);

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $categories = Category::whereNull('parent_id')->get();
        $attributes = Attribute::with('values')->get();
        $attributeGroups = AttributeGroup::all();
        
        return view('admin.categories.create', compact('categories', 'attributes', 'attributeGroups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'icon' => 'nullable|image|max:2048',
            'banner_image' => 'nullable|image|max:2048',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'attributes' => 'nullable|array',
            'attribute_groups' => 'nullable|array',
        ]);

        $data = $request->except(['icon', 'banner_image', 'attributes', 'attribute_groups']);
        $data['slug'] = Str::slug($request->name);

        if ($request->hasFile('icon')) {
            $path = $request->file('icon')->store('categories/icons', 'public');
            $data['icon'] = $path;
        }

        if ($request->hasFile('banner_image')) {
            $path = $request->file('banner_image')->store('categories/banners', 'public');
            $data['banner_image'] = $path;
        }

        $category = Category::create($data);

        // Attach attributes to category
        if ($request->has('attributes')) {
            foreach ($request->attributes as $attributeId => $attributeData) {
                if (isset($attributeData['id'])) {
                    $category->attributes()->attach($attributeId, [
                        'is_required' => $attributeData['is_required'] ?? false,
                        'is_filterable' => $attributeData['is_filterable'] ?? true,
                        'sort_order' => $attributeData['sort_order'] ?? 0,
                    ]);
                }
            }
        }

        // Attach attribute groups to category
        if ($request->has('attribute_groups')) {
            foreach ($request->attribute_groups as $groupId => $groupData) {
                if (isset($groupData['id'])) {
                    $category->attributeGroups()->attach($groupId, [
                        'sort_order' => $groupData['sort_order'] ?? 0,
                    ]);
                }
            }
        }

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        $categories = Category::whereNull('parent_id')->where('id', '!=', $category->id)->get();
        $attributes = Attribute::with('values')->get();
        $attributeGroups = AttributeGroup::all();
        
        // Get currently assigned attributes with pivot data
        $assignedAttributes = $category->attributes()->get()->keyBy('id');
        $assignedGroups = $category->attributeGroups()->get()->keyBy('id');
        
        return view('admin.categories.edit', compact(
            'category', 'categories', 'attributes', 'attributeGroups',
            'assignedAttributes', 'assignedGroups'
        ));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'icon' => 'nullable|image|max:2048',
            'banner_image' => 'nullable|image|max:2048',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'attributes' => 'nullable|array',
            'attribute_groups' => 'nullable|array',
        ]);

        $data = $request->except(['icon', 'banner_image', 'attributes', 'attribute_groups']);

        if ($request->has('name') && $request->name !== $category->name) {
            $data['slug'] = Str::slug($request->name);
        }

        if ($request->hasFile('icon')) {
            if ($category->icon && Storage::disk('public')->exists($category->icon)) {
                Storage::disk('public')->delete($category->icon);
            }
            $path = $request->file('icon')->store('categories/icons', 'public');
            $data['icon'] = $path;
        }

        if ($request->hasFile('banner_image')) {
            if ($category->banner_image && Storage::disk('public')->exists($category->banner_image)) {
                Storage::disk('public')->delete($category->banner_image);
            }
            $path = $request->file('banner_image')->store('categories/banners', 'public');
            $data['banner_image'] = $path;
        }

        $category->update($data);

        // Sync attributes
        if ($request->has('attributes')) {
            $attributesData = [];
            foreach ($request->attributes as $attributeId => $attributeData) {
                if (isset($attributeData['id'])) {
                    $attributesData[$attributeId] = [
                        'is_required' => $attributeData['is_required'] ?? false,
                        'is_filterable' => $attributeData['is_filterable'] ?? true,
                        'sort_order' => $attributeData['sort_order'] ?? 0,
                    ];
                }
            }
            $category->attributes()->sync($attributesData);
        } else {
            $category->attributes()->sync([]);
        }

        // Sync attribute groups
        if ($request->has('attribute_groups')) {
            $groupsData = [];
            foreach ($request->attribute_groups as $groupId => $groupData) {
                if (isset($groupData['id'])) {
                    $groupsData[$groupId] = [
                        'sort_order' => $groupData['sort_order'] ?? 0,
                    ];
                }
            }
            $category->attributeGroups()->sync($groupsData);
        } else {
            $category->attributeGroups()->sync([]);
        }

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete category with products.');
        }

        if ($category->icon && Storage::disk('public')->exists($category->icon)) {
            Storage::disk('public')->delete($category->icon);
        }
        if ($category->banner_image && Storage::disk('public')->exists($category->banner_image)) {
            Storage::disk('public')->delete($category->banner_image);
        }

        // Detach all attributes and groups
        $category->attributes()->detach();
        $category->attributeGroups()->detach();

        $category->delete();
        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    // API endpoint for getting category attributes (used by product page)
    public function getAttributes(Category $category)
    {
        $attributes = $category->attributes()
            ->with(['values' => function($query) {
                $query->orderBy('sort_order');
            }])
            ->orderBy('pivot_sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $attributes
        ]);
    }
}