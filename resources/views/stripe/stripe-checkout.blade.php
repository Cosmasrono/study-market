{{-- resources/views/payments/stripe-checkout.blade.php --}}
@extends('layouts.app')

@section('title', 'Secure Payment - Stripe')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-lg mx-auto">
        {{-- Content Preview --}}
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="text-center">
                @if($type === 'book')
                    @if($item->thumbnail)
                        <img src="{{ asset('storage/' . $item->thumbnail) }}" 
                             alt="{{ $item->title }}" 
                             class="w-32 h-40 mx-auto mb-4 rounded-lg object-cover">
                    @else
                        <div class="w-32 h-40 mx-auto mb-4 bg-blue-500 rounded-lg flex items-center justify-center">
                            <svg class="w-16 h-16 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/>
                            </svg>
                        </div>
                    @endif
                @else
                    @if($item->thumbnail)
                        <img src="{{ asset('storage/' . $item->thumbnail) }}" 
                             alt="{{ $item->title }}" 
                             class="w-32 h-24 mx-auto mb-4 rounded-lg object-cover">
                    @else
                        <div class="w-32 h-24 mx-auto mb-4 bg-red-500 rounded-lg flex items-center justify-center">
                            <svg class="w-16 h-16 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    @endif
                @endif
                
                <h2 class="text-xl font-bold mb-2">{{ $item->title }}</h2>
                <p class="text-gray-600 text-sm mb-4">{{ Str::limit($item->description, 100) }}</p>
                <div class="text-2xl font-bold text-green-600">
                    ${{ number_format($amount, 2) }} USD
                    <span class="text-sm text-gray-500">(≈ KES {{ number_format($item->price, 2) }})</span>
                </div>
            </div>
        </div>

        {{-- Stripe Payment Form --}}
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-center mb-6">
                <h1 class="text-2xl font-bold text-center mr-3">Secure Payment</h1>
                <div class="flex items-center space-x-2">
                    <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm text-gray-600">SSL Secured by Stripe</span>
                </div>
            </div>

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="payment-form" class="space-y-4">
                @csrf
                <input type="hidden" id="payment_intent_id" value="{{ $payment_intent_id }}">
                
                {{-- Card Element --}}
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Card Information</label>
                    <div id="card-element" class="p-3 border border-gray-300 rounded-lg">
                        <!-- Stripe Elements will create form elements here -->
                    </div>
                    <div id="card-errors" class="text-red-500 text-sm mt-1" role="alert"></div>
                </div>

                {{-- Cardholder Name --}}
                <div>
                    <label for="cardholder-name" class="block text-gray-700 font-bold mb-2">
                        Cardholder Name
                    </label>
                    <input type="text" 
                           id="cardholder-name" 
                           name="cardholder_name" 
                           placeholder="John Doe"
                           value="{{ auth()->user()->name }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                           required>
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-gray-700 font-bold mb-2">
                        Email Address
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ auth()->user()->email }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                           required readonly>
                </div>

                {{-- Payment Summary --}}
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-600">{{ ucfirst($type) }}:</span>
                        <span class="font-medium">{{ $item->title }}</span>
                    </div>
                    <div class="flex justify-between items-center text-lg font-bold">
                        <span>Total Amount:</span>
                        <span class="text-green-600">${{ number_format($amount, 2) }} USD</span>
                    </div>
                </div>

                {{-- Submit Button --}}
                <button type="submit" 
                        id="submit-button"
                        class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors duration-200 font-bold disabled:opacity-50 disabled:cursor-not-allowed">
                    <span id="button-text">
                        <svg class="w-5 h-5 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                            <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                        </svg>
                        Pay ${{ number_format($amount, 2) }}
                    </span>
                </button>
            </form>

            {{-- Security Info --}}
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="font-bold text-blue-800 mb-2">Secure Payment by Stripe</h3>
                <ul class="text-blue-700 text-sm space-y-1">
                    <li>• Your payment information is encrypted and secure</li>
                    <li>• We don't store your card details</li>
                    <li>• Supports all major credit and debit cards</li>
                    <li>• Instant access after successful payment</li>
                    <li>• 30-day money-back guarantee</li>
                </ul>
            </div>

            {{-- Cancel Button --}}
            <a href="{{ route($type === 'book' ? 'books' : 'videos') }}"
               class="block w-full text-center bg-gray-500 text-white py-3 px-4 rounded-lg hover:bg-gray-600 transition-colors duration-200 mt-4">
                Cancel Payment
            </a>
        </div>
    </div>
</div>

<script src="https://js.stripe.com/v3/"></script>
<script>
const stripe = Stripe('{{ $publishable_key }}');
const elements = stripe.elements();

// Create card element
const cardElement = elements.create('card', {
    style: {
        base: {
            fontSize: '16px',
            color: '#424770',
            '::placeholder': {
                color: '#aab7c4',
            },
        },
        invalid: {
            color: '#9e2146',
        },
    },
});

cardElement.mount('#card-element');

// Handle card validation errors
cardElement.on('change', ({error}) => {
    const displayError = document.getElementById('card-errors');
    if (error) {
        displayError.textContent = error.message;
    } else {
        displayError.textContent = '';
    }
});

// Handle form submission
const form = document.getElementById('payment-form');
const submitButton = document.getElementById('submit-button');

form.addEventListener('submit', async (event) => {
    event.preventDefault();

    submitButton.disabled = true;
    submitButton.innerHTML = `
        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Processing Payment...
    `;

    const {error} = await stripe.confirmCardPayment('{{ $client_secret }}', {
        payment_method: {
            card: cardElement,
            billing_details: {
                name: document.getElementById('cardholder-name').value,
                email: document.getElementById('email').value,
            },
        }
    });

    if (error) {
        // Show error to customer
        const errorElement = document.getElementById('card-errors');
        errorElement.textContent = error.message;

        submitButton.disabled = false;
        submitButton.innerHTML = `
            <svg class="w-5 h-5 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
            </svg>
            Pay ${{ number_format($amount, 2) }}
        `;
    } else {
        // Payment succeeded - redirect to confirmation
        window.location.href = '/card/confirm?payment_intent_id=' + document.getElementById('payment_intent_id').value;
    }
});
</script>
@endsection