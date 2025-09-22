@extends('layouts.app')

@section('title', 'Complete Payment')

@section('content')
<div class="container mx-auto px-4">
    <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Complete Your Payment</h1>
            <p class="text-gray-600 mt-2">You're one step away from membership!</p>
        </div>

        <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-lg p-4 mb-6">
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-700">Membership Fee:</span>
                    <span class="font-bold text-green-600">KES {{ number_format($payment->amount) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-700">Phone Number:</span>
                    <span class="font-semibold">{{ $phone_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-700">Reference:</span>
                    <span class="font-mono text-xs">{{ $payment->transaction_id }}</span>
                </div>
            </div>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>Instructions:</strong><br>
                        1. Check your phone for M-Pesa payment request<br>
                        2. Enter your M-Pesa PIN to authorize payment<br>
                        3. Click "Complete Registration" once payment is sent
                    </p>
                </div>
            </div>
        </div>

        <!-- Payment Status -->
        <div id="payment-status" class="mb-6">
            <div class="flex items-center justify-center p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-yellow-600 mr-3"></div>
                <span class="text-yellow-700 font-medium">Waiting for payment confirmation...</span>
            </div>
        </div>

        <div class="space-y-4">
            <!-- Confirm Payment Form (shown after simulation) -->
            <form method="POST" action="{{ route('confirm.payment') }}" id="confirm-form" style="display: none;">
                @csrf
                <input type="hidden" name="transaction_id" value="{{ $payment->transaction_id }}">
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-green-600 to-blue-600 text-white py-3 rounded-md hover:from-green-700 hover:to-blue-700 transition duration-300 font-semibold">
                    Complete Registration & Login
                </button>
            </form>

            <!-- Demo Simulation Button -->
            <button id="simulate-payment" 
                    class="w-full bg-green-600 text-white py-3 rounded-md hover:bg-green-700 transition duration-300 font-semibold">
                🔄 Simulate M-Pesa Payment (Demo)
            </button>

            <!-- Start Over -->
            <a href="{{ route('register') }}" 
               class="block text-center w-full bg-gray-100 text-gray-700 py-3 rounded-md hover:bg-gray-200 transition duration-300">
               ← Start Over
            </a>
        </div>

        <div class="text-center mt-6">
            <p class="text-xs text-gray-500">
                🔒 Your payment is secure and encrypted
            </p>
        </div>
    </div>
</div>

<script>
let statusCheckInterval;

// Auto-check payment status every 10 seconds
function startStatusChecking() {
    let attempts = 0;
    const maxAttempts = 30; // 5 minutes total
    
    statusCheckInterval = setInterval(() => {
        attempts++;
        
        if (attempts >= maxAttempts) {
            clearInterval(statusCheckInterval);
            showTimeoutMessage();
            return;
        }
        
        fetch(`{{ route('membership.payment.status', $payment->transaction_id) }}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'completed') {
                    clearInterval(statusCheckInterval);
                    showPaymentSuccess();
                }
            })
            .catch(error => {
                console.log('Status check failed:', error);
            });
    }, 10000); // Check every 10 seconds
}

function showPaymentSuccess() {
    document.getElementById('payment-status').innerHTML = `
        <div class="flex items-center justify-center p-4 bg-green-50 border border-green-200 rounded-lg">
            <svg class="h-5 w-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span class="text-green-700 font-semibold">✅ Payment received successfully!</span>
        </div>
    `;
    document.getElementById('confirm-form').style.display = 'block';
    document.getElementById('simulate-payment').style.display = 'none';
}

function showTimeoutMessage() {
    document.getElementById('payment-status').innerHTML = `
        <div class="flex items-center justify-center p-4 bg-orange-50 border border-orange-200 rounded-lg">
            <svg class="h-5 w-5 text-orange-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.664-.833-2.464 0L5.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
            <span class="text-orange-700 font-medium">Payment check timeout. Try confirming manually.</span>
        </div>
    `;
    document.getElementById('confirm-form').style.display = 'block';
}

// Demo simulation
document.getElementById('simulate-payment').addEventListener('click', function() {
    this.disabled = true;
    this.innerHTML = '🔄 Processing Payment...';
    
    setTimeout(() => {
        showPaymentSuccess();
    }, 3000);
});

// Start checking payment status automatically (uncomment for production)
// startStatusChecking();
</script>
@endsection