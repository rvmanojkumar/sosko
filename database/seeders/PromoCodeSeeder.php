<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PromoCode;
use App\Models\User;
use Carbon\Carbon;

class PromoCodeSeeder extends Seeder
{
    public function run()
    {
        // Get customer user for user-specific promo codes
        $customer = User::where('email', 'customer@example.com')->first();
        
        $promoCodes = [
            [
                'code' => 'WELCOME10',
                'name' => 'Welcome Discount',
                'description' => 'Get 10% off on your first order',
                'type' => 'percentage',
                'value' => 10,
                'min_order_value' => 500,
                'max_discount_amount' => 500,
                'usage_type' => 'multi',
                'usage_limit' => 1000,
                'per_user_limit' => 1,
                'is_first_order_only' => true,
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addMonths(6),
                'is_active' => true,
                'stackable' => false,
            ],
            [
                'code' => 'FLAT200',
                'name' => 'Flat ₹200 Off',
                'description' => 'Get ₹200 off on orders above ₹1000',
                'type' => 'flat',
                'value' => 200,
                'min_order_value' => 1000,
                'max_discount_amount' => 200,
                'usage_type' => 'multi',
                'usage_limit' => 500,
                'per_user_limit' => 2,
                'is_first_order_only' => false,
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addMonths(3),
                'is_active' => true,
                'stackable' => false,
            ],
            [
                'code' => 'SUMMER50',
                'name' => 'Summer Sale',
                'description' => '50% off on summer collection',
                'type' => 'percentage',
                'value' => 50,
                'min_order_value' => 2000,
                'max_discount_amount' => 1000,
                'usage_type' => 'multi',
                'usage_limit' => 200,
                'per_user_limit' => 1,
                'is_first_order_only' => false,
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addMonths(2),
                'is_active' => true,
                'stackable' => false,
            ],
            [
                'code' => 'FREESHIP',
                'name' => 'Free Shipping',
                'description' => 'Free shipping on orders above ₹500',
                'type' => 'flat',
                'value' => 40,
                'min_order_value' => 500,
                'usage_type' => 'multi',
                'usage_limit' => 1000,
                'per_user_limit' => 5,
                'is_first_order_only' => false,
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addMonths(6),
                'is_active' => true,
                'stackable' => true,
            ],
            [
                'code' => 'DIWALI30',
                'name' => 'Diwali Special',
                'description' => '30% off on all orders',
                'type' => 'percentage',
                'value' => 30,
                'min_order_value' => 1500,
                'max_discount_amount' => 1500,
                'usage_type' => 'multi',
                'usage_limit' => 1000,
                'per_user_limit' => 2,
                'is_first_order_only' => false,
                'start_date' => Carbon::now()->addMonths(1),
                'end_date' => Carbon::now()->addMonths(2),
                'is_active' => true,
                'stackable' => false,
            ],
        ];
        
        // Add user-specific promo code for the customer
        if ($customer) {
            $promoCodes[] = [
                'code' => 'VIP' . strtoupper(substr($customer->name, 0, 3)) . '100',
                'name' => 'VIP Exclusive Offer',
                'description' => 'Exclusive ₹100 off for VIP members',
                'type' => 'flat',
                'value' => 100,
                'min_order_value' => 500,
                'usage_type' => 'single',
                'usage_limit' => 1,
                'user_id' => $customer->id,
                'is_first_order_only' => false,
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addMonths(3),
                'is_active' => true,
                'stackable' => false,
            ];
        }
        
        foreach ($promoCodes as $promoCode) {
            PromoCode::updateOrCreate(
                ['code' => $promoCode['code']],
                $promoCode
            );
        }
        
        // Create some expired promo codes for testing
        $expiredPromoCodes = [
            [
                'code' => 'EXPIRED20',
                'name' => 'Expired Offer',
                'description' => 'This offer has expired',
                'type' => 'percentage',
                'value' => 20,
                'min_order_value' => 1000,
                'usage_type' => 'multi',
                'usage_limit' => 100,
                'start_date' => Carbon::now()->subMonths(2),
                'end_date' => Carbon::now()->subMonth(),
                'is_active' => true,
                'stackable' => false,
            ],
            [
                'code' => 'BLACKFRIDAY',
                'name' => 'Black Friday',
                'description' => 'Black Friday Special',
                'type' => 'percentage',
                'value' => 40,
                'min_order_value' => 2000,
                'max_discount_amount' => 2000,
                'usage_type' => 'multi',
                'usage_limit' => 500,
                'start_date' => Carbon::now()->subMonths(3),
                'end_date' => Carbon::now()->subMonths(2),
                'is_active' => false,
                'stackable' => false,
            ],
        ];
        
        foreach ($expiredPromoCodes as $promoCode) {
            PromoCode::updateOrCreate(
                ['code' => $promoCode['code']],
                $promoCode
            );
        }
    }
}