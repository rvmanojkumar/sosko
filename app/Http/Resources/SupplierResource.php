<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'contact_person' => $this->contact_person,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'gst_number' => $this->gst_number,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
        ];
    }
}