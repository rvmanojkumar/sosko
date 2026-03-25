<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'profile_photo' => $this->profile_photo ? Storage::disk('public')->url($this->profile_photo) : null,
            'profile_photo_url' => $this->profile_photo_url,
            'dob' => $this->dob ? $this->dob->format('Y-m-d') : null,
            'is_active' => (bool) $this->is_active,
            'email_verified_at' => $this->email_verified_at ? $this->email_verified_at->toISOString() : null,
            'phone_verified_at' => $this->phone_verified_at ? $this->phone_verified_at->toISOString() : null,
            'roles' => $this->roles->pluck('name'),
            'permissions' => $this->getAllPermissions()->pluck('name'),
            'vendor_profile' => new VendorResource($this->whenLoaded('vendorProfile')),
            'addresses' => UserAddressResource::collection($this->whenLoaded('addresses')),
            'wallet' => new WalletResource($this->whenLoaded('wallet')),
            'statistics' => $this->statistics,
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toISOString() : null,
        ];
    }
}