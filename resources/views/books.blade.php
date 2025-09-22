@extends('layouts.app')

@section('title', 'Books')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Available Books</h1>
        <p class="text-gray-600">All logged-in users can read books online. Downloads require individual payment.</p>
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
                        <strong>Free Reading:</strong> You can read all books online for free. 
                        <strong>Downloads:</strong> Purchase individual books to download and keep them.
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
                        <strong>Login Required:</strong> Please log in to read books online for free.
                        <a href="{{ route('login') }}" class="font-medium underline text-yellow-700 hover:text-yellow-600">
                            Login now
                        </a>
                    </p>
                </div>
            </div>
        </div>
    @endauth

    @if($books->count() > 0)
        <!-- Books Grid - Visual Layout -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
            @foreach($books as $book)
                <div class="group cursor-pointer">
                    <!-- Book Cover Container -->
                    <div class="relative bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform group-hover:scale-105">
                        <!-- Book Cover Image -->
                        <div class="aspect-[3/4] relative overflow-hidden">
                            @if($book->thumbnail)
                                <img src="{{ asset('storage/' . $book->thumbnail) }}" 
                                     alt="{{ $book->title }}" 
                                     class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">
                            @else
                                <!-- Logo-based book cover -->
                                <div class="w-full h-full bg-gradient-to-br from-gray-800 via-gray-900 to-black flex flex-col items-center justify-center p-4 text-white relative">
                                    <!-- Background Pattern -->
                                    <div class="absolute inset-0 opacity-5">
                                        <div class="w-full h-full" style="background-image: radial-gradient(circle at 25% 25%, rgba(255,255,255,0.1) 0%, transparent 50%), radial-gradient(circle at 75% 75%, rgba(255,255,255,0.05) 0%, transparent 50%);"></div>
                                    </div>
                                    
                                    <!-- Main Logo -->
                                    <div class="relative z-10 mb-4">
                                        <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center shadow-lg p-1">
                                            <img src="{{ asset('images/victoria_logo.jpg') }}" 
                                                 alt="Inzoberi School of Professionals" 
                                                 class="w-full h-full object-contain rounded-full">
                                        </div>
                                    </div>
                                    
                                    <!-- School Name -->
                                    <div class="text-center mb-3 relative z-10">
                                        <h4 class="text-xs font-semibold text-gray-300 mb-1 tracking-wide">INZOBERI SCHOOL</h4>
                                        <p class="text-xs text-gray-400 font-light">OF PROFESSIONALS</p>
                                        <div class="w-16 h-px bg-gradient-to-r from-transparent via-white to-transparent mx-auto mt-2"></div>
                                    </div>
                                    
                                    <!-- Book Title -->
                                    <h3 class="text-sm font-bold text-center leading-tight text-white relative z-10 px-2">
                                        {{ $book->title }}
                                    </h3>
                                    
                                    <!-- File Type Badge -->
                                    <div class="absolute bottom-3 right-3 z-10">
                                        <span class="bg-amber-600 text-white text-xs px-2 py-1 rounded font-medium shadow-lg">
                                            {{ strtoupper(pathinfo($book->original_filename ?? $book->title, PATHINFO_EXTENSION)) ?: 'DOC' }}
                                        </span>
                                    </div>
                                    
                                    <!-- Decorative elements -->
                                    <div class="absolute top-3 left-3 w-2 h-2 bg-amber-500 rounded-full opacity-60"></div>
                                    <div class="absolute top-5 left-5 w-1 h-1 bg-white rounded-full opacity-40"></div>
                                    <div class="absolute bottom-5 left-3 w-1 h-1 bg-amber-400 rounded-full opacity-50"></div>
                                    
                                    <!-- Elegant border -->
                                    <div class="absolute inset-2 border border-white/10 rounded"></div>
                                    <div class="absolute inset-3 border border-white/5 rounded"></div>
                                </div>
                            @endif

                            <!-- Status Overlay -->
                            <div class="absolute top-2 right-2">
                                @auth
                                    @if(isset($book->can_download) && $book->can_download)
                                        <span class="bg-green-500 text-white text-xs px-2 py-1 rounded-full shadow-lg">
                                            ✓ Owned
                                        </span>
                                    @else
                                        <span class="bg-blue-500 text-white text-xs px-2 py-1 rounded-full shadow-lg">
                                            Free
                                        </span>
                                    @endif
                                @else
                                    <span class="bg-orange-500 text-white text-xs px-2 py-1 rounded-full shadow-lg">
                                        Login
                                    </span>
                                @endauth
                            </div>

                            <!-- Price Badge -->
                            <div class="absolute bottom-2 left-2">
                                <span class="bg-black/80 text-white text-xs px-2 py-1 rounded shadow-lg">
                                    KSh {{ number_format($book->price) }}
                                </span>
                            </div>
                        </div>

                        <!-- Book Info Overlay (appears on hover) -->
                        <div class="absolute inset-0 bg-black/95 text-white p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-between">
                            <!-- Title and Description -->
                            <div>
                                <h3 class="font-bold text-lg mb-2 line-clamp-2">{{ $book->title }}</h3>
                                @if($book->description)
                                    <p class="text-sm text-gray-300 line-clamp-3 mb-3">
                                        {{ $book->description }}
                                    </p>
                                @endif
                                
                                <!-- File Info -->
                                <div class="text-xs text-gray-400 mb-3">
                                    @if($book->file_size)
                                        <span>{{ number_format($book->file_size / 1024 / 1024, 1) }} MB</span>
                                    @endif
                                    @if($book->mime_type)
                                        <span class="ml-2">{{ strtoupper(str_replace('application/', '', $book->mime_type)) }}</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="space-y-2">
                                @auth
                                    <a href="{{ route('books.view', $book) }}" 
                                       class="block w-full bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2 px-3 rounded text-center transition-colors">
                                        Read Free
                                    </a>
                                    
                                    @if(isset($book->can_download) && $book->can_download)
                                        <a href="{{ route('books.download', $book) }}" 
                                           class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-3 rounded text-center transition-colors">
                                            Download
                                        </a>
                                    @else
                                        <a href="{{ route('mpesa.payment.book', $book) }}" 
                                           class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-3 rounded text-center transition-colors">
                                            Buy Download
                                        </a>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" 
                                       class="block w-full bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2 px-3 rounded text-center transition-colors">
                                        Login to Read
                                    </a>
                                    <a href="{{ route('books.preview', $book) }}" 
                                       class="block w-full bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium py-2 px-3 rounded text-center transition-colors">
                                        Preview
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </div>

                    <!-- Book Title Below (always visible) -->
                    <div class="mt-3 px-1">
                        <h4 class="font-semibold text-gray-800 text-sm line-clamp-2 group-hover:text-blue-600 transition-colors">
                            {{ $book->title }}
                        </h4>
                        @if($book->author)
                            <p class="text-xs text-gray-500 mt-1">{{ $book->author }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12">
            <div class="text-gray-400 text-6xl mb-4">📚</div>
            <h3 class="text-xl font-medium text-gray-600 mb-2">No books available</h3>
            <p class="text-gray-500">Check back later for new books.</p>
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

.aspect-\[3\/4\] {
    aspect-ratio: 3/4;
}

/* Custom hover effects */
.group:hover .group-hover\:scale-110 {
    transform: scale(1.1);
}

.group:hover .group-hover\:scale-105 {
    transform: scale(1.05);
}
</style>
@endsection