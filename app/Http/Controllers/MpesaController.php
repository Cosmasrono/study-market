<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Video;
use App\Models\Transaction;
use App\Services\MpesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class MpesaController extends Controller
{
    protected $mpesaService;

    public function __construct(MpesaService $mpesaService)
    {
        $this->mpesaService = $mpesaService;
    }

    /**
     * Show payment form for books
     */
/**
 * Show payment form for books
 */
public function showPaymentForm(Book $book)  // Changed from $bookId to Book $book
{
    try {
        // $book is already the Book model, no need for findOrFail
        return view('mpesa.payment', compact('book'));
    } catch (\Exception $e) {
        Log::error('Book payment form error: ' . $e->getMessage());
        return redirect()->route('books')->with('error', 'Book not found.');  // Changed to 'books'
    }
}

    /**
     * Show payment form for videos
     */
    public function showVideoPaymentForm($videoId)
    {
        try {
            $video = Video::findOrFail($videoId);

            if (!$video->is_active || $video->is_free) {
                return redirect()->route('videos.index')
                    ->with('error', 'This video is not available for purchase.');
            }

            return view('mpesa.payment', compact('video'));
        } catch (\Exception $e) {
            Log::error('Video payment form error: ' . $e->getMessage());
            return redirect()->route('videos.index')->with('error', 'Video not found.');
        }
    }

    /**
     * Process payment for both books and videos
     */
public function initiatePayment(Request $request)
{
    Log::info('=== PAYMENT FLOW START ===');
    Log::info('Payment initiation attempt', [
        'user_id' => auth()->id(),
        'type' => $request->input('type', 'book'),
        'content_id' => $request->input('id')
    ]);
 
    // Determine content type
    $type = $request->input('type', 'book');
    $model = $type === 'video' ? Video::class : Book::class;
     
    Log::info('Step 1: Content type determined', ['type' => $type, 'model' => $model]);
 
    // Validation rules
    $validator = Validator::make($request->all(), [
        'id' => 'required|integer|exists:' . ($type === 'video' ? 'videos' : 'books') . ',id',
        'phone' => [
            'required',
            'string',
            'regex:/^(0|254)\d{9}$/',
            function ($attribute, $value, $fail) {
                if (!$this->mpesaService->isValidPhoneNumber($value)) {
                    $fail('Please enter a valid Kenyan phone number');
                }
            }
        ],
        'price' => 'required|numeric|min:1|max:70000',
        'title' => 'required|string|max:255'
    ]);
 
    Log::info('Step 2: Validation created');
 
    if ($validator->fails()) {
        Log::error('Validation failed', $validator->errors()->toArray());
        return back()->withErrors($validator)->withInput();
    }
 
    Log::info('Step 3: Validation passed');
 
    try {
        // Get the content
        $content = $model::findOrFail($request->id);
        Log::info('Step 4: Content found', ['content_id' => $content->id, 'price' => $content->price]);
                     
        // Verify price matches
        if ((float)$request->price !== (float)$content->price) {
            Log::error('Price mismatch', ['request_price' => $request->price, 'content_price' => $content->price]);
            return back()->withErrors(['price' => 'Price mismatch'])->withInput();
        }
             
        Log::info('Step 5: Price verified');
             
        // Check if user already has access
        $hasAccess = $this->userHasAccess(auth()->id(), $type, $content->id);
        Log::info('Step 6: Access check', ['has_access' => $hasAccess]);
                     
        if ($hasAccess) {
           $redirectRoute = $type === 'book' ? 'books' : 'videos';
           return redirect()->route($redirectRoute)->with('success', ucfirst($type) . ' already purchased!');
        }
             
        $phone = $this->mpesaService->formatPhoneNumber($request->phone);
        Log::info('Step 7: Phone formatted', ['phone' => $phone]);
                     
        Log::info('Step 8: About to initiate STK Push');
                     
        // Initiate STK Push
        $result = $this->mpesaService->stkPush(
            $phone,
            $content->price,
            strtoupper($type) . '_' . $content->id . '_' . time(),
            'Payment for ' . $content->title
        );
             
        Log::info('Step 9: STK Push completed', $result);

        // ADD THIS PART AFTER STEP 9:
        if ($result['success']) {
            // Create transaction record
            $transaction = Transaction::create([
                'user_id' => auth()->id(),
                'checkout_request_id' => $result['checkout_request_id'],
                'merchant_request_id' => $result['merchant_request_id'] ?? null,
                'content_type' => $type,
                'content_id' => $content->id,
                'phone' => $phone,
                'amount' => $content->price,
                'status' => Transaction::STATUS_PENDING,
                'response_data' => $result
            ]);

            Log::info('Step 10: Transaction created', [
                'transaction_id' => $transaction->id,
                'checkout_request_id' => $result['checkout_request_id']
            ]);

            // Redirect to your existing status page
            return redirect()->route('mpesa.status', $result['checkout_request_id']);

        } else {
            Log::error('STK Push failed', $result);
            return back()->withErrors(['payment' => $result['message'] ?? 'Failed to initiate payment'])->withInput();
        }
             
    } catch (\Exception $e) {
        Log::error('Payment processing failed', [
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
            'trace' => $e->getTraceAsString()
        ]);
                     
        return back()->withErrors(['payment' => 'Payment failed: ' . $e->getMessage()])->withInput();
    }
}



/**
     * Handle M-Pesa callback for membership payments
     */
    public function membershipCallback(Request $request)
    {
        Log::info('M-Pesa membership callback received', [
            'headers' => $request->headers->all(),
            'body' => $request->all()
        ]);

        try {
            $requestData = $request->all();
            
            if (!isset($requestData['Body']['stkCallback'])) {
                return response()->json(['ResponseCode' => '1', 'ResponseDesc' => 'Invalid callback']);
            }

            $stk_callback = $requestData['Body']['stkCallback'];
            $checkout_request_id = $stk_callback['CheckoutRequestID'] ?? null;
            $result_code = $stk_callback['ResultCode'] ?? null;

            if ($checkout_request_id) {
                $this->updateMembershipTransactionFromCallback($checkout_request_id, $result_code, $stk_callback);
            }

            return response()->json(['ResponseCode' => '0', 'ResponseDesc' => 'Success']);

        } catch (\Exception $e) {
            Log::error('Membership callback error: ' . $e->getMessage());
            return response()->json(['ResponseCode' => '1', 'ResponseDesc' => 'Error']);
        }
    }

    /**
     * Update membership payment status from callback
     */
    private function updateMembershipTransactionFromCallback($checkout_request_id, $result_code, $callback_data)
    {
        try {
            $payment = \App\Models\MembershipPayment::where('reference_id', $checkout_request_id)->first();
            
            if (!$payment) {
                Log::error('Membership payment not found for callback', ['checkout_request_id' => $checkout_request_id]);
                return;
            }

            if ($result_code == 0) {
                // Payment successful
                $callbackMetadata = $callback_data['CallbackMetadata']['Item'] ?? [];
                $mpesaReceiptNumber = null;
                $transactionDate = null;
                $paidAmount = null;
                $paidPhoneNumber = null;

                foreach ($callbackMetadata as $item) {
                    switch ($item['Name']) {
                        case 'MpesaReceiptNumber':
                            $mpesaReceiptNumber = $item['Value'];
                            break;
                        case 'TransactionDate':
                            $transactionDate = $item['Value'];
                            break;
                        case 'Amount':
                            $paidAmount = $item['Value'];
                            break;
                        case 'PhoneNumber':
                            $paidPhoneNumber = $item['Value'];
                            break;
                    }
                }

                // Update payment record
                $payment->update([
                    'status' => 'completed',
                    'paid_at' => now(),
                    'payment_data' => array_merge($payment->payment_data ?? [], [
                        'mpesa_receipt_number' => $mpesaReceiptNumber,
                        'transaction_date' => $transactionDate,
                        'paid_amount' => $paidAmount,
                        'paid_phone_number' => $paidPhoneNumber,
                        'callback_data' => $callback_data
                    ])
                ]);

                // Activate user membership
                $payment->user->activateMembership();

                Log::info('Membership payment completed successfully', [
                    'payment_id' => $payment->id,
                    'user_id' => $payment->user_id,
                    'mpesa_receipt' => $mpesaReceiptNumber
                ]);

            } else {
                // Payment failed
                $payment->update([
                    'status' => 'failed',
                    'failure_reason' => $callback_data['ResultDesc'] ?? 'Payment failed'
                ]);

                Log::warning('Membership payment failed', [
                    'payment_id' => $payment->id,
                    'result_code' => $result_code,
                    'result_desc' => $callback_data['ResultDesc']
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to update membership payment from callback: ' . $e->getMessage());
        }
    }
    /**
     * Show payment status page
     */
    // public function showPaymentStatus($checkout_request_id)
    // {
    //     $transaction = Transaction::where('checkout_request_id', $checkout_request_id)->first();
        
    //     if (!$transaction) {
    //         return redirect()->route('home')->with('error', 'Transaction not found');
    //     }
        
    //     $content = $transaction->content;
        
    //     return view('mpesa.payment-status', compact('transaction', 'content', 'checkout_request_id'));
    // }


    public function showPaymentStatus($checkout_request_id)
{
    $transaction = Transaction::where('checkout_request_id', $checkout_request_id)->first();
    
    if (!$transaction) {
        return redirect()->route('home')->with('error', 'Transaction not found');
    }
    
    // Get content and set variables for your existing blade template
    $content = null;
    $book = null; // Your blade template expects this
    $type = $transaction->content_type;
    
    if ($transaction->content_type === 'book') {
        $content = Book::find($transaction->content_id);
        $book = $content; // For your blade compatibility
    } elseif ($transaction->content_type === 'video') {
        $content = Video::find($transaction->content_id);
    }
    
    // Use your existing view
    return view('mpesa.status', compact('transaction', 'content', 'book', 'type', 'checkout_request_id'));
}

    /**
     * Check payment status via AJAX
     */
    public function checkPaymentStatus($checkout_request_id)
    {
        try {
            $transaction = Transaction::where('checkout_request_id', $checkout_request_id)->first();
            
            if (!$transaction) {
                return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
            }

            // If still pending after 2 minutes, query M-Pesa directly
            if ($transaction->status === 'pending' && 
                $transaction->created_at->diffInMinutes(now()) >= 2) {
                
                $mpesaResult = $this->mpesaService->querySTKStatus($checkout_request_id);
                
                if ($mpesaResult['success']) {
                    $newStatus = $mpesaResult['status'] === 'completed' ? 'paid' : 'failed';
                    
                    $transaction->update([
                        'status' => $newStatus,
                        'completed_at' => $newStatus === 'paid' ? now() : null,
                        'response_data' => $mpesaResult
                    ]);
                }
            }

            $transaction->refresh();

            return response()->json([
                'success' => true,
                'status' => $transaction->status,
                'mpesa_receipt' => $transaction->mpesa_receipt_number,
                'amount' => $transaction->amount,
                'completed_at' => $transaction->completed_at
            ]);

        } catch (\Exception $e) {
            Log::error('Status check error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Status check failed'], 500);
        }
    }

    /**
     * Handle M-Pesa callback
     */
    public function callback(Request $request)
    {
        Log::info('M-Pesa Callback received', $request->all());

        try {
            $requestData = $request->all();
            
            if (!isset($requestData['Body']['stkCallback'])) {
                return response()->json(['ResponseCode' => '1', 'ResponseDesc' => 'Invalid callback']);
            }

            $stk_callback = $requestData['Body']['stkCallback'];
            $checkout_request_id = $stk_callback['CheckoutRequestID'] ?? null;
            $result_code = $stk_callback['ResultCode'] ?? null;

            if ($checkout_request_id) {
                $this->updateTransactionFromCallback($checkout_request_id, $result_code, $stk_callback);
            }

            return response()->json(['ResponseCode' => '0', 'ResponseDesc' => 'Success']);

        } catch (\Exception $e) {
            Log::error('Callback error: ' . $e->getMessage());
            return response()->json(['ResponseCode' => '1', 'ResponseDesc' => 'Error']);
        }
    }

    /**
     * Update transaction status from callback
     */
/**
 * Update transaction status from callback
 */
private function updateTransactionFromCallback($checkout_request_id, $result_code, $callback_data)
{
    try {
        $transaction = Transaction::where('checkout_request_id', $checkout_request_id)->first();
        
        if (!$transaction) {
            Log::error('Transaction not found for callback', ['checkout_request_id' => $checkout_request_id]);
            return;
        }

        $status = $result_code == 0 ? 'paid' : 'failed';
        $mpesa_receipt = null;
        
        // Extract M-Pesa receipt for successful payments
        if ($result_code == 0 && isset($callback_data['CallbackMetadata']['Item'])) {
            foreach ($callback_data['CallbackMetadata']['Item'] as $item) {
                if ($item['Name'] === 'MpesaReceiptNumber') {
                    $mpesa_receipt = $item['Value'];
                    break;
                }
            }
        }

        $transaction->update([
            'status' => $status,
            'mpesa_receipt_number' => $mpesa_receipt,
            'response_data' => $callback_data,
            'completed_at' => $status === 'paid' ? now() : null
        ]);

        // Create a session flash message for successful payment
        if ($status === 'paid') {
            session()->flash('payment_success', [
                'checkout_request_id' => $checkout_request_id,
                'mpesa_receipt' => $mpesa_receipt,
                'content_type' => $transaction->content_type,
                'content_id' => $transaction->content_id,
                'amount' => $transaction->amount
            ]);
        }

        Log::info('Transaction updated from callback', [
            'transaction_id' => $transaction->id,
            'status' => $status,
            'mpesa_receipt' => $mpesa_receipt
        ]);

    } catch (\Exception $e) {
        Log::error('Failed to update transaction from callback: ' . $e->getMessage());
    }
}

    /**
     * Check if user has access to content
     */
    private function userHasAccess($userId, $contentType, $contentId)
    {
        return Transaction::where('user_id', $userId)
            ->where('content_type', $contentType)
            ->where('content_id', $contentId)
            ->where('status', 'paid')
            ->exists();
    }
}