@extends('layouts.app')

@section('title', 'Frequently Asked Questions')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-center">Frequently Asked Questions</h1>

    @if($faqs->isEmpty())
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 max-w-2xl mx-auto">
            <p class="text-yellow-700 text-center">No FAQs are currently available.</p>
        </div>
    @else
        <div class="max-w-2xl mx-auto">
            @foreach($faqs as $faq)
                <div class="mb-4 border-b pb-4">
                    <h2 class="text-xl font-semibold text-gray-800 mb-2">
                        {{ $faq->question }}
                    </h2>
                    <p class="text-gray-600">
                        {{ $faq->answer }}
                    </p>
                </div>
            @endforeach
        </div>
    @endif

    <div class="mt-8 text-center">
        <p class="text-gray-600">
            Can't find the answer you're looking for? 
            <a href="{{ route('contact') }}" class="text-blue-600 hover:underline">Contact Us</a>
        </p>
        <p class="mt-4">
            <a href="{{ route('testimonials.index') }}" class="text-blue-600 hover:underline">
                Read Member Testimonials
            </a>
        </p>
    </div>
</div>
@endsection
