<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PayheroService
{
    protected $baseUrl;
    protected $basicAuthToken;
    protected $channelId;
    protected $environment;
    protected $maxRetries = 3;
    protected $retryDelay = 2; // seconds

    public function __construct()
    {
        $this->baseUrl = 'https://backend.payhero.co.ke/api/v2';
        
        $username = config('services.payhero.username');
        $password = config('services.payhero.password');
        
        if (!$username || !$password) {
            throw new \Exception("PayHero credentials not configured properly");
        }
        
        $this->basicAuthToken = base64_encode($username . ':' . $password);
        $this->channelId = config('services.payhero.channel_id');
        $this->environment = config('services.payhero.environment', 'sandbox');
        
        // Validate channel ID
        if (!$this->channelId) {
            throw new \Exception("PayHero channel_id is not configured");
        }
    }

    public function initiatePayment(array $paymentDetails)
    {
        $attemptNumber = 0;
        $lastException = null;

        // Retry logic with exponential backoff
        while ($attemptNumber < $this->maxRetries) {
            try {
                $this->validatePaymentDetails($paymentDetails);

                $paymentDetails['customer_name'] = $paymentDetails['customer_name'] 
                    ?? $this->generateCustomerName($paymentDetails);

                $payload = $this->preparePaymentPayload($paymentDetails);

                Log::info('PayHero Payment Attempt', [
                    'attempt' => $attemptNumber + 1,
                    'max_retries' => $this->maxRetries,
                    'reference' => $payload['external_reference'],
                    'payload' => $this->sanitizePayload($payload)
                ]);

                $response = $this->makePaymentRequest($payload, $attemptNumber);

                return $this->processPaymentResponse($response, $payload['external_reference']);

            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                // Handle timeout - this is CRITICAL
                $lastException = $e;
                $attemptNumber++;
                
                Log::warning('PayHero API Timeout', [
                    'attempt' => $attemptNumber,
                    'reference' => $paymentDetails['transaction_reference'],
                    'error' => $e->getMessage()
                ]);

                // On timeout, assume STK push was sent and return success
                if ($attemptNumber >= $this->maxRetries) {
                    Log::info('Timeout Fallback - Assuming STK Push Sent', [
                        'reference' => $paymentDetails['transaction_reference']
                    ]);
                    
                    return [
                        'status' => 'pending',
                        'transaction_reference' => $paymentDetails['transaction_reference'],
                        'message' => 'Payment initiated (timeout - awaiting callback)',
                        'fallback' => true
                    ];
                }

                // Exponential backoff before retry
                sleep($this->retryDelay * $attemptNumber);
                
            } catch (\Exception $e) {
                $lastException = $e;
                
                Log::error('PayHero Payment Error', [
                    'attempt' => $attemptNumber + 1,
                    'error' => $e->getMessage(),
                    'reference' => $paymentDetails['transaction_reference'] ?? null
                ]);

                // Don't retry for validation errors
                if ($e instanceof \InvalidArgumentException) {
                    throw $e;
                }

                $attemptNumber++;
                
                if ($attemptNumber >= $this->maxRetries) {
                    break;
                }

                sleep($this->retryDelay * $attemptNumber);
            }
        }

        // All retries failed - throw the last exception
        Log::critical('PayHero Payment Failed After All Retries', [
            'retries' => $this->maxRetries,
            'reference' => $paymentDetails['transaction_reference'] ?? null,
            'last_error' => $lastException->getMessage()
        ]);

        throw new \Exception(
            "Payment initiation failed after {$this->maxRetries} attempts: " . $lastException->getMessage(),
            0,
            $lastException
        );
    }

    private function validatePaymentDetails(array $paymentDetails)
    {
        $requiredFields = ['user_id', 'phone_number', 'amount', 'transaction_reference'];

        foreach ($requiredFields as $field) {
            if (!isset($paymentDetails[$field]) || empty($paymentDetails[$field])) {
                throw new \InvalidArgumentException("Missing required payment field: {$field}");
            }
        }

        // Validate phone number format
        $formattedPhone = $this->formatPhoneNumber($paymentDetails['phone_number']);
        if (!preg_match('/^254[17]\d{8}$/', $formattedPhone)) {
            throw new \InvalidArgumentException(
                "Invalid phone number format. Must be Safaricom (254712345678) or Airtel (254101234567)"
            );
        }

        // Validate amount
        if (!is_numeric($paymentDetails['amount']) || $paymentDetails['amount'] <= 0) {
            throw new \InvalidArgumentException("Invalid payment amount: {$paymentDetails['amount']}");
        }

        // Minimum amount check (PayHero typically requires minimum 10 KES)
        if ($paymentDetails['amount'] < 1) {
            throw new \InvalidArgumentException("Amount must be at least KES 1");
        }
    }


    private function preparePaymentPayload(array $paymentDetails)
{
    return [
        'amount' => (float) $paymentDetails['amount'],
        'phone_number' => $this->formatPhoneNumber($paymentDetails['phone_number']),
        'channel_id' => (string) $this->channelId,
        'provider' => 'm-pesa',
        'external_reference' => $paymentDetails['transaction_reference'],
        'customer_name' => $paymentDetails['customer_name'] ?? 'Customer',
        'callback_url' => config('services.payhero.callback_url') ?: (config('app.url') . '/payhero/callback'),  // ✅ FIXED
        'description' => $paymentDetails['description'] ?? 'Membership Payment'
    ];
}
 

    private function formatPhoneNumber($phone)
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/\D/', '', $phone);
        
        // Handle different formats
        if (strlen($phone) === 9) {
            // 712345678 -> 254712345678
            $phone = '254' . $phone;
        } elseif (strlen($phone) === 10 && substr($phone, 0, 1) === '0') {
            // 0712345678 -> 254712345678
            $phone = '254' . substr($phone, 1);
        } elseif (strlen($phone) === 12 && substr($phone, 0, 3) !== '254') {
            // Invalid format
            throw new \InvalidArgumentException("Invalid phone number length: {$phone}");
        }

        return $phone;
    }

    private function makePaymentRequest(array $payload, int $attemptNumber)
    {
        $endpoint = rtrim($this->baseUrl, '/') . '/payments';
        
        // Increase timeout on retries
        $timeout = 30 + ($attemptNumber * 15); // 30s, 45s, 60s

        Log::info('Making PayHero API Request', [
            'url' => $endpoint,
            'timeout' => $timeout,
            'attempt' => $attemptNumber + 1,
            'payload' => $this->sanitizePayload($payload)
        ]);

      $response = Http::withOptions([
    'verify' => config('app.env') === 'production',
    'debug' => false   
])
        ->withHeaders([
            'Authorization' => 'Basic ' . $this->basicAuthToken,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])
        ->timeout($timeout)
        ->connectTimeout(10)
        ->retry(2, 100) // Retry twice with 100ms delay for network issues
        ->post($endpoint, $payload);

        Log::info('PayHero API Response', [
            'status' => $response->status(),
            'body' => $response->json(),
            'headers' => $response->headers()
        ]);

        if (!$response->successful()) {
            $errorBody = $response->json();
            $errorMessage = $errorBody['message'] 
                ?? $errorBody['error_message'] 
                ?? $response->body() 
                ?? 'Unknown error';

            throw new \Exception(
                "PayHero API request failed: {$errorMessage}", 
                $response->status()
            );
        }

        return $response->json();
    }

    private function processPaymentResponse(array $responseData, string $reference)
    {
        Log::info('Processing PayHero Response', [
            'reference' => $reference,
            'response' => $responseData
        ]);

        // Check for explicit errors
        if (isset($responseData['error_code']) || isset($responseData['error_message'])) {
            $errorCode = $responseData['error_code'] ?? 'UNKNOWN_ERROR';
            $errorMessage = $responseData['error_message'] ?? 'Unknown PayHero API error';

            throw new \Exception("PayHero API Error: {$errorMessage} (Code: {$errorCode})", 400);
        }

        // Extract status (be flexible with field names)
        $status = $responseData['status'] 
            ?? $responseData['payment_status'] 
            ?? $responseData['transaction_status'] 
            ?? null;
        
        // Check if response indicates success/pending
        $successIndicators = [
            'success', 'pending', 'initiated', 'processing',
            isset($responseData['transaction_reference']),
            isset($responseData['checkout_request_id']),
            isset($responseData['CheckoutRequestID'])
        ];
        
        $isSuccessful = in_array($status, ['success', 'pending', 'initiated']) 
            || in_array(true, $successIndicators);
        
        if ($isSuccessful) {
            return [
                'status' => $status ?? 'pending',
                'transaction_reference' => $responseData['transaction_reference'] 
                    ?? $responseData['reference'] 
                    ?? $responseData['external_reference'] 
                    ?? $responseData['ExternalReference'] 
                    ?? $reference,
                'checkout_request_id' => $responseData['checkout_request_id'] 
                    ?? $responseData['checkout_id'] 
                    ?? $responseData['CheckoutRequestID'] 
                    ?? null,
                'payment_url' => $responseData['payment_url'] 
                    ?? $responseData['redirect_url'] 
                    ?? null,
                'message' => $responseData['message'] ?? 'Payment initiated successfully'
            ];
        }

        // Unexpected response structure
        throw new \Exception(
            "Unexpected PayHero response structure: " . json_encode($responseData), 
            500
        );
    }

    public function validateCallback(Request $request)
    {
        try {
            $callbackData = $request->all();

            Log::info('PayHero Callback Received', [
                'data' => $callbackData,
                'headers' => $request->headers->all()
            ]);

            // PayHero sends data in 'response' key
            if (!isset($callbackData['response'])) {
                throw new \Exception('Invalid callback structure: missing response key');
            }

            $response = $callbackData['response'];
            
            // Validate required fields
            if (!isset($response['ExternalReference'])) {
                throw new \Exception('Invalid callback: missing ExternalReference');
            }

            $status = $this->determinePaymentStatus($response);

            return [
                'transaction_reference' => $response['ExternalReference'],
                'checkout_request_id' => $response['CheckoutRequestID'] ?? null,
                'status' => $status['status'],
                'mpesa_receipt_number' => $response['MpesaReceiptNumber'] ?? null,
                'amount' => $response['Amount'] ?? null,
                'phone' => $response['Phone'] ?? null,
                'failure_reason' => $status['failure_reason'] ?? null,
                'callback_data' => $response
            ];

        } catch (\Exception $e) {
            Log::error('PayHero Callback Validation Failed', [
                'error' => $e->getMessage(),
                'callback_data' => $request->all()
            ]);

            throw $e;
        }
    }

    private function determinePaymentStatus(array $response)
    {
        $resultCode = $response['ResultCode'] ?? null;
        $status = strtolower($response['Status'] ?? '');

        // Success conditions
        if ($resultCode === 0 || $resultCode === '0' || $status === 'success') {
            return ['status' => 'success'];
        }

        // Failed conditions
        $resultDesc = $response['ResultDesc'] ?? $response['ResultDescription'] ?? 'Payment failed';
        
        return [
            'status' => 'failed',
            'failure_reason' => $resultDesc
        ];
    }

 
 
