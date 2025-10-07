<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - Payhero M-Pesa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
            <h1 class="text-3xl font-bold mb-6 text-center text-gray-900">Create Your Account</h1>
            
            <!-- Info Banner -->
            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">
                            <strong>Instant M-Pesa Activation:</strong> Complete registration and activate your account in under 2 minutes with Payhero!
                        </p>
                    </div>
                </div>
            </div>

            <!-- Laravel Error Display -->
            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <div class="text-sm text-red-700">
                                @foreach ($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Success Message -->
            @if (session('success'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            @endif

            <!-- Client-side Error Display -->
            <div id="errorContainer" class="hidden bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <div class="text-sm text-red-700" id="errorMessage"></div>
                    </div>
                </div>
            </div>
            
            <form method="POST" action="{{ route('register') }}" id="registrationForm">
                @csrf
                
                <!-- Name (Optional) -->
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 font-bold mb-2">Full Name (Optional)</label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Your Full Name"
                           value="{{ old('name') }}">
                    <p class="text-xs text-gray-600 mt-1">
                        If left blank, we'll generate a name from your email
                    </p>
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 font-bold mb-2">Email Address *</label>
                    <input type="email" 
                           name="email" 
                           id="email" 
                           required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="your.email@example.com"
                           value="{{ old('email') }}">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="block text-gray-700 font-bold mb-2">Password *</label>
                    <div class="relative">
                        <input type="password" 
                               name="password" 
                               id="password" 
                               required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror">
                        <button type="button" onclick="togglePasswordVisibility('password')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    <p class="text-xs text-gray-600 mt-1">
                        Must contain: 8+ characters, uppercase, lowercase, numbers, symbols
                    </p>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Confirm Password -->
                <div class="mb-4">
                    <label for="password_confirmation" class="block text-gray-700 font-bold mb-2">Confirm Password *</label>
                    <input type="password" 
                           name="password_confirmation" 
                           id="password_confirmation" 
                           required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Phone Number -->
                <div class="mb-4">
                    <label for="phone_number" class="block text-gray-700 font-bold mb-2">M-Pesa Phone Number *</label>
                    <input type="tel" 
                           name="phone_number" 
                           id="phone_number" 
                           value="{{ old('phone_number') }}"
                           placeholder="254712345678"
                           required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('phone_number') border-red-500 @enderror">
                    <p class="text-xs text-gray-600 mt-1">
                        Enter Kenyan M-Pesa number (254712345678 or 254101234567)
                    </p>
                    @error('phone_number')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Hidden Payment Method -->
                <input type="hidden" name="payment_method" value="payhero">

                <!-- Payment Method Display -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-bold mb-2">Payment Method</label>
                    <div class="p-4 border-2 border-green-200 rounded-lg bg-green-50">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="flex-1">
                                <div class="flex items-center">
                                    <span class="font-semibold text-green-700 text-lg">Payhero M-Pesa</span>
                                    <span class="ml-2 bg-green-200 text-green-800 text-xs px-2 py-1 rounded">Instant</span>
                                </div>
                                <p class="text-sm text-gray-700 mt-1">
                                    Secure & Instant M-Pesa STK Push Payment
                                </p>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center text-xs text-green-700">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            Fully Secured with M-Pesa STK Push
                        </div>
                    </div>
                </div>

                <!-- Subscription Duration -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-bold mb-2">Subscription Duration *</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="flex items-center p-3 border-2 border-blue-300 bg-blue-50 rounded-lg hover:bg-blue-100 cursor-pointer transition duration-200">
                            <input type="radio" 
                                   name="subscription_duration" 
                                   value="1_year" 
                                   class="mr-3 text-blue-600"
                                   {{ old('subscription_duration', '1_year') == '1_year' ? 'checked' : '' }}>
                            <div class="flex-1">
                                <div class="flex items-center">
                                    <span class="font-semibold text-blue-600">1 Year</span>
                                    <span class="ml-1 bg-blue-200 text-blue-800 text-xs px-1.5 py-0.5 rounded">Recommended</span>
                                </div>
                                <p class="text-sm text-gray-600">KES 4,000</p>
                            </div>
                        </label>
                        
                        <label class="flex items-center p-3 border-2 border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition duration-200 hover:border-blue-300">
                            <input type="radio" 
                                   name="subscription_duration" 
                                   value="6_months" 
                                   class="mr-3 text-blue-600"
                                   {{ old('subscription_duration') == '6_months' ? 'checked' : '' }}>
                            <div class="flex-1">
                                <span class="font-semibold text-blue-600">6 Months</span>
                                <p class="text-sm text-gray-600">KES 2,500</p>
                            </div>
                        </label>
                    </div>
                    @error('subscription_duration')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Terms -->
                <div class="mb-6">
                    <label class="flex items-start">
                        <input type="checkbox" 
                               name="terms" 
                               id="terms" 
                               value="1"
                               required 
                               class="mr-2 mt-1"
                               {{ old('terms') ? 'checked' : '' }}>
                        <span class="text-sm text-gray-700">
                            I agree to the <a href="#" class="text-blue-600 hover:underline">Terms and Conditions</a> 
                            and <a href="#" class="text-blue-600 hover:underline">Privacy Policy</a>.
                        </span>
                    </label>
                    @error('terms')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Submit Button -->
                <div>
                    <button type="submit" 
                            id="submitBtn"
                            class="w-full bg-gradient-to-r from-green-600 to-green-800 text-white py-3 rounded-md hover:from-green-700 hover:to-green-900 transition duration-300 font-semibold shadow-lg">
                        <span id="submitText">Activate with Payhero M-Pesa</span>
                        <span id="loadingText" class="hidden">
                            <svg class="animate-spin inline w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Initiating Payhero M-Pesa Payment...
                        </span>
                    </button>
                </div>
            </form>
            
            <!-- Login Link -->
            <div class="text-center mt-6">
                <p class="text-gray-600">
                    Already have a Payhero account? 
                    <a href="{{ route('login') }}" class="text-green-600 hover:underline font-semibold">Login here</a>
                </p>
            </div>

            <!-- Security Badge -->
            <div class="mt-6 text-center">
                <div class="flex items-center justify-center text-xs text-gray-500">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                    </svg>
                    Secure M-Pesa Payments Powered by Payhero
                </div>
            </div>

            <!-- How It Works -->
            <div class="mt-8 bg-gray-50 rounded-lg p-4">
                <h3 class="font-bold text-gray-800 mb-3 text-center">Payhero M-Pesa Payment Process</h3>
                <div class="space-y-2 text-sm text-gray-600">
                    <div class="flex items-start">
                        <span class="bg-green-500 text-white rounded-full w-6 h-6 flex items-center justify-center mr-3 flex-shrink-0">1</span>
                        <p>Complete your registration with accurate M-Pesa phone number</p>
                    </div>
                    <div class="flex items-start">
                        <span class="bg-green-500 text-white rounded-full w-6 h-6 flex items-center justify-center mr-3 flex-shrink-0">2</span>
                        <p>Select your preferred subscription duration</p>
                    </div>
                    <div class="flex items-start">
                        <span class="bg-green-500 text-white rounded-full w-6 h-6 flex items-center justify-center mr-3 flex-shrink-0">3</span>
                        <p>Click submit to receive instant M-Pesa STK Push on your phone</p>
                    </div>
                    <div class="flex items-start">
                        <span class="bg-green-500 text-white rounded-full w-6 h-6 flex items-center justify-center mr-3 flex-shrink-0">4</span>
                        <p>Enter your M-Pesa PIN to complete the secure payment</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Phone number validation
        const phoneInput = document.getElementById('phone_number');
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            // Auto-format: if starts with 0, convert to 254
            if (value.startsWith('0')) {
                value = '254' + value.substring(1);
            }
            // If starts with 7 or 1, add 254
            else if (value.startsWith('7') || value.startsWith('1')) {
                value = '254' + value;
            }
            
            e.target.value = value;
            
            // Validate format for Safaricom and Airtel
            if (value.length === 12) {
                if (!/^254[17]\d{8}$/.test(value)) {
                    e.target.classList.add('border-red-500');
                    showError('Invalid M-Pesa number. Must be Safaricom (254712345678) or Airtel (254101234567)');
                } else {
                    e.target.classList.remove('border-red-500');
                    hideError();
                }
            }
        });

        // Password toggle
        function togglePasswordVisibility(fieldId) {
            const field = document.getElementById(fieldId);
            const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
            field.setAttribute('type', type);
        }

        // Submit form function
        function submitForm() {
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const loadingText = document.getElementById('loadingText');
            
            // Show loading state
            submitBtn.disabled = true;
            submitText.classList.add('hidden');
            loadingText.classList.remove('hidden');
            
            // Submit the form
            document.getElementById('registrationForm').submit();
        }

        // Form validation
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate all fields
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const passwordConfirm = document.getElementById('password_confirmation').value;
            const phone = document.getElementById('phone_number').value;
            const duration = document.querySelector('input[name="subscription_duration"]:checked');
            const terms = document.getElementById('terms').checked;
            
            // Optional name validation (if provided)
            if (name && name.length > 255) {
                showError('Name must be 255 characters or less');
                return;
            }
            
            // Email validation
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showError('Please enter a valid email address');
                return;
            }
            
            // Password validation
            if (password.length < 8) {
                showError('Password must be at least 8 characters long');
                return;
            }
            
            if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])/.test(password)) {
                showError('Password must contain uppercase, lowercase, number, and symbol');
                return;
            }
            
            if (password !== passwordConfirm) {
                showError('Passwords do not match');
                return;
            }
            
            // Phone validation for Payhero M-Pesa
            if (!/^254[17]\d{8}$/.test(phone)) {
                showError('Invalid M-Pesa number. Use Safaricom (254712345678) or Airtel (254101234567)');
                return;
            }
            
            // Subscription duration validation
            if (!duration) {
                showError('Please select a subscription duration');
                return;
            }
            
            // Terms validation
            if (!terms) {
                showError('You must accept the terms and conditions');
                return;
            }
            
            // All validations passed - submit form
            submitForm();
        });

        function showError(message) {
            const errorContainer = document.getElementById('errorContainer');
            const errorMessage = document.getElementById('errorMessage');
            
            errorMessage.textContent = message;
            errorContainer.classList.remove('hidden');
            
            // Scroll to error
            errorContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function hideError() {
            document.getElementById('errorContainer').classList.add('hidden');
        }
    </script>
</body>
</html>