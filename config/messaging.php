<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Messaging Service Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Messaging Service API V2
    | Used for sending SMS notifications for bills, payments, and alerts
    |
    */

    'base_url' => env('MESSAGING_BASE_URL', 'https://messaging-service.co.tz'),
    
    'token' => env('MESSAGING_TOKEN', 'f9a89f439206e27169ead766463ca92c'),
    
    'sender_id' => env('MESSAGING_SENDER_ID', 'FEEDTAN'),
    
    'timeout' => env('MESSAGING_TIMEOUT', 30),
    
    'enabled' => env('MESSAGING_ENABLED', true),
    
    'test_mode' => env('MESSAGING_TEST_MODE', false),
    
    'notifications' => [
        'bill_generation' => env('MESSAGING_BILL_NOTIFICATIONS', true),
        'payment_confirmation' => env('MESSAGING_PAYMENT_NOTIFICATIONS', true),
        'insufficient_funds' => env('MESSAGING_INSUFFICIENT_FUNDS_NOTIFICATIONS', true),
        'payment_failed' => env('MESSAGING_PAYMENT_FAILED_NOTIFICATIONS', true),
    ],
    
    'rate_limit' => [
        'max_per_hour' => env('MESSAGING_MAX_PER_HOUR', 100),
        'max_per_day' => env('MESSAGING_MAX_PER_DAY', 1000),
    ],
];
