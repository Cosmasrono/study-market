<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Customer;
use Exception;

class CardController extends Controller
{
    public function __construct()
    {
        // Set Stripe secret key
        Stripe::setApiKey(env('STRIPE_SECRET'));
    }

    public function initiate(Request $request)
    {
        try {
            // Basic validation
            $this->validateRequest($request);

            // Get the item
            $item = $this->getItem($request);
            if (!$item) {
                return redirect()->back()->with('error', 'Item not found');
            }

            // Convert KES to USD for global payments (you can adjust currency as needed)
            $amountInCents = $this->convertToStripeAmount($item->price, 'usd');

            // Create or get customer
            $customer = $this->createStripeCustomer();

            // Create Payment Intent
            $paymentIntent = PaymentIntent::create([
                'amount' => $amountInCents,
                'currency' => 'usd', // Change to your preferred currency
                'customer' => $customer->id,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'description' => ucfirst($request->type) . ': ' . $item->title,
                'metadata' => [
                    'user_id' => auth()->id(),
                    'item_type' => $request->type,
                    'item_id' => $item->id,
                    'item_title' => $item->title,
                    'original_amount' => $item->price,
                    'original_currency' => 'KES'
                ],
                'receipt_email' => auth()->user()->email,
            ]);

            // Store payment reference
            $this->storePaymentReference($paymentIntent->id, $request, $item, $amountInCents);

            // Return payment page with client secret
            return view('stripe.stripe-checkout', [
                'client_secret' => $paymentIntent->client_secret,
                'publishable_key' => env('STRIPE_PUBLISHABLE'),
                'amount' => $amountInCents / 100, // Convert back to dollars
                'currency' => 'USD',
                'item' => $item,
                'type' => $request->type,
                'payment_intent_id' => $paymentIntent->id
            ]);

        } catch (Exception $e) {
            \Log::error('Stripe payment error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Payment processing failed: ' . $e->getMessage());
        }
    }

    public function confirm(Request $request)
    {
        try {
            $paymentIntentId = $request->payment_intent_id;
            
            // Retrieve the payment intent
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
            
            if ($paymentIntent->status === 'succeeded') {
                // Update payment reference
                \DB::table('payment_references')
                    ->where('payment_intent_id', $paymentIntentId)
                    ->update([
                        'status' => 'completed',
                        'transaction_id' => $paymentIntent->id,
                        'updated_at' => now()
                    ]);

                // Get payment reference details
                $paymentRef = \DB::table('payment_references')
                    ->where('payment_intent_id', $paymentIntentId)
                    ->first();

                if ($paymentRef) {
                    // Create purchase record (optional)
                    $this->createPurchaseRecord($paymentRef);

                    return redirect()->route($paymentRef->item_type === 'book' ? 'books' : 'videos')
                        ->with('success', 'Payment successful! You can now access your ' . $paymentRef->item_type . '. Transaction ID: ' . $paymentIntent->id);
                }
            }

            return redirect()->back()->with('error', 'Payment was not successful');

        } catch (Exception $e) {
            \Log::error('Stripe confirmation error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Payment confirmation failed');
        }
    }

    public function webhook(Request $request)
    {
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            \Log::error('Invalid payload from Stripe webhook');
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            \Log::error('Invalid signature from Stripe webhook');
            return response('Invalid signature', 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                $this->handleSuccessfulPayment($paymentIntent);
                break;
            
            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object;
                $this->handleFailedPayment($paymentIntent);
                break;
            
            default:
                \Log::info('Received unknown event type: ' . $event->type);
        }

        return response('Webhook handled', 200);
    }

