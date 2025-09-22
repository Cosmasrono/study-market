// Payment Module for handling book and other content payments
const PaymentModule = (function() {
    // Private variables
    let config = {
        mpesaPayRoute: null,
        csrfToken: null,
        onSuccess: null,
        onFailure: null
    };

    // Private methods
    function validatePhoneNumber(phoneNumber) {
        // Validate Kenyan phone number format
        const phoneRegex = /^(254|0)[17-9]\d{8}$/;
        return phoneRegex.test(phoneNumber);
    }

    function openMpesaModal(details) {
        // Create modal dynamically
        const modalHtml = `
            <div id="mpesa-payment-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                <div class="bg-white p-6 rounded-lg max-w-md w-full">
                    <h2 class="text-2xl font-bold mb-4 text-primary-700">M-Pesa Payment</h2>
                    <p class="mb-4 text-primary-600">Pay KSh ${details.price.toFixed(2)} for "${details.title}"</p>
                    
                    <form id="mpesa-payment-form">
                        <div class="mb-4">
                            <label for="phone-number" class="block text-sm font-medium text-primary-700 mb-2">
                                Phone Number (e.g., 254712345678)
                            </label>
                            <input 
                                type="tel" 
                                id="phone-number" 
                                name="phone_number" 
                                placeholder="254712345678" 
                                required 
                                class="w-full px-3 py-2 border border-primary-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                            >
                            <p class="text-xs text-primary-500 mt-1">
                                Use the phone number registered with M-Pesa
                            </p>
                        </div>
                        
                        <div class="flex space-x-2">
                            <button 
                                type="submit" 
                                class="flex-grow bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition duration-300"
                            >
                                Pay Now
                            </button>
                            <button 
                                type="button" 
                                id="cancel-mpesa-payment"
                                class="flex-grow bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 transition duration-300"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        `;

        // Remove any existing modal
        document.getElementById('mpesa-payment-modal')?.remove();

        // Insert modal into body
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        // Get modal elements
        const modal = document.getElementById('mpesa-payment-modal');
        const form = document.getElementById('mpesa-payment-form');
        const cancelButton = document.getElementById('cancel-mpesa-payment');

        // Cancel button handler
        cancelButton.addEventListener('click', () => {
            modal.remove();
        });

        // Form submission handler
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const phoneInput = document.getElementById('phone-number');
            const phoneNumber = phoneInput.value.trim();

            // Validate phone number
            if (!validatePhoneNumber(phoneNumber)) {
                alert('Please enter a valid Kenyan phone number (e.g., 254712345678)');
                return;
            }

            try {
                // Send payment request
                const response = await fetch(config.mpesaPayRoute, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken
                    },
                    body: JSON.stringify({
                        id: details.id,
                        title: details.title,
                        price: details.price,
                        type: 'book',
                        phone: phoneNumber
                    })
                });

                const result = await response.json();

                if (result.success) {
                    // Show success message and close modal
                    alert('Payment initiated. Check your phone for M-Pesa prompt.');
                    modal.remove();

                    // Optional: Start polling for payment status
                    startPaymentVerification(result.reference);

                    // Call success callback if provided
                    if (config.onSuccess) {
                        config.onSuccess(result);
                    }
                } else {
                    // Show error message
                    alert(result.message || 'Payment initiation failed');

                    // Call failure callback if provided
                    if (config.onFailure) {
                        config.onFailure(result);
                    }
                }
            } catch (error) {
                console.error('Payment Error:', error);
                alert('An error occurred during payment. Please try again.');

                // Call failure callback if provided
                if (config.onFailure) {
                    config.onFailure(error);
                }
            }
        });
    }

    function startPaymentVerification(reference) {
        // Implement payment verification polling
        let attempts = 0;
        const maxAttempts = 10;
        const interval = 10000; // 10 seconds

        const verificationInterval = setInterval(async () => {
            attempts++;

            try {
                const response = await fetch('/mpesa/verify', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken
                    },
                    body: JSON.stringify({ payment_id: result.payment_id })
                });

                const result = await response.json();

                if (result.success) {
                    // Payment successful
                    clearInterval(verificationInterval);
                    alert('Payment successful! Book access granted.');
                    
                    // Optional: Reload page or update UI
                    window.location.reload();
                } else if (attempts >= maxAttempts) {
                    // Stop polling after max attempts
                    clearInterval(verificationInterval);
                    alert('Payment verification timed out. Please contact support.');
                }
            } catch (error) {
                console.error('Payment Verification Error:', error);
                
                if (attempts >= maxAttempts) {
                    clearInterval(verificationInterval);
                    alert('Unable to verify payment. Please contact support.');
                }
            }
        }, interval);
    }

    // Public methods
    return {
        init: function(options) {
            config.mpesaPayRoute = options.mpesaPayRoute;
            config.csrfToken = options.csrfToken;
            config.onSuccess = options.onSuccess;
            config.onFailure = options.onFailure;
        },

        openModal: function(details) {
            // Validate required details
            if (!details.id || !details.title || details.price === undefined) {
                console.error('Invalid payment details');
                return;
            }

            // Open M-Pesa payment modal
            openMpesaModal(details);
        }
    };
})();
