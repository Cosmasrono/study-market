// resources/views/layouts/payment-scripts.blade.php
<script>
document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.getElementById('phone');
    const form = document.getElementById('mpesa-payment-form');
    const payButton = document.getElementById('pay-button');

    // Check if we have a checkout request ID from the session
    const checkoutRequestId = @json(session('checkout_request_id'));
    
    if (checkoutRequestId) {
        startPaymentStatusCheck(checkoutRequestId);
    }

    // Form submission handler
    if (form) {
        form.addEventListener('submit', function(e) {
            const phoneNumber = phoneInput.value.trim();
            const phoneRegex = /^(254|0)[17-9]\d{8}$/;

            if (!phoneRegex.test(phoneNumber)) {
                e.preventDefault();
                alert('Please enter a valid Kenyan phone number (e.g., 254712345678)');
                return;
            }

            // Show loading state
            payButton.disabled = true;
            payButton.textContent = 'Processing...';
            
            // Re-enable button after 30 seconds as failsafe
            setTimeout(function() {
                if (payButton.disabled) {
                    payButton.disabled = false;
                    payButton.textContent = 'Pay via M-Pesa';
                }
            }, 30000);
        });
    }

    // Format phone number as user types
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, ''); // Remove non-digits
            
            // Convert 07/08/09 format to 254 format
            if (value.startsWith('0')) {
                value = '254' + value.substring(1);
            }
            
            this.value = value;
        });
    }

    // Payment status checking function
    function startPaymentStatusCheck(checkoutRequestId) {
        showStatusModal('Checking payment status...');
        
        let attempts = 0;
        const maxAttempts = 60; // Check for 2 minutes
        
        const checkStatus = setInterval(function() {
            attempts++;
            
            fetch(`/mpesa/check/${checkoutRequestId}`)
                .then(response => response.json())
                .then(data => {
                    console.log('Payment status:', data);
                    
                    if (data.success) {
                        if (data.status === 'completed') {
                            clearInterval(checkStatus);
                            showSuccessModal(data);
                        } else if (data.status === 'failed') {
                            clearInterval(checkStatus);
                            showFailedModal('Payment failed. Please try again.');
                        } else if (attempts >= maxAttempts) {
                            clearInterval(checkStatus);
                            showTimeoutModal();
                        }
                    } else if (attempts >= maxAttempts) {
                        clearInterval(checkStatus);
                        showTimeoutModal();
                    }
                })
                .catch(error => {
                    console.error('Status check error:', error);
                    if (attempts >= maxAttempts) {
                        clearInterval(checkStatus);
                        showTimeoutModal();
                    }
                });
        }, 2000); // Check every 2 seconds
    }

    function showStatusModal(message) {
        removeExistingModal();
        const modal = document.createElement('div');
        modal.id = 'payment-status-modal';
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="bg-white p-6 rounded-lg max-w-md w-full mx-4">
                <div class="text-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto mb-4"></div>
                    <p class="text-lg font-semibold">${message}</p>
                    <p class="text-sm text-gray-600 mt-2">Please complete the payment on your phone</p>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }

    function showSuccessModal(data) {
        updateModalContent(`
            <div class="bg-white p-6 rounded-lg max-w-md w-full mx-4">
                <div class="text-center">
                    <div class="text-green-500 text-6xl mb-4">✓</div>
                    <h3 class="text-xl font-bold text-green-700 mb-2">Payment Successful!</h3>
                    <p class="text-gray-600 mb-4">Your payment has been processed successfully.</p>
                    <div class="text-sm text-gray-500">
                        <p>Receipt: ${data.mpesa_receipt || 'N/A'}</p>
                        <p>Amount: KSh ${data.amount}</p>
                    </div>
                    <button onclick="redirectToHome()" class="mt-4 bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600">
                        Continue
                    </button>
                </div>
            </div>
        `);
    }

    function showFailedModal(message) {
        updateModalContent(`
            <div class="bg-white p-6 rounded-lg max-w-md w-full mx-4">
                <div class="text-center">
                    <div class="text-red-500 text-6xl mb-4">✗</div>
                    <h3 class="text-xl font-bold text-red-700 mb-2">Payment Failed</h3>
                    <p class="text-gray-600 mb-4">${message}</p>
                    <button onclick="closeModal()" class="mt-4 bg-red-500 text-white px-6 py-2 rounded hover:bg-red-600 mr-2">
                        Close
                    </button>
                    <button onclick="window.location.reload()" class="mt-4 bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                        Try Again
                    </button>
                </div>
            </div>
        `);
    }

    function showTimeoutModal() {
        updateModalContent(`
            <div class="bg-white p-6 rounded-lg max-w-md w-full mx-4">
                <div class="text-center">
                    <div class="text-yellow-500 text-6xl mb-4">⚠</div>
                    <h3 class="text-xl font-bold text-yellow-700 mb-2">Payment Status Timeout</h3>
                    <p class="text-gray-600 mb-4">We couldn't confirm your payment status. Please check your M-Pesa messages.</p>
                    <button onclick="closeModal()" class="mt-4 bg-yellow-500 text-white px-6 py-2 rounded hover:bg-yellow-600 mr-2">
                        Close
                    </button>
                    <button onclick="window.location.reload()" class="mt-4 bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                        Check Again
                    </button>
                </div>
            </div>
        `);
    }

    function updateModalContent(content) {
        const modal = document.getElementById('payment-status-modal');
        if (modal) {
            modal.innerHTML = content;
        }
    }

    function removeExistingModal() {
        const existingModal = document.getElementById('payment-status-modal');
        if (existingModal) {
            existingModal.remove();
        }
    }

    // Global functions
    window.closeModal = function() {
        removeExistingModal();
    }

    window.redirectToHome = function() {
        // Determine redirect based on current URL
        const currentPath = window.location.pathname;
        if (currentPath.includes('video')) {
            window.location.href = '/videos';
        } else {
            window.location.href = '/books';
        }
    }
});
</script>