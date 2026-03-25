<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'brand' => $this->brand,
            'category_id' => $this->category_id,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'vendor_id' => $this->vendor_id,
            'vendor' => new VendorResource($this->whenLoaded('vendor')),
            'supplier_id' => $this->supplier_id,
            'supplier' => new SupplierResource($this->whenLoaded('supplier')),
            'images' => $this->images->map(function($image) {
                return [
                    'id' => $image->id,
                    'url' => Storage::disk('public')->url($image->image_path),
                    'is_primary' => (bool) $image->is_primary,
                    'sort_order' => (int) $image->sort_order,
                ];
            }),
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'price' => (float) ($this->default_variant->price ?? 0),
            'sale_price' => $this->default_variant->sale_price ? (float) $this->default_variant->sale_price : null,
            'current_price' => (float) ($this->default_variant->current_price ?? 0),
            'stock_quantity' => (int) $this->variants->sum('stock_quantity'),
            'average_rating' => (float) $this->average_rating,
            'review_count' => (int) $this->review_count,
            'is_featured' => (bool) $this->is_featured,
            'is_active' => (bool) $this->is_active,
            'weight' => $this->weight ? (float) $this->weight : null,
            'specifications' => $this->specifications,
            'view_count' => (int) $this->view_count,
            'sold_count' => (int) $this->sold_count,
            'seo_data' => $this->seo_data,
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toISOString() : null,
        ];
    }
}