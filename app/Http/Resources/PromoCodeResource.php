<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PromoCodeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'value' => (float) $this->value,
            'min_order_value' => $this->min_order_value ? (float) $this->min_order_value : null,
            'max_discount_amount' => $this->max_discount_amount ? (float) $this->max_discount_amount : null,
            'usage_type' => $this->usage_type,
            'usage_limit' => $this->usage_limit ? (int) $this->usage_limit : null,
            'used_count' => (int) $this->used_count,
            'per_user_limit' => $this->per_user_limit ? (int) $this->per_user_limit : null,
            'is_first_order_only' => (bool) $this->is_first_order_only,
            'stackable' => (bool) $this->stackable,
            'start_date' => $this->start_date ? $this->start_date->toISOString() : null,
            'end_date' => $this->end_date ? $this->end_date->toISOString() : null,
            'is_active' => (bool) $this->is_active,
            'is_expired' => $this->end_date ? $this->end_date->isPast() : false,
            'days_remaining' => $this->end_date ? now()->diffInDays($this->end_date, false) : null,
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
        ];
    }
}