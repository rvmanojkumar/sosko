<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class VendorPayout extends Model
{
    use HasUuids;

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
    public function markAsCompleted($transferId = null)
    {
        $data = [
            'status' => 'completed',
            'processed_at' => now(),
        ];
        
        if ($transferId) {
            $data['razorpay_transfer_id'] = $transferId;
        }
        
        $this->update($data);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed($remarks = null)
    {
        $data = ['status' => 'failed'];
        
        if ($remarks) {
            $data['remarks'] = $remarks;
        }
        
        $this->update($data);
    }

    /**
     * Scope for pending payouts
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for completed payouts
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}