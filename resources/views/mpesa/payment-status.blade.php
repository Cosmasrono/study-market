{{-- resources/views/mpesa/payment-status.blade.php --}}
@extends('layouts.app')

@section('title', 'Payment Status')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto bg-white shadow-lg rounded-lg p-6">
        {{-- Header --}}
        <div class="text-center mb-6">
            <div class="w-16 h-16 mx-auto mb-4 bg-blue-100 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">Payment Status</h2>
            <p class="text-gray-600 mt-2">Please check your phone for M-Pesa prompt</p>
        </div>

        {{-- Content Info --}}
        <div class="bg-gray-50 p-4 rounded-lg mb-6">
            <div class="flex justify-between items-center mb-2">
                <span class="text-gray-600">{{ ucfirst($transaction->content_type) }}:</span>
                <span class="font-medium">{{ $content->title }}</span>
            </div>
            <div class="flex justify-between items-center mb-2">
                <span class="text-gray-600">Amount:</span>
                <span class="font-bold text-blue-600">KES {{ number_format($transaction->amount, 2) }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Phone:</span>
                <span class="font-medium">{{ substr($transaction->phone, 0, 6) }}***{{ substr($transaction->phone, -3) }}</span>
            </div>
        </div>

        {{-- Status Display --}}
        <div id="statusContainer" class="text-center mb-6">
            <div id="loadingStatus" class="flex flex-col items-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
                <p class="text-blue-600 font-medium">Waiting for payment confirmation...</p>
                <p class="text-gray-500 text-sm mt-2">This may take up to 2 minutes</p>
            </div>
            
            <div id="successStatus" class="hidden">
                <div class="w-16 h-16 mx-auto mb-4 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-green-600 mb-2">Payment Successful!</h3>
                <p id="receiptNumber" class="text-gray-600 mb-4"></p>
                <p class="text-sm text-gray-500">Redirecting to your content...</p>
            </div>
            
            <div id="failedStatus" class="hidden">
                <div class="w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-red-600 mb-2">Payment Failed</h3>
                <p class="text-gray-600 mb-4">The payment was not completed. Please try again.</p>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="space-y-3">
            <button id="checkStatusBtn" 
                    onclick="checkPaymentStatus()" 
                    class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                Check Status
            </button>
            
            <button id="retryPaymentBtn" 
                    onclick="retryPayment()" 
                    class="w-full bg-orange-600 text-white py-3 px-4 rounded-lg hover:bg-orange-700 transition-colors hidden">
                Try Again
            </button>
            
            <a href="{{ route($transaction->content_type === 'book' ? 'books.index' : 'videos') }}" 
               class="block w-full text-center bg-gray-500 text-white py-3 px-4 rounded-lg hover:bg-gray-600 transition-colors">
                Back to {{ ucfirst($transaction->content_type) }}s
            </a>
        </div>

        {{-- Instructions --}}
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h4 class="font-bold text-blue-800 mb-2">Instructions:</h4>
            <ol class="text-blue-700 text-sm space-y-1">
                <li>1. Check your phone for M-Pesa prompt</li>
                <li>2. Enter your M-Pesa PIN when prompted</li>
                <li>3. Wait for confirmation message</li>
                <li>4. Payment status will update automatically</li>
            </ol>
        </div>
    </div>
</div>

<script>
const checkoutRequestId = '{{ $checkout_request_id }}';
const contentType = '{{ $transaction->content_type }}';
let statusCheckInterval;
let checkCount = 0;
const maxChecks = 40; // Check for up to 5 minutes (40 * 7.5 seconds)

// Start checking status immediately
document.addEventListener('DOMContentLoaded', function() {
    checkPaymentStatus();
    
    // Start auto-refresh every 7.5 seconds
    statusCheckInterval = setInterval(function() {
        if (checkCount < maxChecks) {
            checkPaymentStatus();
        } else {
            clearInterval(statusCheckInterval);
            showTimeoutMessage();
        }
    }, 7500);
});

function checkPaymentStatus() {
    checkCount++;
    
    // Update button state
    const checkBtn = document.getElementById('checkStatusBtn');
    checkBtn.disabled = true;
    checkBtn.innerHTML = 'Checking...';
    
    fetch(`/mpesa/check-status/${checkoutRequestId}`)
        .then(response => response.json())
        .then(data => {
            console.log('Status check response:', data);
            
            if (data.success) {
                handleStatusUpdate(data.status, data);
            } else {
                console.error('Status check failed:', data.message);
                showError('Unable to check payment status');
            }
        })
        .catch(error => {
            console.error('Status check error:', error);
            showError('Network error. Please check your connection.');
        })
        .finally(() => {
            // Re-enable button
            checkBtn.disabled = false;
            checkBtn.innerHTML = 'Check Status';
        });
}

function handleStatusUpdate(status, data) {
    const loadingEl = document.getElementById('loadingStatus');
    const successEl = document.getElementById('successStatus');
    const failedEl = document.getElementById('failedStatus');
    const retryBtn = document.getElementById('retryPaymentBtn');
    
    switch (status) {
        case 'paid':
        case 'completed':
            // Payment successful
            clearInterval(statusCheckInterval);
            loadingEl.classList.add('hidden');
            successEl.classList.remove('hidden');
            
            if (data.mpesa_receipt) {
                document.getElementById('receiptNumber').textContent = 
                    `Receipt: ${data.mpesa_receipt}`;
            }
            
            // Redirect after 3 seconds
            setTimeout(() => {
                window.location.href = contentType === 'book' ? '/books' : '/videos';
            }, 3000);
            break;
            
        case 'failed':
        case 'cancelled':
            // Payment failed
            clearInterval(statusCheckInterval);
            loadingEl.classList.add('hidden');
            failedEl.classList.remove('hidden');
            retryBtn.classList.remove('hidden');
            break;
            
        case 'pending':
        default:
            // Still pending - keep checking
            console.log('Payment still pending, will check again...');
            break;
    }
}

function showTimeoutMessage() {
    const loadingEl = document.getElementById('loadingStatus');
    loadingEl.innerHTML = `
        <div class="w-16 h-16 mx-auto mb-4 bg-yellow-100 rounded-full flex items-center justify-center">
            <svg class="w-8 h-8 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
        </div>
        <h3 class="text-xl font-bold text-yellow-600 mb-2">Checking Timeout</h3>
        <p class="text-gray-600 mb-4">Payment status check timed out. Please verify manually or try again.</p>
    `;
    
    document.getElementById('retryPaymentBtn').classList.remove('hidden');
}

function showError(message) {
    const loadingEl = document.getElementById('loadingStatus');
    loadingEl.innerHTML = `
        <div class="w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center">
            <svg class="w-8 h-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
        </div>
        <h3 class="text-xl font-bold text-red-600 mb-2">Error</h3>
        <p class="text-gray-600 mb-4">${message}</p>
    `;
}

function retryPayment() {
    // Go back to payment form
    const contentId = '{{ $content->id }}';
    window.location.href = `/mpesa/payment/${contentType}/${contentId}`;
}

// Handle page visibility change to pause/resume checking
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        // Page is hidden, pause checking
        if (statusCheckInterval) {
            clearInterval(statusCheckInterval);
        }
    } else {
        // Page is visible again, resume checking if still pending
        const loadingEl = document.getElementById('loadingStatus');
        if (!loadingEl.classList.contains('hidden') && checkCount < maxChecks) {
            statusCheckInterval = setInterval(function() {
                if (checkCount < maxChecks) {
                    checkPaymentStatus();
                } else {
                    clearInterval(statusCheckInterval);
                    showTimeoutMessage();
                }
            }, 7500);
        }
    }
});
</script>
@endsection