<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Helpers\StorageHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        try {
            $banners = Banner::where('is_active', true)
                ->where(function($query) {
                    $query->whereNull('start_date')
                        ->orWhere('start_date', '<=', now());
                })
                ->where(function($query) {
                    $query->whereNull('end_date')
                        ->orWhere('end_date', '>=', now());
                })
                ->orderBy('sort_order')
                ->get();
            
            // Transform each banner with proper URL
            $banners = $banners->map(function($banner) {
                return [
                    'id' => $banner->id,
                    'title' => $banner->title,
                    'subtitle' => $banner->subtitle,
                    'cta_text' => $banner->cta_text,
                    'cta_link' => $banner->cta_link,
                    'image_url' => StorageHelper::getFileUrl($banner->image_path),
                    'type' => $banner->type,
                    'target_type' => $banner->target_type,
                    'target_id' => $banner->target_id,
                    'sort_order' => $banner->sort_order,
                    'is_active' => $banner->is_active,
                    'start_date' => $banner->start_date,
                    'end_date' => $banner->end_date,
                    'created_at' => $banner->created_at,
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $banners
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching banners: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch banners'
            ], 500);
        }
    }
    
    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
                'type' => 'required|in:hero_slider,category_banner,popup,app_notification',
                'subtitle' => 'nullable|string',
                'cta_text' => 'nullable|string',
                'cta_link' => 'nullable|url',
                'target_type' => 'nullable|in:all,category,vendor',
                'target_id' => 'nullable|uuid',
                'sort_order' => 'integer',
                'is_active' => 'boolean',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after:start_date',
            ]);
            
            // Upload image using helper (now using local storage)
            $upload = StorageHelper::uploadFile($request->file('image'), 'banners');
            
            if (!$upload['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to upload image: ' . $upload['message']
                ], 500);
            }
            
            $banner = Banner::create([
                'title' => $request->title,
                'subtitle' => $request->subtitle,
                'cta_text' => $request->cta_text,
                'cta_link' => $request->cta_link,
                'image_path' => $upload['path'],
                'type' => $request->type,
                'target_type' => $request->target_type,
                'target_id' => $request->target_id,
                'sort_order' => $request->sort_order ?? 0,
                'is_active' => $request->is_active ?? true,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Banner created successfully',
                'data' => [
                    'id' => $banner->id,
                    'title' => $banner->title,
                    'image_url' => $upload['url'],
                    'type' => $banner->type,
                ]
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Error creating banner: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create banner: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function update(Request $request, $id)
    {
        try {
            $banner = Banner::findOrFail($id);
            
            $request->validate([
                'title' => 'sometimes|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
                'type' => 'sometimes|in:hero_slider,category_banner,popup,app_notification',
                'subtitle' => 'nullable|string',
                'cta_text' => 'nullable|string',
                'cta_link' => 'nullable|url',
                'target_type' => 'nullable|in:all,category,vendor',
                'target_id' => 'nullable|uuid',
                'sort_order' => 'integer',
                'is_active' => 'boolean',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after:start_date',
            ]);
            
            $data = $request->except('image');
            
            // Handle image update
            if ($request->hasFile('image')) {
                // Delete old image
                StorageHelper::deleteFile($banner->image_path);
                
                // Upload new image
                $upload = StorageHelper::uploadFile($request->file('image'), 'banners');
                
                if (!$upload['success']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to upload image: ' . $upload['message']
                    ], 500);
                }
                
                $data['image_path'] = $upload['path'];
            }
            
            $banner->update($data);
            
            return response()->json([
                'success' => true,
                'message' => 'Banner updated successfully',
                'data' => $banner
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error updating banner: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update banner: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function destroy($id)
    {
        try {
            $banner = Banner::findOrFail($id);
            
            // Delete image file
            StorageHelper::deleteFile($banner->image_path);
            
            $banner->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Banner deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deleting banner: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete banner'
            ], 500);
        }
    }
}