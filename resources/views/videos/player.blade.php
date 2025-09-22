@extends('layouts.app')

@section('title', $video->title)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Back Navigation -->
    <div class="mb-6">
        <a href="{{ route('videos.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Videos
        </a>
    </div>

    <!-- Video Title -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ $video->title }}</h1>
        @if($video->description)
            <p class="text-gray-600">{{ $video->description }}</p>
        @endif
    </div>

    <!-- Video Player -->
    <div class="bg-black rounded-lg overflow-hidden mb-6">
        @if($file_url)
            <video 
                controls 
                class="w-full h-auto max-h-screen"
                poster="{{ $video->getThumbnailUrlAttribute() }}"
            >
                <source src="{{ $file_url }}" type="video/mp4">
                <source src="{{ $file_url }}" type="video/webm">
                <source src="{{ $file_url }}" type="video/ogg">
                
                <!-- Fallback for browsers that don't support video tag -->
                <p class="text-white p-8 text-center">
                    Your browser doesn't support HTML5 video. 
                    <a href="{{ $file_url }}" class="text-blue-400 underline">Download the video</a> instead.
                </p>
            </video>
        @else
            <div class="aspect-video flex items-center justify-center bg-gray-800 text-white">
                <div class="text-center">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 01-2 0V8zM12 8a1 1 0 012 0v4a1 1 0 01-2 0V8z" clip-rule="evenodd"></path>
                    </svg>
                    <p class="text-lg">Video not available</p>
                    <p class="text-sm text-gray-400">Please try again later</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Video Information -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Video Details -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Video Details</h3>
            
            <div class="space-y-3">
                @if($video->duration)
                    <div class="flex justify-between">
                        <span class="text-gray-600">Duration:</span>
                        <span class="font-medium">{{ $video->duration }}</span>
                    </div>
                @endif
                
                @if($video->file_size)
                    <div class="flex justify-between">
                        <span class="text-gray-600">File Size:</span>
                        <span class="font-medium">{{ number_format($video->file_size / 1024 / 1024, 1) }} MB</span>
                    </div>
                @endif
                
                @if($video->format)
                    <div class="flex justify-between">
                        <span class="text-gray-600">Format:</span>
                        <span class="font-medium">{{ strtoupper($video->format) }}</span>
                    </div>
                @endif
                
                <div class="flex justify-between">
                    <span class="text-gray-600">Download Price:</span>
                    <span class="font-medium">KSh {{ number_format($video->price) }}</span>
                </div>
            </div>
        </div>

        <!-- Download Options -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Download Options</h3>
            
            @php
                $user = auth()->user();
                $canDownload = false;
                
                if ($user) {
                    $canDownload = \App\Models\Transaction::where('user_id', $user->id)
                        ->where('content_type', 'video')
                        ->where('content_id', $video->id)
                        ->where('status', 'paid')
                        ->exists();
                }
            @endphp
            
            @if($canDownload)
                <div class="text-center">
                    <div class="mb-4">
                        <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Download Purchased
                        </div>
                    </div>
                    
                    <a href="{{ route('videos.download', $video) }}" 
                       class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-6 rounded-lg transition-colors duration-200 inline-block">
                        Download Video
                    </a>
                </div>
            @else
                <div class="text-center">
                    <p class="text-gray-600 mb-4">
                        Purchase this video to download and keep it forever.
                    </p>
                    
                    <div class="mb-4">
                        <span class="text-2xl font-bold text-blue-600">KSh {{ number_format($video->price) }}</span>
                    </div>
                    
                    <a href="{{ route('mpesa.payment.video', $video) }}" 
                       class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-lg transition-colors duration-200 inline-block">
                        Buy Download Access
                    </a>
                </div>
            @endif
            
            <div class="mt-4 text-xs text-gray-500 text-center">
                <p>✓ Lifetime access after purchase</p>
                <p>✓ High quality video file</p>
                <p>✓ Watch offline anytime</p>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Optional: Add video analytics or custom controls here
    document.addEventListener('DOMContentLoaded', function() {
        const video = document.querySelector('video');
        
        if (video) {
            // Log when video starts playing
            video.addEventListener('play', function() {
                console.log('Video started playing');
                // You could send analytics data here
            });
            
            // Handle video errors
            video.addEventListener('error', function(e) {
                console.error('Video error:', e);
                // You could show a user-friendly error message here
            });
        }
    });
</script>
@endsection