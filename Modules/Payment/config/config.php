<?php

return [
    'name' => 'Payment',
    'gateway' => env('PAYMENT_GATEWAY', 'mock'),
    'stripe' => [
        'secret_key' => env('STRIPE_SECRET_KEY', ''),
        'public_key' => env('STRIPE_PUBLIC_KEY', ''),
    ],
];
