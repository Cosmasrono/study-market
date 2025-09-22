<?php

// config/membership.php

return [
    /*
    |--------------------------------------------------------------------------
    | Membership Fee
    |--------------------------------------------------------------------------
    | The annual membership fee amount in KES
    */
    'fee' => env('MEMBERSHIP_FEE', 1000),

    /*
    |--------------------------------------------------------------------------
    | Membership Duration
    |--------------------------------------------------------------------------
    | Duration of membership in months
    */
    'duration_months' => env('MEMBERSHIP_DURATION_MONTHS', 12),

    /*
    |--------------------------------------------------------------------------
    | Payment Methods
    |--------------------------------------------------------------------------
    | Available payment methods for membership
    */
    'payment_methods' => [
        'mpesa' => [
            'enabled' => env('MPESA_ENABLED', true),
            'name' => 'M-Pesa',
            'shortcode' => env('MPESA_SHORTCODE'),
            'passkey' => env('MPESA_PASSKEY'),
            'consumer_key' => env('MPESA_CONSUMER_KEY'),
            'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
            'environment' => env('MPESA_ENVIRONMENT', 'sandbox'), // sandbox or production
        ],
        'card' => [
            'enabled' => env('CARD_PAYMENT_ENABLED', true),
            'name' => 'Credit/Debit Card',
            'stripe_key' => env('STRIPE_KEY'),
            'stripe_secret' => env('STRIPE_SECRET'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Grace Period
    |--------------------------------------------------------------------------
    | Grace period in days after membership expires
    */
    'grace_period_days' => env('MEMBERSHIP_GRACE_PERIOD', 7),

    /*
    |--------------------------------------------------------------------------
    | Reminder Settings
    |--------------------------------------------------------------------------
    | When to send membership expiry reminders
    */
    'reminders' => [
        'days_before_expiry' => [30, 14, 7, 3, 1],
        'enabled' => env('MEMBERSHIP_REMINDERS_ENABLED', true),
    ],
];