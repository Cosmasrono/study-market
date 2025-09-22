@extends('layouts.app')

@section('title', 'Payment Confirmation')

@section('content')
<div class="container mx-auto px-4">
    <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Payment Confirmation</h1>
            <p class="text-gray-600 mt-2">Please confirm your M-Pesa payment</p>
        </div>

        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Amount:</span>
                    <span class="font-semibold">KES {{ number_format($payment->amount) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Phone Number:</span>
                    <span class="font-semibold">{{ $phone_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Transaction ID:</span>
                    <span class="font-mono text-xs">{{ $payment->transaction_id }}</span>
                </div>
            </div>
        </div>

        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>Next Steps:</strong>
                        <br>1. Check your phone for M-Pesa payment prompt
                        <br>2. Enter your M-Pesa PIN to complete payment
                        <br>3. Click "Confirm Payment" below after paying
                    </p>
                </div>
            </div>
        </div>

        <!-- Simulated M-Pesa Payment Status -->
        <div id="payment-status" class="hidden mb-6">
            <div class="bg-green-50 border-l-4 border-green-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">
                            <strong>Payment Received!</strong> You can now confirm your payment.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <!-- Simulate Payment Button (for demo) -->
            <button id="simulate-payment" 
                    class="w-full bg-green-600 text-white py-3 rounded-md hover:bg-green-700 transition duration-300">
                Simulate M-Pesa Payment (Demo)
            </button>

            <!-- Confirm Payment Form -->
            <form method="POST" action="{{ route('confirm.payment') }}" id="confirm-form" style="display: none;">
                @csrf
                <input type="hidden" name="transaction_id" value="{{ $payment->transaction_id }}">
                <button type="submit" 
                        class="w-full bg-blue-600 text-white py-3 rounded-md hover:bg-blue-700 transition duration-300">
                    Confirm Payment & Complete Registration
                </button>
            </form>

            <!-- Cancel Registration -->
            <a href="{{ route('register') }}" 
               class="block text-center w-full bg-gray-200 text-gray-800 py-3 rounded-md hover:bg-gray-300 transition duration-300">
                Cancel & Try Again
            </a>
        </div>

        <div class="text-center mt-6">
            <p class="text-xs text-gray-500">
                Having issues? Contact our support team for assistance.
            </p>
        </div>
    </div>
</div>

<script>
document.getElementById('simulate-payment').addEventListener('click', function() {
    // Simulate M-Pesa payment delay
    this.disabled = true;
    this.textContent = 'Processing Payment...';
    
    setTimeout(() => {
        document.getElementById('payment-status').classList.remove('hidden');
        document.getElementById('confirm-form').style.display = 'block';
        this.style.display = 'none';
    }, 3000); // 3 second delay to simulate payment processing
});
</script>
@endsection