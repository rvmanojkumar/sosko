<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;

class SubscriptionPlanSeeder extends Seeder
{
    public function run()
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Perfect for getting started with basic features',
                'price' => 0,
                'billing_period' => 'monthly',
                'max_products' => 10,
                'max_images_per_product' => 5,
                'featured_listing' => false,
                'priority_support' => false,
                'commission_rate' => 15,
                'sort_order' => 1,
                'is_active' => true,
                'features' => [
                    ['icon' => 'shopping-bag', 'title' => '10 Products', 'description' => 'List up to 10 products'],
                    ['icon' => 'image', 'title' => '5 Images', 'description' => '5 images per product'],
                    ['icon' => 'percent', 'title' => '15% Commission', 'description' => 'Platform fee per sale'],
                    ['icon' => 'clock', 'title' => 'Basic Support', 'description' => 'Email support within 48 hours'],
                ],
            ],
            [
                'name' => 'Silver',
                'slug' => 'silver',
                'description' => 'For growing businesses with more products',
                'price' => 999,
                'billing_period' => 'monthly',
                'max_products' => 50,
                'max_images_per_product' => 10,
                'featured_listing' => false,
                'priority_support' => false,
                'commission_rate' => 12,
                'sort_order' => 2,
                'is_active' => true,
                'features' => [
                    ['icon' => 'shopping-bag', 'title' => '50 Products', 'description' => 'List up to 50 products'],
                    ['icon' => 'image', 'title' => '10 Images', 'description' => '10 images per product'],
                    ['icon' => 'percent', 'title' => '12% Commission', 'description' => 'Lower platform fee'],
                    ['icon' => 'trending-up', 'title' => 'Analytics', 'description' => 'Basic sales analytics'],
                    ['icon' => 'clock', 'title' => 'Priority Email Support', 'description' => 'Response within 24 hours'],
                ],
            ],
            [
                'name' => 'Gold',
                'slug' => 'gold',
                'description' => 'For established businesses with premium features',
                'price' => 1999,
                'billing_period' => 'monthly',
                'max_products' => 200,
                'max_images_per_product' => 15,
                'featured_listing' => true,
                'priority_support' => false,
                'commission_rate' => 10,
                'sort_order' => 3,
                'is_active' => true,
                'features' => [
                    ['icon' => 'shopping-bag', 'title' => '200 Products', 'description' => 'List up to 200 products'],
                    ['icon' => 'image', 'title' => '15 Images', 'description' => '15 images per product'],
                    ['icon' => 'percent', 'title' => '10% Commission', 'description' => 'Lower platform fee'],
                    ['icon' => 'star', 'title' => 'Featured Listing', 'description' => 'Priority in search results'],
                    ['icon' => 'trending-up', 'title' => 'Advanced Analytics', 'description' => 'Detailed sales reports'],
                    ['icon' => 'headset', 'title' => 'Priority Support', 'description' => 'Priority email & chat support'],
                ],
            ],
            [
                'name' => 'Platinum',
                'slug' => 'platinum',
                'description' => 'Premium plan for top vendors with maximum benefits',
                'price' => 4999,
                'billing_period' => 'monthly',
                'max_products' => -1,
                'max_images_per_product' => 20,
                'featured_listing' => true,
                'priority_support' => true,
                'commission_rate' => 8,
                'sort_order' => 4,
                'is_active' => true,
                'features' => [
                    ['icon' => 'infinite', 'title' => 'Unlimited Products', 'description' => 'No product limits'],
                    ['icon' => 'image', 'title' => '20 Images', 'description' => '20 images per product'],
                    ['icon' => 'percent', 'title' => '8% Commission', 'description' => 'Lowest platform fee'],
                    ['icon' => 'star', 'title' => 'Featured Listing', 'description' => 'Priority in search results'],
                    ['icon' => 'trending-up', 'title' => 'Premium Analytics', 'description' => 'Advanced insights & forecasts'],
                    ['icon' => 'headset', 'title' => '24/7 Priority Support', 'description' => 'Dedicated account manager'],
                    ['icon' => 'megaphone', 'title' => 'Marketing Boost', 'description' => 'Promotional campaigns included'],
                ],
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}