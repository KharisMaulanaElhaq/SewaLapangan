<?php

return [
    'server_key' => env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-demo-key-1234567890'),
    'client_key' => env('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-demo-key-1234567890'),
    'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),
    'is_sanitized' => (bool) env('MIDTRANS_IS_SANITIZED', true),
    'is_3ds' => (bool) env('MIDTRANS_IS_3DS', true),
    'merchant_id' => env('MIDTRANS_MERCHANT_ID', 'G123456789'),
];
