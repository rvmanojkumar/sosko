<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class OrderStatusLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'order_id',
        'order_item_id',
        'status',
        'notes',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    /**
     * Get the order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the order item
     */
    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Get user who changed the status
     */
    public function getChangedByAttribute()
    {
        if ($this->metadata && isset($this->metadata['changed_by'])) {
            return User::find($this->metadata['changed_by']);
        }
        
        return null;
    }

    /**
     * Get formatted status
     */
    public function getFormattedStatusAttribute()
    {
        $labels = [
            'placed' => 'Order Placed',
            'confirmed' => 'Order Confirmed',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            'returned' => 'Returned',
        ];
        
        return $labels[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Scope for order logs
     */
    public function scopeForOrder($query, $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    /**
     * Scope for order item logs
     */
    public function scopeForOrderItem($query, $orderItemId)
    {
        return $query->where('order_item_id', $orderItemId);
    }
}