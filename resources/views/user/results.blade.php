@extends('layouts.app')

@section('title', 'My Results')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">My Test Results</h1>

    @if($results->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($results as $result)
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold mb-3">{{ $result->exam->title ?? 'Exam' }}</h3>
                <div class="space-y-2">
                    <p><strong>Score:</strong> {{ $result->score }}%</p>
                    <p><strong>Date:</strong> {{ $result->created_at->format('M d, Y') }}</p>
                    <p><strong>Status:</strong> 
                        <span class="px-3 py-1 rounded-full text-xs {{ $result->score >= 50 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $result->score >= 50 ? 'Passed' : 'Failed' }}
                        </span>
                    </p>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $results->links() }}
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <i class="fas fa-clipboard-list text-gray-400 text-6xl mb-4"></i>
            <p class="text-gray-600">No test results yet</p>
        </div>
    @endif
</div>
@endsection