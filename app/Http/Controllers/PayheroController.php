<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\MembershipPayment;
use App\Services\PayheroService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PayheroController extends Controller
{
    protected $payheroService;

    public function __construct(PayheroService $payheroService)
    {
        $this->payheroService = $payheroService;
    }

    /**
     * Handle Payhero payment callback
     */
public function callback(Request $request)
{
     
    Log::info('=== PayHero Webhook Received ===', [
        'timestamp' => now()->toDateTimeString(),
        'ip' => $request->ip(),
        'method' => $request->method(),
        'all_data' => $request->all(),
        'raw_content' => $request->getContent(),
    ]);

    try {
        // Get callback data
        $data = $request->all();

        // Validate required fields
        if (!isset($data['external_reference'])) {
            Log::error('PayHero Callback Missing external_reference', ['data' => $data]);
            return response()->json([
                'status' => 'error',
                'message' => 'Missing external_reference'
            ], 400);
        }

        $reference = $data['external_reference'];
        $status = strtoupper($data['status'] ?? 'UNKNOWN');

        Log::info('Processing PayHero Callback', [
            'reference' => $reference,
            'status' => $status,
            'payhero_reference' => $data['reference'] ?? null,
            'full_data' => $data
        ]);

        // Find the membership payment
        $membershipPayment = MembershipPayment::where('reference', $reference)
            ->orWhere('transaction_reference', $reference)
            ->first();

        if (!$membershipPayment) {
            Log::error('PayHero Callback - Payment Not Found', [
                'reference' => $reference,
                'status' => $status
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Payment record not found'
            ], 404);
        }

        Log::info('Payment Record Found', [
            'payment_id' => $membershipPayment->id,
            'user_id' => $membershipPayment->user_id,
            'current_status' => $membershipPayment->status
        ]);

        // Start database transaction
        DB::beginTransaction();

        try {
            // Update payment based on status
            if (in_array($status, ['COMPLETED', 'SUCCESS', 'PAID'])) {
                
                $membershipPayment->update([
                    'status' => 'completed',
                    'paid_at' => now(),
                    'mpesa_receipt_number' => $data['reference'] ?? $data['transaction_id'] ?? null,
                    'payhero_reference' => $data['reference'] ?? null,
                    'phone_number' => $data['phone_number'] ?? $membershipPayment->phone_number,
                    'customer_name' => $data['customer_name'] ?? null,
                ]);

                // Activate user membership
                $user = $membershipPayment->user;
                
                if (!$user) {
                    throw new \Exception('User not found for payment');
                }

                // Activate membership using your User model method
                $user->activateMembership(
                    $membershipPayment->subscription_duration ?? '1_year',
                    $membershipPayment->amount
                );

                // Additional user updates
                $user->update([
                    'membership_fee_paid' => $membershipPayment->amount,
                    'email_verified_at' => $user->email_verified_at ?? now()
                ]);

                Log::info('Membership Activated via PayHero Webhook', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'payment_id' => $membershipPayment->id,
                    'amount' => $membershipPayment->amount,
                    'expires_at' => $user->subscription_end_date?->toDateTimeString(),
                    'subscription_type' => $user->current_subscription_type
                ]);

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Payment processed and membership activated successfully'
                ], 200);

            } elseif (in_array($status, ['FAILED', 'CANCELLED', 'EXPIRED'])) {
                
                $membershipPayment->update([
                    'status' => 'failed',
                    'failure_reason' => $data['message'] ?? $data['reason'] ?? 'Payment failed or cancelled'
                ]);

                Log::warning('Payment Failed via PayHero Webhook', [
                    'payment_id' => $membershipPayment->id,
                    'reference' => $reference,
                    'status' => $status,
                    'reason' => $data['message'] ?? 'Unknown'
                ]);

                DB::commit();

                return response()->json([
                    'status' => 'failed',
                    'message' => 'Payment failed'
                ], 200);

            } else {
                // For PENDING, QUEUED, or other intermediate statuses
                Log::info('PayHero Callback - Intermediate Status', [
                    'reference' => $reference,
                    'status' => $status
                ]);

                DB::rollBack();

                return response()->json([
                    'status' => 'acknowledged',
                    'message' => 'Webhook received, payment processing'
                ], 200);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

    } catch (\Exception $e) {
        Log::error('PayHero Callback Exception', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
            'request_data' => $request->all()
        ]);

        return response()->json([
            'status' => 'error',
            'message' => 'Internal server error: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Check payment status (AJAX endpoint)
     */
    public function checkStatus($reference)
    {
        try {
            // Find the payment record
            $membershipPayment = MembershipPayment::where('transaction_reference', $reference)
                ->with('user')
                ->firstOrFail();

            // Check if payment is already confirmed
            if ($membershipPayment->status === 'paid') {
                return response()->json([
                    'status' => 'completed',
                    'message' => 'Payment verified successfully',
                    'payment_status' => 'paid'
                ]);
            }

            // Query Payhero API for latest status
            $statusResponse = $this->payheroService->checkPaymentStatus($reference);

            // If payment is now successful, update database
            if ($statusResponse['status'] === 'success') {
                DB::beginTransaction();

                $membershipPayment->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'mpesa_receipt_number' => $statusResponse['mpesa_receipt_number'] ?? null
                ]);

                $user = $membershipPayment->user;
                $user->activateMembership(
                    $membershipPayment->subscription_duration,
                    $membershipPayment->amount
                );

                $user->update([
                    'payment_verified' => true,
                    'is_active' => true,
                    'email_verified_at' => $user->email_verified_at ?? now()
                ]);

                DB::commit();

                return response()->json([
                    'status' => 'completed',
                    'message' => 'Payment verified successfully',
                    'payment_status' => 'paid'
                ]);
            }

            // Payment still pending or failed
            return response()->json([
                'status' => $membershipPayment->status,
                'message' => 'Payment is still being processed',
                'payment_status' => $membershipPayment->status
            ]);

        } catch (\Exception $e) {
            Log::error('Payment Status Check Failed', [
                'reference' => $reference,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Unable to check payment status'
            ], 500);
        }
    }

    /**
     * Complete payment and log user in - ONLY if payment is verified
     */
    public function completePayment(Request $request)
    {
        try {
            $transactionReference = $request->input('transaction_reference');
            
            $membershipPayment = MembershipPayment::where('transaction_reference', $transactionReference)
                ->with('user')
                ->firstOrFail();

            $user = $membershipPayment->user;

            // CRITICAL: Only allow login if payment is verified
            if ($membershipPayment->status === 'paid' && $user->payment_verified) {
                
                // Log the user in
                Auth::login($user);

                // Regenerate session for security
                $request->session()->regenerate();

                Log::info('User logged in after successful payment', [
                    'user_id' => $user->id,
                    'payment_id' => $membershipPayment->id
                ]);

                return redirect()->route('user.dashboard')
                    ->with('success', 'Welcome! Your membership has been activated successfully.');
                    
            } elseif ($membershipPayment->status === 'pending') {
                
                // Payment still pending - redirect back to wait page
                return redirect()->route('payment.confirmation', ['reference' => $transactionReference])
                    ->with('warning', 'Payment is still being processed. Please wait.');
                    
            } else {
                
                // Payment failed or not verified
                return redirect()->route('membership.payment')
                    ->with('error', 'Payment verification failed. Please try again or contact support.');
            }

        } catch (\Exception $e) {
            Log::error('Payment Completion Failed', [
                'error' => $e->getMessage(),
                'transaction_reference' => $request->input('transaction_reference')
            ]);

            return redirect()->route('register')
                ->with('error', 'Unable to complete registration. Please contact support.');
        }
    }

    /**
     * Show payment confirmation page
     */
    public function showConfirmation($reference)
    {
        try {
            $membershipPayment = MembershipPayment::where('transaction_reference', $reference)
                ->with('user')
                ->firstOrFail();

            // If already paid, redirect to complete payment
            if ($membershipPayment->status === 'paid') {
                return redirect()->route('complete.payment', ['transaction_reference' => $reference]);
            }

            return view('payment.confirmation', [
                'payment' => $membershipPayment,
                'reference' => $reference,
                'phone_number' => $membershipPayment->user->phone_number
            ]);

        } catch (\Exception $e) {
            Log::error('Show confirmation page failed', [
                'reference' => $reference,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('register')
                ->with('error', 'Payment session not found. Please try again.');
        }
    }
}