<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'order_id',
        'amount',
        'type',
        'source',
        'status',
        'description',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    protected $attributes = [
        'status' => 'completed',
    ];

    /**
     * Get the user who owns the transaction
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order associated with this transaction
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the wallet
     */
    public function wallet()
    {
        return $this->belongsTo(UserWallet::class, 'user_id', 'user_id');
    }

    /**
     * Scope for credit transactions
     */
    public function scopeCredits($query)
    {
        return $query->where('type', 'credit');
    }

    /**
     * Scope for debit transactions
     */
    public function scopeDebits($query)
    {
        return $query->where('type', 'debit');
    }

    /**
     * Scope for completed transactions
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for pending transactions
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for cashback transactions
     */
    public function scopeCashback($query)
    {
        return $query->where('source', 'cashback');
    }

    /**
     * Scope for referral transactions
     */
    public function scopeReferral($query)
    {
        return $query->where('source', 'referral');
    }

    /**
     * Get formatted amount with sign
     */
    public function getFormattedAmountAttribute()
    {
        $sign = $this->type === 'credit' ? '+' : '-';
        return $sign . ' ₹' . number_format($this->amount, 2);
    }

    /**
     * Get source label
     */
    public function getSourceLabelAttribute()
    {
        $labels = [
            'add_funds' => 'Wallet Top-up',
            'order_payment' => 'Order Payment',
            'cashback' => 'Cashback',
            'referral' => 'Referral Bonus',
            'refund' => 'Refund',
            'withdrawal' => 'Withdrawal',
        ];
        
        return $labels[$this->source] ?? ucfirst($this->source);
    }
}