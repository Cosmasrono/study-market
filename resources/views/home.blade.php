@extends('layouts.app')

@section('title', 'Home')

@section('content')
<div class="bg-[#8C1C13] text-white">
    <div class="container mx-auto px-4 py-16 text-center">
        <h1 class="text-4xl font-bold mb-4">Welcome to Inzoberi School of Professionals</h1>
        <p class="text-lg mb-8">Access high-quality educational resources, books, and videos to advance your professional career.</p>
        
        @guest
        <div class="space-x-4">
            <a href="{{ route('register') }}" class="bg-white text-[#8C1C13] px-6 py-3 rounded-md font-semibold hover:bg-gray-100 transition">Register Now</a>
            <a href="#resources" class="bg-transparent border border-white text-white px-6 py-3 rounded-md font-semibold hover:bg-white hover:text-[#8C1C13] transition">Browse Resources</a>
        </div>
        @else
            @if (!auth()->user()->hasMembership())
            <div class="space-x-4">
                <a href="{{ route('membership.payment') }}" class="bg-white text-[#8C1C13] px-6 py-3 rounded-md font-semibold hover:bg-gray-100 transition">Complete Membership</a>
            </div>
            @endif
        @endguest
    </div>
</div>

<div id="resources" class="container mx-auto px-4 py-16">
    <h2 class="text-3xl font-bold text-center mb-12">Available Resources</h2>
    
    <div class="grid md:grid-cols-3 gap-8">
        <div class="bg-white p-6 rounded-lg shadow-md text-center">
            <div class="flex justify-center mb-4">
                <svg class="w-16 h-16 text-[#8C1C13]" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M9 4h6v2H9zm0 2H5v14h14V6h-4v2H9V6zm8-2v2h3v16H4V6h3V4h10z"/>
                </svg>
            </div>
            <h3 class="text-2xl font-semibold mb-4">Educational Books</h3>
            <p class="mb-4">Access our comprehensive collection of professional textbooks and study materials.</p>
            <a href="{{ route('books') }}" class="inline-block bg-[#8C1C13] text-white px-6 py-2 rounded hover:bg-[#6E1610] transition">View Books</a>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-md text-center">
            <div class="flex justify-center mb-4">
                <svg class="w-16 h-16 text-[#8C1C13]" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M4 6h16v12H4zm2 2v8h12V8zm3 7V9l5 3z"/>
                </svg>
            </div>
            <h3 class="text-2xl font-semibold mb-4">Tutorial Videos</h3>
            <p class="mb-4">Watch instructional videos covering various professional topics and courses.</p>
            <a href="{{ route('videos.index') }}" class="inline-block bg-[#8C1C13] text-white px-6 py-2 rounded hover:bg-[#6E1610] transition">Watch Videos</a>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-md text-center">
            <div class="flex justify-center mb-4">
                <svg class="w-16 h-16 text-[#8C1C13]" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm7 16H5V5h2v3h10V5h2v14zm-5-6h-2v2h2v-2zm-4 0H7v2h2v-2zm6 0h-2v2h2v-2z"/>
                </svg>
            </div>
            <h3 class="text-2xl font-semibold mb-4">Mock Exams</h3>
            <p class="mb-4">Practice with our mock exams to prepare for your professional certifications.</p>
            <a href="{{ route('exams.index') }}" class="inline-block bg-[#8C1C13] text-white px-6 py-2 rounded hover:bg-[#6E1610] transition">Take Exams</a>
        </div>
    </div>
</div>

<div class="bg-gray-100 py-16">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold mb-6">Ready to Start Learning?</h2>
        <p class="text-lg mb-8">Join thousands of students who have advanced their careers with Inzoberi School of Professionals.</p>
        @guest
        <a href="{{ route('register') }}" class="bg-[#8C1C13] text-white px-8 py-3 rounded-md font-semibold hover:bg-[#6E1610] transition">Register Today</a>
        @else
            @if (!auth()->user()->hasMembership())
            <a href="{{ route('membership.payment') }}" class="bg-[#8C1C13] text-white px-8 py-3 rounded-md font-semibold hover:bg-[#6E1610] transition">Complete Membership</a>
            @endif
        @endguest
    </div>
</div>
@endsection
