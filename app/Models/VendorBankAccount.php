<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorBankAccount extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'vendor_profile_id',
        'account_holder_name',
        'bank_name',
        'account_number',
        'ifsc_code',
        'branch_address',
        'upi_id',
        'is_default',
        'is_verified',
        'razorpay_fund_account_id',
        'metadata'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_verified' => 'boolean',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'is_default' => false,
        'is_verified' => false,
    ];

    /**
     * Get the vendor profile
     */
    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class);
    }

    /**
     * Mark as default
     */
    public function setAsDefault()
    {
        // Remove default from other accounts
        $this->vendorProfile->bankAccounts()->update(['is_default' => false]);
        
        // Set this as default
        $this->update(['is_default' => true]);
    }

    /**
     * Verify the bank account
     */
    public function verify()
    {
        $this->update(['is_verified' => true]);
    }

    /**
     * Get masked account number (shows last 4 digits only)
     */
    public function getMaskedAccountNumberAttribute()
    {
        if (!$this->account_number) {
            return null;
        }
        
        return '****' . substr($this->account_number, -4);
    }

    /**
     * Get formatted account details
     */
    public function getFormattedDetailsAttribute()
    {
        return "{$this->account_holder_name} - {$this->bank_name} - {$this->masked_account_number}";
    }

    /**
     * Scope for default bank account
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope for verified bank accounts
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope for unverified bank accounts
     */
    public function scopeUnverified($query)
    {
        return $query->where('is_verified', false);
    }

    /**
     * Scope for accounts by IFSC code
     */
    public function scopeByIfsc($query, $ifsc)
    {
        return $query->where('ifsc_code', $ifsc);
    }
}