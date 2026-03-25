<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorEarning extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'vendor_profile_id',
        'order_id',
        'order_item_id',
        'order_amount',
        'commission_amount',
        'commission_rate',
        'vendor_amount',
        'status',
        'payment_date',
        'metadata'
    ];

    protected $casts = [
        'order_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'vendor_amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    /**
     * Get the vendor profile
     */
    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class);
    }

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
     * Mark as processed (paid out)
     */
    public function markAsProcessed($paymentDate = null)
    {
        $this->update([
            'status' => 'processed',
            'payment_date' => $paymentDate ?? now(),
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed($reason = null)
    {
        $metadata = $this->metadata ?? [];
        $metadata['failure_reason'] = $reason;
        
        $this->update([
            'status' => 'failed',
            'metadata' => $metadata,
        ]);
    }

    /**
     * Check if earning is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if earning is processed
     */
    public function isProcessed()
    {
        return $this->status === 'processed';
    }

    /**
     * Get formatted order amount
     */
    public function getFormattedOrderAmountAttribute()
    {
        return '₹' . number_format($this->order_amount, 2);
    }

    /**
     * Get formatted commission amount
     */
    public function getFormattedCommissionAmountAttribute()
    {
        return '₹' . number_format($this->commission_amount, 2);
    }

    /**
     * Get formatted vendor amount
     */
    public function getFormattedVendorAmountAttribute()
    {
        return '₹' . number_format($this->vendor_amount, 2);
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'warning',
            'processed' => 'success',
            'failed' => 'danger',
        ];
        
        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Scope for pending earnings
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for processed earnings
     */
    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    /**
     * Scope for failed earnings
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for earnings by date range
     */
    public function scopeDateBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope for earnings for a specific vendor
     */
    public function scopeForVendor($query, $vendorId)
    {
        return $query->where('vendor_profile_id', $vendorId);
    }

    /**
     * Scope for earnings in current month
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    /**
     * Get total pending amount for vendor
     */
    public static function getPendingTotal($vendorId)
    {
        return self::forVendor($vendorId)->pending()->sum('vendor_amount');
    }

    /**
     * Get total processed amount for vendor
     */
    public static function getProcessedTotal($vendorId)
    {
        return self::forVendor($vendorId)->processed()->sum('vendor_amount');
    }
}