<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPlanResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => (float) $this->price,
            'formatted_price' => $this->getFormattedPrice(),
            'billing_period' => $this->billing_period,
            'billing_period_label' => $this->billing_period === 'monthly' ? 'Monthly' : 'Yearly',
            'max_products' => (int) $this->max_products,
            'max_products_label' => $this->max_products === -1 ? 'Unlimited' : (string) $this->max_products,
            'max_images_per_product' => (int) $this->max_images_per_product,
            'featured_listing' => (bool) $this->featured_listing,
            'priority_support' => (bool) $this->priority_support,
            'commission_rate' => (float) $this->commission_rate,
            'commission_rate_formatted' => $this->commission_rate . '%',
            'features' => $this->getFormattedFeatures(),
            'sort_order' => (int) $this->sort_order,
            'is_active' => (bool) $this->is_active,
            'subscriptions_count' => (int) ($this->subscriptions_count ?? $this->subscriptions()->count()),
            'active_subscriptions_count' => (int) ($this->active_subscriptions_count ?? $this->activeSubscriptions()->count()),
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toISOString() : null,
        ];
    }

    protected function getFormattedPrice()
    {
        if ($this->price == 0) {
            return 'Free';
        }
        
        return '₹' . number_format($this->price, 2) . '/' . ($this->billing_period === 'monthly' ? 'month' : 'year');
    }

    protected function getFormattedFeatures()
    {
        $features = [];
        
        // Add max products feature
        $features[] = [
            'icon' => 'shopping-bag',
            'title' => 'Products',
            'description' => $this->max_products === -1 
                ? 'Unlimited products' 
                : "Up to {$this->max_products} products",
        ];
        
        // Add images per product feature
        $features[] = [
            'icon' => 'image',
            'title' => 'Images per product',
            'description' => "Up to {$this->max_images_per_product} images",
        ];
        
        // Add commission rate feature
        $features[] = [
            'icon' => 'percent',
            'title' => 'Commission Rate',
            'description' => "{$this->commission_rate}% per sale",
        ];
        
        // Add featured listing feature
        if ($this->featured_listing) {
            $features[] = [
                'icon' => 'star',
                'title' => 'Featured Listing',
                'description' => 'Get priority in search results',
            ];
        }
        
        // Add priority support feature
        if ($this->priority_support) {
            $features[] = [
                'icon' => 'headphones',
                'title' => 'Priority Support',
                'description' => '24/7 dedicated support',
            ];
        }
        
        // Add custom features from JSON
        if ($this->features && is_array($this->features)) {
            foreach ($this->features as $feature) {
                $features[] = [
                    'icon' => $feature['icon'] ?? 'check',
                    'title' => $feature['title'] ?? 'Feature',
                    'description' => $feature['description'] ?? '',
                ];
            }
        }
        
        return $features;
    }
}