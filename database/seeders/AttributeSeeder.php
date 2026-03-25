<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\AttributeGroup;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AttributeSeeder extends Seeder
{
    public function run()
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Clear existing data
        AttributeGroup::truncate();
        Attribute::truncate();
        AttributeValue::truncate();
        DB::table('attribute_group_mappings')->truncate();
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        // Create attribute groups with explicit UUIDs
        $sizeGroupId = Str::uuid();
        $colorGroupId = Str::uuid();
        $electronicsGroupId = Str::uuid();
        
        AttributeGroup::create([
            'id' => $sizeGroupId,
            'name' => 'Size & Fit',
            'slug' => 'size-fit',
            'description' => 'Size and fitting options',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        AttributeGroup::create([
            'id' => $colorGroupId,
            'name' => 'Color & Style',
            'slug' => 'color-style',
            'description' => 'Color and style options',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        AttributeGroup::create([
            'id' => $electronicsGroupId,
            'name' => 'Technical Specifications',
            'slug' => 'technical-specs',
            'description' => 'Technical specifications',
            'sort_order' => 3,
            'is_active' => true,
        ]);

        // Create Size attribute
        $sizeAttributeId = Str::uuid();
        Attribute::create([
            'id' => $sizeAttributeId,
            'name' => 'Size',
            'slug' => 'size',
            'type' => 'select',
            'display_type' => 'button',
            'is_required' => true,
            'is_filterable' => true,
            'is_global' => true,
            'sort_order' => 1,
        ]);

        $sizeValues = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL'];
        foreach ($sizeValues as $index => $value) {
            AttributeValue::create([
                'id' => Str::uuid(),
                'attribute_id' => $sizeAttributeId,
                'value' => $value,
                'sort_order' => $index,
            ]);
        }

        // Create Color attribute
        $colorAttributeId = Str::uuid();
        Attribute::create([
            'id' => $colorAttributeId,
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'color',
            'display_type' => 'swatch',
            'is_required' => true,
            'is_filterable' => true,
            'is_global' => true,
            'sort_order' => 2,
        ]);

        $colors = [
            ['Red', '#FF0000'],
            ['Blue', '#0000FF'],
            ['Green', '#00FF00'],
            ['Black', '#000000'],
            ['White', '#FFFFFF'],
            ['Yellow', '#FFFF00'],
            ['Purple', '#800080'],
            ['Pink', '#FFC0CB'],
        ];
        
        foreach ($colors as $index => $color) {
            AttributeValue::create([
                'id' => Str::uuid(),
                'attribute_id' => $colorAttributeId,
                'value' => $color[0],
                'color_code' => $color[1],
                'sort_order' => $index,
            ]);
        }

        // Create RAM attribute
        $ramAttributeId = Str::uuid();
        Attribute::create([
            'id' => $ramAttributeId,
            'name' => 'RAM',
            'slug' => 'ram',
            'type' => 'select',
            'display_type' => 'dropdown',
            'is_required' => true,
            'is_filterable' => true,
            'is_global' => true,
            'sort_order' => 3,
        ]);

        $ramValues = ['4GB', '6GB', '8GB', '12GB', '16GB', '32GB', '64GB'];
        foreach ($ramValues as $index => $value) {
            AttributeValue::create([
                'id' => Str::uuid(),
                'attribute_id' => $ramAttributeId,
                'value' => $value,
                'sort_order' => $index,
            ]);
        }

        // Create Storage attribute
        $storageAttributeId = Str::uuid();
        Attribute::create([
            'id' => $storageAttributeId,
            'name' => 'Storage',
            'slug' => 'storage',
            'type' => 'select',
            'display_type' => 'dropdown',
            'is_required' => true,
            'is_filterable' => true,
            'is_global' => true,
            'sort_order' => 4,
        ]);

        $storageValues = ['64GB', '128GB', '256GB', '512GB', '1TB', '2TB'];
        foreach ($storageValues as $index => $value) {
            AttributeValue::create([
                'id' => Str::uuid(),
                'attribute_id' => $storageAttributeId,
                'value' => $value,
                'sort_order' => $index,
            ]);
        }

        // Create Processor attribute
        $processorAttributeId = Str::uuid();
        Attribute::create([
            'id' => $processorAttributeId,
            'name' => 'Processor',
            'slug' => 'processor',
            'type' => 'select',
            'display_type' => 'dropdown',
            'is_required' => true,
            'is_filterable' => true,
            'is_global' => true,
            'sort_order' => 5,
        ]);

        $processorValues = [
            'Intel Core i3', 
            'Intel Core i5', 
            'Intel Core i7', 
            'Intel Core i9', 
            'AMD Ryzen 3', 
            'AMD Ryzen 5', 
            'AMD Ryzen 7', 
            'AMD Ryzen 9'
        ];
        
        foreach ($processorValues as $index => $value) {
            AttributeValue::create([
                'id' => Str::uuid(),
                'attribute_id' => $processorAttributeId,
                'value' => $value,
                'sort_order' => $index,
            ]);
        }

        // Create Battery attribute
        $batteryAttributeId = Str::uuid();
        Attribute::create([
            'id' => $batteryAttributeId,
            'name' => 'Battery',
            'slug' => 'battery',
            'type' => 'select',
            'display_type' => 'dropdown',
            'is_required' => false,
            'is_filterable' => true,
            'is_global' => true,
            'sort_order' => 6,
        ]);

        $batteryValues = ['3000mAh', '4000mAh', '5000mAh', '6000mAh', '7000mAh'];
        foreach ($batteryValues as $index => $value) {
            AttributeValue::create([
                'id' => Str::uuid(),
                'attribute_id' => $batteryAttributeId,
                'value' => $value,
                'sort_order' => $index,
            ]);
        }

        // Assign attributes to groups using DB::table to avoid UUID issues
        DB::table('attribute_group_mappings')->insert([
            [
                'id' => Str::uuid(),
                'attribute_group_id' => $sizeGroupId,
                'attribute_id' => $sizeAttributeId,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'attribute_group_id' => $colorGroupId,
                'attribute_id' => $colorAttributeId,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'attribute_group_id' => $electronicsGroupId,
                'attribute_id' => $ramAttributeId,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'attribute_group_id' => $electronicsGroupId,
                'attribute_id' => $storageAttributeId,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'attribute_group_id' => $electronicsGroupId,
                'attribute_id' => $processorAttributeId,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'attribute_group_id' => $electronicsGroupId,
                'attribute_id' => $batteryAttributeId,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        
        $this->command->info('Attribute seeder completed successfully!');
        $this->command->info('Created ' . Attribute::count() . ' attributes');
        $this->command->info('Created ' . AttributeValue::count() . ' attribute values');
        $this->command->info('Created ' . AttributeGroup::count() . ' attribute groups');
    }
}