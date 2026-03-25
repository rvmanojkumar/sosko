<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\VendorEarning;
use App\Models\WalletTransaction;
use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\VendorSubscription;

class RazorpayWebhookController extends Controller
{
    protected $razorpayService;

    public function __construct(RazorpayService $razorpayService)
    {
        $this->razorpayService = $razorpayService;
    }

    public function handle(Request $request)
    {
        // Verify webhook signature
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');
        
        $isValid = $this->razorpayService->verifyWebhookSignature(
            $payload, 
            $signature, 
            config('services.razorpay.webhook_secret')
        );
        
        if (!$isValid) {
            Log::warning('Invalid webhook signature');
            return response()->json(['message' => 'Invalid signature'], 401);
        }
        
        $event = $request->input('event');
        $payload = $request->input('payload');
        
        switch ($event) {
            case 'payment.captured':
                $this->handlePaymentCaptured($payload);
                break;
            case 'payment.failed':
                $this->handlePaymentFailed($payload);
                break;
            case 'refund.created':
                $this->handleRefundCreated($payload);
                break;
            case 'refund.processed':
                $this->handleRefundProcessed($payload);
                break;
            case 'subscription.activated':
                $this->handleSubscriptionActivated($payload);
                break;
            case 'subscription.charged':
                $this->handleSubscriptionCharged($payload);
                break;
            case 'subscription.cancelled':
                $this->handleSubscriptionCancelled($payload);
                break;
            default:
                Log::info('Unhandled webhook event: ' . $event);
        }
        
        return response()->json(['message' => 'Webhook received']);
    }

    protected function handlePaymentCaptured($payload)
    {
        $paymentId = $payload['payment']['entity']['id'];
        $orderId = $payload['payment']['entity']['order_id'];
        
        $order = Order::where('razorpay_order_id', $orderId)->first();
        
        if (!$order) {
            Log::warning('Order not found for webhook', ['order_id' => $orderId]);
            return;
        }
        
        DB::beginTransaction();
        
        try {
            $order->update([
                'razorpay_payment_id' => $paymentId,
                'payment_status' => 'paid',
                'paid_amount' => $order->total_amount,
                'order_status' => 'confirmed',
            ]);
            
            // Create vendor earnings
            foreach ($order->items as $item) {
                $commissionRate = $item->vendorProfile->subscription->plan->commission_rate ?? 10;
                $commissionAmount = $item->total_price * ($commissionRate / 100);
                $vendorAmount = $item->total_price - $commissionAmount;
                
                VendorEarning::create([
                    'vendor_profile_id' => $item->vendor_profile_id,
                    'order_id' => $order->id,
                    'order_amount' => $item->total_price,
                    'commission_amount' => $commissionAmount,
                    'vendor_amount' => $vendorAmount,
                    'status' => 'pending',
                ]);
            }
            
            DB::commit();
            
            Log::info('Payment captured for order', ['order_id' => $order->order_number]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process captured payment', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function handlePaymentFailed($payload)
    {
        $orderId = $payload['payment']['entity']['order_id'];
        
        $order = Order::where('razorpay_order_id', $orderId)->first();
        
        if ($order) {
            $order->update([
                'payment_status' => 'failed',
                'order_status' => 'cancelled',
            ]);
            
            // Restore stock
            foreach ($order->items as $item) {
                $item->productVariant->increment('stock_quantity', $item->quantity);
            }
            
            Log::info('Payment failed for order', ['order_id' => $order->order_number]);
        }
    }

    protected function handleRefundCreated($payload)
    {
        $paymentId = $payload['refund']['entity']['payment_id'];
        $refundAmount = $payload['refund']['entity']['amount'] / 100;
        
        $order = Order::where('razorpay_payment_id', $paymentId)->first();
        
        if ($order) {
            $order->update([
                'payment_status' => 'refunded',
                'order_status' => 'cancelled',
            ]);
            
            Log::info('Refund created for order', [
                'order_id' => $order->order_number,
                'amount' => $refundAmount,
            ]);
        }
    }

    protected function handleRefundProcessed($payload)
    {
        $paymentId = $payload['refund']['entity']['payment_id'];
        
        $order = Order::where('razorpay_payment_id', $paymentId)->first();
        
        if ($order) {
            Log::info('Refund processed for order', ['order_id' => $order->order_number]);
        }
    }

    protected function handleSubscriptionActivated($payload)
    {
        $subscriptionId = $payload['subscription']['entity']['id'];
        
        // Update vendor subscription
        $vendorSubscription = VendorSubscription::where('razorpay_subscription_id', $subscriptionId)->first();
        
        if ($vendorSubscription) {
            $vendorSubscription->update([
                'status' => 'active',
                'start_date' => now(),
            ]);
            
            Log::info('Subscription activated', ['subscription_id' => $subscriptionId]);
        }
    }

    protected function handleSubscriptionCharged($payload)
    {
        $subscriptionId = $payload['subscription']['entity']['id'];
        $paymentId = $payload['payment']['entity']['id'];
        
        // Record payment for subscription renewal
        Log::info('Subscription charged', [
            'subscription_id' => $subscriptionId,
            'payment_id' => $paymentId,
        ]);
    }

    protected function handleSubscriptionCancelled($payload)
    {
        $subscriptionId = $payload['subscription']['entity']['id'];
        
        $vendorSubscription = VendorSubscription::where('razorpay_subscription_id', $subscriptionId)->first();
        
        if ($vendorSubscription) {
            $vendorSubscription->update(['status' => 'cancelled']);
            Log::info('Subscription cancelled', ['subscription_id' => $subscriptionId]);
        }
    }
}