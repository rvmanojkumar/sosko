<?php

namespace App\Http\Controllers\Api;

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductVariantResource;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Helpers\StorageHelper;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()
            ->with(['variants', 'images', 'category'])
            ->where('is_active', true)
            ->when($request->category_id, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($request->vendor_id, function ($query, $vendorId) {
                $query->where('vendor_id', $vendorId);
            })
            ->when($request->search, function ($query, $search) {
                $query->whereFullText(['name', 'description'], $search);
            })
            ->when($request->min_price, function ($query, $minPrice) {
                $query->whereHas('variants', function ($q) use ($minPrice) {
                    $q->where('price', '>=', $minPrice);
                });
            })
            ->when($request->max_price, function ($query, $maxPrice) {
                $query->whereHas('variants', function ($q) use ($maxPrice) {
                    $q->where('price', '<=', $maxPrice);
                });
            })
            ->when($request->rating, function ($query, $rating) {
                $query->having('average_rating', '>=', $rating);
            })
            ->withAvg('reviews as average_rating', 'rating')
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc')
            ->paginate($request->per_page ?? 20);
            
        return ProductResource::collection($products);
    }

    public function store(ProductRequest $request)
    {
        $user = $request->user();
        
        // Check vendor subscription limits
        $vendorProfile = $user->vendorProfile;
        if ($vendorProfile->products()->count() >= $vendorProfile->subscription->plan->max_products) {
            return response()->json(['message' => 'Product limit reached for your subscription'], 403);
        }
        
        DB::beginTransaction();
        
        try {
            $product = Product::create([
                'vendor_id' => $user->id,
                'category_id' => $request->category_id,
                'supplier_id' => $request->supplier_id,
                'name' => $request->name,
                'slug' => Str::slug($request->name) . '-' . Str::random(6),
                'description' => $request->description,
                'short_description' => $request->short_description,
                'brand' => $request->brand,
                'weight' => $request->weight,
                'specifications' => $request->specifications,
                'is_featured' => $request->is_featured ?? false,
                'is_active' => $request->is_active ?? true,
                'seo_data' => $request->seo_data
            ]);
            
            // Create variants
            foreach ($request->variants as $variantData) {
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'supplier_id' => $variantData['supplier_id'] ?? $request->supplier_id,
                    'sku' => $variantData['sku'],
                    'price' => $variantData['price'],
                    'sale_price' => $variantData['sale_price'] ?? null,
                    'discount_percent' => $variantData['discount_percent'] ?? 0,
                    'stock_quantity' => $variantData['stock_quantity'],
                    'weight' => $variantData['weight'] ?? $request->weight,
                    'is_default' => $variantData['is_default'] ?? false
                ]);
                
                // Attach attribute values
                if (isset($variantData['attribute_values'])) {
                    $variant->attributeValues()->sync($variantData['attribute_values']);
                }
            }
            
            // Upload images
            if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $upload = StorageHelper::uploadFile($image, 'products/' . $product->id);
                if ($upload['success']) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $upload['path'],
                        'sort_order' => $index,
                        'is_primary' => $index === 0
                    ]);
                }
            }
        }

            
            DB::commit();
            
            return new ProductResource($product->load(['variants', 'images']));
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create product', 'error' => $e->getMessage()], 500);
        }
    }

     public function show(Product $product)
    {
        $product->increment('view_count');
        return new ProductResource($product->load(['variants.attributeValues', 'images', 'reviews.user', 'questions.answers']));
    }

    public function update(ProductRequest $request, Product $product)
    {
        $this->authorize('update', $product);
        
        DB::beginTransaction();
        
        try {
            $product->update($request->validated());
            
            // Update variants
            if ($request->has('variants')) {
                $existingVariantIds = $product->variants->pluck('id')->toArray();
                $updatedVariantIds = [];
                
                foreach ($request->variants as $variantData) {
                    if (isset($variantData['id'])) {
                        $variant = ProductVariant::find($variantData['id']);
                        $variant->update($variantData);
                        $updatedVariantIds[] = $variant->id;
                    } else {
                        $variant = ProductVariant::create([
                            'product_id' => $product->id,
                            ...$variantData
                        ]);
                        $updatedVariantIds[] = $variant->id;
                    }
                    
                    // Update attribute values
                    if (isset($variantData['attribute_values'])) {
                        $variant->attributeValues()->sync($variantData['attribute_values']);
                    }
                }
                
                // Delete removed variants
                $toDelete = array_diff($existingVariantIds, $updatedVariantIds);
                ProductVariant::whereIn('id', $toDelete)->delete();
            }
            
            DB::commit();
            
            return new ProductResource($product->load(['variants', 'images']));
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update product'], 500);
        }
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);
        
        $product->delete();
        
        return response()->json(['message' => 'Product deleted successfully']);
    }

    public function variants(Product $product)
    {
        return ProductVariantResource::collection($product->variants()->with('attributeValues')->get());
    }
}