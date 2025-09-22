<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MpesaService
{
    private $consumer_key;
    private $consumer_secret;
    private $shortcode;
    private $passkey;
    private $callback_url;
    private $base_url;

    public function __construct()
    {
        // For testing - use direct values
        $this->consumer_key = env('MPESA_CONSUMER_KEY');
        $this->consumer_secret = env('MPESA_CONSUMER_SECRET');
        $this->shortcode = env('MPESA_SHORTCODE', '174379'); // Default sandbox shortcode
        $this->passkey = env('MPESA_PASSKEY');
        $this->callback_url = env('MPESA_CALLBACK_URL');
        $this->base_url = 'https://sandbox.safaricom.co.ke'; // Hardcode for testing
        
        // Debug log
        Log::info('MpesaService Test Configuration', [
            'consumer_key' => substr($this->consumer_key ?? 'NULL', 0, 10) . '...',
            'consumer_secret' => substr($this->consumer_secret ?? 'NULL', 0, 10) . '...',
            'shortcode' => $this->shortcode,
            'passkey' => substr($this->passkey ?? 'NULL', 0, 10) . '...',
            'base_url' => $this->base_url,
            'callback_url' => $this->callback_url
        ]);
    }

    /**
     * Generate access token
     */
    public function generateAccessToken()
    {
        $url = $this->base_url . '/oauth/v1/generate?grant_type=client_credentials';
        $credentials = base64_encode($this->consumer_key . ':' . $this->consumer_secret);

        try {
            $response = Http::withOptions([
                'verify' => config('mpesa.verify_ssl', false), // Disable SSL verification for development
                'timeout' => 30,
            ])->withHeaders([
                'Authorization' => 'Basic ' . $credentials,
                'Content-Type' => 'application/json',
            ])->get($url);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Access token generated successfully');
                return $data['access_token'] ?? null;
            }

            Log::error('Failed to generate access token', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exception generating access token: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate password for STK push
     */
    private function generatePassword()
    {
        $timestamp = Carbon::now()->format('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);
        
        return [
            'password' => $password,
            'timestamp' => $timestamp
        ];
    }

    /**
     * Initiate STK Push
     */
    public function stkPush($phone, $amount, $account_reference = '', $transaction_desc = '')
    {
        // Validate required configuration
        if (!$this->shortcode) {
            Log::error('Cannot initiate STK Push: Missing shortcode');
            return [
                'success' => false,
                'message' => 'System configuration error: Missing business shortcode'
            ];
        }

        $access_token = $this->generateAccessToken();
        
        if (!$access_token) {
            return [
                'success' => false,
                'message' => 'Failed to generate access token'
            ];
        }

        $url = $this->base_url . '/mpesa/stkpush/v1/processrequest';
        $password_data = $this->generatePassword();

        // Format phone number
        $phone = $this->formatPhoneNumber($phone);
        
        if (!$phone) {
            return [
                'success' => false,
                'message' => 'Invalid phone number format'
            ];
        }

        $payload = [
            'BusinessShortCode' => $this->shortcode, // Keep as string for testing
            'Password' => $password_data['password'],
            'Timestamp' => $password_data['timestamp'],
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => (int) $amount,
            'PartyA' => $phone, // Keep as string for testing
            'PartyB' => $this->shortcode, // Keep as string for testing
            'PhoneNumber' => $phone, // Keep as string for testing
            'CallBackURL' => $this->callback_url,
            'AccountReference' => $account_reference ?: 'BookPurchase',
            'TransactionDesc' => $transaction_desc ?: 'Payment for book purchase'
        ];

        Log::info('STK Push payload', [
            'payload' => $payload,
            'url' => $url
        ]);

        try {
            $response = Http::withOptions([
                'verify' => config('mpesa.verify_ssl', false), // Disable SSL verification for development
                'timeout' => 30,
            ])->withHeaders([
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            $data = $response->json();

            if ($response->successful() && isset($data['ResponseCode']) && $data['ResponseCode'] == '0') {
                Log::info('STK Push initiated successfully', [
                    'checkout_request_id' => $data['CheckoutRequestID'] ?? 'N/A',
                    'merchant_request_id' => $data['MerchantRequestID'] ?? 'N/A',
                    'phone' => $phone,
                    'amount' => $amount
                ]);

                return [
                    'success' => true,
                    'message' => $data['ResponseDescription'] ?? 'STK Push sent successfully',
                    'checkout_request_id' => $data['CheckoutRequestID'] ?? null,
                    'merchant_request_id' => $data['MerchantRequestID'] ?? null,
                ];
            }

            Log::error('STK Push failed', [
                'response' => $data,
                'phone' => $phone,
                'amount' => $amount
            ]);

            return [
                'success' => false,
                'message' => $data['ResponseDescription'] ?? 'Failed to initiate payment'
            ];

        } catch (\Exception $e) {
            Log::error('Exception during STK Push: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Payment system error. Please try again.'
            ];
        }
    }

    /**
     * Query STK Push status
     */
    public function querySTKStatus($checkout_request_id)
    {
        Log::info('Querying STK Status', [
            'checkout_request_id' => $checkout_request_id,
            'timestamp' => now()->toDateTimeString()
        ]);

        $access_token = $this->generateAccessToken();
        
        if (!$access_token) {
            Log::error('Failed to generate access token for STK status query', [
                'checkout_request_id' => $checkout_request_id
            ]);
            return [
                'success' => false,
                'message' => 'Failed to generate access token'
            ];
        }

        $url = $this->base_url . '/mpesa/stkpushquery/v1/query';
        $password_data = $this->generatePassword();

        $payload = [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $password_data['password'],
            'Timestamp' => $password_data['timestamp'],
            'CheckoutRequestID' => $checkout_request_id
        ];

        Log::info('STK Status Query Payload', [
            'url' => $url,
            'payload' => $payload
        ]);

        try {
            $response = Http::withOptions([
                'verify' => config('mpesa.verify_ssl', false), // Disable SSL verification for development
                'timeout' => 30,
            ])->withHeaders([
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            Log::info('STK Status Query Response', [
                'status_code' => $response->status(),
                'response_body' => $response->body()
            ]);

            $data = $response->json();

            // Interpret the response
            if ($response->successful()) {
                $status = $this->interpretSTKStatus($data['ResultCode'] ?? null);
                
                Log::info('STK Status Interpretation', [
                    'checkout_request_id' => $checkout_request_id,
                    'result_code' => $data['ResultCode'] ?? 'N/A',
                    'interpreted_status' => $status
                ]);

                return [
                    'success' => true,
                    'status' => $status,
                    'raw_response' => $data
                ];
            }

            Log::error('STK Status query failed', [
                'response' => $data,
                'checkout_request_id' => $checkout_request_id
            ]);

            return [
                'success' => false,
                'message' => $data['errorMessage'] ?? 'STK status query failed',
                'raw_response' => $data
            ];
            
        } catch (\Exception $e) {
            Log::error('Exception querying STK status', [
                'checkout_request_id' => $checkout_request_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'message' => 'Query failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Interpret STK Push status codes
     */
    private function interpretSTKStatus($resultCode)
    {
        Log::info('Interpreting STK Status', [
            'result_code' => $resultCode
        ]);

        switch ($resultCode) {
            case 0:
                return 'completed';
            case 1032:
                return 'cancelled';
            case 1037:
                return 'timeout';
            default:
                return 'failed';
        }
    }

    /**
     * Validate phone number format
     */
    public function isValidPhoneNumber($phone)
    {
        $formatted = $this->formatPhoneNumber($phone);
        return preg_match('/^254[17-9]\d{8}$/', $formatted) === 1;
    }

    /**
     * Format phone number to required format
     */
    public function formatPhoneNumber($phone)
    {
        // Remove any non-digit characters
        $phone = preg_replace('/\D/', '', $phone);
        
        // Convert to 254 format if starts with 0
        if (substr($phone, 0, 1) === '0') {
            $phone = '254' . substr($phone, 1);
        }
        
        // Ensure it starts with 254
        if (!str_starts_with($phone, '254')) {
            $phone = '254' . $phone;
        }

        return $phone;
    }
}