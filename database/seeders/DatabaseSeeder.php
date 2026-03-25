<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
             RolePermissionSeeder::class,      // First: Create roles and permissions
            SubscriptionPlanSeeder::class,    // Second: Create subscription plans
            AttributeSeeder::class,           // Third: Create attributes
            CategorySeeder::class,            // Fourth: Create categories
            SupplierSeeder::class,            // Fifth: Create suppliers
            VendorSeeder::class,              // Sixth: Create vendors (depends on subscription plans)
            ProductSeeder::class,             // Seventh: Create products (depends on vendors and categories)
            PromoCodeSeeder::class,           // Eighth: Create promo codes
            BannerSeeder::class,              // Ninth: Create banners
            OrderSeeder::class,               // Tenth: Create orders (depends on products and customers)
        ]);
    }
}