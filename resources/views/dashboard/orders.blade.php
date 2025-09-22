@extends('layouts.app')

@section('title', 'My Orders')

@section('content')
<div class="container mx-auto px-4">
    <h1 class="text-4xl font-bold mb-6">My Orders</h1>
    
    @if($payments->isEmpty())
        <div class="bg-white p-8 rounded-lg shadow-md text-center">
            <p class="text-xl text-gray-600">You haven't made any purchases yet.</p>
            <a href="/program" class="mt-4 inline-block bg-blue-600 text-white px-6 py-3 rounded hover:bg-blue-700">
                Browse Courses
            </a>
        </div>
    @else
        <div class="bg-white p-6 rounded-lg shadow-md">
            <table class="w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 text-left">Date</th>
                        <th class="p-3 text-left">Item</th>
                        <th class="p-3 text-left">Type</th>
                        <th class="p-3 text-right">Amount</th>
                        <th class="p-3 text-left">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                        <tr class="border-b">
                            <td class="p-3">
                                {{ $payment->created_at->format('M d, Y') }}
                            </td>
                            <td class="p-3 font-bold">
                                {{ $payment->course ? $payment->course->title : $payment->book->title }}
                            </td>
                            <td class="p-3">
                                {{ $payment->course ? 'Course' : 'Book' }}
                            </td>
                            <td class="p-3 text-right">
                                ${{ number_format($payment->amount, 2) }}
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-1 rounded 
                                    {{ $payment->status === 'completed' ? 'bg-green-200 text-green-800' : 
                                       ($payment->status === 'pending' ? 'bg-yellow-200 text-yellow-800' : 'bg-red-200 text-red-800') }}">
                                    {{ ucfirst($payment->status) }}
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
