<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Shipment;
use App\Models\Cart;
use App\Models\PromoCode;
use App\Models\VendorEarning;
use Razorpay\Api\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    protected $razorpay;

    public function __construct()
    {
        $this->razorpay = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
    }

    public function checkout(Request $request)
    {
        $user = $request->user();
        $cart = Cart::with(['items.productVariant.product'])->where('user_id', $user->id)->first();
        
        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }
        
        $request->validate([
            'address_id' => 'required|exists:user_addresses,id',
            'payment_method' => 'required|in:razorpay,cod,wallet',
            'promo_code' => 'nullable|exists:promo_codes,code'
        ]);
        
        $address = $user->addresses()->find($request->address_id);
        
        // Group by vendor
        $vendorGroups = $cart->items->groupBy(function ($item) {
            return $item->productVariant->product->vendor_id;
        });
        
        DB::beginTransaction();
        
        try {
            $orderNumber = 'ORD-' . strtoupper(uniqid());
            
            // Calculate totals
            $subtotal = 0;
            $totalDiscount = 0;
            
            foreach ($cart->items as $item) {
                $variant = $item->productVariant;
                $price = $variant->current_price;
                $itemTotal = $price * $item->quantity;
                $subtotal += $itemTotal;
            }
            
            // Apply promo code
            if ($request->promo_code) {
                $promoCode = PromoCode::where('code', $request->promo_code)
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now())
                    ->where('is_active', true)
                    ->first();
                    
                if ($promoCode) {
                    $discountAmount = $promoCode->type === 'percentage' 
                        ? $subtotal * ($promoCode->value / 100)
                        : $promoCode->value;
                        
                    $totalDiscount = min($discountAmount, $subtotal);
                    $promoCode->increment('used_count');
                }
            }
            
            $taxAmount = $subtotal * 0.18; // 18% GST
            $shippingAmount = 40; // Flat shipping
            $totalAmount = $subtotal - $totalDiscount + $taxAmount + $shippingAmount;
            
            // Create order
            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $user->id,
                'subtotal' => $subtotal,
                'discount_amount' => $totalDiscount,
                'tax_amount' => $taxAmount,
                'shipping_amount' => $shippingAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
                'order_status' => 'placed',
                'billing_address' => $address->toArray(),
                'shipping_address' => $address->toArray()
            ]);
            
            // Create order items
            foreach ($cart->items as $item) {
                $variant = $item->productVariant;
                $price = $variant->current_price;
                $itemTotal = $price * $item->quantity;
                
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'vendor_profile_id' => $variant->product->vendor->vendorProfile->id,
                    'product_name' => $variant->product->name,
                    'variant_sku' => $variant->sku,
                    'variant_attributes' => $variant->attributeValues->pluck('value')->toJson(),
                    'quantity' => $item->quantity,
                    'unit_price' => $price,
                    'discount_amount' => 0,
                    'total_price' => $itemTotal,
                    'status' => 'pending'
                ]);
                
                // Reduce stock
                $variant->decrement('stock_quantity', $item->quantity);
            }
            
            // Clear cart
            $cart->items()->delete();
            
            // Create Razorpay order if payment method is razorpay
            if ($request->payment_method === 'razorpay') {
                $razorpayOrder = $this->razorpay->order->create([
                    'amount' => $totalAmount * 100,
                    'currency' => 'INR',
                    'receipt' => $orderNumber,
                    'payment_capture' => 1
                ]);
                
                $order->update([
                    'razorpay_order_id' => $razorpayOrder->id
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'order' => $order->load('items'),
                'razorpay_order_id' => $order->razorpay_order_id ?? null
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create order', 'error' => $e->getMessage()], 500);
        }
    }

    public function verifyPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'razorpay_payment_id' => 'required',
            'razorpay_signature' => 'required'
        ]);
        
        $order = Order::find($request->order_id);
        
        // Verify signature
        $attributes = [
            'razorpay_order_id' => $order->razorpay_order_id,
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'razorpay_signature' => $request->razorpay_signature
        ];
        
        try {
            $this->razorpay->utility->verifyPaymentSignature($attributes);
            
            $order->update([
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
                'payment_status' => 'paid',
                'paid_amount' => $order->total_amount
            ]);
            
            // Create vendor earnings
            foreach ($order->items as $item) {
                $commissionRate = $item->vendorProfile->subscription->plan->commission_rate;
                $commissionAmount = $item->total_price * ($commissionRate / 100);
                $vendorAmount = $item->total_price - $commissionAmount;
                
                VendorEarning::create([
                    'vendor_profile_id' => $item->vendor_profile_id,
                    'order_id' => $order->id,
                    'order_amount' => $item->total_price,
                    'commission_amount' => $commissionAmount,
                    'vendor_amount' => $vendorAmount,
                    'status' => 'pending'
                ]);
            }
            
            return response()->json(['message' => 'Payment verified successfully']);
            
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid signature', 'error' => $e->getMessage()], 400);
        }
    }

    public function myOrders(Request $request)
    {
        $orders = Order::with(['items.productVariant.product', 'items.vendorProfile'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return response()->json($orders);
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);
        
        return response()->json($order->load(['items.productVariant.product', 'items.vendorProfile', 'statusLogs']));
    }

    public function cancel(Order $order)
    {
        $this->authorize('cancel', $order);
        
        if (!in_array($order->order_status, ['placed', 'confirmed'])) {
            return response()->json(['message' => 'Order cannot be cancelled at this stage'], 400);
        }
        
        DB::beginTransaction();
        
        try {
            $order->update(['order_status' => 'cancelled']);
            
            // Restore stock
            foreach ($order->items as $item) {
                $item->productVariant->increment('stock_quantity', $item->quantity);
                $item->update(['status' => 'cancelled']);
            }
            
            // Handle refund if payment was made
            if ($order->payment_status === 'paid') {
                // Initiate refund via Razorpay
                $refund = $this->razorpay->payment->fetch($order->razorpay_payment_id)->refund([
                    'amount' => $order->paid_amount * 100,
                    'notes' => ['order_id' => $order->order_number]
                ]);
                
                $order->update(['payment_status' => 'refunded']);
            }
            
            DB::commit();
            
            return response()->json(['message' => 'Order cancelled successfully']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to cancel order'], 500);
        }
    }
}