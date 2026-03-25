<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderStatusLogResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'order_item_id' => $this->order_item_id,
            'status' => $this->status,
            'notes' => $this->notes,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
        ];
    }
}