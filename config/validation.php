<?php

return [
    'customer' => [
        'first_name' => ['min' => 2, 'max' => 255],
        'last_name' => ['min' => 2, 'max' => 255],
        'email' => ['max' => 255],
        'phone' => ['min' => 8, 'max' => 20],
        'address_line_1' => ['min' => 5, 'max' => 255],
        'address_line_2' => ['max' => 255],
        'city' => ['min' => 2, 'max' => 100],
        'state' => ['min' => 2, 'max' => 100],
        'postal_code' => ['min' => 3, 'max' => 20],
    ],

    'cart' => [
        'max_items' => 50,
        'max_quantity_per_item' => 999,
    ],

    'discount' => [
        'cache_ttl' => 300, // 5 minutes
        'max_percentage' => 100,
        'max_amount' => 1000000, // 1000 KWD in fils
    ],
];
