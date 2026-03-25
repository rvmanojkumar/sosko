<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\VendorEarning;
use App\Models\VendorPayout;
use App\Models\VendorWithdrawal;
use App\Models\VendorBankAccount;
use Illuminate\Support\Facades\DB;

class EarningController extends Controller
{
    /**
     * Display a listing of earnings
     */
    public function index(Request $request)
    {
        $vendor = Auth::user()->vendorProfile;
        
        $earnings = VendorEarning::with('order')
            ->where('vendor_profile_id', $vendor->id)
            ->when($request->status, function($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->date_from, function($query, $dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($request->date_to, function($query, $dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->orderBy($request->sort ?? 'created_at', $request->order ?? 'desc')
            ->paginate(20);

        // Statistics
        $stats = [
            'total_earned' => VendorEarning::where('vendor_profile_id', $vendor->id)
                ->where('status', 'processed')
                ->sum('vendor_amount'),
            'pending_earnings' => VendorEarning::where('vendor_profile_id', $vendor->id)
                ->where('status', 'pending')
                ->sum('vendor_amount'),
            'this_month' => VendorEarning::where('vendor_profile_id', $vendor->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('vendor_amount'),
            'last_month' => VendorEarning::where('vendor_profile_id', $vendor->id)
                ->whereMonth('created_at', now()->subMonth()->month)
                ->whereYear('created_at', now()->subMonth()->year)
                ->sum('vendor_amount'),
            'commission_paid' => VendorEarning::where('vendor_profile_id', $vendor->id)
                ->sum('commission_amount'),
        ];

        // Monthly earnings chart
        $monthlyEarnings = VendorEarning::where('vendor_profile_id', $vendor->id)
            ->whereYear('created_at', now()->year)
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(vendor_amount) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $earningsData = array_fill(0, 12, 0);
        foreach ($monthlyEarnings as $earning) {
            $earningsData[$earning->month - 1] = $earning->total;
        }

        return view('vendor.earnings.index', compact('earnings', 'stats', 'earningsData'));
    }

    /**
     * Display earning details
     */
    public function show($id)
    {
        $vendor = Auth::user()->vendorProfile;
        $earning = VendorEarning::with(['order', 'order.user'])
            ->where('vendor_profile_id', $vendor->id)
            ->findOrFail($id);

        return view('vendor.earnings.show', compact('earning'));
    }

    /**
     * Request withdrawal
     */
    public function requestWithdrawal(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
            'bank_account_id' => 'required|exists:vendor_bank_accounts,id',
        ]);

        $vendor = Auth::user()->vendorProfile;
        $pendingEarnings = VendorEarning::where('vendor_profile_id', $vendor->id)
            ->where('status', 'pending')
            ->sum('vendor_amount');

        if ($request->amount > $pendingEarnings) {
            return redirect()->back()->with('error', 'Insufficient pending earnings.');
        }

        $bankAccount = VendorBankAccount::findOrFail($request->bank_account_id);

        DB::beginTransaction();

        try {
            $withdrawal = VendorWithdrawal::create([
                'vendor_profile_id' => $vendor->id,
                'amount' => $request->amount,
                'bank_account_id' => $bankAccount->id,
                'ifsc_code' => $bankAccount->ifsc_code,
                'account_holder_name' => $bankAccount->account_holder_name,
                'status' => 'pending',
                'metadata' => [
                    'requested_at' => now(),
                    'bank_name' => $bankAccount->bank_name,
                    'account_number' => $bankAccount->masked_account_number,
                ]
            ]);

            DB::commit();

            return redirect()->route('vendor.withdrawals.index')
                ->with('success', 'Withdrawal request submitted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to submit withdrawal request: ' . $e->getMessage());
        }
    }

    /**
     * Export earnings report
     */
    public function export(Request $request)
    {
        $vendor = Auth::user()->vendorProfile;
        
        $earnings = VendorEarning::with('order')
            ->where('vendor_profile_id', $vendor->id)
            ->when($request->date_from, function($query, $dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($request->date_to, function($query, $dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->get();

        $filename = "earnings_report_{$vendor->store_slug}_" . now()->format('Y-m-d') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($earnings) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'Order Number',
                'Date',
                'Order Amount',
                'Commission Rate',
                'Commission Amount',
                'Your Earnings',
                'Status'
            ]);
            
            foreach ($earnings as $earning) {
                fputcsv($file, [
                    $earning->order->order_number,
                    $earning->created_at->format('Y-m-d H:i:s'),
                    $earning->order_amount,
                    $earning->commission_rate . '%',
                    $earning->commission_amount,
                    $earning->vendor_amount,
                    $earning->status
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}