public function checkPaymentStatus($transactionReference)
{
    try {
        Log::info('Payment Status Check Requested', [
            'reference' => $transactionReference
        ]);

        // Query PayHero API for transaction status
        // Try both possible endpoints
        $endpoints = [
            rtrim($this->baseUrl, '/') . '/transactions/' . $transactionReference,
            rtrim($this->baseUrl, '/') . '/payments/' . $transactionReference
        ];
        
        $response = null;
        $lastError = null;
        
        foreach ($endpoints as $endpoint) {
            try {
                $response = Http::withOptions([
                        'verify' => config('app.env') === 'production',
                        'debug' => false
                    ])
                    ->withHeaders([
                        'Authorization' => 'Basic ' . $this->basicAuthToken,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json'
                    ])
                    ->timeout(15)
                    ->get($endpoint);

                if ($response->successful()) {
                    break; // Success, exit loop
                }
            } catch (\Exception $e) {
                $lastError = $e->getMessage();
                continue; // Try next endpoint
            }
        }

        if (!$response || !$response->successful()) {
            Log::warning('PayHero Status Check Failed - All Endpoints', [
                'reference' => $transactionReference,
                'last_error' => $lastError
            ]);

            // If API call fails, return pending (callback will update it)
            return [
                'status' => 'pending',
                'message' => 'Awaiting payment confirmation via callback'
            ];
        }

        $data = $response->json();
        
        Log::info('PayHero Status Check Response', [
            'reference' => $transactionReference,
            'response' => $data
        ]);

        // Parse the response
        $status = strtolower($data['status'] ?? $data['payment_status'] ?? 'pending');
        $resultCode = $data['ResultCode'] ?? $data['result_code'] ?? null;

        // Check if payment is successful
        if ($status === 'success' || $status === 'completed' || $status === 'paid' || $resultCode === 0 || $resultCode === '0') {
            return [
                'status' => 'success',
                'mpesa_receipt_number' => $data['MpesaReceiptNumber'] 
                    ?? $data['mpesa_receipt_number'] 
                    ?? $data['receipt_number'] 
                    ?? null,
                'amount' => $data['Amount'] ?? $data['amount'] ?? null,
                'phone' => $data['Phone'] ?? $data['phone_number'] ?? null,
                'message' => 'Payment completed successfully'
            ];
        }

        // Check if payment failed
        if ($status === 'failed' || $status === 'cancelled' || ($resultCode && $resultCode != 0)) {
            return [
                'status' => 'failed',
                'failure_reason' => $data['ResultDesc'] 
                    ?? $data['result_description'] 
                    ?? $data['failure_reason'] 
                    ?? 'Payment failed',
                'message' => 'Payment failed'
            ];
        }

        // Payment still pending/queued/processing
        return [
            'status' => 'pending',
            'message' => 'Payment is being processed'
        ];

    } catch (\Exception $e) {
        Log::error('PayHero Payment Status Check Error', [
            'reference' => $transactionReference,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        // Return pending on error - callback will still update it
        return [
            'status' => 'pending',
            'message' => 'Awaiting payment confirmation'
        ];
    }
}

    private function generateCustomerName(array $paymentDetails)
    {
        if (isset($paymentDetails['email'])) {
            return ucfirst(explode('@', $paymentDetails['email'])[0]);
        }

        if (isset($paymentDetails['name'])) {
            return $paymentDetails['name'];
        }

        $phone = $this->formatPhoneNumber($paymentDetails['phone_number']);
        return 'Customer ' . substr($phone, -4);
    }

    private function sanitizePayload(array $payload)
    {
        $sanitized = $payload;
        
        // Mask phone number
        if (isset($sanitized['phone_number'])) {
            $phone = $sanitized['phone_number'];
            if (strlen($phone) > 6) {
                $sanitized['phone_number'] = substr($phone, 0, 6) . str_repeat('X', strlen($phone) - 6);
            }
        }

        return $sanitized;
    }

    /**
     * Check if PayHero is properly configured
     */
    public function isConfigured(): bool
    {
        return !empty(config('services.payhero.username')) 
            && !empty(config('services.payhero.password'))
            && !empty(config('services.payhero.channel_id'));
    }

    /**
     * Get environment status
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * Check if running in production
     */
    public function isProduction(): bool
    {
        return $this->environment === 'production';
    }
}