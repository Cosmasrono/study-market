<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\BookPurchase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected $mpesaService;

    public function __construct(MpesaService $mpesaService)
    {
        $this->mpesaService = $mpesaService;
    }

    /**
     * Initiate payment for various content types
     */
    public function initiatePayment($type, $itemId, $amount, $phone)
    {
        try {
            // Generate unique transaction ID
            $transactionId = $this->generateTransactionId($type, $itemId);
            
            // Create simplified payment record
            $payment = Payment::create([
                'user_id' => auth()->id(),
                'amount' => $amount,
                'status' => 'pending',
                'payment_method' => 'mpesa',
                'transaction_id' => $transactionId,
            ]);

            // For books, create a BookPurchase record
            $bookPurchase = null;
            if ($type === 'book') {
                $bookPurchase = BookPurchase::create([
                    'user_id' => auth()->id(),
                    'book_id' => $itemId,
                    'amount' => $amount,
                    'status' => 'pending',
                    'transaction_id' => $transactionId,
                    'phone_number' => $phone,
                ]);
            }

            // Initiate M-Pesa STK Push
            $stkResponse = $this->mpesaService->initiateSTKPush(
                $phone,
                $amount,
                $transactionId,
                $this->getPaymentDescription($type, $itemId)
            );

            Log::info('STK Push Response', $stkResponse);

            if ($stkResponse['success']) {
                // Update payment with STK details
                $payment->update([
                    'status' => 'processing',
                    'mpesa_response' => $stkResponse
                ]);

                // Update book purchase if exists
                if ($bookPurchase) {
                    $bookPurchase->update([
                        'status' => 'processing',
                        'mpesa_response' => $stkResponse
                    ]);
                }

                return [
                    'success' => true,
                    'message' => 'STK Push sent to your phone. Please complete the payment.',
                    'transaction_id' => $transactionId,
                    'payment_id' => $payment->id,
                    'checkout_request_id' => $stkResponse['checkout_request_id']
                ];
            } else {
                // Mark payment as failed
                $payment->update(['status' => 'failed']);
                if ($bookPurchase) {
                    $bookPurchase->update(['status' => 'failed']);
                }

                return [
                    'success' => false,
                    'message' => $stkResponse['message'] ?? 'Failed to send STK Push',
                    'transaction_id' => $transactionId
                ];
            }

        } catch (\Exception $e) {
            Log::error('PaymentService Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'An unexpected error occurred during payment',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate unique transaction ID
     */
    private function generateTransactionId($type, $itemId)
    {
        return strtoupper($type) . '_' . $itemId . '_' . time() . '_' . Str::random(6);
    }

    /**
     * Get payment description based on type and item
     */
    private function getPaymentDescription($type, $itemId)
    {
        switch ($type) {
            case 'book':
                $book = \App\Models\Book::find($itemId);
                return $book ? "Book: {$book->title}" : 'Book Purchase';
            case 'video':
                return 'Video Purchase';
            case 'course':
                return 'Course Purchase';
            default:
                return 'Payment';
        }
    }

    /**
     * Verify payment status
     */
    public function verifyPayment($paymentId)
    {
        try {
            $payment = Payment::findOrFail($paymentId);
            
            if ($payment->status === 'completed') {
                return [
                    'success' => true,
                    'status' => 'completed',
                    'payment' => $payment
                ];
            }

            // Query M-Pesa for status if we have checkout request ID
            $mpesaResponse = $payment->mpesa_response;
            if (isset($mpesaResponse['checkout_request_id'])) {
                $statusResponse = $this->mpesaService->querySTKStatus(
                    $mpesaResponse['checkout_request_id']
                );

                if ($statusResponse['success']) {
                    $data = $statusResponse['data'];
                    $resultCode = $data['ResultCode'] ?? null;

                    if ($resultCode === '0') {
                        // Payment successful
                        $this->markPaymentAsCompleted($payment, $data);
                        return [
                            'success' => true,
                            'status' => 'completed',
                            'payment' => $payment->fresh()
                        ];
                    } elseif ($resultCode !== null && $resultCode !== '1032') {
                        // Payment failed (1032 means still processing)
                        $this->markPaymentAsFailed($payment);
                        return [
                            'success' => true,
                            'status' => 'failed',
                            'payment' => $payment->fresh()
                        ];
                    }
                }
            }

            return [
                'success' => true,
                'status' => $payment->status,
                'payment' => $payment
            ];

        } catch (\Exception $e) {
            Log::error('Payment verification error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Payment verification failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Mark payment as completed
     */
    private function markPaymentAsCompleted($payment, $mpesaData = null)
    {
        $payment->update([
            'status' => 'completed',
            'paid_at' => now(),
            'mpesa_response' => array_merge($payment->mpesa_response ?? [], $mpesaData ?? [])
        ]);

        // Also update book purchase
        BookPurchase::where('transaction_id', $payment->transaction_id)
            ->update([
                'status' => 'completed',
                'paid_at' => now(),
                'mpesa_response' => $mpesaData
            ]);
    }

    /**
     * Mark payment as failed
     */
    private function markPaymentAsFailed($payment)
    {
        $payment->update(['status' => 'failed']);

        BookPurchase::where('transaction_id', $payment->transaction_id)
            ->update(['status' => 'failed']);
    }
}