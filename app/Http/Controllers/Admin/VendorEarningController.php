<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorEarning;
use App\Models\VendorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorEarningController extends Controller
{
    /**
     * Display a listing of all vendor earnings
     */
    public function index(Request $request)
    {
        $earnings = VendorEarning::with(['vendorProfile.user', 'order'])
            ->when($request->vendor_id, function($query, $vendorId) {
                $query->where('vendor_profile_id', $vendorId);
            })
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
            'total_earnings' => VendorEarning::sum('vendor_amount'),
            'pending_earnings' => VendorEarning::where('status', 'pending')->sum('vendor_amount'),
            'processed_earnings' => VendorEarning::where('status', 'processed')->sum('vendor_amount'),
            'total_commission' => VendorEarning::sum('commission_amount'),
            'this_month' => VendorEarning::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('vendor_amount'),
        ];

        // Monthly earnings chart
        $monthlyEarnings = VendorEarning::whereYear('created_at', now()->year)
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

        // Top vendors by earnings
        $topVendors = VendorProfile::with('user')
            ->withSum('earnings', 'vendor_amount')
            ->orderBy('earnings_sum_vendor_amount', 'desc')
            ->limit(10)
            ->get();

        $vendors = VendorProfile::where('status', 'approved')->get();

        return view('admin.vendor-earnings.index', compact(
            'earnings', 'stats', 'earningsData', 'topVendors', 'vendors'
        ));
    }

    /**
     * Display earnings for a specific vendor
     */
    public function vendorEarnings(VendorProfile $vendor, Request $request)
    {
        $earnings = $vendor->earnings()->with('order')
            ->when($request->status, function($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy($request->sort ?? 'created_at', $request->order ?? 'desc')
            ->paginate(20);

        $stats = [
            'total' => $vendor->earnings()->sum('vendor_amount'),
            'pending' => $vendor->earnings()->where('status', 'pending')->sum('vendor_amount'),
            'processed' => $vendor->earnings()->where('status', 'processed')->sum('vendor_amount'),
            'commission' => $vendor->earnings()->sum('commission_amount'),
            'order_count' => $vendor->earnings()->count(),
        ];

        return view('admin.vendor-earnings.vendor', compact('vendor', 'earnings', 'stats'));
    }

    /**
     * Display earning details
     */
    public function show(VendorEarning $earning)
    {
        $earning->load(['vendorProfile.user', 'order', 'order.user']);
        return view('admin.vendor-earnings.show', compact('earning'));
    }

    /**
     * Process a pending earning (mark as processed)
     */
    public function process(Request $request, VendorEarning $earning)
    {
        if ($earning->status !== 'pending') {
            return redirect()->back()->with('error', 'This earning has already been processed.');
        }

        $earning->update([
            'status' => 'processed',
            'payment_date' => now(),
            'metadata' => array_merge($earning->metadata ?? [], [
                'processed_by' => auth()->id(),
                'processed_at' => now(),
                'processed_by_name' => auth()->user()->name,
            ])
        ]);

        return redirect()->back()->with('success', 'Earning marked as processed.');
    }

    /**
     * Bulk process earnings
     */
    public function bulkProcess(Request $request)
    {
        $request->validate([
            'earning_ids' => 'required|array',
            'earning_ids.*' => 'exists:vendor_earnings,id',
        ]);

        $count = VendorEarning::whereIn('id', $request->earning_ids)
            ->where('status', 'pending')
            ->update([
                'status' => 'processed',
                'payment_date' => now(),
                'metadata' => DB::raw("JSON_SET(IFNULL(metadata, '{}'), '$.processed_by', " . auth()->id() . ", '$.processed_at', NOW())")
            ]);

        return redirect()->back()->with('success', "{$count} earnings marked as processed.");
    }

    /**
     * Export earnings report
     */
    public function export(Request $request)
    {
        $earnings = VendorEarning::with(['vendorProfile.user', 'order'])
            ->when($request->vendor_id, function($query, $vendorId) {
                $query->where('vendor_profile_id', $vendorId);
            })
            ->when($request->status, function($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->date_from, function($query, $dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($request->date_to, function($query, $dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->get();

        $filename = "earnings_report_" . now()->format('Y-m-d') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($earnings) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'Vendor',
                'Order Number',
                'Order Amount',
                'Commission Rate',
                'Commission Amount',
                'Vendor Amount',
                'Status',
                'Payment Date',
                'Created At'
            ]);
            
            foreach ($earnings as $earning) {
                fputcsv($file, [
                    $earning->vendorProfile->store_name ?? 'N/A',
                    $earning->order->order_number ?? 'N/A',
                    number_format($earning->order_amount, 2),
                    $earning->commission_rate . '%',
                    number_format($earning->commission_amount, 2),
                    number_format($earning->vendor_amount, 2),
                    $earning->status,
                    $earning->payment_date ? $earning->payment_date->format('Y-m-d') : '',
                    $earning->created_at->format('Y-m-d H:i:s')
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get earnings statistics for dashboard
     */
    public function statistics(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfYear());
        $endDate = $request->get('end_date', now());

        $dailyEarnings = VendorEarning::whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(vendor_amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $vendorStats = VendorProfile::withCount('earnings')
            ->withSum('earnings', 'vendor_amount')
            ->orderBy('earnings_sum_vendor_amount', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'daily_earnings' => $dailyEarnings,
            'top_vendors' => $vendorStats,
            'total_earnings' => VendorEarning::sum('vendor_amount'),
            'pending_earnings' => VendorEarning::where('status', 'pending')->sum('vendor_amount'),
        ]);
    }
}