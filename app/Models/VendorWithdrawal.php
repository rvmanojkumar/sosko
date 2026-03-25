<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorWithdrawal extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'vendor_profile_id',
        'amount',
        'status',
        'bank_account_id',
        'ifsc_code',
        'account_holder_name',
        'remarks',
        'approved_by',
        'approved_at',
        'processed_at',
        'metadata'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'processed_at' => 'datetime',
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
     * Get the admin who approved the withdrawal
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Approve the withdrawal
     */
    public function approve($adminId)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);
    }

    /**
     * Reject the withdrawal
     */
    public function reject($remarks = null)
    {
        $this->update([
            'status' => 'rejected',
            'remarks' => $remarks,
        ]);
    }

    /**
     * Process the withdrawal (mark as processed)
     */
    public function process()
    {
        $this->update([
            'status' => 'processed',
            'processed_at' => now(),
        ]);
    }

    /**
     * Mark as failed
     */
    public function fail($remarks = null)
    {
        $data = ['status' => 'failed'];
        
        if ($remarks) {
            $data['remarks'] = $remarks;
        }
        
        $this->update($data);
    }

    /**
     * Check if withdrawal is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if withdrawal is approved
     */
    public function isApproved()
    {
        return $this->status === 'approved';
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
            'approved' => 'info',
            'processed' => 'success',
            'rejected' => 'danger',
            'failed' => 'danger',
        ];
        
        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Scope for pending withdrawals
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved withdrawals
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for processed withdrawals
     */
    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    /**
     * Scope for withdrawals by date range
     */
    public function scopeDateBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope for withdrawals for a specific vendor
     */
    public function scopeForVendor($query, $vendorId)
    {
        return $query->where('vendor_profile_id', $vendorId);
    }
}