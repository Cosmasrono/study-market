@extends('layouts.app')

@section('title', 'My Testimonials')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-center">My Testimonials</h1>

    <div class="max-w-4xl mx-auto">
        <div class="mb-6 text-right">
            <a href="{{ route('testimonials.create') }}" 
               class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-300">
                Submit New Testimonial
            </a>
        </div>

        @if($testimonials->isEmpty())
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 text-center">
                <p class="text-blue-700">You haven't submitted any testimonials yet.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($testimonials as $testimonial)
                    <div class="bg-white shadow-md rounded-lg p-6 relative">
                        <div class="flex items-start mb-4">
                            <div class="flex-grow">
                                <h3 class="text-lg font-semibold text-gray-800">
                                    {{ $testimonial->name }}
                                </h3>
                                @if($testimonial->position && $testimonial->company)
                                    <p class="text-sm text-gray-600">
                                        {{ $testimonial->position }} at {{ $testimonial->company }}
                                    </p>
                                @endif
                            </div>
                            
                            {{-- Status Badge --}}
                            <div class="ml-auto">
                                @switch($testimonial->status)
                                    @case('pending')
                                        <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs">
                                            Pending Review
                                        </span>
                                        @break
                                    @case('approved')
                                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs">
                                            Approved
                                        </span>
                                        @break
                                    @case('rejected')
                                        <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs">
                                            Rejected
                                        </span>
                                        @break
                                @endswitch
                            </div>
                        </div>

                        <div class="flex items-center mb-4">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-5 h-5 {{ $i <= $testimonial->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endfor
                        </div>

                        <p class="text-gray-600 italic mb-4">
                            "{{ $testimonial->content }}"
                        </p>

                        @if($testimonial->status === 'rejected' && $testimonial->admin_comment)
                            <div class="bg-red-50 border-l-4 border-red-400 p-3 mt-4">
                                <p class="text-sm text-red-700">
                                    <strong>Admin Feedback:</strong> {{ $testimonial->admin_comment }}
                                </p>
                            </div>
                        @endif

                        <div class="text-sm text-gray-500 mt-2">
                            Submitted on: {{ $testimonial->created_at->format('M d, Y \a\t h:i A') }}
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $testimonials->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
