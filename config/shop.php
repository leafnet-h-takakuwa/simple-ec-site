<?php

return [
    'pagination' => [
        'products' => 12,
        'orders' => 10,
        'admin_products' => 10,
        'admin_orders' => 10,
        'admin_users' => 20,
        'admin_user_orders' => 10,
    ],
    'home' => [
        'popular_products' => 3,
        'new_products' => 3,
    ],
    'products' => [
        'new_days' => 30,
        // Laravelのファイルサイズ検証と同じKB単位
        'image_max_kb' => 5120,
    ],
    'dashboard' => [
        'recent_order_days' => 7,
    ],
];
