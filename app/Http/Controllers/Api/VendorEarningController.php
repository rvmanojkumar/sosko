<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VendorEarning;
use App\Models\VendorPayout;
use App\Models\VendorWithdrawal;
use App\Models\VendorBankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VendorEarningController extends Controller
{
    /**
     * Get vendor earnings
     */
    public function index(Request $request)
    {
        $vendor = $request->user()->vendorProfile;
        
        $earnings = VendorEarning::where('vendor_profile_id', $vendor->id)
            ->with('order')
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
        
        $summary = [
            'total_earned' => VendorEarning::where('vendor_profile_id', $vendor->id)->sum('vendor_amount'),
            'pending_earnings' => VendorEarning::where('vendor_profile_id', $vendor->id)
                ->where('status', 'pending')
                ->sum('vendor_amount'),
            'processed_earnings' => VendorEarning::where('vendor_profile_id', $vendor->id)
                ->where('status', 'processed')
                ->sum('vendor_amount'),
            'this_month' => VendorEarning::where('vendor_profile_id', $vendor->id)
                ->whereMonth('created_at', now()->month)
                ->sum('vendor_amount'),
            'last_month' => VendorEarning::where('vendor_profile_id', $vendor->id)
                ->whereMonth('created_at', now()->subMonth()->month)
                ->sum('vendor_amount'),
        ];
        
        return response()->json([
            'success' => true,
            'data' => [
                'earnings' => $earnings,
                'summary' => $summary,
            ]
        ]);
    }
    
    /**
     * Get earning statistics
     */
    public function statistics(Request $request)
    {
        $vendor = $request->user()->vendorProfile;
        
        $monthlyData = [];
        for ($i = 0; $i < 12; $i++) {
            $month = now()->subMonths($i);
            $monthlyData[] = [
                'month' => $month->format('M Y'),
                'earnings' => VendorEarning::where('vendor_profile_id', $vendor->id)
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->sum('vendor_amount'),
            ];
        }
        
        $dailyData = VendorEarning::where('vendor_profile_id', $vendor->id)
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(vendor_amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => [
                'monthly_earnings' => array_reverse($monthlyData),
                'daily_earnings' => $dailyData,
            ]
        ]);
    }
    
    /**
     * Request withdrawal
     */
    public function requestWithdrawal(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
            'bank_account_id' => 'nullable|exists:vendor_bank_accounts,id',
        ]);
        
        $vendor = $request->user()->vendorProfile;
        
        try {
            $withdrawal = $vendor->requestWithdrawal($request->amount, $request->bank_account_id);
            
            return response()->json([
                'success' => true,
                'message' => 'Withdrawal request submitted successfully',
                'data' => $withdrawal
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Get withdrawal history
     */
    public function withdrawals(Request $request)
    {
        $vendor = $request->user()->vendorProfile;
        
        $withdrawals = VendorWithdrawal::where('vendor_profile_id', $vendor->id)
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);
        
        return response()->json([
            'success' => true,
            'data' => $withdrawals
        ]);
    }
    
    /**
     * Get payout history
     */
    public function payouts(Request $request)
    {
        $vendor = $request->user()->vendorProfile;
        
        $payouts = VendorPayout::where('vendor_profile_id', $vendor->id)
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);
        
        return response()->json([
            'success' => true,
            'data' => $payouts
        ]);
    }
    
    /**
     * Get bank accounts
     */
    public function bankAccounts(Request $request)
    {
        $vendor = $request->user()->vendorProfile;
        
        $accounts = $vendor->bankAccounts()->orderBy('is_default', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'data' => $accounts
        ]);
    }
    
    /**
     * Add bank account
     */
    public function addBankAccount(Request $request)
    {
        $request->validate([
            'account_holder_name' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'ifsc_code' => 'required|string|max:20',
            'branch_address' => 'nullable|string',
            'upi_id' => 'nullable|string|max:100',
            'is_default' => 'boolean',
        ]);
        
        $vendor = $request->user()->vendorProfile;
        
        // Check if account number already exists
        if ($vendor->bankAccounts()->where('account_number', $request->account_number)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Bank account already exists'
            ], 400);
        }
        
        $data = $request->all();
        
        // If setting as default, remove default from others
        if ($request->is_default) {
            $vendor->bankAccounts()->update(['is_default' => false]);
        }
        
        $account = $vendor->bankAccounts()->create($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Bank account added successfully',
            'data' => $account
        ], 201);
    }
    
    /**
     * Delete bank account
     */
    public function deleteBankAccount($id)
    {
        $account = VendorBankAccount::findOrFail($id);
        
        // Check if account belongs to vendor
        if ($account->vendor_profile_id !== auth()->user()->vendorProfile->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        // Check if it's the only account
        if (auth()->user()->vendorProfile->bankAccounts()->count() === 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete the only bank account'
            ], 400);
        }
        
        // If deleting default account, set another as default
        if ($account->is_default) {
            $anotherAccount = auth()->user()->vendorProfile->bankAccounts()
                ->where('id', '!=', $account->id)
                ->first();
            if ($anotherAccount) {
                $anotherAccount->update(['is_default' => true]);
            }
        }
        
        $account->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Bank account deleted successfully'
        ]);
    }
    
    /**
     * Set default bank account
     */
    public function setDefaultBankAccount($id)
    {
        $account = VendorBankAccount::findOrFail($id);
        
        // Check if account belongs to vendor
        if ($account->vendor_profile_id !== auth()->user()->vendorProfile->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $account->setAsDefault();
        
        return response()->json([
            'success' => true,
            'message' => 'Default bank account updated successfully'
        ]);
    }
}