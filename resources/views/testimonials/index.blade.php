@extends('layouts.app')

@section('title', 'Member Testimonials')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-center">What Our Members Say</h1>

    @auth
    <div class="text-center mb-8">
        <a href="{{ route('testimonials.create') }}" 
           class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 transition duration-300 inline-block">
            Share Your Experience
        </a>
    </div>
    @endauth

    @if($testimonials->isEmpty())
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 max-w-2xl mx-auto">
            <p class="text-blue-700 text-center">No testimonials are currently available.</p>
        </div>
    @else
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
            @foreach($testimonials as $testimonial)
                <div class="bg-white shadow-md rounded-lg p-6 transform transition duration-300 hover:scale-105">
                    <div class="flex items-start mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">
                                {{ $testimonial->name }}
                            </h3>
                            @if($testimonial->position && $testimonial->company)
                                <p class="text-sm text-gray-600">
                                    {{ $testimonial->position }} at {{ $testimonial->company }}
                                </p>
                            @endif
                        </div>
                        @if($testimonial->rating)
                            <div class="ml-auto flex items-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-5 h-5 {{ $i <= $testimonial->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                @endfor
                            </div>
                        @endif
                    </div>
                    <p class="text-gray-600 italic">
                        "{{ $testimonial->content }}"
                    </p>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-8 flex justify-center">
            {{ $testimonials->links() }}
        </div>
    @endif

    <div class="mt-8 text-center">
        <p class="text-gray-600">
            Want to share your experience? 
            @auth
                <a href="{{ route('testimonials.create') }}" class="text-blue-600 hover:underline">Submit Your Testimonial</a>
            @else
                <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Login to Submit a Testimonial</a>
            @endauth
        </p>
    </div>
</div>
@endsection