    private function createStripeCustomer()
    {
        $user = auth()->user();
        
        // Check if customer already exists
        $existingCustomer = \DB::table('stripe_customers')
            ->where('user_id', $user->id)
            ->first();

        if ($existingCustomer) {
            return Customer::retrieve($existingCustomer->stripe_customer_id);
        }

        // Create new customer
        $customer = Customer::create([
            'email' => $user->email,
            'name' => $user->name,
            'description' => 'Customer for Inzoberi School',
            'metadata' => [
                'user_id' => $user->id
            ]
        ]);

        // Store customer ID
        \DB::table('stripe_customers')->insert([
            'user_id' => $user->id,
            'stripe_customer_id' => $customer->id,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return $customer;
    }



    // private function convertToStripeAmount($amount, $currency = 'usd')
    // {
    //     // Convert KES to USD (approximate rate - you should use a real currency API)
    //     $exchangeRate = 0.0077; // 1 KES = 0.0077 USD (update this with real rates)
        
    //     if ($currency === 'usd') {
    //         $convertedAmount = $amount * $exchangeRate;
    //         return round($convertedAmount * 100); // Stripe uses cents
    //     }
        
    //     // For other currencies, implement accordingly
    //     return round($amount * 100);
    // }


    private function convertToStripeAmount($amount, $currency = 'usd')
{
    // Convert KES to USD (approximate rate - you should use a real currency API)
    $exchangeRate = 0.0077; // 1 KES = 0.0077 USD
    
    if ($currency === 'usd') {
        $convertedAmount = $amount * $exchangeRate;
        
        // Ensure minimum Stripe amount ($0.50 USD)
        if ($convertedAmount < 0.50) {
            $convertedAmount = 0.50; // Set to minimum allowed
        }
        
        return round($convertedAmount * 100); // Stripe uses cents
    }
    
    // For other currencies, implement accordingly
    return round($amount * 100);
}



    private function storePaymentReference($paymentIntentId, $request, $item, $amount)
    {
        \DB::table('payment_references')->insert([
            'payment_intent_id' => $paymentIntentId,
            'user_id' => auth()->id(),
            'item_type' => $request->type,
            'item_id' => $item->id,
            'amount' => $amount / 100, // Convert back from cents
            'original_amount' => $item->price,
            'currency' => 'USD',
            'original_currency' => 'KES',
            'status' => 'pending',
            'payment_method' => 'stripe_card',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    private function handleSuccessfulPayment($paymentIntent)
    {
        \DB::table('payment_references')
            ->where('payment_intent_id', $paymentIntent->id)
            ->update([
                'status' => 'completed',
                'transaction_id' => $paymentIntent->id,
                'updated_at' => now()
            ]);

        // Additional logic for successful payment
        \Log::info('Payment succeeded via webhook: ' . $paymentIntent->id);
    }

    private function handleFailedPayment($paymentIntent)
    {
        \DB::table('payment_references')
            ->where('payment_intent_id', $paymentIntent->id)
            ->update([
                'status' => 'failed',
                'updated_at' => now()
            ]);

        \Log::error('Payment failed via webhook: ' . $paymentIntent->id);
    }

    private function createPurchaseRecord($paymentRef)
    {
        try {
            // Retrieve the item to get its title
            $item = $paymentRef->item_type === 'book' 
                ? \App\Models\Book::find($paymentRef->item_id)
                : \App\Models\Video::find($paymentRef->item_id);

            \DB::table('purchases')->insert([
                'user_id' => $paymentRef->user_id,
                'item_type' => $paymentRef->item_type,
                'item_id' => $paymentRef->item_id,
                'item_title' => $item ? $item->title : null,
                'amount' => $paymentRef->amount,
                'payment_method' => 'stripe_card',
                'transaction_id' => $paymentRef->transaction_id,
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (Exception $e) {
            \Log::error('Failed to create purchase record: ' . $e->getMessage());
        }
    }

    private function validateRequest($request)
    {
        if (empty($request->cardholder_name)) {
            throw new Exception('Cardholder name is required');
        }
        if (empty($request->type) || !in_array($request->type, ['book', 'video'])) {
            throw new Exception('Invalid item type');
        }
        if (empty($request->id)) {
            throw new Exception('Item ID is required');
        }
    }

    private function getItem($request)
    {
        if ($request->type === 'book') {
            return \App\Models\Book::find($request->id);
        } else {
            return \App\Models\Video::find($request->id);
        }
    }
}