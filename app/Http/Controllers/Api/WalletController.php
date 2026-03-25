<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserWallet;
use App\Models\WalletTransaction;
use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\WalletResource;
use App\Http\Resources\WalletTransactionResource;

class WalletController extends Controller
{
    protected $razorpayService;

    public function __construct(RazorpayService $razorpayService)
    {
        $this->razorpayService = $razorpayService;
    }

    /**
     * Get wallet details
     */
    public function show(Request $request)
{
    $user = $request->user();
    $wallet = $user->wallet;
    
    if (!$wallet) {
        $wallet = $user->wallet()->create([
            'balance' => 0,
            'total_earned' => 0,
            'total_redeemed' => 0,
        ]);
    }
    
    return response()->json([
        'success' => true,
        'data' => new WalletResource($wallet)
    ]);
}

    /**
     * Get wallet transactions
     */
   public function transactions(Request $request)
{
    $transactions = WalletTransaction::where('user_id', $request->user()->id)
        ->with('order')
        ->when($request->type, function ($query, $type) {
            $query->where('type', $type);
        })
        ->when($request->source, function ($query, $source) {
            $query->where('source', $source);
        })
        ->when($request->status, function ($query, $status) {
            $query->where('status', $status);
        })
        ->when($request->date_from, function ($query, $dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        })
        ->when($request->date_to, function ($query, $dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        })
        ->orderBy('created_at', 'desc')
        ->paginate($request->per_page ?? 20);
        
    return response()->json([
        'success' => true,
        'data' => WalletTransactionResource::collection($transactions)
    ]);
}

