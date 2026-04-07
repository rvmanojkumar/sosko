<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\StorageHelper;

class ProductVariantResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'sku' => $this->sku,
            'price' => (float) $this->price,
            'sale_price' => $this->sale_price ? (float) $this->sale_price : null,
            'current_price' => (float) $this->current_price,
            'discount_percent' => (float) $this->discount_percent,
            'stock_quantity' => (int) $this->stock_quantity,
            'low_stock_threshold' => (int) $this->low_stock_threshold,
            'weight' => $this->weight ? (float) $this->weight : null,
            'is_default' => (bool) $this->is_default,
            'is_active' => (bool) $this->is_active,
            'attributes' => $this->whenLoaded('attributeValues', function() {
                return $this->attributeValues->map(function($attributeValue) {
                    return [
                        'id' => $attributeValue->id,
                        'attribute_id' => $attributeValue->attribute_id,
                        'attribute_name' => $attributeValue->attribute->name,
                        'value' => $attributeValue->value,
                        'color_code' => $attributeValue->color_code,
                        'image' => $attributeValue->image ? StorageHelper::getFileUrl($attributeValue->image) : null,
                    ];
                });
            }),
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toISOString() : null,
        ];
    }
}