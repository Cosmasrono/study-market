@extends('layouts.app')

@section('title', 'Videos')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Available Videos</h1>
        <p class="text-gray-600">All logged-in users can watch videos online for free.</p>
    </div>

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            {{ session('error') }}
        </div>
    @endif

    @if(session('info'))
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-6">
            {{ session('info') }}
        </div>
    @endif

    <!-- Access Information Alert -->
    @auth
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>Free Watching:</strong> You can watch all videos online for free.
                    </p>
                </div>
            </div>
        </div>
    @else
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        <strong>Login Required:</strong> Please log in to watch videos online for free.
                        <a href="{{ route('login') }}" class="font-medium underline text-yellow-700 hover:text-yellow-600">
                            Login now
                        </a>
                    </p>
                </div>
            </div>
        </div>
    @endauth

    @if($videos->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($videos as $video)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <!-- Video Thumbnail -->
                    @if($video->thumbnail)
                        <div class="relative h-48 overflow-hidden">
                            <img src="{{ $video->thumbnail }}" alt="{{ $video->title }}" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-black bg-opacity-30 flex items-center justify-center">
                                <div class="bg-white bg-opacity-90 rounded-full p-3 shadow-lg">
                                    <svg class="w-8 h-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Logo-based video thumbnail -->
                        <div class="w-full h-48 bg-gradient-to-br from-red-900 via-red-950 to-black flex flex-col items-center justify-center p-4 text-white relative overflow-hidden">
                            <!-- Background Pattern -->
                            <div class="absolute inset-0 opacity-5">
                                <div class="w-full h-full" style="background-image: radial-gradient(circle at 25% 25%, rgba(255,255,255,0.1) 0%, transparent 50%), radial-gradient(circle at 75% 75%, rgba(255,255,255,0.05) 0%, transparent 50%);"></div>
                            </div>
                            
                            <!-- Video play button background -->
                            <div class="absolute inset-0 bg-black bg-opacity-20"></div>
                            
                            <!-- Main Logo -->
                            <div class="relative z-10 mb-3">
                                <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-lg p-1">
                                    <img src="{{ asset('images/victoria_logo.jpg') }}" 
                                         alt="Inzoberi School of Professionals" 
                                         class="w-full h-full object-contain rounded-full">
                                </div>
                            </div>
                            
                            <!-- School Name -->
                            <div class="text-center mb-2 relative z-10">
                                <h4 class="text-xs font-semibold text-gray-200 mb-1 tracking-wide">INZOBERI SCHOOL</h4>
                                <p class="text-xs text-gray-300 font-light">OF PROFESSIONALS</p>
                                <div class="w-12 h-px bg-gradient-to-r from-transparent via-white to-transparent mx-auto mt-1"></div>
                            </div>
                            
                            <!-- Video Title -->
                            <h3 class="text-sm font-bold text-center leading-tight text-white relative z-10 px-2 mb-3">
                                {{ $video->title }}
                            </h3>
                            
                            <!-- Large Play Button -->
                            <div class="relative z-20 bg-white bg-opacity-90 rounded-full p-3 shadow-lg">
                                <svg class="w-8 h-8 text-red-900" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            
                            <!-- Duration Badge -->
                            @if($video->duration)
                                <div class="absolute bottom-3 right-3 z-10">
                                    <span class="bg-black bg-opacity-70 text-white text-xs px-2 py-1 rounded font-medium">
                                        {{ $video->duration }}
                                    </span>
                                </div>
                            @endif
                            
                            <!-- Video Type Badge -->
                            <div class="absolute top-3 left-3 z-10">
                                <span class="bg-red-900 text-white text-xs px-2 py-1 rounded font-medium shadow-lg">
                                    {{ strtoupper($video->format ?? 'VIDEO') }}
                                </span>
                            </div>
                            
                            <!-- Decorative elements -->
                            <div class="absolute top-3 right-3 w-2 h-2 bg-yellow-400 rounded-full opacity-60"></div>
                            <div class="absolute top-5 right-5 w-1 h-1 bg-white rounded-full opacity-40"></div>
                            <div class="absolute bottom-5 left-3 w-1 h-1 bg-yellow-300 rounded-full opacity-50"></div>
                            
                            <!-- Elegant border -->
                            <div class="absolute inset-2 border border-white/10 rounded"></div>
                        </div>
                    @endif

                    <!-- Status Overlay on Thumbnail - Removed price badge -->
                    <div class="absolute top-2 right-2">
                        @auth
                            <span class="bg-blue-500 text-white text-xs px-2 py-1 rounded-full shadow-lg">
                                Free
                            </span>
                        @else
                            <span class="bg-orange-500 text-white text-xs px-2 py-1 rounded-full shadow-lg">
                                Login
                            </span>
                        @endauth
                    </div>

                    <!-- Video Content -->
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-3">
                            <h3 class="text-xl font-semibold text-gray-800 line-clamp-2 flex-1">
                                {{ $video->title }}
                            </h3>
                        </div>

                        <!-- Description -->
                        @if($video->description)
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                                {{ $video->description }}
                            </p>
                        @endif

                        <!-- Access Status -->
                        @auth
                            <div class="flex items-center text-green-600 text-sm mb-4">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Free to watch online
                            </div>
                        @else
                            <div class="flex items-center text-orange-600 text-sm mb-4">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                                </svg>
                                Login required
                            </div>
                        @endauth

                        <!-- File Info -->
                        <div class="text-xs text-gray-500 mb-4">
                            @if($video->duration)
                                <span>Duration: {{ $video->duration }}</span>
                            @endif
                            @if($video->file_size)
                                <span class="ml-2">Size: {{ number_format($video->file_size / 1024 / 1024, 1) }} MB</span>
                            @endif
                            @if($video->format)
                                <span class="ml-2">Format: {{ strtoupper($video->format) }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Action Buttons - Download buttons removed -->
                    <div class="px-6 pb-6">
                        @auth
                            <!-- Authenticated users can watch all videos -->
                            <div class="space-y-2">
                                <a href="{{ route('videos.play', $video) }}" 
                                   class="w-full bg-red-900 hover:bg-red-950 text-white font-medium py-3 px-4 rounded transition-colors duration-200 text-center block flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path>
                                    </svg>
                                    Watch Video (Free)
                                </a>
                                
                                <a href="{{ route('videos.show', $video) }}" 
                                   class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded transition-colors duration-200 text-center block">
                                    View Details
                                </a>
                            </div>
                        @else
                            <!-- Guest users need to login -->
                            <div class="space-y-2">
                                <a href="{{ route('login') }}" 
                                   class="w-full bg-red-900 hover:bg-red-950 text-white font-medium py-3 px-4 rounded transition-colors duration-200 text-center block flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path>
                                    </svg>
                                    Login to Watch (Free)
                                </a>
                                
                                <div class="flex space-x-2">
                                    <a href="{{ route('videos.preview', $video) }}" 
                                       class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white font-medium py-2 px-4 rounded transition-colors duration-200 text-center block text-sm">
                                        Preview
                                    </a>
                                    <a href="{{ route('videos.show', $video) }}" 
                                       class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded transition-colors duration-200 text-center block text-sm">
                                        Details
                                    </a>
                                </div>
                            </div>
                        @endauth
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12">
            <div class="w-24 h-24 bg-gradient-to-br from-red-600 to-red-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <h3 class="text-xl font-medium text-gray-600 mb-2">No videos available</h3>
            <p class="text-gray-500">Check back later for new videos.</p>
        </div>
    @endif
</div>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Custom hover effects */
.hover\:scale-105:hover {
    transform: scale(1.05);
}

/* Smooth transitions */
.transition-all {
    transition: all 0.3s ease;
}

/* Video thumbnail aspect ratio */
.aspect-video {
    aspect-ratio: 16/9;
}
</style>
@endsection