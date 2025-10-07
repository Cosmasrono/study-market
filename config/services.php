<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'mpesa' => [
        'consumer_key' => env('MPESA_CONSUMER_KEY'),
        'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
        'business_shortcode' => env('MPESA_BUSINESS_SHORTCODE'),
        'passkey' => env('MPESA_PASSKEY'),
        'environment' => env('MPESA_ENVIRONMENT', 'sandbox'),
    ],

  'payhero' => [
        'username' => env('PAYHERO_USERNAME'),
        'password' => env('PAYHERO_PASSWORD'),
        'channel_id' => env('PAYHERO_CHANNEL_ID'),
        'environment' => env('PAYHERO_ENVIRONMENT', 'sandbox'),
        'provider' => 'm-pesa',
        'paybill' => env('PAYHERO_PAYBILL', '4103208'),
        
        // API Configuration
        'base_url' => 'https://backend.payhero.co.ke/api/v2',
        'timeout' => 30,
        'max_retries' => 3,
        
        // Callback URL
        'callback_url' => env('APP_URL') . '/payhero/callback',
    ],

];
