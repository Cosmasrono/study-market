@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container mx-auto px-4">
    <h1 class="text-4xl font-bold mb-6">Dashboard</h1>
    
    <div class="grid md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-semibold mb-4">Profile</h2>
            <div class="text-gray-700">
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Joined:</strong> {{ $user->created_at->format('M d, Y') }}</p>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-semibold mb-4">Enrollments</h2>
            @if($enrollments->isEmpty())
                <p class="text-gray-600">No courses enrolled yet.</p>
            @else
                <ul class="space-y-2">
                    @foreach($enrollments as $enrollment)
                        <li>
                            <span class="font-bold">{{ $enrollment->course->title }}</span>
                            <span class="text-sm text-gray-600 block">
                                Enrolled: {{ $enrollment->created_at->format('M d, Y') }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-semibold mb-4">Recent Payments</h2>
            @if($payments->isEmpty())
                <p class="text-gray-600">No payments made yet.</p>
            @else
                <ul class="space-y-2">
                    @foreach($payments->take(3) as $payment)
                        <li>
                            <span class="font-bold">${{ number_format($payment->amount, 2) }}</span>
                            <span class="text-sm text-gray-600 block">
                                {{ $payment->course ? $payment->course->title : $payment->book->title }}
                                - {{ $payment->created_at->format('M d, Y') }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
    
    <div class="mt-6 grid md:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-semibold mb-4">Test Attempts</h2>
            @if($attempts->isEmpty())
                <p class="text-gray-600">No test attempts yet.</p>
            @else
                <ul class="space-y-2">
                    @foreach($attempts->take(3) as $attempt)
                        <li>
                            <span class="font-bold">{{ $attempt->course->title }}</span>
                            <span class="text-sm text-gray-600 block">
                                Score: {{ number_format($attempt->score_percentage, 2) }}%
                                - {{ $attempt->created_at->format('M d, Y') }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-semibold mb-4">Quick Links</h2>
            <div class="space-y-2">
                <a href="{{ route('orders') }}" class="block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    View All Orders
                </a>
                <a href="{{ route('results') }}" class="block bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    View All Test Results
                </a>
                <a href="/program" class="block bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                    Browse Courses
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
