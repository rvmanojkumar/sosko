<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VendorProfile;
use App\Models\VendorEarning;
use App\Models\VendorPayout;
use App\Models\VendorReview;
use App\Models\Category;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display vendor dashboard
     */
    public function index(Request $request)
    {
        $vendor = Auth::user()->vendorProfile;
        
        if (!$vendor) {
            return redirect()->route('vendor.profile.create')
                ->with('error', 'Please complete your vendor profile first.');
        }

        // Date range filter
        $dateRange = $request->get('date_range', 'this_month');
        $startDate = $this->getStartDate($dateRange);
        $endDate = Carbon::now();

        // Statistics
        $stats = $this->getStatistics($vendor->id, $startDate, $endDate);
        
        // Sales data for chart
        $salesData = $this->getSalesData($vendor->id, $startDate, $endDate);
        
        // Recent orders
        $recentOrders = $this->getRecentOrders($vendor->id);
        
        // Top selling products
        $topProducts = $this->getTopProducts($vendor->id, $startDate, $endDate);
        
        // Recent earnings
        $recentEarnings = $this->getRecentEarnings($vendor->id);
        
        // Low stock products
        $lowStockProducts = $this->getLowStockProducts(Auth::id());
        
        // Pending orders count
        $pendingOrders = OrderItem::where('vendor_profile_id', $vendor->id)
            ->where('status', 'pending')
            ->count();
        
        // Pending reviews
        $pendingReviews = VendorReview::where('vendor_profile_id', $vendor->id)
            ->where('is_approved', false)
            ->count();
        
        // Subscription info
        $subscription = $vendor->currentSubscription;
        $plan = $subscription ? $subscription->plan : null;
        
        return view('vendor.dashboard', compact(
            'stats',
            'salesData',
            'recentOrders',
            'topProducts',
            'recentEarnings',
            'lowStockProducts',
            'pendingOrders',
            'pendingReviews',
            'subscription',
            'plan',
            'dateRange'
        ));
    }

    /**
     * Get dashboard statistics
     */
    private function getStatistics($vendorId, $startDate, $endDate)
    {
        // Total sales (amount)
        $totalSales = VendorEarning::where('vendor_profile_id', $vendorId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('vendor_amount');
        
        // Total orders
        $totalOrders = OrderItem::where('vendor_profile_id', $vendorId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        
        // Total products
        $totalProducts = Product::where('vendor_id', Auth::id())
            ->count();
        
        // Average order value
        $averageOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;
        
        // Pending earnings
        $pendingEarnings = VendorEarning::where('vendor_profile_id', $vendorId)
            ->where('status', 'pending')
            ->sum('vendor_amount');
        
        // Total earnings (all time)
        $totalEarningsAllTime = VendorEarning::where('vendor_profile_id', $vendorId)
            ->where('status', 'processed')
            ->sum('vendor_amount');
        
        // This month's sales
        $thisMonthSales = VendorEarning::where('vendor_profile_id', $vendorId)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('vendor_amount');
        
        // Last month's sales
        $lastMonthSales = VendorEarning::where('vendor_profile_id', $vendorId)
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->sum('vendor_amount');
        
        // Sales growth percentage
        $salesGrowth = 0;
        if ($lastMonthSales > 0) {
            $salesGrowth = (($thisMonthSales - $lastMonthSales) / $lastMonthSales) * 100;
        }
        
        // Total views
        $totalViews = Product::where('vendor_id', Auth::id())
            ->sum('view_count');
        
        // Conversion rate (orders / views)
        $conversionRate = $totalViews > 0 ? ($totalOrders / $totalViews) * 100 : 0;
        
        return [
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
            'total_products' => $totalProducts,
            'average_order_value' => $averageOrderValue,
            'pending_earnings' => $pendingEarnings,
            'total_earnings_all_time' => $totalEarningsAllTime,
            'this_month_sales' => $thisMonthSales,
            'last_month_sales' => $lastMonthSales,
            'sales_growth' => round($salesGrowth, 2),
            'total_views' => $totalViews,
            'conversion_rate' => round($conversionRate, 2),
        ];
    }

    /**
     * Get sales data for charts
     */
    private function getSalesData($vendorId, $startDate, $endDate)
    {
        $salesData = OrderItem::where('vendor_profile_id', $vendorId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_price) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Prepare data for chart
        $dates = [];
        $amounts = [];
        
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $dates[] = $currentDate->format('d M');
            
            $sale = $salesData->firstWhere('date', $dateStr);
            $amounts[] = $sale ? (float) $sale->total : 0;
            
            $currentDate->addDay();
        }
        
        return [
            'labels' => $dates,
            'values' => $amounts,
        ];
    }

    /**
     * Get recent orders
     */
    private function getRecentOrders($vendorId)
    {
        return OrderItem::with(['order.user', 'productVariant.product'])
            ->where('vendor_profile_id', $vendorId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'order_number' => $item->order->order_number,
                    'customer_name' => $item->order->user->name,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'total' => $item->total_price,
                    'status' => $item->status,
                    'status_badge' => $this->getStatusBadge($item->status),
                    'created_at' => $item->created_at->diffForHumans(),
                ];
            });
    }

    /**
     * Get top selling products
     */
    private function getTopProducts($vendorId, $startDate, $endDate)
    {
        return OrderItem::where('vendor_profile_id', $vendorId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                'product_id',
                'product_name',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(total_price) as total_revenue')
            )
            ->groupBy('product_id', 'product_name')
            ->orderBy('total_quantity', 'desc')
            ->limit(5)
            ->get()
            ->map(function($item) {
                // Get product image
                $product = Product::find($item->product_id);
                $image = $product && $product->images->first() 
                    ? Storage::url($product->images->first()->image_path) 
                    : null;
                
                return [
                    'id' => $item->product_id,
                    'name' => $item->product_name,
                    'image' => $image,
                    'quantity' => $item->total_quantity,
                    'revenue' => $item->total_revenue,
                ];
            });
    }

    /**
     * Get recent earnings
     */
    private function getRecentEarnings($vendorId)
    {
        return VendorEarning::with('order')
            ->where('vendor_profile_id', $vendorId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($earning) {
                return [
                    'id' => $earning->id,
                    'order_number' => $earning->order->order_number,
                    'amount' => $earning->vendor_amount,
                    'commission' => $earning->commission_amount,
                    'status' => $earning->status,
                    'status_badge' => $this->getEarningStatusBadge($earning->status),
                    'created_at' => $earning->created_at->diffForHumans(),
                ];
            });
    }

    /**
     * Get low stock products
     */
    private function getLowStockProducts($vendorId)
    {
        return Product::where('vendor_id', $vendorId)
            ->whereHas('variants', function($query) {
                $query->where('stock_quantity', '<=', DB::raw('low_stock_threshold'))
                    ->where('stock_quantity', '>', 0);
            })
            ->with(['variants' => function($query) {
                $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                    ->where('stock_quantity', '>', 0);
            }])
            ->limit(5)
            ->get()
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'variants' => $product->variants->map(function($variant) {
                        return [
                            'sku' => $variant->sku,
                            'stock' => $variant->stock_quantity,
                            'threshold' => $variant->low_stock_threshold,
                        ];
                    }),
                ];
            });
    }

    /**
     * Get status badge class
     */
    private function getStatusBadge($status)
    {
        $badges = [
            'pending' => 'warning',
            'confirmed' => 'info',
            'processing' => 'primary',
            'shipped' => 'primary',
            'delivered' => 'success',
            'cancelled' => 'danger',
            'returned' => 'danger',
        ];
        
        return $badges[$status] ?? 'secondary';
    }

    /**
     * Get earning status badge
     */
    private function getEarningStatusBadge($status)
    {
        $badges = [
            'pending' => 'warning',
            'processed' => 'success',
            'failed' => 'danger',
        ];
        
        return $badges[$status] ?? 'secondary';
    }

    /**
     * Get start date based on date range
     */
    private function getStartDate($dateRange)
    {
        switch ($dateRange) {
            case 'today':
                return Carbon::today();
            case 'yesterday':
                return Carbon::yesterday();
            case 'this_week':
                return Carbon::now()->startOfWeek();
            case 'last_week':
                return Carbon::now()->subWeek()->startOfWeek();
            case 'this_month':
                return Carbon::now()->startOfMonth();
            case 'last_month':
                return Carbon::now()->subMonth()->startOfMonth();
            case 'this_year':
                return Carbon::now()->startOfYear();
            case 'last_30_days':
                return Carbon::now()->subDays(30);
            default:
                return Carbon::now()->startOfMonth();
        }
    }

    /**
     * Get sales analytics
     */
    public function analytics(Request $request)
    {
        $vendor = Auth::user()->vendorProfile;
        $dateRange = $request->get('date_range', 'this_month');
        $startDate = $this->getStartDate($dateRange);
        $endDate = Carbon::now();
        
        // Daily sales
        $dailySales = $this->getSalesData($vendor->id, $startDate, $endDate);
        
        // Top categories
        $topCategories = OrderItem::where('vendor_profile_id', $vendor->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->join('products', 'order_items.product_variant_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'categories.id',
                'categories.name',
                DB::raw('SUM(order_items.total_price) as revenue'),
                DB::raw('COUNT(order_items.id) as orders')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('revenue', 'desc')
            ->limit(5)
            ->get();
        
        // Hourly sales distribution
        $hourlySales = OrderItem::where('vendor_profile_id', $vendor->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('SUM(total_price) as total')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
        
        $hours = range(0, 23);
        $hourlyData = [];
        foreach ($hours as $hour) {
            $sale = $hourlySales->firstWhere('hour', $hour);
            $hourlyData[] = $sale ? (float) $sale->total : 0;
        }
        
        return response()->json([
            'daily_sales' => $dailySales,
            'top_categories' => $topCategories,
            'hourly_sales' => [
                'labels' => $hours,
                'values' => $hourlyData,
            ],
        ]);
    }

    /**
     * Get recent activities
     */
    public function activities()
    {
        $vendor = Auth::user()->vendorProfile;
        
        $activities = collect();
        
        // New orders
        $newOrders = OrderItem::where('vendor_profile_id', $vendor->id)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->get()
            ->map(function($item) {
                return [
                    'type' => 'order',
                    'title' => 'New Order Received',
                    'description' => "Order #{$item->order->order_number} - {$item->product_name}",
                    'amount' => $item->total_price,
                    'created_at' => $item->created_at,
                ];
            });
        
        // Low stock alerts
        $lowStock = $this->getLowStockProducts(Auth::id())
            ->map(function($product) {
                return [
                    'type' => 'stock',
                    'title' => 'Low Stock Alert',
                    'description' => "{$product['name']} is running low on stock",
                    'created_at' => Carbon::now(),
                ];
            });
        
        // New reviews
        $newReviews = VendorReview::where('vendor_profile_id', $vendor->id)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->get()
            ->map(function($review) {
                return [
                    'type' => 'review',
                    'title' => 'New Review',
                    'description' => "New {$review->rating}-star review received",
                    'rating' => $review->rating,
                    'created_at' => $review->created_at,
                ];
            });
        
        // Earnings
        $newEarnings = VendorEarning::where('vendor_profile_id', $vendor->id)
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->get()
            ->map(function($earning) {
                return [
                    'type' => 'earning',
                    'title' => 'New Earnings',
                    'description' => "₹{$earning->vendor_amount} earned from order #{$earning->order->order_number}",
                    'amount' => $earning->vendor_amount,
                    'created_at' => $earning->created_at,
                ];
            });
        
        $activities = $newOrders->concat($lowStock)->concat($newReviews)->concat($newEarnings)
            ->sortByDesc('created_at')
            ->take(20);
        
        return response()->json($activities);
    }

    /**
     * Export sales report
     */
    public function exportReport(Request $request)
    {
        $vendor = Auth::user()->vendorProfile;
        $dateRange = $request->get('date_range', 'this_month');
        $startDate = $this->getStartDate($dateRange);
        $endDate = Carbon::now();
        
        $orders = OrderItem::where('vendor_profile_id', $vendor->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['order.user', 'productVariant.product'])
            ->get();
        
        $filename = "sales_report_{$vendor->store_slug}_{$startDate->format('Y-m-d')}_to_{$endDate->format('Y-m-d')}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];
        
        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, [
                'Order Number',
                'Date',
                'Customer',
                'Product',
                'Quantity',
                'Unit Price',
                'Total',
                'Status',
            ]);
            
            // Add data
            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->order->order_number,
                    $order->created_at->format('Y-m-d H:i:s'),
                    $order->order->user->name,
                    $order->product_name,
                    $order->quantity,
                    $order->unit_price,
                    $order->total_price,
                    $order->status,
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get dashboard widgets data for AJAX
     */
    public function widgets(Request $request)
    {
        $vendor = Auth::user()->vendorProfile;
        $widget = $request->get('widget');
        
        switch ($widget) {
            case 'sales_chart':
                $startDate = $this->getStartDate($request->get('date_range', 'this_month'));
                $salesData = $this->getSalesData($vendor->id, $startDate, Carbon::now());
                return response()->json($salesData);
                
            case 'top_products':
                $startDate = $this->getStartDate($request->get('date_range', 'this_month'));
                $topProducts = $this->getTopProducts($vendor->id, $startDate, Carbon::now());
                return response()->json($topProducts);
                
            case 'recent_orders':
                $recentOrders = $this->getRecentOrders($vendor->id);
                return response()->json($recentOrders);
                
            case 'stats':
                $startDate = $this->getStartDate($request->get('date_range', 'this_month'));
                $stats = $this->getStatistics($vendor->id, $startDate, Carbon::now());
                return response()->json($stats);
                
            default:
                return response()->json(['error' => 'Invalid widget'], 400);
        }
    }
}