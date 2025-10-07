@extends('layouts.app')

@section('title', 'Login')

@push('styles')
<!-- SweetAlert2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.10.1/sweetalert2.min.css" rel="stylesheet">
@endpush

@section('content')
<div class="container mx-auto px-4">
    <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
        <h1 class="text-3xl font-bold mb-6 text-center">Login to Your Account</h1>
        
        @if(session('warning'))
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6" id="warning-alert">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">{{ session('warning') }}</p>
                </div>
            </div>
        </div>
        @endif

        @if(session('success'))
        <div class="hidden" id="success-message" data-message="{{ session('success') }}"></div>
        @endif

        @if(session('error'))
        <div class="hidden" id="error-message" data-message="{{ session('error') }}"></div>
        @endif
        
        <form method="POST" action="{{ route('login') }}" id="loginForm">
            @csrf
            
            <div class="mb-4">
                <label for="email" class="block text-gray-700 font-bold mb-2">Email Address *</label>
                <input type="email" 
                       name="email" 
                       id="email" 
                       value="{{ old('email') }}"
                       required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror"
                       placeholder="Enter your email address">
                @error('email')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4">
                <label for="password" class="block text-gray-700 font-bold mb-2">Password *</label>
                <div class="relative">
                    <input type="password" 
                           name="password" 
                           id="password" 
                           required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror"
                           placeholder="Enter your password">
                    <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <svg id="eye-icon" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-6 flex items-center justify-between">
                <label class="flex items-center">
                    <input type="checkbox" 
                           name="remember" 
                           id="remember" 
                           class="mr-2 rounded border-gray-300 text-blue-600 focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <span class="text-sm text-gray-700">Remember Me</span>
                </label>
                <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:underline">
                    Forgot Password?
                </a>
            </div>
            
            <div>
                <button type="submit" 
                        id="loginBtn"
                        class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-3 rounded-md hover:from-blue-700 hover:to-blue-800 transition duration-300 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <span id="loginText">Login</span>
                    <span id="loadingText" class="hidden">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Logging in...
                    </span>
                </button>
            </div>
        </form>
        
        <div class="text-center mt-6 space-y-3">
            <p class="text-gray-600">
                Don't have an account? 
                <a href="{{ route('register') }}" class="text-blue-600 hover:underline font-semibold">Register here</a>
            </p>
            <p class="text-gray-600">
                <span class="text-sm bg-green-100 text-green-800 px-2 py-1 rounded">Annual membership: KES {{ number_format(\App\Models\User::MEMBERSHIP_FEE) }}</span>
            </p>
            <p class="text-gray-600">
                <a href="{{ route('admin.login') }}" class="text-green-600 hover:underline font-semibold">
                    Admin Login
                </a>
            </p>
        </div>

        {{-- Password Reset Link --}}
        <div class="text-center mt-3">
            <a href="{{ route('password.request') }}" class="text-muted">
                <small>Forgot your password?</small>
            </a>
        </div>

        <!-- Security Notice -->
        <div class="mt-6 text-center">
            <div class="flex items-center justify-center text-xs text-gray-500">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                </svg>
                Your login is secure and encrypted
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- SweetAlert2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.10.1/sweetalert2.all.min.js"></script>

<script>
// Password visibility toggle
function togglePasswordVisibility() {
    const passwordField = document.getElementById('password');
    const eyeIcon = document.getElementById('eye-icon');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        eyeIcon.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3.4 3.4m6.478 6.478L21 21"/>
        `;
    } else {
        passwordField.type = 'password';
        eyeIcon.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        `;
    }
}

// Form submission handling
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const loginBtn = document.getElementById('loginBtn');
    const loginText = document.getElementById('loginText');
    const loadingText = document.getElementById('loadingText');
    
    loginBtn.disabled = true;
    loginText.classList.add('hidden');
    loadingText.classList.remove('hidden');
});

// Handle success messages
document.addEventListener('DOMContentLoaded', function() {
    // Success message
    const successMessage = document.getElementById('success-message');
    if (successMessage) {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: successMessage.dataset.message,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            toast: true,
            position: 'top-end'
        });
    }

    // Error message
    const errorMessage = document.getElementById('error-message');
    if (errorMessage) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: errorMessage.dataset.message,
            showConfirmButton: true,
            confirmButtonColor: '#ef4444'
        });
    }

    // Warning alert enhancement
    const warningAlert = document.getElementById('warning-alert');
    if (warningAlert) {
        // Add a subtle animation
        warningAlert.classList.add('animate-pulse');
        setTimeout(() => {
            warningAlert.classList.remove('animate-pulse');
        }, 3000);
    }
});

// Auto-focus email field
document.getElementById('email').focus();

// Enhanced form validation
function validateForm() {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    if (!email || !password) {
        Swal.fire({
            icon: 'warning',
            title: 'Missing Information',
            text: 'Please fill in all required fields.',
            confirmButtonColor: '#3b82f6'
        });
        return false;
    }
    
    return true;
}
</script>
@endpush