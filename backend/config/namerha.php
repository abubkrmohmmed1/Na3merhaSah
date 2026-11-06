<?php

return [
    'verification_price' => (int) env('VERIFICATION_PRICE_PER_POINT', 150),
    'b2b' => [
        'default_tier_limit' => 100,
    ],
    'sudapad' => [
        'url' => env('SUDAPAD_API_URL', 'https://api.sudapad.sd/v1'),
        'key' => env('SUDAPAD_API_KEY'),
    ],
];
