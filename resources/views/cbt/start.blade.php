@extends('layouts.app')

@section('title', 'Start Test - ' . $course->title)

@section('content')
<div class="container mx-auto px-4">
    <div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-md">
        <h1 class="text-3xl font-bold mb-6 text-center">{{ $course->title }} - CBT Test</h1>
        
        @if($questions->isEmpty())
            <div class="text-center py-12">
                <p class="text-xl text-gray-600">No questions available for this test.</p>
            </div>
        @else
            <form method="POST" action="{{ url("/cbt/{$course->id}/submit") }}">
                @csrf
                
                @foreach($questions as $index => $question)
                    <div class="mb-6 p-4 bg-gray-100 rounded-lg">
                        <h3 class="text-xl font-semibold mb-4">
                            Question {{ $index + 1 }}: {{ $question->question_text }}
                        </h3>
                        
                        <div class="space-y-2">
                            @foreach(['A', 'B', 'C', 'D'] as $option)
                                <label class="block">
                                    <input type="radio" 
                                           name="answer_{{ $question->id }}" 
                                           value="{{ $option }}" 
                                           class="mr-2"
                                           required>
                                    {{ $question->{'option_' . strtolower($option)} }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
                
                <div class="text-center">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700">
                        Submit Test
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection
