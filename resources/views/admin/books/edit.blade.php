@extends('layouts.admin')

@section('title', 'Edit Book')
@section('page-title', 'Edit Book')

@section('content')
<div class="bg-maroon-50 p-6 rounded-lg shadow-md">
    <form action="{{ route('admin.books.update', $book->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white p-6 rounded-lg">
            <h2 class="text-xl font-semibold mb-4 text-maroon-800">Book Details</h2>
            
            <!-- Title -->
            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-maroon-700 mb-2">Book Title</label>
                <input type="text" name="title" id="title" 
                       value="{{ old('title', $book->title) }}" 
                       class="w-full px-3 py-2 border border-maroon-300 rounded-md focus:outline-none focus:ring-2 focus:ring-maroon-500"
                       required>
                @error('title')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-maroon-700 mb-2">Description</label>
                <textarea name="description" id="description" 
                          rows="4" 
                          class="w-full px-3 py-2 border border-maroon-300 rounded-md focus:outline-none focus:ring-2 focus:ring-maroon-500">{{ old('description', $book->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Book Type and Pricing -->
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-maroon-700 mb-2">Book Type</label>
                    <div class="space-y-2">
                        <label class="inline-flex items-center">
                            <input type="radio" name="book_type" value="free" 
                                   {{ old('book_type', $book->is_free ? 'free' : 'paid') == 'free' ? 'checked' : '' }}
                                   class="form-radio text-maroon-600">
                            <span class="ml-2">Free</span>
                        </label>
                        <label class="inline-flex items-center ml-6">
                            <input type="radio" name="book_type" value="paid" 
                                   {{ old('book_type', $book->is_free ? 'free' : 'paid') == 'paid' ? 'checked' : '' }}
                                   class="form-radio text-maroon-600">
                            <span class="ml-2">Paid</span>
                        </label>
                    </div>
                </div>

                <div id="price-section" class="{{ old('book_type', $book->is_free ? 'free' : 'paid') == 'free' ? 'hidden' : '' }}">
                    <label for="price" class="block text-sm font-medium text-maroon-700 mb-2">Price (KSh)</label>
                    <input type="number" name="price" id="price" 
                           value="{{ old('price', $book->price) }}" 
                           step="0.01" min="0"
                           class="w-full px-3 py-2 border border-maroon-300 rounded-md focus:outline-none focus:ring-2 focus:ring-maroon-500">
                    @error('price')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Availability -->
            <div class="mt-4">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_available" 
                           {{ old('is_available', $book->is_available) ? 'checked' : '' }}
                           class="form-checkbox text-maroon-600">
                    <span class="ml-2">Book is Available</span>
                </label>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('admin.books') }}" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300">
                Cancel
            </a>
            <button type="submit" class="bg-maroon-600 text-white px-4 py-2 rounded hover:bg-maroon-700">
                Update Book
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Dynamic price section visibility
    document.addEventListener('DOMContentLoaded', function() {
        const freeRadio = document.querySelector('input[name="book_type"][value="free"]');
        const paidRadio = document.querySelector('input[name="book_type"][value="paid"]');
        const priceSection = document.getElementById('price-section');

        function togglePriceSection() {
            priceSection.classList.toggle('hidden', freeRadio.checked);
        }

        freeRadio.addEventListener('change', togglePriceSection);
        paidRadio.addEventListener('change', togglePriceSection);
    });
</script>
@endpush
@endsection