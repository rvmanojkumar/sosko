<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class Order extends Model
{
    use HasFactory, HasUuids, SoftDeletes, Auditable;

    protected $fillable = [
        'order_number',
        'user_id',
        'subtotal',
        'discount_amount',
        'promo_code_discount',
        'tax_amount',
        'shipping_amount',
        'total_amount',
        'paid_amount',
        'payment_method',
        'payment_status',
        'order_status',
        'notes',
        'billing_address',
        'shipping_address',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
        'promo_code_id'
    ];

    protected $casts = [
        'billing_address' => 'array',
        'shipping_address' => 'array',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'promo_code_discount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'payment_status' => 'pending',
        'order_status' => 'placed',
        'paid_amount' => 0,
        'discount_amount' => 0,
        'promo_code_discount' => 0,
    ];

    /**
     * Get the user who placed the order
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the promo code used for this order
     */
    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }

    /**
     * Get the order items
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the status logs
     */
    public function statusLogs()
    {
        return $this->hasMany(OrderStatusLog::class);
    }

    /**
     * Check if order is paid
     */
    public function isPaid()
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Check if order is delivered
     */
    public function isDelivered()
    {
        return $this->order_status === 'delivered';
    }

    /**
     * Check if order is cancelled
     */
    public function isCancelled()
    {
        return $this->order_status === 'cancelled';
    }

    /**
     * Check if order can be cancelled
     */
    public function canBeCancelled()
    {
        return in_array($this->order_status, ['placed', 'confirmed']);
    }

    /**
     * Get remaining amount to pay
     */
    public function getRemainingAmountAttribute()
    {
        return max(0, $this->total_amount - $this->paid_amount);
    }

    /**
     * Get formatted order number with prefix
     */
    public function getFormattedOrderNumberAttribute()
    {
        return 'ORD-' . str_pad($this->order_number, 8, '0', STR_PAD_LEFT);
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
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
        
        return $labels[$this->order_status] ?? ucfirst($this->order_status);
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'placed' => 'warning',
            'confirmed' => 'info',
            'processing' => 'primary',
            'shipped' => 'primary',
            'delivered' => 'success',
            'cancelled' => 'danger',
            'returned' => 'danger',
        ];
        
        return $colors[$this->order_status] ?? 'secondary';
    }

    /**
     * Update order status
     */
    public function updateStatus($newStatus, $notes = null)
    {
        $oldStatus = $this->order_status;
        
        $this->update(['order_status' => $newStatus]);
        
        // Log status change
        $this->statusLogs()->create([
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
     * Calculate total after discount
     */
    public function calculateTotal()
    {
        $this->total_amount = $this->subtotal 
            - $this->discount_amount 
            - $this->promo_code_discount 
            + $this->tax_amount 
            + $this->shipping_amount;
            
        $this->save();
        
        return $this->total_amount;
    }

    /**
     * Scope for pending orders
     */
    public function scopePending($query)
    {
        return $query->where('order_status', 'placed');
    }

    /**
     * Scope for processing orders
     */
    public function scopeProcessing($query)
    {
        return $query->whereIn('order_status', ['confirmed', 'processing']);
    }

    /**
     * Scope for completed orders
     */
    public function scopeCompleted($query)
    {
        return $query->where('order_status', 'delivered');
    }

    /**
     * Scope for cancelled orders
     */
    public function scopeCancelled($query)
    {
        return $query->where('order_status', 'cancelled');
    }

    /**
     * Scope for paid orders
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope for user orders
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for date range
     */
    public function scopeDateBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}