<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\VendorPayout;
use App\Models\VendorWithdrawal;

class PayoutController extends Controller
{
    /**
     * Display a listing of payouts
     */
    public function index(Request $request)
    {
        $vendor = Auth::user()->vendorProfile;
        
        $payouts = VendorPayout::where('vendor_profile_id', $vendor->id)
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

        $stats = [
            'total_payouts' => VendorPayout::where('vendor_profile_id', $vendor->id)
                ->where('status', 'completed')
                ->sum('amount'),
            'pending_payouts' => VendorPayout::where('vendor_profile_id', $vendor->id)
                ->where('status', 'pending')
                ->sum('amount'),
            'processing_payouts' => VendorPayout::where('vendor_profile_id', $vendor->id)
                ->where('status', 'processing')
                ->sum('amount'),
            'failed_payouts' => VendorPayout::where('vendor_profile_id', $vendor->id)
                ->where('status', 'failed')
                ->sum('amount'),
        ];

        return view('vendor.payouts.index', compact('payouts', 'stats'));
    }

    /**
     * Display payout details
     */
    public function show($id)
    {
        $vendor = Auth::user()->vendorProfile;
        $payout = VendorPayout::where('vendor_profile_id', $vendor->id)
            ->findOrFail($id);

        return view('vendor.payouts.show', compact('payout'));
    }
}