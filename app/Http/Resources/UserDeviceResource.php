<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserDeviceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'device_id' => $this->device_id,
            'device_type' => $this->device_type,
            'device_type_label' => $this->getDeviceTypeLabel(),
            'app_version' => $this->app_version,
            'is_active' => (bool) $this->is_active,
            'last_used_at' => $this->updated_at ? $this->updated_at->toISOString() : null,
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
        ];
    }

    protected function getDeviceTypeLabel()
    {
        $types = [
            'ios' => 'iOS',
            'android' => 'Android',
            'web' => 'Web Browser',
        ];
        
        return $types[$this->device_type] ?? ucfirst($this->device_type);
    }
}