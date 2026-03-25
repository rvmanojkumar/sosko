<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'product_name' => $this->product_name,
            'variant_sku' => $this->variant_sku,
            'variant_attributes' => $this->variant_attributes ? json_decode($this->variant_attributes) : null,
            'quantity' => (int) $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'discount_amount' => (float) $this->discount_amount,
            'total_price' => (float) $this->total_price,
            'status' => $this->status,
            'product' => new ProductResource($this->whenLoaded('productVariant', function() {
                return $this->productVariant->product;
            })),
            'variant' => new ProductVariantResource($this->whenLoaded('productVariant')),
            'vendor' => new VendorResource($this->whenLoaded('vendorProfile')),
            'shipment' => new ShipmentResource($this->whenLoaded('shipment')),
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
        ];
    }
}