<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\VendorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $dateRange = $request->get('date_range', 'this_month');
        $startDate = $this->getStartDate($dateRange);
        $endDate = now();

        // Sales Report
        $salesData = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('AVG(total_amount) as avg_order_value')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $totalRevenue = $salesData->sum('revenue');
        $totalOrders = $salesData->sum('orders');
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        // Top Products
        $topProducts = DB::table('order_items')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->whereBetween('order_items.created_at', [$startDate, $endDate])
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.total_price) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_quantity', 'desc')
            ->limit(10)
            ->get();

        // Top Vendors
        $topVendors = VendorProfile::with('user')
            ->withSum(['earnings' => function($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            }], 'vendor_amount')
            ->orderBy('earnings_sum_vendor_amount', 'desc')
            ->limit(10)
            ->get();

        // User Statistics
        $userStats = [
            'total' => User::count(),
            'new_this_period' => User::whereBetween('created_at', [$startDate, $endDate])->count(),
            'customers' => User::role('customer')->count(),
            'vendors' => User::role('vendor')->count(),
        ];

        // Order Status Distribution
        $orderStatus = Order::select('order_status', DB::raw('count(*) as count'))
            ->groupBy('order_status')
            ->get();

        return view('admin.reports.index', compact(
            'salesData', 'totalRevenue', 'totalOrders', 'avgOrderValue',
            'topProducts', 'topVendors', 'userStats', 'orderStatus', 'dateRange'
        ));
    }

    public function export(Request $request)
    {
        $request->validate([
            'type' => 'required|in:sales,products,vendors,users',
            'format' => 'required|in:csv,excel',
            'date_range' => 'string',
        ]);

        $startDate = $this->getStartDate($request->date_range);
        $endDate = now();

        $data = [];

        switch ($request->type) {
            case 'sales':
                $data = $this->getSalesData($startDate, $endDate);
                break;
            case 'products':
                $data = $this->getProductsData($startDate, $endDate);
                break;
            case 'vendors':
                $data = $this->getVendorsData($startDate, $endDate);
                break;
            case 'users':
                $data = $this->getUsersData($startDate, $endDate);
                break;
        }

        $filename = "report_{$request->type}_" . now()->format('Y-m-d') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            if (!empty($data)) {
                fputcsv($file, array_keys($data[0]));
                foreach ($data as $row) {
                    fputcsv($file, $row);
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getStartDate($dateRange)
    {
        switch ($dateRange) {
            case 'today': return now()->startOfDay();
            case 'yesterday': return now()->subDay()->startOfDay();
            case 'this_week': return now()->startOfWeek();
            case 'last_week': return now()->subWeek()->startOfWeek();
            case 'this_month': return now()->startOfMonth();
            case 'last_month': return now()->subMonth()->startOfMonth();
            case 'this_year': return now()->startOfYear();
            case 'last_30_days': return now()->subDays(30);
            default: return now()->startOfMonth();
        }
    }

    private function getSalesData($startDate, $endDate)
    {
        return Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('order_number', 'created_at', 'total_amount', 'order_status')
            ->get()
            ->toArray();
    }

    private function getProductsData($startDate, $endDate)
    {
        return DB::table('order_items')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->whereBetween('order_items.created_at', [$startDate, $endDate])
            ->select(
                'products.name',
                DB::raw('SUM(order_items.quantity) as sold'),
                DB::raw('SUM(order_items.total_price) as revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderBy('revenue', 'desc')
            ->get()
            ->toArray();
    }

    private function getVendorsData($startDate, $endDate)
    {
        return VendorProfile::with('user')
            ->withSum(['earnings' => function($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            }], 'vendor_amount')
            ->orderBy('earnings_sum_vendor_amount', 'desc')
            ->get()
            ->map(function($vendor) {
                return [
                    'store_name' => $vendor->store_name,
                    'owner' => $vendor->user->name,
                    'email' => $vendor->user->email,
                    'total_earnings' => $vendor->earnings_sum_vendor_amount ?? 0,
                ];
            })
            ->toArray();
    }

    private function getUsersData($startDate, $endDate)
    {
        return User::whereBetween('created_at', [$startDate, $endDate])
            ->select('name', 'email', 'phone', 'created_at')
            ->get()
            ->toArray();
    }
}