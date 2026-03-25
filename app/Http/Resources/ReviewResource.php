<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\StorageHelper;

class ReviewResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'user' => new UserResource($this->whenLoaded('user')),
            'rating' => (int) $this->rating,
            'review' => $this->review,
            'is_verified_purchase' => (bool) $this->is_verified_purchase,
            'is_approved' => (bool) $this->is_approved,
            'helpful_count' => (int) $this->helpful_count,
            'images' => $this->media->map(function($media) {
                return [
                    'id' => $media->id,
                    'url' => StorageHelper::getFileUrl($media->media_path),
                    'type' => $media->media_type,
                ];
            }),
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toISOString() : null,
        ];
    }
}