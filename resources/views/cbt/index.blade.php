@extends('layouts.app')

@section('title', 'Computer-Based Test')

@section('content')
<div class="container mx-auto px-4">
    <h1 class="text-4xl font-bold mb-6 text-center">Computer-Based Test (CBT)</h1>
    
    @if($courses->isEmpty())
        <div class="text-center py-12 bg-white rounded-lg shadow-md">
            <p class="text-xl text-gray-600">No CBT courses are currently available.</p>
        </div>
    @else
        <div class="max-w-4xl mx-auto">
            <div class="bg-white p-8 rounded-lg shadow-md">
                <h2 class="text-2xl font-semibold mb-4 text-center">Select a Course to Start Your Test</h2>
                
                <div class="grid md:grid-cols-2 gap-4">
                    @foreach($courses as $course)
                        <div class="bg-gray-100 p-4 rounded-lg hover:bg-gray-200 transition">
                            <h3 class="text-xl font-bold mb-2">{{ $course->title }}</h3>
                            <p class="text-gray-600 mb-4">{{ $course->description }}</p>
                            
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">
                                    Difficulty: {{ $course->difficulty_level }}
                                </span>
                                
                                <a href="{{ url("/cbt/{$course->id}") }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                                    Start Test
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
