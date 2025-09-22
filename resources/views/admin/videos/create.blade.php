@extends('layouts.admin')

@section('title', 'Upload New Video')
@section('page-title', 'Upload New Video')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white shadow-md rounded-lg p-6">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Upload New Video</h1>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.videos.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label for="title" class="block text-gray-700 font-bold mb-2">Video Title *</label>
                    <input type="text" name="title" id="title" required value="{{ old('title') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                           placeholder="Enter video title">
                    @error('title')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="category" class="block text-gray-700 font-bold mb-2">Category</label>
                    <select name="category" id="category" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">Select Category</option>
                        <option value="academic" {{ old('category') == 'academic' ? 'selected' : '' }}>Academic</option>
                        <option value="professional" {{ old('category') == 'professional' ? 'selected' : '' }}>Professional</option>
                        <option value="personal_development" {{ old('category') == 'personal_development' ? 'selected' : '' }}>Personal Development</option>
                    </select>
                    @error('category')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="description" class="block text-gray-700 font-bold mb-2">Video Description</label>
                <textarea name="description" id="description" rows="4"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                          placeholder="Provide a brief description of the video">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="video_file" class="block text-gray-700 font-bold mb-2">Video File *</label>
                <input type="file" name="video_file" id="video_file" required
                       accept="video/mp4,video/mpeg,video/quicktime,video/x-msvideo,video/webm"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                @error('video_file')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mt-1">
                    Supported formats: MP4, MPEG, MOV, AVI, WebM | Max size: 500MB
                </p>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Video Type</label>
                    <div class="flex items-center space-x-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="video_type" value="free" 
                                   {{ old('video_type', 'free') == 'free' ? 'checked' : '' }}
                                   class="form-radio text-purple-600">
                            <span class="ml-2">Free Video</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="video_type" value="paid" 
                                   {{ old('video_type') == 'paid' ? 'checked' : '' }}
                                   class="form-radio text-purple-600">
                            <span class="ml-2">Paid Video</span>
                        </label>
                    </div>
                </div>

                <div id="price-section" class="{{ old('video_type') == 'paid' ? '' : 'hidden' }}">
                    <label for="price" class="block text-gray-700 font-bold mb-2">Price (KSh)</label>
                    <input type="number" name="price" id="price" step="0.01" min="0"
                           value="{{ old('price') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                           placeholder="Enter video price">
                    @error('price')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_active" value="1" 
                           {{ old('is_active', true) ? 'checked' : '' }}
                           class="form-checkbox text-purple-600">
                    <span class="ml-2">Video is Active</span>
                </label>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.videos') }}"
                   class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Cancel
                </a>
                <button type="submit" id="submit-btn"
                        class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                    Upload Video
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const freeRadio = document.querySelector('input[name="video_type"][value="free"]');
        const paidRadio = document.querySelector('input[name="video_type"][value="paid"]');
        const priceSection = document.getElementById('price-section');
        const priceInput = document.getElementById('price');

        function togglePriceSection() {
            if (paidRadio.checked) {
                priceSection.classList.remove('hidden');
                priceInput.required = true;
            } else {
                priceSection.classList.add('hidden');
                priceInput.required = false;
                priceInput.value = '';
            }
        }

        freeRadio.addEventListener('change', togglePriceSection);
        paidRadio.addEventListener('change', togglePriceSection);

        // Form submission handling
        const form = document.querySelector('form');
        const submitBtn = document.getElementById('submit-btn');

        form.addEventListener('submit', function(e) {
            const title = document.getElementById('title');
            const videoFile = document.getElementById('video_file');

            if (!title.value.trim()) {
                alert('Please enter a video title');
                e.preventDefault();
                return;
            }

            if (!videoFile.files.length) {
                alert('Please select a video file to upload');
                e.preventDefault();
                return;
            }

            // Disable submit button to prevent multiple submissions
            submitBtn.disabled = true;
            submitBtn.textContent = 'Uploading...';
        });
    });
</script>
@endpush
@endsection