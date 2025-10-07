<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation - Payhero</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4 animate-pulse">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Check Your Phone</h1>
                <p class="text-gray-600 mt-2">M-Pesa payment request sent</p>
            </div>

            <!-- Payment Details -->
            <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-lg p-4 mb-6">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-700">Amount:</span>
                        <span class="font-bold text-green-600">KES {{ $payment->amount }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-700">Phone Number:</span>
                        <span class="font-semibold">{{ $phone_number }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-700">Reference:</span>
                        <span class="font-mono text-xs">{{ $reference }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-700">Duration:</span>
                        <span class="font-semibold">{{ ucwords(str_replace('_', ' ', $payment->subscription_duration)) }}</span>
                    </div>
                </div>
            </div>

            <!-- Instructions -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>Next Steps:</strong><br>
                            1. Check your phone {{ $phone_number }}<br>
                            2. You'll receive an M-Pesa STK push prompt<br>
                            3. Enter your M-Pesa PIN to complete payment<br>
                            4. Payment will be verified automatically
                        </p>
                    </div>
                </div>
            </div>

            <!-- Payment Status -->
            <div id="payment-status" class="mb-6">
                <div class="flex items-center justify-center p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-yellow-600 mr-3"></div>
                    <span class="text-yellow-700 font-medium">Waiting for payment...</span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-4" id="action-buttons">
                <!-- Check Status Button -->
                <button onclick="checkPaymentStatus()" 
                        id="checkStatusBtn"
                        class="w-full bg-blue-600 text-white py-3 rounded-md hover:bg-blue-700 transition duration-300 font-semibold">
                    🔄 Check Payment Status
                </button>

                <!-- Complete Registration Form (hidden until payment confirmed) -->
                <form method="POST" action="{{ route('confirm.payment') }}" id="confirm-form" style="display: none;">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="transaction_id" value="{{ $reference }}">
                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-green-600 to-blue-600 text-white py-3 rounded-md hover:from-green-700 hover:to-blue-700 transition duration-300 font-semibold">
                        ✅ Complete Registration & Login
                    </button>
                </form>

                <!-- Troubleshooting -->
                <div class="text-center">
                    <button onclick="toggleHelp()" class="text-sm text-gray-600 hover:text-gray-800">
                        Having issues? Click for help
                    </button>
                </div>
            </div>

            <!-- Help Section (Hidden by default) -->
            <div id="help-section" class="hidden mt-6 bg-gray-50 rounded-lg p-4">
                <h3 class="font-bold text-gray-800 mb-3">Troubleshooting</h3>
                <div class="space-y-3 text-sm text-gray-600">
                    <div>
                        <strong class="text-gray-800">No prompt received?</strong>
                        <p>• Check if your phone is on and has network<br>
                        • Ensure {{ $phone_number }} is correct<br>
                        • Wait 30 seconds and click "Check Status"</p>
                    </div>
                    <div>
                        <strong class="text-gray-800">Payment failed?</strong>
                        <p>• Ensure you have sufficient M-Pesa balance<br>
                        • Try again or contact support</p>
                    </div>
                    <div>
                        <strong class="text-gray-800">Need help?</strong>
                        <p>Contact us: support@example.com<br>
                        WhatsApp: +254700000000</p>
                    </div>
                </div>
                
                <a href="{{ route('register') }}" 
                   class="block text-center w-full mt-4 bg-gray-200 text-gray-700 py-2 rounded-md hover:bg-gray-300 transition duration-300">
                   Start Over
                </a>
            </div>

            <!-- Security Notice -->
            <div class="text-center mt-6">
                <p class="text-xs text-gray-500">
                    🔒 Secure payment powered by Payhero. Your data is encrypted.
                </p>
            </div>
        </div>
    </div>

    <script>
        let statusCheckInterval;
        let checkAttempts = 0;
        const maxAttempts = 30; // 5 minutes total (10 seconds each)
        const reference = '{{ $reference }}';
        
        // Auto-check payment status every 10 seconds
        function startAutoStatusCheck() {
            statusCheckInterval = setInterval(() => {
                checkAttempts++;
                
                if (checkAttempts >= maxAttempts) {
                    clearInterval(statusCheckInterval);
                    showTimeoutMessage();
                    return;
                }
                
                checkPaymentStatus();
            }, 10000); // Check every 10 seconds
        }

        // Manual status check
        function checkPaymentStatus() {
            const statusBtn = document.getElementById('checkStatusBtn');
            statusBtn.disabled = true;
            statusBtn.innerHTML = '⏳ Checking...';
            
            fetch(`{{ route('membership.payment.status', '') }}/${reference}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'completed') {
                        clearInterval(statusCheckInterval);
                        showPaymentSuccess();
                    } else if (data.status === 'failed') {
                        clearInterval(statusCheckInterval);
                        showPaymentFailed();
                    } else {
                        // Still pending
                        statusBtn.disabled = false;
                        statusBtn.innerHTML = '🔄 Check Payment Status';
                    }
                })
                .catch(error => {
                    console.error('Status check failed:', error);
                    statusBtn.disabled = false;
                    statusBtn.innerHTML = '🔄 Check Payment Status';
                });
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
            
            document.getElementById('checkStatusBtn').style.display = 'none';
            document.getElementById('confirm-form').style.display = 'block';
            
            // Play success sound (optional)
            playSuccessSound();
        }

        function showPaymentFailed() {
            document.getElementById('payment-status').innerHTML = `
                <div class="flex items-center justify-center p-4 bg-red-50 border border-red-200 rounded-lg">
                    <svg class="h-5 w-5 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <span class="text-red-700 font-semibold">❌ Payment failed. Please try again.</span>
                </div>
            `;
            
            document.getElementById('checkStatusBtn').innerHTML = '🔄 Try Again';
            document.getElementById('checkStatusBtn').disabled = false;
            document.getElementById('checkStatusBtn').onclick = function() {
                window.location.href = '{{ route('register') }}';
            };
        }

        function showTimeoutMessage() {
            document.getElementById('payment-status').innerHTML = `
                <div class="flex items-center justify-center p-4 bg-orange-50 border border-orange-200 rounded-lg">
                    <svg class="h-5 w-5 text-orange-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.664-1.333-2.464 0L5.732 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span class="text-orange-700 font-medium">⏱️ Check timed out. Click "Check Status" to verify payment.</span>
                </div>
            `;
        }

        function toggleHelp() {
            const helpSection = document.getElementById('help-section');
            helpSection.classList.toggle('hidden');
        }

        function playSuccessSound() {
            // Optional: Add success sound effect
            try {
                const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZijkJGGS57OihUBILTKXh8bllHw==');
                audio.play();
            } catch(e) {}
        }

        // Start auto-checking when page loads
        window.addEventListener('load', function() {
            setTimeout(startAutoStatusCheck, 5000); // Start after 5 seconds
        });

        // Clear interval when page unloads
        window.addEventListener('beforeunload', function() {
            if (statusCheckInterval) {
                clearInterval(statusCheckInterval);
            }
        });
    </script>
</body>
</html>