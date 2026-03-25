<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AttributeValueResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'attribute_id' => $this->attribute_id,
            'value' => $this->value,
            'color_code' => $this->color_code,
            'image' => $this->image ? Storage::disk('public')->url($this->image) : null,
            'swatch_image' => $this->swatch_image ? Storage::disk('public')->url($this->swatch_image) : null,
            'sort_order' => (int) $this->sort_order,
            'metadata' => $this->metadata,
            'attribute' => new AttributeResource($this->whenLoaded('attribute')),
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toISOString() : null,
        ];
    }
}