    /**
     * Add funds to wallet
     */
    public function addFunds(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10|max:10000',
        ]);
        
        $user = $request->user();
        $amount = $request->amount;
        
        // Create Razorpay order
        $order = $this->razorpayService->createOrder([
            'amount' => $amount * 100, // Convert to paise
            'currency' => 'INR',
            'receipt' => 'wallet_' . uniqid(),
            'notes' => [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_phone' => $user->phone,
                'type' => 'wallet_add',
            ],
        ]);
        
        if (!$order['success']) {
            return response()->json([
                'message' => 'Failed to create payment order',
                'error' => $order['message']
            ], 500);
        }
        
        // Store transaction in pending state
        $transaction = WalletTransaction::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'type' => 'credit',
            'source' => 'add_funds',
            'status' => 'pending',
            'description' => 'Wallet top-up',
            'metadata' => [
                'razorpay_order_id' => $order['id'],
                'razorpay_amount' => $order['amount'],
            ],
        ]);
        
        return response()->json([
            'message' => 'Funds addition initiated',
            'order_id' => $order['id'],
            'amount' => $amount,
            'transaction_id' => $transaction->id,
            'razorpay_key' => config('services.razorpay.key'),
        ]);
    }

    /**
     * Verify wallet top-up payment
     */
    public function verifyAddFunds(Request $request)
    {
        $request->validate([
            'razorpay_order_id' => 'required',
            'razorpay_payment_id' => 'required',
            'razorpay_signature' => 'required',
            'transaction_id' => 'required|exists:wallet_transactions,id',
        ]);
        
        // Verify signature
        $isValid = $this->razorpayService->verifyPaymentSignature([
            'razorpay_order_id' => $request->razorpay_order_id,
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'razorpay_signature' => $request->razorpay_signature,
        ]);
        
        if (!$isValid) {
            return response()->json([
                'message' => 'Invalid payment signature'
            ], 400);
        }
        
        $transaction = WalletTransaction::find($request->transaction_id);
        
        if ($transaction->status !== 'pending') {
            return response()->json([
                'message' => 'Transaction already processed'
            ], 400);
        }
        
        DB::beginTransaction();
        
        try {
            $user = $transaction->user;
            $wallet = $user->wallet;
            
            if (!$wallet) {
                $wallet = $user->wallet()->create([
                    'balance' => 0,
                    'total_earned' => 0,
                    'total_redeemed' => 0,
                ]);
            }
            
            // Update wallet balance
            $wallet->increment('balance', $transaction->amount);
            $wallet->increment('total_earned', $transaction->amount);
            
            // Update transaction
            $transaction->update([
                'status' => 'completed',
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'razorpay_payment_id' => $request->razorpay_payment_id,
                    'verified_at' => now()->toISOString(),
                ]),
            ]);
            
            DB::commit();
            
            return response()->json([
                'message' => 'Funds added successfully',
                'wallet' => $wallet,
                'transaction' => $transaction,
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Wallet fund addition verification failed: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to add funds',
                'error' => 'An error occurred while processing your request'
            ], 500);
        }
    }

    /**
     * Redeem wallet balance for order
     */
    public function redeem(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'order_id' => 'required|exists:orders,id',
        ]);
        
        $user = $request->user();
        $wallet = $user->wallet;
        
        if (!$wallet || $wallet->balance < $request->amount) {
            return response()->json([
                'message' => 'Insufficient wallet balance',
                'available_balance' => $wallet ? $wallet->balance : 0,
            ], 400);
        }
        
        $order = $user->orders()->find($request->order_id);
        
        if (!$order) {
            return response()->json([
                'message' => 'Order not found'
            ], 404);
        }
        
        if ($order->payment_status === 'paid') {
            return response()->json([
                'message' => 'Order already paid'
            ], 400);
        }
        
        DB::beginTransaction();
        
        try {
            // Deduct from wallet
            $wallet->decrement('balance', $request->amount);
            $wallet->increment('total_redeemed', $request->amount);
            
            // Create transaction record
            $transaction = $user->walletTransactions()->create([
                'order_id' => $order->id,
                'amount' => $request->amount,
                'type' => 'debit',
                'source' => 'order_payment',
                'status' => 'completed',
                'description' => 'Used wallet for order #' . $order->order_number,
                'metadata' => [
                    'order_number' => $order->order_number,
                    'balance_before' => $wallet->balance + $request->amount,
                    'balance_after' => $wallet->balance,
                ],
            ]);
            
            // Update order paid amount
            $order->increment('paid_amount', $request->amount);
            
            // If order is fully paid, update status
            if ($order->paid_amount >= $order->total_amount) {
                $order->update([
                    'payment_status' => 'paid',
                    'order_status' => 'confirmed',
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'message' => 'Amount redeemed successfully',
                'wallet' => $wallet,
                'transaction' => $transaction,
                'order_paid_amount' => $order->paid_amount,
                'remaining_to_pay' => max(0, $order->total_amount - $order->paid_amount),
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Wallet redemption failed: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to redeem amount',
                'error' => 'An error occurred while processing your request'
            ], 500);
        }
    }

    /**
     * Get referral earnings
     */
    public function referralEarnings(Request $request)
    {
        $earnings = WalletTransaction::where('user_id', $request->user()->id)
            ->where('source', 'referral')
            ->where('type', 'credit')
            ->where('status', 'completed')
            ->sum('amount');
            
        $referrals = WalletTransaction::where('user_id', $request->user()->id)
            ->where('source', 'referral')
            ->where('type', 'credit')
            ->where('status', 'completed')
            ->with('order')
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);
            
        return response()->json([
            'total_earnings' => $earnings,
            'referrals' => $referrals,
        ]);
    }

    /**
     * Get cashback earnings
     */
    public function cashbackEarnings(Request $request)
    {
        $earnings = WalletTransaction::where('user_id', $request->user()->id)
            ->where('source', 'cashback')
            ->where('type', 'credit')
            ->where('status', 'completed')
            ->sum('amount');
            
        $cashbacks = WalletTransaction::where('user_id', $request->user()->id)
            ->where('source', 'cashback')
            ->where('type', 'credit')
            ->where('status', 'completed')
            ->with('order')
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);
            
        return response()->json([
            'total_cashback' => $earnings,
            'cashbacks' => $cashbacks,
        ]);
    }

    /**
     * Get wallet summary
     */
    public function summary(Request $request)
    {
        $user = $request->user();
        $wallet = $user->wallet;
        
        $summary = [
            'current_balance' => $wallet ? $wallet->balance : 0,
            'total_earned' => $wallet ? $wallet->total_earned : 0,
            'total_redeemed' => $wallet ? $wallet->total_redeemed : 0,
            'total_cashback' => WalletTransaction::where('user_id', $user->id)
                ->where('source', 'cashback')
                ->where('type', 'credit')
                ->where('status', 'completed')
                ->sum('amount'),
            'total_referral' => WalletTransaction::where('user_id', $user->id)
                ->where('source', 'referral')
                ->where('type', 'credit')
                ->where('status', 'completed')
                ->sum('amount'),
            'pending_credits' => WalletTransaction::where('user_id', $user->id)
                ->where('type', 'credit')
                ->where('status', 'pending')
                ->sum('amount'),
            'transactions_this_month' => WalletTransaction::where('user_id', $user->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'spent_this_month' => WalletTransaction::where('user_id', $user->id)
                ->where('type', 'debit')
                ->where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount'),
        ];
        
        return response()->json($summary);
    }

    /**
     * Get transaction details
     */
    public function transactionDetails(Request $request, $id)
{
    $transaction = WalletTransaction::where('user_id', $request->user()->id)
        ->with('order')
        ->findOrFail($id);
        
    return response()->json([
        'success' => true,
        'data' => new WalletTransactionResource($transaction)
    ]);
}
}