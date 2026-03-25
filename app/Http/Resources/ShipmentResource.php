<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'tracking_number' => $this->tracking_number,
            'courier_name' => $this->courier_name,
            'tracking_url' => $this->tracking_url,
            'status' => $this->status,
            'estimated_delivery' => $this->estimated_delivery ? $this->estimated_delivery->toISOString() : null,
            'delivered_at' => $this->delivered_at ? $this->delivered_at->toISOString() : null,
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
        ];
    }
}