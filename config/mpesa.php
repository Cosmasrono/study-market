<?php

return [
    /*
    |--------------------------------------------------------------------------
    | M-Pesa Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for M-Pesa Daraja API integration
    |
    */

    'consumer_key' => env('MPESA_CONSUMER_KEY'),
    'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
    'shortcode' => env('MPESA_SHORTCODE'),
    'business_shortcode' => env('MPESA_SHORTCODE'), // Alias for compatibility
    'passkey' => env('MPESA_PASSKEY'),
    'callback_url' => env('MPESA_CALLBACK_URL'),
    
    // Use sandbox for testing, production for live
    'base_url' => env('MPESA_ENV', 'sandbox') === 'production' 
        ? 'https://api.safaricom.co.ke' 
        : 'https://sandbox.safaricom.co.ke',
        
    'environment' => env('MPESA_ENV', 'sandbox'),
    
    // SSL verification (disable for development, enable for production)
    'verify_ssl' => false,
];