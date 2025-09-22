@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="container mx-auto px-4">
    <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
        <h1 class="text-3xl font-bold mb-6 text-center">Create Your Account</h1>
        
        <!-- Membership Fee Notice -->
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>Quick Setup:</strong> KES {{ number_format($membership_fee) }} annual membership fee. Complete registration and payment in under 2 minutes!
                    </p>
                </div>
            </div>
        </div>

        @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <div class="text-sm text-red-700">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        <form method="POST" action="{{ route('register') }}" id="registrationForm">
            @csrf
            
            <div class="mb-4">
                <label for="email" class="block text-gray-700 font-bold mb-2">Email Address *</label>
                <input type="email" 
                       name="email" 
                       id="email" 
                       value="{{ old('email') }}"
                       required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror"
                       placeholder="your.email@example.com">
            </div>
            
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
            </div>
            
            <div class="mb-4">
                <label for="password_confirmation" class="block text-gray-700 font-bold mb-2">Confirm Password *</label>
                <input type="password" 
                       name="password_confirmation" 
                       id="password_confirmation" 
                       required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label for="phone_number" class="block text-gray-700 font-bold mb-2">Phone Number *</label>
                <input type="tel" 
                       name="phone_number" 
                       id="phone_number" 
                       value="{{ old('phone_number') }}"
                       placeholder="254712345678"
                       required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('phone_number') border-red-500 @enderror">
                <p class="text-xs text-gray-600 mt-1">
                    Kenyan number starting with 254 (for M-Pesa payments)
                </p>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 font-bold mb-2">Payment Method *</label>
                <div class="space-y-3">
                    <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="radio" 
                               name="payment_method" 
                               value="mpesa" 
                               class="mr-3 text-green-600" 
                               {{ old('payment_method', 'mpesa') === 'mpesa' ? 'checked' : '' }}>
                        <div class="flex-1">
                            <div class="flex items-center">
                                <span class="font-semibold text-green-600">M-Pesa</span>
                                <span class="ml-2 text-sm bg-green-100 text-green-800 px-2 py-1 rounded">Recommended</span>
                            </div>
                            <p class="text-sm text-gray-600">KES {{ number_format($membership_fee) }} - Instant payment via STK Push</p>
                        </div>
                    </label>
                    <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="radio" 
                               name="payment_method" 
                               value="card" 
                               class="mr-3 text-blue-600"
                               {{ old('payment_method') === 'card' ? 'checked' : '' }}>
                        <div class="flex-1">
                            <span class="font-semibold text-blue-600">Credit/Debit Card</span>
                            <p class="text-sm text-gray-600">KES {{ number_format($membership_fee) }} - Visa, Mastercard accepted</p>
                        </div>
                    </label>
                </div>
            </div>

            <div class="mb-6">
                <label class="flex items-start">
                    <input type="checkbox" 
                           name="terms" 
                           id="terms" 
                           required 
                           class="mr-2 mt-1">
                    <span class="text-sm text-gray-700">
                        I agree to the <a href="#" class="text-blue-600 hover:underline">Terms and Conditions</a> 
                        and <a href="#" class="text-blue-600 hover:underline">Privacy Policy</a>. 
                        I understand the annual membership fee is KES {{ number_format($membership_fee) }}.
                    </span>
                </label>
            </div>
            
            <div>
                <button type="submit" 
                        id="submitBtn"
                        class="w-full bg-gradient-to-r from-blue-600 to-green-600 text-white py-3 rounded-md hover:from-blue-700 hover:to-green-700 transition duration-300 font-semibold">
                    <span id="submitText">Complete Registration & Pay KES {{ number_format($membership_fee) }}</span>
                    <span id="loadingText" class="hidden">Processing...</span>
                </button>
            </div>
        </form>
        
        <div class="text-center mt-6">
            <p class="text-gray-600">
                Already have an account? 
                <a href="{{ route('login') }}" class="text-blue-600 hover:underline font-semibold">Login here</a>
            </p>
        </div>

        <!-- Security Notice -->
        <div class="mt-6 text-center">
            <div class="flex items-center justify-center text-xs text-gray-500">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                </svg>
                Your data is secure and encrypted
            </div>
        </div>
    </div>
</div>

<script>
function togglePasswordVisibility(fieldId) {
    const field = document.getElementById(fieldId);
    const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
    field.setAttribute('type', type);
}

document.getElementById('registrationForm').addEventListener('submit', function() {
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const loadingText = document.getElementById('loadingText');
    
    submitBtn.disabled = true;
    submitText.classList.add('hidden');
    loadingText.classList.remove('hidden');
});
</script>
@endsection