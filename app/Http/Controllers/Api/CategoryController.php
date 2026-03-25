<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Helpers\StorageHelper;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'icon' => 'nullable|image|max:2048',
            'banner_image' => 'nullable|image|max:2048',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'meta_data' => 'nullable|array',
        ]);

        $data = $request->except(['icon', 'banner_image']);
        $data['slug'] = Str::slug($request->name);
        
        // Handle icon upload - store only the path
        if ($request->hasFile('icon')) {
            $upload = StorageHelper::uploadFile($request->file('icon'), 'categories/icons');
            if ($upload['success']) {
                $data['icon'] = $upload['path'];
            } else {
                return response()->json(['message' => 'Icon upload failed'], 500);
            }   
        }
        
        // Handle banner upload - store only the path
        if ($request->hasFile('banner_image')) {
            $upload = StorageHelper::uploadFile($request->file('banner_image'), 'categories/banners');
            if ($upload['success']) {
                $data['banner_image'] = $upload['path'];
            } else {
                return response()->json(['message' => 'Banner image upload failed'], 500);
            }
        }
        
        $category = Category::create($data);
        
        // Transform the response to include URLs
        return new CategoryResource($category);
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'icon' => 'nullable|image|max:2048',
            'banner_image' => 'nullable|image|max:2048',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'meta_data' => 'nullable|array',
        ]);

        $data = $request->except(['icon', 'banner_image']);
        $data = $request->except(['icon', 'banner_image']);
        
        if ($request->has('name')) {
            $data['slug'] = Str::slug($request->name);
        }
        
        // Handle icon upload
        if ($request->hasFile('icon')) {
            // Delete old icon
            if ($category->icon) {
                StorageHelper::deleteFile($category->icon);
            }
            $upload = StorageHelper::uploadFile($request->file('icon'), 'categories/icons');
            if ($upload['success']) {
                $data['icon'] = $upload['path'];
            }
        }
        
        // Handle banner upload
        if ($request->hasFile('banner_image')) {
            // Delete old banner
            if ($category->banner_image) {
                StorageHelper::deleteFile($category->banner_image);
            }
            $upload = StorageHelper::uploadFile($request->file('banner_image'), 'categories/banners');
            if ($upload['success']) {
                $data['banner_image'] = $upload['path'];
            }
        }
        
        $category->update($data);
        
        return response()->json($category);
    }

    public function destroy(Category $category)
    {
        if ($category->icon) {
            StorageHelper::deleteFile($category->icon);
        }
        if ($category->banner_image) {
            StorageHelper::deleteFile($category->banner_image);
        }
        
        $category->delete();
        
        return response()->json(['message' => 'Category deleted successfully']);

    }
}