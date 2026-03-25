<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasUuids;

    protected $fillable = [
        'order_item_id',
        'tracking_number',
        'courier_name',
        'tracking_url',
        'status',
        'estimated_delivery',
        'delivered_at'
    ];

    protected $casts = [
        'estimated_delivery' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    /**
     * Get the order item
     */
    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Check if shipment is delivered
     */
    public function isDelivered()
    {
        return $this->status === 'delivered';
    }

    /**
     * Mark as picked up
     */
    public function markAsPickedUp()
    {
        $this->update(['status' => 'picked_up']);
    }

    /**
     * Mark as in transit
     */
    public function markAsInTransit()
    {
        $this->update(['status' => 'in_transit']);
    }

    /**
     * Mark as out for delivery
     */
    public function markAsOutForDelivery()
    {
        $this->update(['status' => 'out_for_delivery']);
    }

    /**
     * Mark as delivered
     */
    public function markAsDelivered()
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    /**
     * Get tracking URL
     */
    public function getTrackingUrlAttribute()
    {
        if ($this->tracking_url) {
            return $this->tracking_url;
        }
        
        // Generate tracking URL based on courier
        if ($this->courier_name && $this->tracking_number) {
            $courierUrls = [
                'dhl' => 'https://www.dhl.com/in-en/home/tracking.html?tracking-id=',
                'fedex' => 'https://www.fedex.com/apps/fedextracking/?tracknumbers=',
                'ups' => 'https://www.ups.com/track?tracknum=',
                'usps' => 'https://tools.usps.com/go/TrackConfirmAction?tLabels=',
                'delhivery' => 'https://www.delhivery.com/track/package/',
                'bluedart' => 'https://www.bluedart.com/servlet/RoutingServlet?handler=tnt&action=trackForm&trackingNo=',
                'ekart' => 'https://www.ekartlogistics.com/track/order/',
            ];
            
            $courierKey = strtolower($this->courier_name);
            if (isset($courierUrls[$courierKey])) {
                return $courierUrls[$courierKey] . $this->tracking_number;
            }
        }
        
        return null;
    }

    /**
     * Scope for pending shipments
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for in transit shipments
     */
    public function scopeInTransit($query)
    {
        return $query->whereIn('status', ['picked_up', 'in_transit', 'out_for_delivery']);
    }

    /**
     * Scope for delivered shipments
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }
}