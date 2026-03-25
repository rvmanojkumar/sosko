<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'quantity' => (int) $this->quantity,
            'variant' => new ProductVariantResource($this->whenLoaded('productVariant')),
            'product' => new ProductResource($this->whenLoaded('productVariant', function() {
                return $this->productVariant->product;
            })),
            'unit_price' => (float) $this->productVariant->current_price,
            'total_price' => (float) ($this->quantity * $this->productVariant->current_price),
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
        ];
    }
}