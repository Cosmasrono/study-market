@extends('layouts.app')

@section('title', 'Test Results - ' . $course->title)

@section('content')
<div class="container mx-auto px-4">
    <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md text-center">
        <h1 class="text-3xl font-bold mb-6">{{ $course->title }} - Test Results</h1>
        
        <div class="mb-6">
            @php
                $resultClass = $scorePercentage >= 70 ? 'text-green-600' : 'text-red-600';
            @endphp
            
            <div class="text-6xl font-bold {{ $resultClass }} mb-4">
                {{ number_format($scorePercentage, 2) }}%
            </div>
            
            <div class="text-xl mb-2">
                {{ $correctAnswers }} / {{ $totalQuestions }} Correct
            </div>
            
            <div class="{{ $resultClass }} font-semibold">
                @if($scorePercentage >= 70)
                    Congratulations! You Passed
                @else
                    Sorry, You Did Not Pass
                @endif
            </div>
        </div>
        
        <div class="bg-gray-100 p-4 rounded-lg">
            <h2 class="text-xl font-semibold mb-4">Performance Breakdown</h2>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <div class="text-gray-600">Total Questions</div>
                    <div class="font-bold text-lg">{{ $totalQuestions }}</div>
                </div>
                
                <div>
                    <div class="text-green-600">Correct Answers</div>
                    <div class="font-bold text-lg text-green-700">{{ $correctAnswers }}</div>
                </div>
                
                <div>
                    <div class="text-red-600">Incorrect Answers</div>
                    <div class="font-bold text-lg text-red-700">{{ $totalQuestions - $correctAnswers }}</div>
                </div>
                
                <div>
                    <div class="text-blue-600">Passing Score</div>
                    <div class="font-bold text-lg text-blue-700">70%</div>
                </div>
            </div>
        </div>
        
        <div class="mt-6 flex justify-center space-x-4">
            <a href="/cbt" class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700">
                Back to Courses
            </a>
            
            <a href="#" class="bg-green-600 text-white px-6 py-3 rounded-md hover:bg-green-700">
                Review Answers
            </a>
        </div>
    </div>
</div>
@endsection
