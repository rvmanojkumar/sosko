<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Truncate existing data (optional - if you want to reset)
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        DB::table('roles')->truncate();
        DB::table('permissions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Create permissions
        $permissions = [
            // User permissions
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            
            // Product permissions
            'view_products',
            'create_products',
            'edit_products',
            'delete_products',
            
            // Order permissions
            'view_orders',
            'manage_orders',
            
            // Category permissions
            'view_categories',
            'create_categories',
            'edit_categories',
            'delete_categories',
            
            // Vendor permissions
            'view_vendors',
            'approve_vendors',
            'suspend_vendors',
            
            // Promo code permissions
            'view_promo_codes',
            'create_promo_codes',
            'edit_promo_codes',
            'delete_promo_codes',
            
            // Banner permissions
            'view_banners',
            'create_banners',
            'edit_banners',
            'delete_banners',
            
            // Settings permissions
            'manage_settings',
            'view_reports',
            'export_reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api']);
        }

        // Create roles
        $roles = ['super-admin', 'admin', 'vendor', 'customer'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'api']);
        }

        // Get roles
        $superAdminRole = Role::where('name', 'super-admin')->where('guard_name', 'api')->first();
        $adminRole = Role::where('name', 'admin')->where('guard_name', 'api')->first();
        $vendorRole = Role::where('name', 'vendor')->where('guard_name', 'api')->first();
        $customerRole = Role::where('name', 'customer')->where('guard_name', 'api')->first();

        // Assign permissions to roles
        $superAdminRole->syncPermissions(Permission::all());
        
        $adminRole->syncPermissions(
            Permission::whereIn('name', [
                'view_users', 'create_users', 'edit_users', 'delete_users',
                'view_products', 'create_products', 'edit_products', 'delete_products',
                'view_orders', 'manage_orders',
                'view_categories', 'create_categories', 'edit_categories', 'delete_categories',
                'view_vendors', 'approve_vendors', 'suspend_vendors',
                'view_promo_codes', 'create_promo_codes', 'edit_promo_codes', 'delete_promo_codes',
                'view_banners', 'create_banners', 'edit_banners', 'delete_banners',
                'manage_settings', 'view_reports', 'export_reports',
            ])->get()
        );
        
        $vendorRole->syncPermissions(
            Permission::whereIn('name', [
                'view_products', 'create_products', 'edit_products', 'delete_products', 'view_orders'
            ])->get()
        );
        
        $customerRole->syncPermissions(
            Permission::where('name', 'view_products')->get()
        );

        // Create users
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'admin@example.com',
                'phone' => '9876543210',
                'password' => Hash::make('password'),
                'role' => 'super-admin',
            ],
            [
                'name' => 'Admin',
                'email' => 'admin2@example.com',
                'phone' => '9876543211',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
            [
                'name' => 'Vendor User',
                'email' => 'vendor@example.com',
                'phone' => '9876543213',
                'password' => Hash::make('password'),
                'role' => 'vendor',
            ],
            [
                'name' => 'Customer',
                'email' => 'customer@example.com',
                'phone' => '9876543212',
                'password' => Hash::make('password'),
                'role' => 'customer',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'id' => Str::uuid(),
                    'name' => $userData['name'],
                    'phone' => $userData['phone'],
                    'password' => $userData['password'],
                    'phone_verified_at' => now(),
                ]
            );
            
            if (!$user->hasRole($userData['role'])) {
                $user->assignRole($userData['role']);
            }
        }
    }
}