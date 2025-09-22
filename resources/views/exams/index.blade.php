@extends('layouts.app')

@section('title', 'Mock Exams')

@section('content')
<div class="container mx-auto px-4 py-16">
    <h1 class="text-4xl font-bold text-center mb-12">Mock Exams</h1>

    @if($errors->any())
    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
        <p class="text-red-700">
            @foreach($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
        </p>
    </div>
    @endif

    <div class="grid md:grid-cols-3 gap-8">
        @forelse($exams as $exam)
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            @if($exam->thumbnail)
            <img src="{{ $exam->thumbnail }}" 
                 alt="{{ $exam->title }}" 
                 class="w-full h-64 object-cover">
            @endif
            
            <div class="p-6">
                <h2 class="text-2xl font-bold mb-4">{{ $exam->title }}</h2>
                <p class="text-gray-600 mb-4">{{ Str::limit($exam->description, 100) }}</p>
                
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <span class="text-lg font-semibold text-primary-500">
                            {{ $exam->is_free ? 'Free' : 'Paid' }}
                            @if(!$exam->is_free)
                                - KSh {{ number_format($exam->price, 2) }}
                            @endif
                        </span>
                        @if($exam->duration)
                        <span class="ml-4 text-gray-600 text-sm">
                            Duration: {{ $exam->duration }} minutes
                        </span>
                        @endif
                    </div>
                </div>

                @auth
                    @if(auth()->user()->hasMembership())
                        <div class="space-y-2">
                            <a href="{{ route('exams.show', $exam->id) }}" 
                               class="w-full block text-center bg-primary-500 text-white px-4 py-2 rounded hover:bg-primary-600 transition">
                                View Exam Details
                            </a>

                            @if($exam->can_start)
                                <a href="{{ route('exams.start', $exam->id) }}" 
                                   class="w-full block text-center bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition">
                                    Start Exam
                                </a>
                            @else
                                <a href="{{ route('exams.purchase', $exam->id) }}" 
                                   class="w-full block text-center bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition">
                                    Purchase Exam
                                </a>
                            @endif

                            @if($exam->previous_result)
                                <a href="{{ route('exams.result', $exam->previous_result->id) }}" 
                                   class="w-full block text-center bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition">
                                    View Previous Result
                                </a>
                            @endif
                        </div>
                    @else
                        <a href="{{ route('membership.payment') }}" 
                           class="w-full block text-center bg-primary-500 text-white px-4 py-2 rounded hover:bg-primary-600 transition">
                            Complete Membership
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}" 
                       class="w-full block text-center bg-primary-500 text-white px-4 py-2 rounded hover:bg-primary-600 transition">
                        Login to Access
                    </a>
                @endauth
            </div>
        </div>
        @empty
        <div class="col-span-full text-center">
            <p class="text-gray-600">No exams are currently available.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
