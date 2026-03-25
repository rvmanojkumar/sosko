<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use App\Models\Category;
use App\Models\User;
use App\Models\AttributeValue;
use App\Models\VendorProfile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run()
    {
        // Get vendor user
        $vendor = User::where('email', 'vendor@example.com')->first();
        
        if (!$vendor) {
            $this->command->error('Vendor user not found! Please run VendorSeeder first.');
            return;
        }
        
        // Get vendor profile
        $vendorProfile = VendorProfile::where('user_id', $vendor->id)->first();
        
        if (!$vendorProfile) {
            $this->command->error('Vendor profile not found! Please run VendorSeeder first.');
            return;
        }
        
        // Get categories
        $electronicsCategory = Category::where('name', 'Electronics')->first();
        $smartphonesCategory = Category::where('name', 'Smartphones')->first();
        $laptopsCategory = Category::where('name', 'Laptops')->first();
        $audioCategory = Category::where('name', 'Audio')->first();
        
        // Get attribute values for products
        $ram8gb = AttributeValue::where('value', '8GB')->first();
        $ram16gb = AttributeValue::where('value', '16GB')->first();
        $storage128gb = AttributeValue::where('value', '128GB')->first();
        $storage256gb = AttributeValue::where('value', '256GB')->first();
        $storage512gb = AttributeValue::where('value', '512GB')->first();
        $colorBlack = AttributeValue::where('value', 'Black')->first();
        $colorWhite = AttributeValue::where('value', 'White')->first();
        $colorRed = AttributeValue::where('value', 'Red')->first();
        $colorBlue = AttributeValue::where('value', 'Blue')->first();
        
        // Product 1: iPhone 14 Pro
        $iphoneId = Str::uuid();
        Product::create([
            'id' => $iphoneId,
            'vendor_id' => $vendor->id,
            'category_id' => $smartphonesCategory ? $smartphonesCategory->id : null,
            'name' => 'iPhone 14 Pro',
            'slug' => 'iphone-14-pro',
            'description' => 'The latest iPhone with A16 Bionic chip, 48MP camera, and Dynamic Island. Experience the future of smartphones with iOS 16.',
            'short_description' => 'iPhone 14 Pro with A16 Bionic chip and 48MP camera',
            'brand' => 'Apple',
            'weight' => 0.206,
            'specifications' => [
                'display' => '6.1-inch Super Retina XDR',
                'processor' => 'A16 Bionic',
                'camera' => '48MP Main + 12MP Ultra Wide + 12MP Telephoto',
                'battery' => 'Up to 23 hours video playback',
                'water_resistant' => 'IP68',
                'connectivity' => '5G, Wi-Fi 6, Bluetooth 5.3',
            ],
            'is_featured' => true,
            'is_active' => true,
            'view_count' => 0,
            'sold_count' => 0,
        ]);

        // Create variants for iPhone
        $variants = [
            [
                'sku' => 'IP14P-BLK-128',
                'price' => 129900,
                'sale_price' => 119900,
                'discount_percent' => 7.7,
                'stock_quantity' => 50,
                'is_default' => true,
                'attributes' => [$ram8gb, $storage128gb, $colorBlack],
            ],
            [
                'sku' => 'IP14P-BLK-256',
                'price' => 139900,
                'sale_price' => 129900,
                'discount_percent' => 7.1,
                'stock_quantity' => 30,
                'is_default' => false,
                'attributes' => [$ram8gb, $storage256gb, $colorBlack],
            ],
            [
                'sku' => 'IP14P-WHT-128',
                'price' => 129900,
                'sale_price' => 119900,
                'discount_percent' => 7.7,
                'stock_quantity' => 45,
                'is_default' => false,
                'attributes' => [$ram8gb, $storage128gb, $colorWhite],
            ],
            [
                'sku' => 'IP14P-WHT-256',
                'price' => 139900,
                'sale_price' => 129900,
                'discount_percent' => 7.1,
                'stock_quantity' => 25,
                'is_default' => false,
                'attributes' => [$ram8gb, $storage256gb, $colorWhite],
            ],
        ];

        foreach ($variants as $variantData) {
            $attributes = $variantData['attributes'];
            unset($variantData['attributes']);
            
            $variantId = Str::uuid();
            
            ProductVariant::create(array_merge($variantData, [
                'id' => $variantId,
                'product_id' => $iphoneId,
                'weight' => 0.206,
                'low_stock_threshold' => 10,
            ]));
            
            // Attach attribute values using DB::table to avoid UUID issues
            if (!empty($attributes)) {
                foreach ($attributes as $attributeValue) {
                    if ($attributeValue) {
                        DB::table('product_variant_attribute_values')->insert([
                            'id' => Str::uuid(),
                            'product_variant_id' => $variantId,
                            'attribute_value_id' => $attributeValue->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        // Product 2: MacBook Pro 14"
        $macbookId = Str::uuid();
        Product::create([
            'id' => $macbookId,
            'vendor_id' => $vendor->id,
            'category_id' => $laptopsCategory ? $laptopsCategory->id : null,
            'name' => 'MacBook Pro 14"',
            'slug' => 'macbook-pro-14',
            'description' => 'Apple M2 Pro chip with 12‑core CPU, 19‑core GPU, 16GB RAM, 512GB SSD. The most powerful MacBook Pro ever.',
            'short_description' => 'MacBook Pro with M2 Pro chip',
            'brand' => 'Apple',
            'weight' => 1.6,
            'specifications' => [
                'display' => '14.2-inch Liquid Retina XDR',
                'processor' => 'M2 Pro',
                'memory' => '16GB',
                'storage' => '512GB SSD',
                'battery' => 'Up to 18 hours',
                'ports' => 'HDMI, SDXC, MagSafe 3, Thunderbolt 4',
            ],
            'is_featured' => true,
            'is_active' => true,
            'view_count' => 0,
            'sold_count' => 0,
        ]);

        $macVariantId = Str::uuid();
        ProductVariant::create([
            'id' => $macVariantId,
            'product_id' => $macbookId,
            'sku' => 'MBP14-M2-16-512',
            'price' => 199900,
            'sale_price' => 189900,
            'discount_percent' => 5,
            'stock_quantity' => 25,
            'is_default' => true,
            'weight' => 1.6,
            'low_stock_threshold' => 5,
        ]);

        // Attach attributes for MacBook
        if ($ram16gb) {
            DB::table('product_variant_attribute_values')->insert([
                'id' => Str::uuid(),
                'product_variant_id' => $macVariantId,
                'attribute_value_id' => $ram16gb->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        if ($storage512gb) {
            DB::table('product_variant_attribute_values')->insert([
                'id' => Str::uuid(),
                'product_variant_id' => $macVariantId,
                'attribute_value_id' => $storage512gb->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Product 3: Sony WH-1000XM5 Headphones
        $sonyId = Str::uuid();
        Product::create([
            'id' => $sonyId,
            'vendor_id' => $vendor->id,
            'category_id' => $audioCategory ? $audioCategory->id : null,
            'name' => 'Sony WH-1000XM5',
            'slug' => 'sony-wh-1000xm5',
            'description' => 'Industry-leading noise cancellation, exceptional sound quality, and all-day comfort. The ultimate wireless headphones.',
            'short_description' => 'Sony WH-1000XM5 Wireless Noise Cancelling Headphones',
            'brand' => 'Sony',
            'weight' => 0.25,
            'specifications' => [
                'noise_cancellation' => 'Industry-leading',
                'battery_life' => 'Up to 30 hours',
                'charging' => 'Fast charging: 3 hours playtime in 3 minutes',
                'connectivity' => 'Bluetooth 5.2',
                'drivers' => '30mm',
            ],
            'is_featured' => true,
            'is_active' => true,
            'view_count' => 0,
            'sold_count' => 0,
        ]);

        ProductVariant::create([
            'id' => Str::uuid(),
            'product_id' => $sonyId,
            'sku' => 'SONY-WH1000XM5-BLK',
            'price' => 29990,
            'sale_price' => 27990,
            'discount_percent' => 6.7,
            'stock_quantity' => 100,
            'is_default' => true,
            'weight' => 0.25,
            'low_stock_threshold' => 20,
        ]);

        // Product 4: Samsung Galaxy S23 Ultra
        $samsungId = Str::uuid();
        Product::create([
            'id' => $samsungId,
            'vendor_id' => $vendor->id,
            'category_id' => $smartphonesCategory ? $smartphonesCategory->id : null,
            'name' => 'Samsung Galaxy S23 Ultra',
            'slug' => 'samsung-galaxy-s23-ultra',
            'description' => '200MP camera, built-in S Pen, Snapdragon 8 Gen 2, and a massive 6.8-inch display.',
            'short_description' => 'Samsung Galaxy S23 Ultra with 200MP camera',
            'brand' => 'Samsung',
            'weight' => 0.234,
            'specifications' => [
                'display' => '6.8-inch Dynamic AMOLED',
                'processor' => 'Snapdragon 8 Gen 2',
                'camera' => '200MP Main + 12MP Ultra Wide + 10MP Telephoto',
                'battery' => '5000mAh',
                's_pen' => 'Built-in',
            ],
            'is_featured' => true,
            'is_active' => true,
            'view_count' => 0,
            'sold_count' => 0,
        ]);

        $samsungVariants = [
            [
                'sku' => 'S23U-BLK-256',
                'price' => 124999,
                'sale_price' => 114999,
                'discount_percent' => 8,
                'stock_quantity' => 40,
                'is_default' => true,
                'attributes' => [$storage256gb, $colorBlack],
            ],
            [
                'sku' => 'S23U-BLK-512',
                'price' => 134999,
                'sale_price' => 124999,
                'discount_percent' => 7.4,
                'stock_quantity' => 25,
                'is_default' => false,
                'attributes' => [$storage512gb, $colorBlack],
            ],
        ];

        foreach ($samsungVariants as $variantData) {
            $attributes = $variantData['attributes'];
            unset($variantData['attributes']);
            
            $variantId = Str::uuid();
            
            ProductVariant::create(array_merge($variantData, [
                'id' => $variantId,
                'product_id' => $samsungId,
                'weight' => 0.234,
                'low_stock_threshold' => 10,
            ]));
            
            // Attach attribute values
            if (!empty($attributes)) {
                foreach ($attributes as $attributeValue) {
                    if ($attributeValue) {
                        DB::table('product_variant_attribute_values')->insert([
                            'id' => Str::uuid(),
                            'product_variant_id' => $variantId,
                            'attribute_value_id' => $attributeValue->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        $this->command->info('Product seeder completed successfully!');
        $this->command->info('Created ' . Product::count() . ' products');
        $this->command->info('Created ' . ProductVariant::count() . ' product variants');
    }
}