<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VendorProfile;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\VendorEarning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $stats = [
            'total_revenue' => Order::where('payment_status', 'paid')->sum('total_amount'),
            'total_orders' => Order::count(),
            'total_users' => User::count(),
            'total_vendors' => VendorProfile::count(),
            'total_products' => Product::count(),
            'pending_orders' => Order::where('order_status', 'placed')->count(),
            'pending_vendors' => VendorProfile::where('status', 'pending')->count(),
            'pending_products' => Product::where('is_active', false)->count(),
        ];
        
        $recentOrders = Order::with(['user', 'items.vendorProfile'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        $topVendors = VendorProfile::with('user')
            ->withSum('earnings as total_earned', 'vendor_amount')
            ->orderBy('total_earned', 'desc')
            ->limit(10)
            ->get();
            
        $salesByDay = Order::where('payment_status', 'paid')
            ->where('created_at', '>=', now()->subDays(30))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
            
        return response()->json([
            'stats' => $stats,
            'recent_orders' => $recentOrders,
            'top_vendors' => $topVendors,
            'sales_by_day' => $salesByDay,
        ]);
    }

    public function users(Request $request)
    {
        $users = User::with('roles')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%");
            })
            ->when($request->role, function ($query, $role) {
                $query->role($role);
            })
            ->when($request->is_active !== null, function ($query) use ($request) {
                $query->where('is_active', $request->is_active);
            })
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc')
            ->paginate($request->per_page ?? 20);
            
        return response()->json($users);
    }

    public function updateUserStatus(Request $request, User $user)
    {
        $request->validate([
            'is_active' => 'required|boolean',
        ]);
        
        $user->update(['is_active' => $request->is_active]);
        
        return response()->json([
            'message' => 'User status updated',
            'user' => $user
        ]);
    }

    public function vendors(Request $request)
    {
        $vendors = VendorProfile::with('user')
            ->when($request->search, function ($query, $search) {
                $query->where('store_name', 'LIKE', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc')
            ->paginate($request->per_page ?? 20);
            
        return response()->json($vendors);
    }

    public function approveVendor(Request $request, VendorProfile $vendor)
    {
        $request->validate([
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
        ]);
        
        DB::beginTransaction();
        
        try {
            $vendor->update(['status' => 'approved']);
            
            // Create subscription
            $vendor->subscriptions()->create([
                'subscription_plan_id' => $request->subscription_plan_id,
                'start_date' => now(),
                'end_date' => now()->addDays(30),
                'status' => 'active',
            ]);
            
            DB::commit();
            
            return response()->json([
                'message' => 'Vendor approved successfully',
                'vendor' => $vendor
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to approve vendor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function rejectVendor(Request $request, VendorProfile $vendor)
    {
        $request->validate([
            'rejection_reason' => 'required|string',
        ]);
        
        $vendor->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);
        
        return response()->json([
            'message' => 'Vendor rejected',
            'vendor' => $vendor
        ]);
    }

    public function suspendVendor(Request $request, VendorProfile $vendor)
    {
        $request->validate([
            'suspension_reason' => 'required|string',
        ]);
        
        $vendor->update([
            'status' => 'suspended',
            'rejection_reason' => $request->suspension_reason,
        ]);
        
        // Deactivate all vendor products
        $vendor->user->products()->update(['is_active' => false]);
        
        return response()->json([
            'message' => 'Vendor suspended',
            'vendor' => $vendor
        ]);
    }

    public function pendingProducts(Request $request)
    {
        $products = Product::with(['vendor', 'category'])
            ->where('is_active', false)
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'LIKE', "%{$search}%");
            })
            ->orderBy('created_at', 'asc')
            ->paginate($request->per_page ?? 20);
            
        return response()->json($products);
    }

    public function approveProduct(Request $request, Product $product)
    {
        $product->update(['is_active' => true]);
        
        return response()->json([
            'message' => 'Product approved',
            'product' => $product
        ]);
    }

    public function orders(Request $request)
    {
        $orders = Order::with(['user', 'items.vendorProfile'])
            ->when($request->status, function ($query, $status) {
                $query->where('order_status', $status);
            })
            ->when($request->payment_status, function ($query, $paymentStatus) {
                $query->where('payment_status', $paymentStatus);
            })
            ->when($request->order_number, function ($query, $orderNumber) {
                $query->where('order_number', 'LIKE', "%{$orderNumber}%");
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc')
            ->paginate($request->per_page ?? 20);
            
        return response()->json($orders);
    }

    public function updateOrderStatus(Request $request, Order $order)
    {
        $request->validate([
            'order_status' => 'required|in:placed,confirmed,processing,shipped,delivered,cancelled,returned',
        ]);
        
        $oldStatus = $order->order_status;
        $order->update(['order_status' => $request->order_status]);
        
        // Log status change
        $order->statusLogs()->create([
            'status' => $request->order_status,
            'notes' => 'Status updated by admin',
        ]);
        
        return response()->json([
            'message' => 'Order status updated',
            'order' => $order
        ]);
    }
}