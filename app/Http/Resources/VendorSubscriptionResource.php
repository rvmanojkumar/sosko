<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VendorSubscriptionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'vendor_profile_id' => $this->vendor_profile_id,
            'subscription_plan_id' => $this->subscription_plan_id,
            'plan' => new SubscriptionPlanResource($this->whenLoaded('plan')),
            'start_date' => $this->start_date ? $this->start_date->toISOString() : null,
            'end_date' => $this->end_date ? $this->end_date->toISOString() : null,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'status_color' => $this->getStatusColor(),
            'is_active' => $this->isActive(),
            'is_expired' => $this->isExpired(),
            'days_remaining' => $this->daysRemaining(),
            'days_remaining_text' => $this->getDaysRemainingText(),
            'razorpay_subscription_id' => $this->razorpay_subscription_id,
            'auto_renew' => (bool) $this->auto_renew,
            'payment_data' => $this->payment_data,
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toISOString() : null,
        ];
    }

    protected function getStatusLabel()
    {
        $statuses = [
            'active' => 'Active',
            'expired' => 'Expired',
            'cancelled' => 'Cancelled',
        ];
        
        return $statuses[$this->status] ?? ucfirst($this->status);
    }

    protected function getStatusColor()
    {
        $colors = [
            'active' => 'success',
            'expired' => 'danger',
            'cancelled' => 'warning',
        ];
        
        return $colors[$this->status] ?? 'secondary';
    }

    protected function getDaysRemainingText()
    {
        $days = $this->daysRemaining();
        
        if ($days <= 0) {
            return 'Expired';
        }
        
        if ($days == 1) {
            return '1 day remaining';
        }
        
        return "{$days} days remaining";
    }
}