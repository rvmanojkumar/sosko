<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class VendorDocument extends Model
{
    use HasUuids;

    protected $fillable = [
        'vendor_profile_id',
        'document_type',
        'document_number',
        'document_path',
        'document_url',
        'status',
        'remarks'
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    /**
     * Get the vendor profile that owns the document
     */
    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class);
    }

    /**
     * Scope for pending documents
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for verified documents
     */
    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    /**
     * Check if document is verified
     */
    public function isVerified()
    {
        return $this->status === 'verified';
    }

    /**
     * Verify document
     */
    public function verify($remarks = null)
    {
        $this->update([
            'status' => 'verified',
            'remarks' => $remarks,
            'verified_at' => now(),
        ]);
    }

    /**
     * Reject document
     */
    public function reject($remarks)
    {
        $this->update([
            'status' => 'rejected',
            'remarks' => $remarks,
        ]);
    }
}