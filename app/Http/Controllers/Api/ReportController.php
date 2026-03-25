<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\VendorProfile;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'group_by' => 'in:day,week,month',
        ]);
        
        $groupBy = $request->group_by ?? 'day';
        
        $sales = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$request->date_from, $request->date_to])
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$this->getDateFormat($groupBy)}') as period"),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('AVG(total_amount) as average_order_value'),
                DB::raw('SUM(shipping_amount) as total_shipping'),
                DB::raw('SUM(tax_amount) as total_tax'),
                DB::raw('SUM(discount_amount) as total_discount')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();
            
        $summary = [
            'total_revenue' => $sales->sum('total_revenue'),
            'total_orders' => $sales->sum('total_orders'),
            'average_order_value' => $sales->avg('average_order_value'),
            'total_shipping' => $sales->sum('total_shipping'),
            'total_tax' => $sales->sum('total_tax'),
            'total_discount' => $sales->sum('total_discount'),
        ];
        
        return response()->json([
            'sales' => $sales,
            'summary' => $summary,
        ]);
    }

    public function vendors(Request $request)
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);
        
        $query = VendorProfile::with('user')
            ->withSum(['earnings' => function($q) use ($request) {
                if ($request->date_from) {
                    $q->where('created_at', '>=', $request->date_from);
                }
                if ($request->date_to) {
                    $q->where('created_at', '<=', $request->date_to);
                }
            }], 'vendor_amount')
            ->withCount(['products' => function($q) {
                $q->where('is_active', true);
            }])
            ->withCount(['orders' => function($q) use ($request) {
                if ($request->date_from) {
                    $q->where('created_at', '>=', $request->date_from);
                }
                if ($request->date_to) {
                    $q->where('created_at', '<=', $request->date_to);
                }
            }]);
            
        $vendors = $query->orderBy('earnings_sum_vendor_amount', 'desc')
            ->limit($request->limit ?? 20)
            ->get();
            
        return response()->json($vendors);
    }

    public function products(Request $request)
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'category_id' => 'nullable|exists:categories,id',
            'vendor_id' => 'nullable|exists:users,id',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);
        
        $query = Product::with(['vendor', 'category'])
            ->withCount(['orderItems as total_sold' => function($q) use ($request) {
                if ($request->date_from) {
                    $q->where('created_at', '>=', $request->date_from);
                }
                if ($request->date_to) {
                    $q->where('created_at', '<=', $request->date_to);
                }
            }])
            ->withSum(['orderItems as total_revenue' => function($q) use ($request) {
                if ($request->date_from) {
                    $q->where('created_at', '>=', $request->date_from);
                }
                if ($request->date_to) {
                    $q->where('created_at', '<=', $request->date_to);
                }
            }], 'total_price');
            
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        
        if ($request->vendor_id) {
            $query->where('vendor_id', $request->vendor_id);
        }
        
        $products = $query->where('total_sold', '>', 0)
            ->orderBy('total_sold', 'desc')
            ->limit($request->limit ?? 20)
            ->get();
            
        return response()->json($products);
    }

    public function export(Request $request)
    {
        $request->validate([
            'type' => 'required|in:sales,vendors,products,orders',
            'format' => 'required|in:csv,excel',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);
        
        $data = [];
        $filename = "report_{$request->type}_" . now()->format('Y-m-d_His');
        
        switch ($request->type) {
            case 'sales':
                $data = $this->getSalesData($request);
                break;
            case 'vendors':
                $data = $this->getVendorsData($request);
                break;
            case 'products':
                $data = $this->getProductsData($request);
                break;
            case 'orders':
                $data = $this->getOrdersData($request);
                break;
        }
        
        // Log the export
        app(\App\Services\AuditService::class)->logExport(
            $request->user(),
            $request->type,
            $request->all(),
            count($data)
        );
        
        if ($request->format === 'csv') {
            return $this->exportAsCsv($data, $filename);
        } else {
            return $this->exportAsExcel($data, $filename);
        }
    }

    protected function getSalesData($request)
    {
        return Order::where('payment_status', 'paid')
            ->when($request->date_from, function ($query, $dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->select(
                'order_number',
                'created_at',
                'subtotal',
                'discount_amount',
                'tax_amount',
                'shipping_amount',
                'total_amount',
                'payment_method',
                'order_status'
            )
            ->get()
            ->toArray();
    }

    protected function getVendorsData($request)
    {
        return VendorProfile::with('user')
            ->withSum('earnings', 'vendor_amount')
            ->withCount('products')
            ->withCount('orders')
            ->get()
            ->map(function($vendor) {
                return [
                    'store_name' => $vendor->store_name,
                    'owner_name' => $vendor->user->name,
                    'email' => $vendor->user->email,
                    'phone' => $vendor->user->phone,
                    'total_products' => $vendor->products_count,
                    'total_orders' => $vendor->orders_count,
                    'total_earnings' => $vendor->earnings_sum_vendor_amount,
                    'status' => $vendor->status,
                    'rating' => $vendor->rating,
                    'created_at' => $vendor->created_at,
                ];
            })
            ->toArray();
    }

    protected function getProductsData($request)
    {
        return Product::with(['vendor', 'category'])
            ->withCount('orderItems as total_sold')
            ->withSum('orderItems', 'total_price')
            ->get()
            ->map(function($product) {
                return [
                    'name' => $product->name,
                    'vendor' => $product->vendor->name,
                    'category' => $product->category->name ?? 'N/A',
                    'price' => $product->default_variant->price ?? 0,
                    'stock' => $product->variants->sum('stock_quantity'),
                    'total_sold' => $product->order_items_count,
                    'total_revenue' => $product->order_items_sum_total_price,
                    'views' => $product->view_count,
                    'rating' => $product->average_rating,
                    'created_at' => $product->created_at,
                ];
            })
            ->toArray();
    }

    protected function getOrdersData($request)
    {
        return Order::with(['user', 'items.vendorProfile'])
            ->when($request->date_from, function ($query, $dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->get()
            ->map(function($order) {
                return [
                    'order_number' => $order->order_number,
                    'customer' => $order->user->name,
                    'customer_phone' => $order->user->phone,
                    'total_amount' => $order->total_amount,
                    'payment_method' => $order->payment_method,
                    'payment_status' => $order->payment_status,
                    'order_status' => $order->order_status,
                    'items_count' => $order->items->count(),
                    'created_at' => $order->created_at,
                ];
            })
            ->toArray();
    }

    protected function exportAsCsv($data, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}.csv",
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

    protected function exportAsExcel($data, $filename)
    {
        // This would use Laravel Excel package
        // return Excel::download(new ReportExport($data), "{$filename}.xlsx");
        
        // Fallback to CSV for now
        return $this->exportAsCsv($data, $filename);
    }

    protected function getDateFormat($groupBy)
    {
        switch ($groupBy) {
            case 'day':
                return '%Y-%m-%d';
            case 'week':
                return '%Y-%u';
            case 'month':
                return '%Y-%m';
            default:
                return '%Y-%m-%d';
        }
    }
}