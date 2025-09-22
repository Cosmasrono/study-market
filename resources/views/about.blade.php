@extends('layouts.app')

@section('title', 'About Us')

@section('content')
<div class="container mx-auto px-4">
    <h1 class="text-4xl font-bold mb-6 text-center animate-fade-in">About Inzoberi School of Professional</h1>
    
    <div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-md animate-slide-in">
        <section class="mb-6 transform transition-all duration-700 hover:scale-[1.02] hover:shadow-lg">
            <h2 class="text-2xl font-semibold mb-4 text-primary-700">Our Mission</h2>
            <p class="text-gray-700 leading-relaxed">
                Inzoberi School of Professional is dedicated to providing high-quality educational resources and comprehensive test preparation 
                to help students and professionals achieve their academic and career goals. We believe in empowering 
                learners through innovative, accessible, and effective learning solutions.
            </p>
        </section>

        <section class="mb-6 transform transition-all duration-700 hover:scale-[1.02] hover:shadow-lg">
            <h2 class="text-2xl font-semibold mb-4 text-primary-700">What We Offer</h2>
            <ul class="list-disc list-inside text-gray-700 space-y-2">
                @php
                    $offerings = [
                        'Comprehensive Computer-Based Test (CBT) Preparation',
                        'Expertly Designed Online Courses',
                        'Extensive Library of Educational Books',
                        'Engaging Instructional Videos',
                        'Personalized Learning Paths'
                    ];
                @endphp
                @foreach($offerings as $index => $offering)
                    <li class="transform transition-all duration-500 hover:translate-x-4 hover:text-primary-600" 
                        style="transition-delay: {{ $index * 100 }}ms">
                        {{ $offering }}
                    </li>
                @endforeach
            </ul>
        </section>
        
        <section class="transform transition-all duration-700 hover:scale-[1.02] hover:shadow-lg">
            <h2 class="text-2xl font-semibold mb-4 text-primary-700">Our Team</h2>
            <p class="text-gray-700 leading-relaxed">
                Our team consists of experienced educators, subject matter experts, and technology professionals 
                who are passionate about education and committed to helping learners succeed.
            </p>
        </section>
    </div>
</div>

@push('styles')
<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes slideIn {
        from { transform: translateX(-100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    .animate-fade-in {
        animation: fadeIn 1s ease-out;
    }

    .animate-slide-in {
        animation: slideIn 0.7s ease-out;
    }
</style>
@endpush

@endsection
