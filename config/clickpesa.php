<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ClickPesa API Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your ClickPesa API settings. These settings
    | are used by the ClickPesaAPIService to communicate with the ClickPesa
    | payment gateway API.
    |
    */

    'api_base_url' => env('CLICKPESA_API_BASE_URL', 'https://api.clickpesa.com/v2'),
    
    'api_key' => env('CLICKPESA_API_KEY'),
    
    'client_id' => env('CLICKPESA_CLIENT_ID'),
    
    'currency' => env('CLICKPESA_DEFAULT_CURRENCY', 'TZS'),

    /*
    |--------------------------------------------------------------------------
    | Callback Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for handling webhooks and callbacks from ClickPesa.
    |
    */

    'callback' => [
        'secret_key' => env('CLICKPESA_CALLBACK_SECRET'),
        'url' => env('CLICKPESA_CALLBACK_URL', '/webhooks/clickpesa'),
        'timeout' => env('CLICKPESA_CALLBACK_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for payment processing.
    |
    */

    'payment' => [
        'default_amount_min' => env('CLICKPESA_MIN_AMOUNT', 100),
        'default_amount_max' => env('CLICKPESA_MAX_AMOUNT', 1000000),
        'timeout' => env('CLICKPESA_PAYMENT_TIMEOUT', 300), // 5 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Payout Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for payout processing.
    |
    */

    'payout' => [
        'default_amount_min' => env('CLICKPESA_PAYOUT_MIN', 100),
        'default_amount_max' => env('CLICKPESA_PAYOUT_MAX', 10000000),
        'timeout' => env('CLICKPESA_PAYOUT_TIMEOUT', 600), // 10 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configure logging for ClickPesa operations.
    |
    */

    'logging' => [
        'enabled' => env('CLICKPESA_LOGGING_ENABLED', true),
        'level' => env('CLICKPESA_LOG_LEVEL', 'info'),
        'channel' => env('CLICKPESA_LOG_CHANNEL', 'default'),
    ],
];
