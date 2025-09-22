{{-- resources/views/mpesa/payment.blade.php --}}
@extends('layouts.app')

@section('title', isset($book) ? 'Purchase Book' : 'Purchase Video')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto">
        {{-- Content Preview --}}
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="text-center">
                @if(isset($book))
                    @if($book->thumbnail)
                        <img src="{{ asset('storage/' . $book->thumbnail) }}" 
                             alt="{{ $book->title }}" 
                             class="w-32 h-40 mx-auto mb-4 rounded-lg object-cover">
                    @else
                        <div class="w-32 h-40 mx-auto mb-4 bg-blue-500 rounded-lg flex items-center justify-center">
                            <svg class="w-16 h-16 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/>
                            </svg>
                        </div>
                    @endif
                    <h2 class="text-xl font-bold mb-2">{{ $book->title }}</h2>
                    <p class="text-gray-600 text-sm mb-4">{{ Str::limit($book->description, 100) }}</p>
                    <div class="text-2xl font-bold text-blue-600">KES {{ number_format($book->price, 2) }}</div>
                @else
                    @if($video->thumbnail)
                        <img src="{{ asset('storage/' . $video->thumbnail) }}" 
                             alt="{{ $video->title }}" 
                             class="w-32 h-24 mx-auto mb-4 rounded-lg object-cover">
                    @else
                        <div class="w-32 h-24 mx-auto mb-4 bg-red-500 rounded-lg flex items-center justify-center">
                            <svg class="w-16 h-16 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    @endif
                    <h2 class="text-xl font-bold mb-2">{{ $video->title }}</h2>
                    <p class="text-gray-600 text-sm mb-4">{{ Str::limit($video->description, 100) }}</p>
                    <div class="text-2xl font-bold text-blue-600">KES {{ number_format($video->price, 2) }}</div>
                @endif
            </div>
        </div>

        {{-- M-Pesa Payment Form --}}
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h1 class="text-2xl font-bold mb-6 text-center">Pay with M-Pesa</h1>

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- M-Pesa Header --}}
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                    </svg>
                </div>
                <p class="text-gray-600">Fast, secure mobile money payment</p>
            </div>

            <form method="POST" action="{{ route('mpesa.initiate') }}" id="mpesaPaymentForm">
                @csrf
                
                {{-- Hidden fields --}}
                <input type="hidden" name="type" value="{{ isset($book) ? 'book' : 'video' }}">
                <input type="hidden" name="id" value="{{ isset($book) ? $book->id : $video->id }}">
                <input type="hidden" name="title" value="{{ isset($book) ? $book->title : $video->title }}">
                <input type="hidden" name="price" value="{{ isset($book) ? $book->price : $video->price }}">

                {{-- Phone Number --}}
                <div class="mb-6">
                    <label for="phone" class="block text-gray-700 font-bold mb-2">
                        M-Pesa Phone Number
                    </label>
                    <input type="tel" 
                           id="phone" 
                           name="phone" 
                           value="{{ old('phone') }}"
                           placeholder="254712345678 or 0712345678"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500 @error('phone') border-red-500 @enderror">
                    @error('phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-gray-500 text-xs mt-1">
                        Enter your M-Pesa number in format 254XXXXXXXXX or 0XXXXXXXXX
                    </p>
                </div>

                {{-- Payment Summary --}}
                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-600">{{ isset($book) ? 'Book' : 'Video' }}:</span>
                        <span class="font-medium">{{ isset($book) ? $book->title : $video->title }}</span>
                    </div>
                    <div class="flex justify-between items-center text-lg font-bold">
                        <span>Total Amount:</span>
                        <span class="text-green-600">KES {{ number_format(isset($book) ? $book->price : $video->price, 2) }}</span>
                    </div>
                </div>

                {{-- Submit Button --}}
                <button type="submit" 
                        id="mpesaPayButton"
                        class="w-full bg-green-600 text-white py-3 px-4 rounded-lg hover:bg-green-700 transition-colors duration-200 font-bold">
                    <svg class="w-5 h-5 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                    </svg>
                    Pay with M-Pesa
                </button>
            </form>

            {{-- M-Pesa Instructions --}}
            <div class="mt-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <h3 class="font-bold text-green-800 mb-2">Payment Steps:</h3>
                <ol class="text-green-700 text-sm space-y-1">
                    <li>1. Enter your M-Pesa phone number above</li>
                    <li>2. Click "Pay with M-Pesa"</li>
                    <li>3. Check your phone for M-Pesa prompt</li>
                    <li>4. Enter your M-Pesa PIN to complete payment</li>
                    <li>5. Access your {{ isset($book) ? 'book' : 'video' }} immediately after payment</li>
                </ol>
            </div>

            {{-- Cancel Button --}}
            <a href="{{ route(isset($book) ? 'books' : 'videos.index') }}"
               class="block w-full text-center bg-gray-500 text-white py-3 px-4 rounded-lg hover:bg-gray-600 transition-colors duration-200 mt-4">
                Cancel
            </a>
        </div>
    </div>
</div>

<script>
// Form submission handler
document.getElementById('mpesaPaymentForm').addEventListener('submit', function(e) {
    const button = document.getElementById('mpesaPayButton');
    button.disabled = true;
    button.innerHTML = `
        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Processing M-Pesa...
    `;
});
</script>
@endsection