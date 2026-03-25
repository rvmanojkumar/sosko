<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\VendorProfile;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalRevenue = Order::where('payment_status', 'paid')->sum('total_amount');
        $totalOrders = Order::count();
        $totalUsers = User::count();
        $totalVendors = VendorProfile::count();
        $totalProducts = Product::count();
        $pendingOrders = Order::where('order_status', 'placed')->count();
        $pendingVendors = VendorProfile::where('status', 'pending')->count();

        // Monthly sales data for chart
        $monthlySales = Order::where('payment_status', 'paid')
            ->whereYear('created_at', now()->year)
            ->select(DB::raw('MONTH(created_at) as month'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $salesData = array_fill(0, 12, 0);
        foreach ($monthlySales as $sale) {
            $salesData[$sale->month - 1] = $sale->total;
        }

        $recentOrders = Order::with('user')->latest()->limit(10)->get();
        $topProducts = Product::withCount('orderItems')
            ->orderBy('order_items_count', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalRevenue', 'totalOrders', 'totalUsers', 'totalVendors', 'totalProducts',
            'pendingOrders', 'pendingVendors', 'recentOrders', 'salesData', 'topProducts'
        ));
    }
}