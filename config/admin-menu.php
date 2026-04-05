<?php

return [
    'menu' => [
        [
            'text' => 'Dashboard',
            'url' => 'admin/dashboard',
            'icon' => 'tachometer-alt',
            'active' => ['admin/dashboard'],
        ],
        [
            'text' => 'Products',
            'url' => 'admin/products',
            'icon' => 'box',
            'active' => ['admin/products*'],
        ],
        [
            'text' => 'Orders',
            'url' => 'admin/orders',
            'icon' => 'shopping-cart',
            'active' => ['admin/orders*'],
        ],
        [
            'text' => 'Categories',
            'url' => 'admin/categories',
            'icon' => 'tags',
            'active' => ['admin/categories*'],
        ],
        [
            'text' => 'Attributes',
            'icon' => 'cogs',
            'active' => ['admin/attributes'],
            'submenu' => [
                [
                    'text' => 'All Attributes',
                    'url' => 'admin/attributes',
                    'icon' => 'list',
                    'active' => ['admin/attributes*'],
                ],
                [
                    'text' => 'Add Attribute',
                    'url' => 'admin/attributes/create',
                    'icon' => 'plus',
                    'active' => ['admin/attributes/create'],
                ],
                [
                    'text' => 'Attribute Groups',
                    'url' => 'admin/attribute-groups',
                    'icon' => 'layer-group',
                    'active' => ['admin/attribute-groups*'],
                ],
                [
                    'text' => 'Add Group',
                    'url' => 'admin/attribute-groups/create',
                    'icon' => 'plus',
                    'active' => ['admin/attribute-groups/create'],
                ],
            ],
        ],
        [
            'text' => 'Vendors',
            'url' => 'admin/vendors',
            'icon' => 'store',
            'active' => ['admin/vendors*'],
        ],
        [
            'text' => 'Users',
            'url' => 'admin/users',
            'icon' => 'users',
            'active' => ['admin/users*'],
        ],
        [
            'text' => 'Promo Codes',
            'url' => 'admin/promo-codes',
            'icon' => 'ticket-alt',
            'active' => ['admin/promo-codes*'],
        ],
        [
            'text' => 'Banners',
            'url' => 'admin/banners',
            'icon' => 'image',
            'active' => ['admin/banners*'],
        ],
        [
            'text' => 'Reviews',
            'url' => 'admin/reviews',
            'icon' => 'star',
            'active' => ['admin/reviews*'],
        ],
        [
            'text' => 'Reports',
            'url' => 'admin/reports',
            'icon' => 'chart-bar',
            'active' => ['admin/reports*'],
        ],
    ],
];