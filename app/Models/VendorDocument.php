<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class VendorDocument extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'vendor_profile_id',
        'document_type',
        'document_number',
        'document_path',
        'document_url',
        'status',
        'remarks',
        'verified_at'
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
     * Get the document URL
     */
    public function getDocumentUrlAttribute($value)
    {
        if ($value) {
            return $value;
        }
        
        if ($this->document_path && Storage::disk('public')->exists($this->document_path)) {
            return Storage::disk('public')->url($this->document_path);
        }
        
        return null;
    }

    /**
     * Get document type label
     */
    public function getDocumentTypeLabelAttribute()
    {
        $types = [
            'pan' => 'PAN Card',
            'gst' => 'GST Certificate',
            'bank_statement' => 'Bank Statement',
            'address_proof' => 'Address Proof',
            'shop_license' => 'Shop License',
            'incorporation_certificate' => 'Incorporation Certificate',
        ];
        
        return $types[$this->document_type] ?? ucfirst(str_replace('_', ' ', $this->document_type));
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        $statuses = [
            'pending' => 'Pending Verification',
            'verified' => 'Verified',
            'rejected' => 'Rejected',
        ];
        
        return $statuses[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'warning',
            'verified' => 'success',
            'rejected' => 'danger',
        ];
        
        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Check if document is verified
     */
    public function isVerified()
    {
        return $this->status === 'verified';
    }

    /**
     * Check if document is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if document is rejected
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    /**
     * Verify the document
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
     * Reject the document
     */
    public function reject($remarks)
    {
        $this->update([
            'status' => 'rejected',
            'remarks' => $remarks,
        ]);
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
     * Scope for rejected documents
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope for specific document type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('document_type', $type);
    }
}