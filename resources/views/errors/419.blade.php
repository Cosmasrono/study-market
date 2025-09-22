@extends('layouts.app')

@section('title', 'Page Expired')

@section('content')
<div class="container mx-auto px-4 py-16 text-center">
    <div class="max-w-md mx-auto bg-white shadow-lg rounded-lg p-8">
        <div class="mb-6">
            <svg class="mx-auto h-16 w-16 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-800 mb-4">Page Expired</h1>
        <p class="text-gray-600 mb-6">
            Your session has timed out. This can happen if you've been inactive for too long or if you've logged out in another tab.
        </p>
        <div class="space-y-4">
            <a href="{{ route('login') }}" class="w-full bg-maroon-600 text-white py-3 rounded hover:bg-maroon-700 transition-colors">
                Login Again
            </a>
            <a href="{{ url('/') }}" class="w-full bg-gray-200 text-gray-800 py-3 rounded hover:bg-gray-300 transition-colors">
                Go to Home
            </a>
        </div>
    </div>
</div>
@endsection
