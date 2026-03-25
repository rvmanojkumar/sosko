<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'balance' => (float) $this->balance,
            'formatted_balance' => '₹' . number_format($this->balance, 2),
            'total_earned' => (float) $this->total_earned,
            'formatted_total_earned' => '₹' . number_format($this->total_earned, 2),
            'total_redeemed' => (float) $this->total_redeemed,
            'formatted_total_redeemed' => '₹' . number_format($this->total_redeemed, 2),
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toISOString() : null,
        ];
    }
}