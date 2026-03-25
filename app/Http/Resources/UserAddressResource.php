<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserAddressResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'address_line1' => $this->address_line1,
            'address_line2' => $this->address_line2,
            'full_address' => $this->getFullAddressAttribute(),
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'postal_code' => $this->postal_code,
            'phone' => $this->phone,
            'address_type' => $this->address_type,
            'address_type_label' => $this->getAddressTypeLabel(),
            'is_default' => (bool) $this->is_default,
            'latitude' => $this->latitude ? (float) $this->latitude : null,
            'longitude' => $this->longitude ? (float) $this->longitude : null,
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toISOString() : null,
        ];
    }

    protected function getFullAddressAttribute()
    {
        $parts = [
            $this->address_line1,
            $this->address_line2,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ];
        
        return implode(', ', array_filter($parts));
    }

    protected function getAddressTypeLabel()
    {
        $types = [
            'home' => 'Home',
            'work' => 'Work',
            'other' => 'Other',
        ];
        
        return $types[$this->address_type] ?? ucfirst($this->address_type);
    }
}