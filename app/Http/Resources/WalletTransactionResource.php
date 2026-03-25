<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WalletTransactionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'order_id' => $this->order_id,
            'order' => new OrderResource($this->whenLoaded('order')),
            'amount' => (float) $this->amount,
            'formatted_amount' => ($this->type === 'credit' ? '+' : '-') . '₹' . number_format($this->amount, 2),
            'type' => $this->type,
            'type_label' => $this->type === 'credit' ? 'Credited' : 'Debited',
            'source' => $this->source,
            'source_label' => $this->source_label,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'status_color' => $this->getStatusColor(),
            'description' => $this->description,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
        ];
    }

    protected function getStatusLabel()
    {
        $statuses = [
            'pending' => 'Pending',
            'completed' => 'Completed',
            'failed' => 'Failed',
        ];
        
        return $statuses[$this->status] ?? ucfirst($this->status);
    }

    protected function getStatusColor()
    {
        $colors = [
            'pending' => 'warning',
            'completed' => 'success',
            'failed' => 'danger',
        ];
        
        return $colors[$this->status] ?? 'secondary';
    }
}