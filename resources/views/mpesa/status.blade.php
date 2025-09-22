{{-- resources/views/mpesa/status.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto bg-white shadow-lg rounded-lg p-6 text-center payment-status-container">
        <div class="mb-4">
            <h2 class="text-2xl font-bold text-primary-700">Payment Status</h2>
            <p class="text-gray-600 mt-2">Waiting for payment confirmation...</p>
        </div>

        <div class="mb-4">
            <p class="font-semibold">{{ ucfirst($type ?? 'Content') }}: <span>{{ $content->title ?? $book->title }}</span></p>
            <p>Amount: KSh {{ number_format($content->price ?? $book->price, 2) }}</p>
        </div>

        <div class="flex justify-center space-x-4">
            <button id="check-status-btn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Check Status
            </button>
            <button id="cancel-btn" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                Cancel
            </button>
        </div>

        <div id="status-message" class="mt-4 text-center"></div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkoutRequestId = '{{ $checkout_request_id }}';
    const contentType = '{{ $type ?? "book" }}';
    const bookId = '{{ $content->id ?? $book->id }}';
    const redirectUrl = contentType === 'video' ? '/videos' : '/books';
    const bookUrl = contentType === 'book' ? `/books/${bookId}` : redirectUrl;
    
    let checkCount = 0;
    const maxChecks = 24; // Check for 2 minutes (24 * 5 seconds)
    let autoCheckInterval;

    function checkPaymentStatus() {
        checkCount++;
        document.getElementById('status-message').innerHTML = '<p class="text-blue-500">Checking...</p>';
        
        fetch(`/mpesa/check-status/${checkoutRequestId}`)
            .then(response => response.json())
            .then(data => {
                const statusEl = document.getElementById('status-message');
                
                if (!data.success) {
                    statusEl.innerHTML = `<p class="text-red-500">${data.message || 'Unable to check status'}</p>`;
                    return;
                }

                switch(data.status) {
                    case 'pending':
                        statusEl.innerHTML = '<p class="text-yellow-500">Payment pending. Complete M-Pesa prompt on your phone.</p>';
                        
                        // Continue checking if not exceeded max attempts
                        if (checkCount >= maxChecks) {
                            clearInterval(autoCheckInterval);
                            showTimeoutMessage();
                        }
                        break;
                        
                    case 'paid':
                    case 'completed':
                        clearInterval(autoCheckInterval);
                        showSuccessAndRedirect(data);
                        break;
                        
                    case 'failed':
                        clearInterval(autoCheckInterval);
                        showFailureMessage();
                        break;
                        
                    default:
                        statusEl.innerHTML = '<p class="text-gray-500">Unknown status. Contact support.</p>';
                }
            })
            .catch(() => {
                document.getElementById('status-message').innerHTML = '<p class="text-red-500">Network error. Try again.</p>';
                
                if (checkCount >= maxChecks) {
                    clearInterval(autoCheckInterval);
                }
            });
    }

    function showSuccessAndRedirect(data) {
        // Update the entire container with success message
        document.querySelector('.payment-status-container').innerHTML = `
            <div class="text-center">
                <div class="mb-4">
                    <svg class="w-16 h-16 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-green-600 mb-2">Payment Successful!</h2>
                <p class="text-gray-600 mb-4">Your payment has been confirmed.</p>
                
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <p class="text-sm"><strong>{{ ucfirst($type ?? 'Content') }}:</strong> {{ $content->title ?? $book->title }}</p>
                    <p class="text-sm"><strong>Amount:</strong> KSh ${data.amount || '{{ $content->price ?? $book->price }}'}</p>
                    <p class="text-sm"><strong>M-Pesa Receipt:</strong> ${data.mpesa_receipt || 'N/A'}</p>
                </div>
                
                <div class="space-y-3">
                    <button onclick="redirectToContent()" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium w-full">
                        ${contentType === 'book' ? 'Access Your Book Now' : 'Access Your Video Now'}
                    </button>
                    <div>
                        <a href="${redirectUrl}" class="text-blue-600 hover:text-blue-700 text-sm">
                            Back to ${contentType === 'book' ? 'Books' : 'Videos'}
                        </a>
                    </div>
                </div>
                
                <p class="text-sm text-gray-500 mt-4">You will be redirected automatically in <span id="countdown">5</span> seconds...</p>
            </div>
        `;
        
        // Start countdown for automatic redirect
        let countdown = 5;
        const countdownElement = document.getElementById('countdown');
        const countdownInterval = setInterval(() => {
            countdown--;
            if (countdownElement) {
                countdownElement.textContent = countdown;
            }
            if (countdown <= 0) {
                clearInterval(countdownInterval);
                redirectToContent();
            }
        }, 1000);
    }

    function showFailureMessage() {
        document.querySelector('.payment-status-container').innerHTML = `
            <div class="text-center">
                <div class="mb-4">
                    <svg class="w-16 h-16 text-red-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-red-600 mb-2">Payment Failed</h2>
                <p class="text-gray-600 mb-6">Your payment could not be processed. Please try again.</p>
                
                <div class="space-y-3">
                    <button onclick="window.location.href='${bookUrl}'" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium w-full">
                        Try Again
                    </button>
                    <div>
                        <a href="${redirectUrl}" class="text-blue-600 hover:text-blue-700 text-sm">
                            Back to ${contentType === 'book' ? 'Books' : 'Videos'}
                        </a>
                    </div>
                </div>
            </div>
        `;
    }

    function showTimeoutMessage() {
        document.querySelector('.payment-status-container').innerHTML = `
            <div class="text-center">
                <div class="mb-4">
                    <svg class="w-16 h-16 text-yellow-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-yellow-600 mb-2">Payment Pending</h2>
                <p class="text-gray-600 mb-6">We're still waiting for payment confirmation. This may take a few minutes.</p>
                
                <div class="space-y-3">
                    <button onclick="window.location.reload()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium w-full">
                        Check Again
                    </button>
                    <div>
                        <a href="${redirectUrl}" class="text-blue-600 hover:text-blue-700 text-sm">
                            Back to ${contentType === 'book' ? 'Books' : 'Videos'}
                        </a>
                    </div>
                </div>
            </div>
        `;
    }

    function redirectToContent() {
        // Redirect to the specific book/video page with success parameter
        window.location.href = `${bookUrl}?payment_success=1`;
    }

    // Initial check and button handlers
    checkPaymentStatus();
    document.getElementById('check-status-btn').addEventListener('click', checkPaymentStatus);
    document.getElementById('cancel-btn').addEventListener('click', () => window.location.href = redirectUrl);

    // Auto-check every 5 seconds for 2 minutes
    autoCheckInterval = setInterval(checkPaymentStatus, 5000);
});
</script>
@endsection