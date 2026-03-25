<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Audit Configuration
    |--------------------------------------------------------------------------
    */
    
    // Enable/disable auditing globally
    'enabled' => env('AUDIT_ENABLED', true),
    
    // Log console commands
    'log_console_events' => env('AUDIT_LOG_CONSOLE', false),
    
    // Log API requests
    'log_api_requests' => env('AUDIT_LOG_API_REQUESTS', false),
    
    // Retention period in days (null = forever)
    'retention_days' => env('AUDIT_RETENTION_DAYS', 90),
    
    // Models to audit (these will automatically log changes)
    'models' => [
        \App\Models\User::class,
        \App\Models\Product::class,
        \App\Models\Order::class,
        \App\Models\VendorProfile::class,
        \App\Models\Category::class,
        \App\Models\PromoCode::class,
        \App\Models\Banner::class,
        \App\Models\ProductVariant::class,
    ],
    
    // Actions to audit
    'actions' => [
        'created' => 'create',
        'updated' => 'update',
        'deleted' => 'delete',
        'restored' => 'restore',
        'forceDeleted' => 'force_delete',
    ],
    
    // Sensitive fields to mask
    'sensitive_fields' => [
        'password',
        'token',
        'api_key',
        'secret',
        'credit_card',
        'bank_account',
        'cvv',
        'otp',
        'remember_token',
        'card_number',
        'cvv2',
        'pan_number',
    ],
    
    // Exclude fields from audit (never log these)
    'exclude_fields' => [
        'updated_at',
        'remember_token',
    ],
    
    // IP address handling
    'ip_address' => [
        'enabled' => true,
        'anonymize' => false,
    ],
    
    // User agent logging
    'user_agent' => [
        'enabled' => true,
        'store' => true,
    ],
];