<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorPayout extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'vendor_profile_id',
        'amount',
        'status',
        'razorpay_transfer_id',
        'transfer_data',
        'processed_at',
        'remarks'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transfer_data' => 'array',
        'processed_at' => 'datetime',
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
     * Mark as processing
     */
    public function markAsProcessing()
    {
        $this->update(['status' => 'processing']);
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted($transferId = null, $transferData = null)
    {
        $data = [
            'status' => 'completed',
            'processed_at' => now(),
        ];
        
        if ($transferId) {
            $data['razorpay_transfer_id'] = $transferId;
        }
        
        if ($transferData) {
            $data['transfer_data'] = $transferData;
        }
        
        $this->update($data);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed($remarks = null)
    {
        $data = [
            'status' => 'failed',
            'remarks' => $remarks,
        ];
        
        $this->update($data);
    }

    /**
     * Check if payout is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payout is completed
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute()
    {
        return '₹' . number_format($this->amount, 2);
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'warning',
            'processing' => 'info',
            'completed' => 'success',
            'failed' => 'danger',
        ];
        
        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Scope for pending payouts
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for processing payouts
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Scope for completed payouts
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for failed payouts
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for payouts by date range
     */
    public function scopeDateBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope for payouts for a specific vendor
     */
    public function scopeForVendor($query, $vendorId)
    {
        return $query->where('vendor_profile_id', $vendorId);
    }
}