<?php

namespace App\Services;

use Razorpay\Api\Api;

class RazorpayService
{
    protected $api;
    
    public function __construct()
    {
        $this->api = new Api(
            config('services.razorpay.key'),
            config('services.razorpay.secret')
        );
    }
    
    /**
     * Create a new order
     */
    public function createOrder($data)
    {
        return $this->api->order->create($data);
    }
    
    /**
     * Verify payment signature
     */
    public function verifyPaymentSignature($attributes)
    {
        try {
            $this->api->utility->verifyPaymentSignature($attributes);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Fetch payment details
     */
    public function fetchPayment($paymentId)
    {
        return $this->api->payment->fetch($paymentId);
    }
    
    /**
     * Create refund
     */
    public function createRefund($paymentId, $amount = null, $notes = [])
    {
        $data = ['notes' => $notes];
        
        if ($amount) {
            $data['amount'] = $amount * 100; // Convert to paise
        }
        
        return $this->api->payment->fetch($paymentId)->refund($data);
    }
}