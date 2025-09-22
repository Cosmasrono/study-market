@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white shadow-md rounded-lg p-6">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Edit Video</h1>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Fixed: Use the correct admin route names --}}
        <form action="{{ route('admin.videos.update', $video->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label for="title" class="block text-gray-700 font-bold mb-2">Title</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $video->title) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500" 
                           required>
                </div>

                <div>
                    <label for="category" class="block text-gray-700 font-bold mb-2">Category</label>
                    <input type="text" name="category" id="category" value="{{ old('category', $video->category) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
            </div>

            <div>
                <label for="description" class="block text-gray-700 font-bold mb-2">Description</label>
                <textarea name="description" id="description" rows="4" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('description', $video->description) }}</textarea>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Video Type</label>
                    <div class="flex items-center">
                        <input type="radio" name="video_type" id="free" value="free" 
                               {{ old('video_type', $video->is_free ? 'free' : 'paid') == 'free' ? 'checked' : '' }}
                               class="mr-2 focus:ring-purple-500">
                        <label for="free" class="mr-4">Free Video</label>
                        
                        <input type="radio" name="video_type" id="paid" value="paid" 
                               {{ old('video_type', $video->is_free ? 'free' : 'paid') == 'paid' ? 'checked' : '' }}
                               class="mr-2 focus:ring-purple-500">
                        <label for="paid">Paid Video</label>
                    </div>
                </div>

                <div id="price-container" style="display: {{ old('video_type', $video->is_free ? 'free' : 'paid') == 'paid' ? 'block' : 'none' }};">
                    <label for="price" class="block text-gray-700 font-bold mb-2">Price (KSh)</label>
                    <input type="number" name="price" id="price" step="0.01" min="0" 
                           value="{{ old('price', $video->price) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
            </div>

            <div>
                <label class="block text-gray-700 font-bold mb-2">
                    <input type="checkbox" name="is_active" value="1" 
                           {{ old('is_active', $video->is_active) ? 'checked' : '' }}
                           class="mr-2 focus:ring-purple-500">
                    Is Active
                </label>
            </div>

            <div class="flex justify-end space-x-4">
                {{-- Fixed: Use correct admin route --}}
                <a href="{{ route('admin.videos') }}" 
                   class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Cancel
                </a>
                <button type="submit" 
                        class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                    Update Video
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const freeRadio = document.getElementById('free');
        const paidRadio = document.getElementById('paid');
        const priceContainer = document.getElementById('price-container');

        function togglePriceVisibility() {
            priceContainer.style.display = paidRadio.checked ? 'block' : 'none';
        }

        freeRadio.addEventListener('change', togglePriceVisibility);
        paidRadio.addEventListener('change', togglePriceVisibility);

        // Initial state
        togglePriceVisibility();
    });
</script>
@endpush
@endsection