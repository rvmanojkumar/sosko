<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\ProductVariant;
use App\Models\VendorProfile;
use Faker\Factory as Faker;

class OrderSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $customers = User::where('email', 'customer@example.com')->get();
        $variants = ProductVariant::with('product')->take(5)->get();
        $vendorProfile = VendorProfile::first();
        
        if ($customers->isEmpty() || $variants->isEmpty()) {
            return;
        }

        foreach ($customers as $customer) {
            for ($i = 0; $i < 3; $i++) {
                $order = Order::create([
                    'order_number' => 'ORD-' . strtoupper(uniqid()),
                    'user_id' => $customer->id,
                    'subtotal' => 0,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'shipping_amount' => 40,
                    'total_amount' => 0,
                    'payment_method' => $faker->randomElement(['razorpay', 'cod']),
                    'payment_status' => $faker->randomElement(['pending', 'paid']),
                    'order_status' => $faker->randomElement(['placed', 'confirmed', 'processing', 'shipped', 'delivered']),
                    'billing_address' => [
                        'address_line1' => $faker->streetAddress,
                        'city' => $faker->city,
                        'state' => $faker->state,
                        'country' => 'India',
                        'postal_code' => $faker->postcode,
                    ],
                    'shipping_address' => [
                        'address_line1' => $faker->streetAddress,
                        'city' => $faker->city,
                        'state' => $faker->state,
                        'country' => 'India',
                        'postal_code' => $faker->postcode,
                    ],
                    'created_at' => $faker->dateTimeBetween('-3 months', 'now'),
                ]);
                
                $subtotal = 0;
                $itemCount = rand(1, 2);
                
                for ($j = 0; $j < $itemCount; $j++) {
                    $variant = $variants->random();
                    $quantity = rand(1, 2);
                    $unitPrice = $variant->price;
                    $totalPrice = $unitPrice * $quantity;
                    $subtotal += $totalPrice;
                    
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_variant_id' => $variant->id,
                        'vendor_profile_id' => $vendorProfile ? $vendorProfile->id : null,
                        'product_name' => $variant->product->name,
                        'variant_sku' => $variant->sku,
                        'variant_attributes' => $variant->attributeValues->pluck('value', 'attribute.name')->toArray(),
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                        'status' => $order->order_status,
                    ]);
                }
                
                $tax = $subtotal * 0.18;
                $total = $subtotal + $tax + 40;
                
                $order->update([
                    'subtotal' => $subtotal,
                    'tax_amount' => $tax,
                    'total_amount' => $total,
                    'paid_amount' => $order->payment_status === 'paid' ? $total : 0,
                ]);
            }
        }
    }
}