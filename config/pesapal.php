<?php

return [
    'consumer_key' => env('PESAPAL_CONSUMER_KEY'),
    'consumer_secret' => env('PESAPAL_CONSUMER_SECRET'),
    'environment' => env('PESAPAL_ENVIRONMENT', 'sandbox'),
    'ipn_url' => env('PESAPAL_IPN_URL'),
    'callback_url' => env('PESAPAL_CALLBACK_URL'),
    'api_url' => env('PESAPAL_ENVIRONMENT', 'sandbox') === 'live' 
        ? 'https://pay.pesapal.com/v3' 
        : 'https://cybqa.pesapal.com/pesapalv3',
];