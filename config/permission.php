<?php

return [
    'models' => [
        'permission' => Spatie\Permission\Models\Permission::class,
        'role' => Spatie\Permission\Models\Role::class,
    ],

    'table_names' => [
        'roles' => 'roles',
        'permissions' => 'permissions',
        'model_has_permissions' => 'model_has_permissions',
        'model_has_roles' => 'model_has_roles',
        'role_has_permissions' => 'role_has_permissions',
    ],

    'column_names' => [
        'role_pivot_key' => null,
        'permission_pivot_key' => null,
        'model_morph_key' => 'model_id',
    ],

    'teams' => false,
    'display_permission_in_exception' => false,
    'enable_wildcard' => false,
    'use_passport_client_credentials' => false,
    'register_permission_check_method' => true,
    
    // Set default guard to API
    'guard_name' => 'api',
    
    'excluded_guards' => ['web'],
    
    'middleware' => [
        'role' => Spatie\Permission\Middlewares\RoleMiddleware::class,
        'permission' => Spatie\Permission\Middlewares\PermissionMiddleware::class,
        'role_or_permission' => Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class,
    ],
];