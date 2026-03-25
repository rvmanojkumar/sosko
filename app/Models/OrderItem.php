<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'order_id',
        'product_variant_id',
        'vendor_profile_id',
        'product_name',
        'variant_sku',
        'variant_attributes',
        'quantity',
        'unit_price',
        'discount_amount',
        'total_price',
        'status'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_price' => 'decimal:2',
        'variant_attributes' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'pending',
        'discount_amount' => 0,
    ];

    /**
     * Get the order that owns this item
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product variant
     */
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * Get the vendor profile
     */
    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class);
    }

    /**
     * Get the shipment for this order item
     */
    public function shipment()
    {
        return $this->hasOne(Shipment::class);
    }

    /**
     * Get status logs for this order item
     */
    public function statusLogs()
    {
        return $this->hasMany(OrderStatusLog::class);
    }

    /**
     * Check if item is delivered
     */
    public function isDelivered()
    {
        return $this->status === 'delivered';
    }

    /**
     * Check if item is cancelled
     */
    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if item is returned
     */
    public function isReturned()
    {
        return $this->status === 'returned';
    }

    /**
     * Update item status
     */
    public function updateStatus($newStatus, $notes = null)
    {
        $oldStatus = $this->status;
        
        $this->update(['status' => $newStatus]);
        
        // Log status change
        $this->statusLogs()->create([
            'order_id' => $this->order_id,
            'order_item_id' => $this->id,
            'status' => $newStatus,
            'notes' => $notes,
            'metadata' => [
                'old_status' => $oldStatus,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
            ],
        ]);
        
        return $this;
    }

    /**
     * Calculate subtotal
     */
    public function getSubtotalAttribute()
    {
        return $this->quantity * $this->unit_price;
    }

    /**
     * Get formatted variant attributes
     */
    public function getFormattedAttributesAttribute()
    {
        if (!$this->variant_attributes) {
            return null;
        }
        
        $attributes = is_array($this->variant_attributes) 
            ? $this->variant_attributes 
            : json_decode($this->variant_attributes, true);
            
        return collect($attributes)->map(function($value, $key) {
            return [
                'name' => $key,
                'value' => $value,
            ];
        })->values();
    }

    /**
     * Scope for pending items
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for confirmed items
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope for processing items
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Scope for shipped items
     */
    public function scopeShipped($query)
    {
        return $query->where('status', 'shipped');
    }

    /**
     * Scope for delivered items
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Scope for cancelled items
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope for returned items
     */
    public function scopeReturned($query)
    {
        return $query->where('status', 'returned');
    }

    /**
     * Scope for items by vendor
     */
    public function scopeForVendor($query, $vendorId)
    {
        return $query->where('vendor_profile_id', $vendorId);
    }
}