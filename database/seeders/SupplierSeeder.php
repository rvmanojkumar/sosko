<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    public function run()
    {
        $suppliers = [
            [
                'name' => 'Tech Distributors Inc.',
                'contact_person' => 'John Smith',
                'email' => 'john@techdist.com',
                'phone' => '9876543210',
                'address' => '123 Tech Park, Bangalore',
                'gst_number' => 'GST123456789',
                'is_active' => true,
            ],
            [
                'name' => 'Fashion Hub Ltd.',
                'contact_person' => 'Jane Doe',
                'email' => 'jane@fashionhub.com',
                'phone' => '9876543211',
                'address' => '456 Fashion Street, Mumbai',
                'gst_number' => 'GST987654321',
                'is_active' => true,
            ],
            [
                'name' => 'Home Essentials Co.',
                'contact_person' => 'Mike Johnson',
                'email' => 'mike@homeessentials.com',
                'phone' => '9876543212',
                'address' => '789 Home Lane, Delhi',
                'gst_number' => 'GST456789123',
                'is_active' => true,
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}