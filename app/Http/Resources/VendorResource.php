<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class VendorResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'store_name' => $this->store_name,
            'store_slug' => $this->store_slug,
            'description' => $this->description,
            'logo' => $this->logo ? Storage::disk('public')->url($this->logo) : null,
            'banner' => $this->banner ? Storage::disk('public')->url($this->banner) : null,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'address' => $this->address,
            'latitude' => $this->latitude ? (float) $this->latitude : null,
            'longitude' => $this->longitude ? (float) $this->longitude : null,
            'status' => $this->status,
            'status_text' => $this->getStatusTextAttribute(),
            'rejection_reason' => $this->rejection_reason,
            'rating' => (float) $this->rating,
            'follower_count' => (int) $this->follower_count,
            'settings' => $this->settings,
            'user' => new UserResource($this->whenLoaded('user')),
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'documents' => VendorDocumentResource::collection($this->whenLoaded('documents')),
            'subscription' => new VendorSubscriptionResource($this->whenLoaded('currentSubscription')),
            'earnings_summary' => $this->when($this->earnings_summary, [
                'total_earned' => (float) ($this->earnings_summary['total_earned'] ?? 0),
                'pending_earnings' => (float) ($this->earnings_summary['pending_earnings'] ?? 0),
                'processed_earnings' => (float) ($this->earnings_summary['processed_earnings'] ?? 0),
            ]),
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toISOString() : null,
        ];
    }

    protected function getStatusTextAttribute()
    {
        $statuses = [
            'pending' => 'Pending Approval',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'suspended' => 'Suspended',
        ];
        
        return $statuses[$this->status] ?? ucfirst($this->status);
    }
}