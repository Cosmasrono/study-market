<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual Payment - Alternative Payment Method</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            
            <!-- Alert Banner -->
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <strong>Automatic payment is temporarily unavailable.</strong><br>
                            Please use the manual payment method below to complete your registration.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Main Card -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-6 py-8 text-white">
                    <div class="flex items-center justify-center mb-4">
                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-center">Manual Payment</h1>
                    <p class="text-center text-blue-100 mt-2">Complete your membership payment</p>
                </div>

                <!-- Payment Details -->
                <div class="p-6">
                    
                    <!-- Amount Section -->
                    <div class="bg-gradient-to-br from-green-50 to-blue-50 rounded-lg p-6 mb-6 border-2 border-green-200">
                        <div class="text-center">
                            <p class="text-gray-600 text-sm mb-2">Amount to Pay</p>
                            <p class="text-4xl font-bold text-green-600">KES {{ number_format($amount, 2) }}</p>
                            <p class="text-gray-500 text-sm mt-2">{{ ucwords(str_replace('_', ' ', $payment->subscription_duration)) }} Membership</p>
                        </div>
                    </div>

                    <!-- Payment Instructions -->
                    <div class="space-y-6">
                        
                        <!-- M-Pesa Paybill Instructions -->
                        <div class="border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                                <span class="bg-green-600 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm">1</span>
                                Pay via M-Pesa Paybill
                            </h3>
                            
                            <div class="space-y-4 ml-11">
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase">Paybill Number</p>
                                            <p class="text-2xl font-bold text-gray-900">{{ $paybill }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase">Account Number</p>
                                            <p class="text-lg font-bold text-gray-900 break-all">{{ $reference }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-sm text-gray-700 space-y-2">
                                    <p class="font-semibold">Step-by-step instructions:</p>
                                    <ol class="list-decimal list-inside space-y-1 ml-2">
                                        <li>Go to M-Pesa menu on your phone</li>
                                        <li>Select "Lipa na M-Pesa"</li>
                                        <li>Select "Pay Bill"</li>
                                        <li>Enter Business Number: <strong>{{ $paybill }}</strong></li>
                                        <li>Enter Account Number: <strong>{{ $reference }}</strong></li>
                                        <li>Enter Amount: <strong>{{ $amount }}</strong></li>
                                        <li>Enter your M-Pesa PIN and confirm</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <!-- After Payment Instructions -->
                        <div class="border border-gray-200 rounded-lg p-6 bg-blue-50">
                            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                                <span class="bg-blue-600 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm">2</span>
                                After Making Payment
                            </h3>
                            
                            <div class="ml-11 space-y-3 text-sm text-gray-700">
                                <p>• You will receive an M-Pesa confirmation SMS</p>
                                <p>• Your payment will be verified within <strong>5-10 minutes</strong></p>
                                <p>• You will receive an email confirmation once verified</p>
                                <p>• You can then log in and access your membership</p>
                            </div>
                        </div>

                        <!-- Transaction Reference -->
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <p class="text-sm text-gray-600 mb-2">Your Transaction Reference:</p>
                            <div class="flex items-center justify-between bg-white rounded px-4 py-3 border border-gray-300">
                                <code class="text-sm font-mono text-gray-900">{{ $reference }}</code>
                                <button onclick="copyReference()" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Copy
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Keep this reference for your records</p>
                        </div>

                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-8 space-y-3">
                        <a href="{{ route('login') }}" 
                           class="block w-full bg-blue-600 text-white text-center py-3 rounded-lg hover:bg-blue-700 transition duration-300 font-semibold">
                            I've Made the Payment - Go to Login
                        </a>
                        
                        <a href="{{ route('contact') }}" 
                           class="block w-full bg-gray-200 text-gray-700 text-center py-3 rounded-lg hover:bg-gray-300 transition duration-300">
                            Need Help? Contact Support
                        </a>
                    </div>

                    <!-- Help Section -->
                    <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                        <h4 class="font-semibold text-gray-900 mb-2">Need Assistance?</h4>
                        <p class="text-sm text-gray-600">
                            If you encounter any issues or have questions about payment:
                        </p>
                        <ul class="text-sm text-gray-600 mt-2 space-y-1">
                            <li>• Email: support@yoursite.com</li>
                            <li>• Phone: +254 XXX XXX XXX</li>
                            <li>• Include your transaction reference: <code>{{ $reference }}</code></li>
                        </ul>
                    </div>

                </div>
            </div>

            <!-- Security Notice -->
            <div class="text-center mt-6">
                <p class="text-xs text-gray-500">
                    🔒 Your payment is secure. All transactions are encrypted and processed securely.
                </p>
            </div>

        </div>
    </div>

    <script>
        function copyReference() {
            const reference = '{{ $reference }}';
            navigator.clipboard.writeText(reference).then(() => {
                // Show temporary success message
                const btn = event.target;
                const originalText = btn.textContent;
                btn.textContent = 'Copied!';
                btn.classList.add('text-green-600');
                
                setTimeout(() => {
                    btn.textContent = originalText;
                    btn.classList.remove('text-green-600');
                }, 2000);
            }).catch(err => {
                alert('Failed to copy: ' + err);
            });
        }
    </script>
</body>
</html>