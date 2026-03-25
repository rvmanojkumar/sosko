<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class UserWallet extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'balance',
        'total_earned',
        'total_redeemed',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'total_earned' => 'decimal:2',
        'total_redeemed' => 'decimal:2',
    ];

    protected $attributes = [
        'balance' => 0,
        'total_earned' => 0,
        'total_redeemed' => 0,
    ];

    /**
     * Get the user that owns the wallet
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all wallet transactions
     */
    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * Add funds to wallet
     */
    public function addFunds($amount, $source, $description = null, $orderId = null)
    {
        $this->increment('balance', $amount);
        $this->increment('total_earned', $amount);
        
        return $this->transactions()->create([
            'order_id' => $orderId,
            'amount' => $amount,
            'type' => 'credit',
            'source' => $source,
            'status' => 'completed',
            'description' => $description,
        ]);
    }

    /**
     * Deduct funds from wallet
     */
    public function deductFunds($amount, $source, $description = null, $orderId = null)
    {
        if ($this->balance < $amount) {
            throw new \Exception('Insufficient balance');
        }
        
        $this->decrement('balance', $amount);
        $this->increment('total_redeemed', $amount);
        
        return $this->transactions()->create([
            'order_id' => $orderId,
            'amount' => $amount,
            'type' => 'debit',
            'source' => $source,
            'status' => 'completed',
            'description' => $description,
        ]);
    }

    /**
     * Check if wallet has sufficient balance
     */
    public function hasSufficientBalance($amount)
    {
        return $this->balance >= $amount;
    }
}