<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\VendorProfile;
use App\Models\VendorDocument;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class VendorSeeder extends Seeder
{
    public function run()
    {
        // Get the free subscription plan
        $freePlan = SubscriptionPlan::where('slug', 'free')->first();
        
        $vendors = [
            [
                'name' => 'Tech Haven',
                'email' => 'tech@vendor.com',
                'phone' => '9876543215',
                'store_name' => 'Tech Haven',
                'store_slug' => 'tech-haven',
                'description' => 'Latest gadgets and electronics at best prices',
                'address' => '123 Electronics City, Bangalore, Karnataka - 560100',
                'contact_email' => 'contact@techhaven.com',
                'contact_phone' => '9876543215',
                'status' => 'approved',
                'rating' => 4.5,
            ],
            [
                'name' => 'Fashion Fusion',
                'email' => 'fashion@vendor.com',
                'phone' => '9876543216',
                'store_name' => 'Fashion Fusion',
                'store_slug' => 'fashion-fusion',
                'description' => 'Trendy clothing and accessories for all',
                'address' => '456 Fashion Street, Mumbai, Maharashtra - 400001',
                'contact_email' => 'support@fashionfusion.com',
                'contact_phone' => '9876543216',
                'status' => 'approved',
                'rating' => 4.2,
            ],
            [
                'name' => 'Home Comforts',
                'email' => 'home@vendor.com',
                'phone' => '9876543217',
                'store_name' => 'Home Comforts',
                'store_slug' => 'home-comforts',
                'description' => 'Premium home decor and furniture',
                'address' => '789 Home Lane, Delhi - 110001',
                'contact_email' => 'hello@homecomforts.com',
                'contact_phone' => '9876543217',
                'status' => 'pending',
                'rating' => 0,
            ],
            [
                'name' => 'Sports Zone',
                'email' => 'sports@vendor.com',
                'phone' => '9876543218',
                'store_name' => 'Sports Zone',
                'store_slug' => 'sports-zone',
                'description' => 'All sports equipment and accessories',
                'address' => '321 Sports Complex, Chennai, Tamil Nadu - 600001',
                'contact_email' => 'info@sportszone.com',
                'contact_phone' => '9876543218',
                'status' => 'approved',
                'rating' => 4.7,
            ],
        ];

        foreach ($vendors as $vendorData) {
            // Create user
            $user = User::firstOrCreate(
                ['email' => $vendorData['email']],
                [
                    'id' => Str::uuid(),
                    'name' => $vendorData['name'],
                    'phone' => $vendorData['phone'],
                    'password' => Hash::make('password'),
                    'phone_verified_at' => now(),
                ]
            );
            
            // Assign vendor role
            if (!$user->hasRole('vendor')) {
                $user->assignRole('vendor');
            }
            
            // Create vendor profile
            $vendor = VendorProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'store_name' => $vendorData['store_name'],
                    'store_slug' => $vendorData['store_slug'],
                    'description' => $vendorData['description'],
                    'address' => $vendorData['address'],
                    'contact_email' => $vendorData['contact_email'],
                    'contact_phone' => $vendorData['contact_phone'],
                    'status' => $vendorData['status'],
                    'rating' => $vendorData['rating'],
                    'follower_count' => rand(0, 500),
                ]
            );
            
            // Add subscription if vendor is approved
            if ($vendor->status === 'approved' && $freePlan) {
                // Check if subscription already exists
                if (!$vendor->subscriptions()->exists()) {
                    $vendor->subscriptions()->create([
                        'subscription_plan_id' => $freePlan->id,
                        'start_date' => now(),
                        'end_date' => now()->addDays(30),
                        'status' => 'active',
                    ]);
                }
            }
            
            // Add some documents for approved vendors
            if ($vendor->status === 'approved') {
                $documents = [
                    [
                        'document_type' => 'pan',
                        'document_number' => 'ABCDE1234F',
                        'status' => 'verified',
                    ],
                    [
                        'document_type' => 'gst',
                        'document_number' => '27ABCDE1234F1Z5',
                        'status' => 'verified',
                    ],
                ];
                
                foreach ($documents as $doc) {
                    VendorDocument::firstOrCreate(
                        [
                            'vendor_profile_id' => $vendor->id,
                            'document_type' => $doc['document_type'],
                        ],
                        [
                            'document_number' => $doc['document_number'],
                            'document_path' => 'documents/' . $doc['document_type'] . '.pdf',
                            'document_url' => 'https://example.com/documents/' . $doc['document_type'] . '.pdf',
                            'status' => $doc['status'],
                        ]
                    );
                }
            }
        }
    }
}