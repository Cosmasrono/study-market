@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Membership Payments</h1>

    @if($membershipPayments->isEmpty())
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 text-center">
            <p class="text-blue-700">You have not made any membership payments yet.</p>
            <a href="{{ route('membership.payment') }}" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                Make a Payment
            </a>
        </div>
    @else
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100 border-b">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Membership Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($membershipPayments as $payment)
                        <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }} hover:bg-gray-100 transition">
                            <td class="px-4 py-4 whitespace-nowrap">
                                {{ $payment->created_at->format('Y-m-d') }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                {{ $payment->formatted_duration }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                KES {{ number_format($payment->amount, 2) }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                {{ ucfirst($payment->payment_method) }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full 
                                    {{ $payment->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                       ($payment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                @if($payment->status === 'completed')
                                    <a href="{{ route('pdf.membership.receipt', $payment->id) }}" 
                                       class="text-blue-600 hover:text-blue-900 flex items-center">
                                        <i class="fas fa-download mr-2"></i>Receipt
                                    </a>
                                @else
                                    <span class="text-gray-400 cursor-not-allowed">
                                        Receipt Unavailable
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($membershipPayments->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $membershipPayments->links() }}
            </div>
            @endif
        </div>
    @endif
</div>
@endsection
