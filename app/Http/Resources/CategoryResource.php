<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'parent_id' => $this->parent_id,
            'icon' => $this->icon ? Storage::disk('public')->url($this->icon) : null,
            'banner_image' => $this->banner_image ? Storage::disk('public')->url($this->banner_image) : null,
            'sort_order' => $this->sort_order,
            'is_active' => (bool) $this->is_active,
            'meta_data' => $this->meta_data,
            'seo_data' => $this->seo_data,
            'children' => CategoryResource::collection($this->whenLoaded('children')),
            'parent' => new CategoryResource($this->whenLoaded('parent')),
            'attributes' => AttributeResource::collection($this->whenLoaded('attributes')),
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toISOString() : null,
        ];
    }
}