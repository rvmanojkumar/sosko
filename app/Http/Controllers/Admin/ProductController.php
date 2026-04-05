<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
    {
        /**
         * Display a listing of products
         */
        public function index(Request $request)
    {
        $products = Product::with(['vendor', 'category', 'variants', 'images'])
            ->when($request->search, function($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            })
            ->when($request->category, function($query, $category) {
                $query->where('category_id', $category);
            })
            ->when($request->vendor, function($query, $vendor) {
                $query->where('vendor_id', $vendor);
            })
            ->when($request->status !== null, function($query) use ($request) {
                $query->where('is_active', $request->status);
            })
            ->orderBy($request->sort ?? 'created_at', $request->order ?? 'desc')
            ->paginate(15);

        // Make sure to handle null relationships in the view
        $categories = Category::where('is_active', true)->get();
        $vendors = User::whereHas('roles', fn($q) => $q->where('name', 'vendor'))->get();

        return view('admin.products.index', compact('products', 'categories', 'vendors'));
    }

    /**
     * Show the form for creating a new product
     */
    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        $vendors = User::whereHas('roles', fn($q) => $q->where('name', 'vendor'))->get();
        $attributes = \App\Models\Attribute::with('values')->get();
        
        return view('admin.products.create', compact('categories', 'vendors', 'attributes'));
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request)
{
    // Debug: Log the incoming request
    Log::info('=== PRODUCT CREATION ATTEMPT ===');
    Log::info('Form data:', $request->except('_token'));
    Log::info('Has files: ' . ($request->hasFile('images') ? 'Yes' : 'No'));
    
    // Validate
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'required|string',
        'short_description' => 'nullable|string|max:500',
        'category_id' => 'required|exists:categories,id',
        'vendor_id' => 'required|exists:users,id',
        'brand' => 'nullable|string|max:100',
        'weight' => 'nullable|numeric',
        'specifications' => 'nullable|array',
        'price' => 'required|numeric|min:0',
        'sale_price' => 'nullable|numeric|min:0|lt:price',
        'stock_quantity' => 'required|integer|min:0',
        'sku' => 'required|string|unique:product_variants,sku',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
    ]);

    Log::info('Validation passed');

    try {
        // Create product
        $product = Product::create([
            'vendor_id' => $request->vendor_id,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . Str::random(6),
            'description' => $request->description,
            'short_description' => $request->short_description,
            'brand' => $request->brand,
            'weight' => $request->weight,
            'specifications' => $request->specifications,
            'is_featured' => $request->is_featured ?? false,
            'is_active' => $request->is_active ?? true,
            'seo_data' => $request->seo_data,
        ]);

        Log::info('Product created', ['product_id' => $product->id]);

        // Create variant
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $request->sku,
            'price' => $request->price,
            'sale_price' => $request->sale_price,
            'discount_percent' => $request->sale_price ? round((($request->price - $request->sale_price) / $request->price) * 100, 2) : 0,
            'stock_quantity' => $request->stock_quantity,
            'low_stock_threshold' => $request->low_stock_threshold ?? 5,
            'weight' => $request->weight,
            'is_default' => true,
            'is_active' => true,
        ]);

        Log::info('Variant created', ['variant_id' => $variant->id]);

        // Upload images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('products/' . $product->id, 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                    'sort_order' => $index,
                    'is_primary' => $index === 0,
                    'alt_text' => $product->name,
                ]);
            }
            Log::info('Images uploaded', ['count' => count($request->file('images'))]);
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');

    } catch (\Exception $e) {
        Log::error('Product creation failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return redirect()->back()
            ->with('error', 'Error creating product: ' . $e->getMessage())
            ->withInput();
    }
}

    /**
     * Display the specified product
     */
    public function show(Product $product)
    {
        $product->load(['vendor', 'category', 'variants.attributeValues.attribute', 'images', 'reviews.user']);
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product
     */
    public function edit(Product $product)
    {
        $product->load(['variants.attributeValues', 'images']);
        $categories = Category::where('is_active', true)->get();
        $vendors = User::whereHas('roles', fn($q) => $q->where('name', 'vendor'))->get();
        $attributes = \App\Models\Attribute::with('values')->get();
        
        return view('admin.products.edit', compact('product', 'categories', 'vendors', 'attributes'));
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'category_id' => 'required|exists:categories,id',
            'vendor_id' => 'required|exists:users,id',
            'brand' => 'nullable|string|max:100',
            'weight' => 'nullable|numeric',
            'specifications' => 'nullable|array',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'stock_quantity' => 'required|integer|min:0',
            'sku' => 'required|string|unique:product_variants,sku,' . ($product->variants->first()->id ?? ''),
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        try {
            // Update product
            $product->update([
                'vendor_id' => $request->vendor_id,
                'category_id' => $request->category_id,
                'name' => $request->name,
                'slug' => $request->name !== $product->name ? Str::slug($request->name) . '-' . Str::random(6) : $product->slug,
                'description' => $request->description,
                'short_description' => $request->short_description,
                'brand' => $request->brand,
                'weight' => $request->weight,
                'specifications' => $request->specifications,
                'is_featured' => $request->is_featured ?? false,
                'is_active' => $request->is_active ?? true,
                'seo_data' => $request->seo_data,
            ]);

            // Update or create variant
            $variant = $product->variants()->first();
            if ($variant) {
                $variant->update([
                    'sku' => $request->sku,
                    'price' => $request->price,
                    'sale_price' => $request->sale_price,
                    'discount_percent' => $request->sale_price ? round((($request->price - $request->sale_price) / $request->price) * 100, 2) : 0,
                    'stock_quantity' => $request->stock_quantity,
                    'low_stock_threshold' => $request->low_stock_threshold ?? 5,
                    'weight' => $request->weight,
                ]);

                // Update attribute values
                if ($request->has('attribute_values')) {
                    $variant->attributeValues()->sync($request->attribute_values);
                }
            }

            // Upload new images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('products/' . $product->id, 'public');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $path,
                        'sort_order' => $product->images()->count(),
                        'is_primary' => false,
                        'alt_text' => $product->name,
                    ]);
                }
            }

            return redirect()->route('admin.products.index')
                ->with('success', 'Product updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating product: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified product
     */
    public function destroy(Product $product)
    {
        try {
            // Delete product images from storage
            foreach ($product->images as $image) {
                if (Storage::disk('public')->exists($image->image_path)) {
                    Storage::disk('public')->delete($image->image_path);
                }
            }
            
            // Delete product (variants will be cascade deleted)
            $product->delete();

            return redirect()->route('admin.products.index')
                ->with('success', 'Product deleted successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting product: ' . $e->getMessage());
        }
    }

    /**
     * Delete a specific image from product
     */
    public function deleteImage($productId, $imageId)
    {
        try {
            $image = ProductImage::where('product_id', $productId)->findOrFail($imageId);
            
            if (Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }
            
            $image->delete();

            return response()->json(['success' => true, 'message' => 'Image deleted successfully.']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting image.'], 500);
        }
    }

    /**
     * Set an image as primary
     */
    public function setPrimaryImage($productId, $imageId)
    {
        try {
            // Reset all images to non-primary
            ProductImage::where('product_id', $productId)->update(['is_primary' => false]);
            
            // Set the selected image as primary
            $image = ProductImage::where('product_id', $productId)->findOrFail($imageId);
            $image->update(['is_primary' => true]);

            return response()->json(['success' => true, 'message' => 'Primary image updated.']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error updating primary image.'], 500);
        }
    }

    /**
     * Toggle product status (active/inactive)
     */
    public function toggleStatus(Product $product)
    {
        try {
            $product->update(['is_active' => !$product->is_active]);
            
            $status = $product->is_active ? 'activated' : 'deactivated';
            return redirect()->back()->with('success', "Product {$status} successfully.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error toggling product status.');
        }
    }

    /**
     * Bulk delete products
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:products,id',
        ]);

        try {
            $products = Product::whereIn('id', $request->ids)->get();
            
            foreach ($products as $product) {
                // Delete images
                foreach ($product->images as $image) {
                    if (Storage::disk('public')->exists($image->image_path)) {
                        Storage::disk('public')->delete($image->image_path);
                    }
                }
                $product->delete();
            }

            return redirect()->route('admin.products.index')
                ->with('success', count($request->ids) . ' products deleted successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting products.');
        }
    }
}