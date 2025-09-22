@extends('layouts.app')

@section('title', 'My Test Results')

@section('content')
<div class="container mx-auto px-4">
    <h1 class="text-4xl font-bold mb-6">My Test Results</h1>
    
    @if($attempts->isEmpty())
        <div class="bg-white p-8 rounded-lg shadow-md text-center">
            <p class="text-xl text-gray-600">You haven't taken any tests yet.</p>
            <a href="/cbt" class="mt-4 inline-block bg-blue-600 text-white px-6 py-3 rounded hover:bg-blue-700">
                Start a Test
            </a>
        </div>
    @else
        <div class="bg-white p-6 rounded-lg shadow-md">
            <table class="w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 text-left">Date</th>
                        <th class="p-3 text-left">Course</th>
                        <th class="p-3 text-center">Total Questions</th>
                        <th class="p-3 text-center">Correct Answers</th>
                        <th class="p-3 text-center">Score</th>
                        <th class="p-3 text-left">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attempts as $attempt)
                        <tr class="border-b">
                            <td class="p-3">
                                {{ $attempt->created_at->format('M d, Y') }}
                            </td>
                            <td class="p-3 font-bold">
                                {{ $attempt->course->title }}
                            </td>
                            <td class="p-3 text-center">
                                {{ $attempt->total_questions }}
                            </td>
                            <td class="p-3 text-center">
                                {{ $attempt->correct_answers }}
                            </td>
                            <td class="p-3 text-center">
                                <span class="{{ $attempt->score_percentage >= 70 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($attempt->score_percentage, 2) }}%
                                </span>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-1 rounded 
                                    {{ $attempt->score_percentage >= 70 ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800' }}">
                                    {{ $attempt->score_percentage >= 70 ? 'Passed' : 'Failed' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
