@extends('layouts.app')

@section('title', 'Transactions')

@section('content')
<div class="container mx-auto px-4">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-6">
                    <div class="h-20 w-20 bg-blue-600 rounded-full flex items-center justify-center text-white text-2xl font-bold">
                        {{ substr($user->name, 0, 2) }}
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">{{ $user->name }}</h1>
                        <p class="text-gray-600">{{ $user->email }}</p>
                        <p class="text-sm text-blue-600">Member since {{ $user->created_at->format('F Y') }}</p>
                    </div>
                </div>

                <!-- PDF Report Generation Section -->
                <div class="flex space-x-3">
                    <a href="{{ route('user.pdf.payment-report') }}" 
                       class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition-colors flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3a1 1 0 102 0V8zm-3 4a1 1 0 100 2h3a1 1 0 100-2H8z" clip-rule="evenodd"></path>
                        </svg>
                        Payment Report
                    </a>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">Transaction History</h2>
                
                <!-- Optional Date Range Filter -->
                <form method="GET" class="flex items-center space-x-2">
                    <input type="date" name="start_date" 
                           class="border border-gray-300 rounded px-2 py-1 text-sm"
                           value="{{ request('start_date') }}">
                    <input type="date" name="end_date" 
                           class="border border-gray-300 rounded px-2 py-1 text-sm"
                           value="{{ request('end_date') }}">
                    <button type="submit" 
                            class="bg-primary-600 text-white px-3 py-1 rounded text-sm hover:bg-primary-700">
                        Filter
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($transactions as $transaction)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $transaction->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @switch($transaction->content_type)
                                        @case('book')
                                            Book Purchase
                                            @break
                                        @case('video')
                                            Video Purchase
                                            @break
                                        @case('membership')
                                            Membership
                                            @break
                                        @default
                                            Other
                                    @endswitch
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    KSh {{ number_format($transaction->amount, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold 
                                        {{ $transaction->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }} 
                                        rounded-full">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    @if($transaction->content_type === 'membership' && $transaction->status === 'completed')
                                        <a href="{{ route('user.pdf.receipt', $transaction->id) }}" 
                                           class="text-blue-600 hover:text-blue-900 mr-2">
                                            Receipt
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    No transactions found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($transactions->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection