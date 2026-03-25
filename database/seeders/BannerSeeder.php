<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Banner;

class BannerSeeder extends Seeder
{
    public function run()
    {
        $banners = [
            [
                'title' => 'Summer Sale',
                'subtitle' => 'Get up to 50% off on summer collection',
                'cta_text' => 'Shop Now',
                'cta_link' => '/products?collection=summer',
                'type' => 'hero_slider',
                'sort_order' => 1,
                'is_active' => true,
                'start_date' => now(),
                'end_date' => now()->addMonths(1),
            ],
            [
                'title' => 'New Arrivals',
                'subtitle' => 'Check out our latest collection',
                'cta_text' => 'Explore',
                'cta_link' => '/products?sort=newest',
                'type' => 'hero_slider',
                'sort_order' => 2,
                'is_active' => true,
                'start_date' => now(),
                'end_date' => now()->addMonths(1),
            ],
            [
                'title' => 'Electronics Sale',
                'subtitle' => 'Best deals on electronics',
                'cta_text' => 'Shop Electronics',
                'cta_link' => '/categories/electronics',
                'type' => 'category_banner',
                'target_type' => 'category',
                'sort_order' => 1,
                'is_active' => true,
                'start_date' => now(),
                'end_date' => now()->addMonths(1),
            ],
        ];

        foreach ($banners as $banner) {
            Banner::create($banner);
        }
    }
}