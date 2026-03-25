<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VendorProfile;
use App\Models\VendorDocument;
use App\Models\SubscriptionPlan;
use App\Models\OrderItem;
use App\Models\VendorEarning;
use App\Models\VendorPayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage; // Add this import
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Helpers\StorageHelper;
use App\Http\Resources\VendorResource;
use App\Http\Resources\VendorCollection;
use App\Http\Resources\VendorDocumentResource;
use App\Http\Resources\ProductResource;

class VendorController extends Controller
{
    /**
     * Get vendor dashboard data
     */
    public function dashboard(Request $request)
    {
        $vendor = $request->user()->vendorProfile;
        
        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found'
            ], 404);
        }
        
        $stats = [
            'total_sales' => VendorEarning::where('vendor_profile_id', $vendor->id)
                ->where('status', 'processed')
                ->sum('vendor_amount'),
            'total_orders' => OrderItem::where('vendor_profile_id', $vendor->id)->count(),
            'pending_orders' => OrderItem::where('vendor_profile_id', $vendor->id)
                ->where('status', 'pending')
                ->count(),
            'total_products' => $request->user()->products()->count(),
            'total_earnings' => VendorEarning::where('vendor_profile_id', $vendor->id)
                ->where('status', 'pending')
                ->sum('vendor_amount'),
            'total_payouts' => VendorPayout::where('vendor_profile_id', $vendor->id)
                ->where('status', 'completed')
                ->sum('amount'),
        ];
        
        $recentOrders = OrderItem::with(['order', 'productVariant.product'])
            ->where('vendor_profile_id', $vendor->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        $topProducts = DB::table('order_items')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->where('order_items.vendor_profile_id', $vendor->id)
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
            
        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'recent_orders' => $recentOrders,
                'top_products' => $topProducts,
            ]
        ]);
    }

    /**
     * Get vendor products
     */
    public function products(Request $request)
    {
        $products = $request->user()->products()
            ->with(['variants', 'images', 'category'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'LIKE', "%{$search}%");
            })
            ->when($request->status !== null, function ($query) use ($request) {
                $query->where('is_active', $request->status);
            })
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc')
            ->paginate($request->per_page ?? 20);
            
        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Get vendor orders
     */
    public function orders(Request $request)
    {
        $vendor = $request->user()->vendorProfile;
        
        $orders = OrderItem::with(['order.user', 'productVariant.product'])
            ->where('vendor_profile_id', $vendor->id)
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->order_number, function ($query, $orderNumber) {
                $query->whereHas('order', function ($q) use ($orderNumber) {
                    $q->where('order_number', 'LIKE', "%{$orderNumber}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);
            
        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /**
     * Update order status
     */
    public function updateOrderStatus(Request $request, $orderItemId)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
            'tracking_number' => 'nullable|string',
            'courier_name' => 'nullable|string',
        ]);
        
        $vendor = $request->user()->vendorProfile;
        $orderItem = OrderItem::findOrFail($orderItemId);
        
        // Check if order belongs to vendor
        if ($orderItem->vendor_profile_id !== $vendor->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        DB::beginTransaction();
        
        try {
            $oldStatus = $orderItem->status;
            $orderItem->update(['status' => $request->status]);
            
            // Update shipment tracking
            if ($request->tracking_number && $request->courier_name) {
                $orderItem->shipment()->updateOrCreate([], [
                    'tracking_number' => $request->tracking_number,
                    'courier_name' => $request->courier_name,
                    'status' => $request->status === 'shipped' ? 'in_transit' : 'pending',
                ]);
            }
            
            // Log status change
            $orderItem->order->statusLogs()->create([
                'order_item_id' => $orderItem->id,
                'status' => $request->status,
                'notes' => "Status updated by vendor: {$vendor->store_name}",
            ]);
            
            // If all items are delivered, update main order status
            if ($request->status === 'delivered') {
                $order = $orderItem->order;
                $allDelivered = $order->items()->where('status', '!=', 'delivered')->doesntExist();
                
                if ($allDelivered) {
                    $order->update(['order_status' => 'delivered']);
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Order status updated',
                'data' => $orderItem
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update order status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get vendor earnings
     */
    public function earnings(Request $request)
    {
        $vendor = $request->user()->vendorProfile;
        
        $earnings = VendorEarning::with('order')
            ->where('vendor_profile_id', $vendor->id)
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);
            
        $summary = [
            'total_earned' => VendorEarning::where('vendor_profile_id', $vendor->id)
                ->where('status', 'processed')
                ->sum('vendor_amount'),
            'pending_earnings' => VendorEarning::where('vendor_profile_id', $vendor->id)
                ->where('status', 'pending')
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
     * Get vendor payouts
     */
    public function payouts(Request $request)
    {
        $vendor = $request->user()->vendorProfile;
        
        $payouts = VendorPayout::where('vendor_profile_id', $vendor->id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);
            
        $availableBalance = VendorEarning::where('vendor_profile_id', $vendor->id)
            ->where('status', 'pending')
            ->sum('vendor_amount');
            
        return response()->json([
            'success' => true,
            'data' => [
                'payouts' => $payouts,
                'available_balance' => $availableBalance,
            ]
        ]);
    }

    /**
     * Get vendor profile
     */
   public function profile(Request $request)
{
    $vendor = $request->user()->vendorProfile;
    
    if (!$vendor) {
        return response()->json([
            'success' => false,
            'message' => 'Vendor profile not found'
        ], 404);
    }
    
    return response()->json([
        'success' => true,
        'data' => new VendorResource($vendor)
    ]);
}

    /**
     * Update vendor profile
     */
    public function updateProfile(Request $request)
    {
        $vendor = $request->user()->vendorProfile;
        
        $request->validate([
            'store_name' => 'string|unique:vendor_profiles,store_name,' . $vendor->id,
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'contact_email' => 'email',
            'contact_phone' => 'string|max:20',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);
        
        $data = $request->except(['logo', 'banner']);
        
        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($vendor->logo && Storage::disk('public')->exists($vendor->logo)) {
                Storage::disk('public')->delete($vendor->logo);
            }
            $path = $request->file('logo')->store('vendors/logos', 'public');
            $data['logo'] = $path;
        }
        
        // Handle banner upload
        if ($request->hasFile('banner')) {
            // Delete old banner
            if ($vendor->banner && Storage::disk('public')->exists($vendor->banner)) {
                Storage::disk('public')->delete($vendor->banner);
            }
            $path = $request->file('banner')->store('vendors/banners', 'public');
            $data['banner'] = $path;
        }
        
        // Update store slug if name changed
        if ($request->has('store_name') && $request->store_name !== $vendor->store_name) {
            $data['store_slug'] = Str::slug($request->store_name) . '-' . uniqid();
        }
        
        $vendor->update($data);
        
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $vendor
        ]);
    }

    /**
     * Upload vendor document (KYC)
     */
    public function uploadDocument(Request $request)
    {
        $request->validate([
            'document_type' => 'required|in:pan,gst,bank_statement,address_proof',
            'document_number' => 'nullable|string|max:50',
            'document' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png',
        ]);
        
        $vendor = $request->user()->vendorProfile;
        
        // Upload document
        $path = $request->file('document')->store('vendors/documents/' . $vendor->id, 'public');
        
        $document = VendorDocument::create([
            'vendor_profile_id' => $vendor->id,
            'document_type' => $request->document_type,
            'document_number' => $request->document_number,
            'document_path' => $path,
            'document_url' => Storage::disk('public')->url($path),
            'status' => 'pending',
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Document uploaded successfully',
            'data' => $document
        ], 201);
    }

    /**
     * Register as a vendor
     */
    public function store(Request $request)
    {
        $request->validate([
            'store_name' => 'required|string|max:255|unique:vendor_profiles,store_name',
            'description' => 'nullable|string',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:20',
            'address' => 'required|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'gst_number' => 'nullable|string|max:15',
            'pan_number' => 'nullable|string|max:10',
        ]);
        
        // Check if user already has vendor profile
        if ($request->user()->vendorProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile already exists'
            ], 400);
        }
        
        $data = $request->except(['logo', 'gst_number', 'pan_number']);
        $data['user_id'] = $request->user()->id;
        $data['store_slug'] = Str::slug($request->store_name) . '-' . uniqid();
        $data['status'] = 'pending';
        
        // Handle logo upload
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('vendors/logos', 'public');
            $data['logo'] = $path;
        }
        
        $vendor = VendorProfile::create($data);
        
        // Upload GST and PAN documents if provided
        if ($request->gst_number) {
            // Create placeholder for GST document
            VendorDocument::create([
                'vendor_profile_id' => $vendor->id,
                'document_type' => 'gst',
                'document_number' => $request->gst_number,
                'status' => 'pending',
            ]);
        }
        
        if ($request->pan_number) {
            // Create placeholder for PAN document
            VendorDocument::create([
                'vendor_profile_id' => $vendor->id,
                'document_type' => 'pan',
                'document_number' => $request->pan_number,
                'status' => 'pending',
            ]);
        }
        
        // Assign free subscription plan
        $freePlan = SubscriptionPlan::where('name', 'Free')->first();
        if ($freePlan) {
            $vendor->subscriptions()->create([
                'subscription_plan_id' => $freePlan->id,
                'start_date' => now(),
                'end_date' => now()->addDays(30),
                'status' => 'active',
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Vendor registration submitted for approval',
            'data' => $vendor
        ], 201);
    }
    /**
 * Delete vendor document
 */
    public function deleteDocument(Request $request, $documentId)
        {
            $vendor = $request->user()->vendorProfile;
            $document = VendorDocument::findOrFail($documentId);
            
            // Check if document belongs to vendor
            if ($document->vendor_profile_id !== $vendor->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }
            
            // Delete file if exists
            if ($document->document_path && Storage::disk('public')->exists($document->document_path)) {
                Storage::disk('public')->delete($document->document_path);
            }
            
            $document->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully'
            ]);
        }
    /**
     * Get vendor store page (public)
     */
    public function storePage($slug)
    {
        $vendor = VendorProfile::where('store_slug', $slug)
        ->where('status', 'approved')
        ->firstOrFail();
        
        $products = $vendor->user->products()
        ->where('is_active', true)
        ->with(['variants', 'images'])
        ->paginate(20);
        
        return response()->json([
        'success' => true,
        'data' => [
            'vendor' => new VendorResource($vendor),
            'products' => ProductResource::collection($products),
            ]
        ]);
    }   
    /**
     * Toggle follow/unfollow vendor
     */
    public function toggleFollow(Request $request, $vendorId)
    {
        $vendor = VendorProfile::findOrFail($vendorId);
        $user = $request->user();
        
        if ($vendor->followers()->where('user_id', $user->id)->exists()) {
            $vendor->followers()->detach($user->id);
            $vendor->decrement('follower_count');
            $isFollowing = false;
        } else {
            $vendor->followers()->attach($user->id);
            $vendor->increment('follower_count');
            $isFollowing = true;
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'is_following' => $isFollowing,
                'followers_count' => $vendor->follower_count
            ]
        ]);
    }

    /**
     * Get vendor followers
     */
    public function followers(Request $request)
    {
        $vendor = $request->user()->vendorProfile;
        
        $followers = $vendor->followers()
            ->paginate($request->per_page ?? 20);
            
        return response()->json([
            'success' => true,
            'data' => $followers
        ]);
    }

    /**
     * Get vendor subscription status
     */
    public function subscriptionStatus(Request $request)
    {
        $vendor = $request->user()->vendorProfile;
        $currentSubscription = $vendor->currentSubscription;
        $remainingProducts = $vendor->remainingProductSlots();
        
        return response()->json([
            'success' => true,
            'data' => [
                'subscription' => $currentSubscription,
                'plan' => $currentSubscription->plan ?? null,
                'remaining_products' => $remainingProducts,
                'has_reached_limit' => $vendor->hasReachedProductLimit(),
            ]
        ]);
    }

    /**
     * Get vendor documents
     */
    
    public function documents(Request $request)
    {
    $vendor = $request->user()->vendorProfile;
    
    $documents = $vendor->documents()
        ->orderBy('created_at', 'desc')
        ->get();
        
    return response()->json([
        'success' => true,
        'data' => VendorDocumentResource::collection($documents)
    ]);
}
}