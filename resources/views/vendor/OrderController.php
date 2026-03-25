<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Shipment;
use App\Models\VendorProfile;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of orders
     */
    public function index(Request $request)
    {
        $vendor = Auth::user()->vendorProfile;
        
        $orders = OrderItem::with(['order.user', 'productVariant.product', 'shipment'])
            ->where('vendor_profile_id', $vendor->id)
            ->when($request->status, function($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->search, function($query, $search) {
                $query->whereHas('order', function($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%");
                })->orWhere('product_name', 'like', "%{$search}%");
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
            'total' => OrderItem::where('vendor_profile_id', $vendor->id)->count(),
            'pending' => OrderItem::where('vendor_profile_id', $vendor->id)->where('status', 'pending')->count(),
            'processing' => OrderItem::where('vendor_profile_id', $vendor->id)->where('status', 'processing')->count(),
            'shipped' => OrderItem::where('vendor_profile_id', $vendor->id)->where('status', 'shipped')->count(),
            'delivered' => OrderItem::where('vendor_profile_id', $vendor->id)->where('status', 'delivered')->count(),
            'cancelled' => OrderItem::where('vendor_profile_id', $vendor->id)->where('status', 'cancelled')->count(),
        ];

        return view('vendor.orders.index', compact('orders', 'stats'));
    }

    /**
     * Display the specified order
     */
    public function show($id)
    {
        $vendor = Auth::user()->vendorProfile;
        
        $orderItem = OrderItem::with([
            'order.user', 
            'order.user.addresses',
            'productVariant.product',
            'productVariant.attributeValues.attribute',
            'shipment',
            'statusLogs'
        ])
        ->where('vendor_profile_id', $vendor->id)
        ->findOrFail($id);

        return view('vendor.orders.show', compact('orderItem'));
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled,returned',
            'tracking_number' => 'nullable|required_if:status,shipped|string',
            'courier_name' => 'nullable|required_if:status,shipped|string',
            'notes' => 'nullable|string'
        ]);

        $vendor = Auth::user()->vendorProfile;
        $orderItem = OrderItem::where('vendor_profile_id', $vendor->id)->findOrFail($id);

        DB::beginTransaction();

        try {
            $oldStatus = $orderItem->status;
            $orderItem->update(['status' => $request->status]);

            // Update shipment if shipped
            if ($request->status == 'shipped') {
                $shipment = $orderItem->shipment ?: new Shipment();
                $shipment->order_item_id = $orderItem->id;
                $shipment->tracking_number = $request->tracking_number;
                $shipment->courier_name = $request->courier_name;
                $shipment->status = 'in_transit';
                $shipment->save();
            }

            // Log status change
            $orderItem->statusLogs()->create([
                'order_id' => $orderItem->order_id,
                'order_item_id' => $orderItem->id,
                'status' => $request->status,
                'notes' => $request->notes ?? "Status updated from {$oldStatus} to {$request->status} by vendor",
                'metadata' => [
                    'changed_by' => Auth::id(),
                    'changed_by_name' => Auth::user()->name,
                    'old_status' => $oldStatus,
                    'new_status' => $request->status,
                ]
            ]);

            // Update main order status if all items are delivered
            if ($request->status == 'delivered') {
                $order = $orderItem->order;
                $allDelivered = $order->items()->where('status', '!=', 'delivered')->doesntExist();
                
                if ($allDelivered) {
                    $order->update(['order_status' => 'delivered']);
                }
            }

            DB::commit();

            return redirect()->back()->with('success', 'Order status updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update order status: ' . $e->getMessage());
        }
    }

    /**
     * Bulk update order statuses
     */
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:order_items,id',
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled,returned',
        ]);

        $vendor = Auth::user()->vendorProfile;
        $orderItems = OrderItem::where('vendor_profile_id', $vendor->id)
            ->whereIn('id', $request->order_ids)
            ->get();

        DB::beginTransaction();

        try {
            foreach ($orderItems as $orderItem) {
                $orderItem->update(['status' => $request->status]);
                
                $orderItem->statusLogs()->create([
                    'order_id' => $orderItem->order_id,
                    'order_item_id' => $orderItem->id,
                    'status' => $request->status,
                    'notes' => "Bulk status update by vendor",
                    'metadata' => ['bulk_update' => true]
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', count($orderItems) . ' orders updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update orders: ' . $e->getMessage());
        }
    }

    /**
     * Print invoice
     */
    public function printInvoice($id)
    {
        $vendor = Auth::user()->vendorProfile;
        $orderItem = OrderItem::with(['order.user', 'productVariant.product'])
            ->where('vendor_profile_id', $vendor->id)
            ->findOrFail($id);

        return view('vendor.orders.invoice', compact('orderItem'));
    }

    /**
     * Generate packing slip
     */
    public function packingSlip($id)
    {
        $vendor = Auth::user()->vendorProfile;
        $orderItem = OrderItem::with(['order.user', 'productVariant.product'])
            ->where('vendor_profile_id', $vendor->id)
            ->findOrFail($id);

        return view('vendor.orders.packing-slip', compact('orderItem'));
    }
}