@extends('layouts.app')

@section('title', 'Submit Testimonial')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-center">Share Your Experience</h1>

    <div class="max-w-2xl mx-auto bg-white shadow-md rounded-lg p-8">
        <form method="POST" action="{{ route('testimonials.store') }}" class="space-y-6">
            @csrf

            <div>
                <label for="name" class="block text-gray-700 font-bold mb-2">Your Name *</label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old('name', auth()->user()->name) }}"
                       required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="position" class="block text-gray-700 font-bold mb-2">Your Position (Optional)</label>
                <input type="text" 
                       id="position" 
                       name="position" 
                       value="{{ old('position') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('position') border-red-500 @enderror">
                @error('position')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="company" class="block text-gray-700 font-bold mb-2">Company (Optional)</label>
                <input type="text" 
                       id="company" 
                       name="company" 
                       value="{{ old('company') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('company') border-red-500 @enderror">
                @error('company')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="content" class="block text-gray-700 font-bold mb-2">Your Testimonial *</label>
                <textarea 
                    id="content" 
                    name="content" 
                    rows="4" 
                    required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('content') border-red-500 @enderror"
                    placeholder="Share your experience with our platform..."
                >{{ old('content') }}</textarea>
                @error('content')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-gray-700 font-bold mb-2">Rating *</label>
                <div class="flex space-x-2">
                    @for($i = 1; $i <= 5; $i++)
                        <label class="inline-flex items-center">
                            <input type="radio" 
                                   name="rating" 
                                   value="{{ $i }}" 
                                   {{ old('rating') == $i ? 'checked' : '' }}
                                   class="form-radio text-blue-600 focus:ring-blue-500">
                            <span class="ml-2">{{ $i }} Star{{ $i > 1 ? 's' : '' }}</span>
                        </label>
                    @endfor
                </div>
                @error('rating')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="text-center">
                <button type="submit" 
                        class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 transition duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Submit Testimonial
                </button>
            </div>
        </form>
    </div>

    <div class="mt-8 text-center max-w-2xl mx-auto">
        <p class="text-gray-600">
            Your testimonial will be reviewed by our admin team before being published. 
            We appreciate your feedback and will notify you once it's approved.
        </p>
    </div>
</div>
@endsection
