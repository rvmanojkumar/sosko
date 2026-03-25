<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'Electronics',
                'description' => 'Electronic devices and accessories',
                'children' => [
                    ['name' => 'Smartphones', 'description' => 'Mobile phones and accessories'],
                    ['name' => 'Laptops', 'description' => 'Laptops and computers'],
                    ['name' => 'Tablets', 'description' => 'Tablets and e-readers'],
                    ['name' => 'Audio', 'description' => 'Headphones, speakers, and audio equipment'],
                    ['name' => 'Cameras', 'description' => 'Digital cameras and photography equipment'],
                ]
            ],
            [
                'name' => 'Fashion',
                'description' => 'Clothing, shoes, and accessories',
                'children' => [
                    ['name' => "Men's Clothing", 'description' => 'Shirts, pants, suits for men'],
                    ['name' => "Women's Clothing", 'description' => 'Dresses, tops, skirts for women'],
                    ['name' => "Kids' Clothing", 'description' => 'Clothing for children'],
                    ['name' => 'Footwear', 'description' => 'Shoes, sandals, sneakers'],
                    ['name' => 'Accessories', 'description' => 'Bags, jewelry, watches'],
                ]
            ],
            [
                'name' => 'Home & Living',
                'description' => 'Furniture, decor, and home essentials',
                'children' => [
                    ['name' => 'Furniture', 'description' => 'Sofas, tables, beds'],
                    ['name' => 'Home Decor', 'description' => 'Decorative items, lighting'],
                    ['name' => 'Kitchen', 'description' => 'Cookware, appliances, utensils'],
                    ['name' => 'Bedding', 'description' => 'Sheets, comforters, pillows'],
                    ['name' => 'Bath', 'description' => 'Towels, bath accessories'],
                ]
            ],
        ];

        foreach ($categories as $categoryData) {
            $parent = Category::create([
                'name' => $categoryData['name'],
                'slug' => Str::slug($categoryData['name']),
                'description' => $categoryData['description'],
                'is_active' => true,
            ]);

            if (isset($categoryData['children'])) {
                foreach ($categoryData['children'] as $childData) {
                    Category::create([
                        'name' => $childData['name'],
                        'slug' => Str::slug($childData['name']),
                        'description' => $childData['description'],
                        'parent_id' => $parent->id,
                        'is_active' => true,
                    ]);
                }
            }
        }
    }
}