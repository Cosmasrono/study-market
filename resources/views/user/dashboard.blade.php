@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Welcome Header -->
    <div class="bg-gradient-to-r from-primary-600 to-primary-800 text-white rounded-lg shadow-lg p-6 mb-8">
        <h1 class="text-3xl font-bold mb-2">Welcome back, {{ $user->name }}! 👋</h1>
        <p class="text-primary-100">Your dashboard overview</p>
    </div>

    <!-- Membership Status Card -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8 border-l-4 {{ $membershipDetails['color'] === 'green' ? 'border-green-500' : ($membershipDetails['color'] === 'yellow' ? 'border-yellow-500' : 'border-red-500') }}">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold mb-2">Membership Status</h2>
                <div class="flex items-center space-x-3">
                    <span class="px-4 py-2 rounded-full text-sm font-medium {{ $stats['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ ucfirst($stats['membership_status']) }}
                    </span>
                    @if($stats['subscription_type'])
                        <span class="text-gray-600">
                            {{ str_replace('_', ' ', ucfirst($stats['subscription_type'])) }}
                        </span>
                    @endif
                </div>
                @if($stats['subscription_expires'])
                    <p class="text-gray-600 mt-3">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        @if($stats['is_active'])
                            Expires: {{ $stats['subscription_expires']->format('M d, Y') }}
                            <span class="ml-2 text-sm text-gray-500">({{ $stats['days_until_expiry'] }} days remaining)</span>
                        @else
                            Expired: {{ $stats['subscription_expires']->format('M d, Y') }}
                        @endif
                    </p>
                @endif
            </div>
            <div class="text-right">
                @if(!$stats['is_active'] || $stats['days_until_expiry'] <= 30)
                    <a href="{{ route('membership.renew') }}" class="bg-primary-600 text-white px-6 py-3 rounded-lg hover:bg-primary-700 transition-colors duration-300 inline-flex items-center">
                        <i class="fas fa-sync-alt mr-2"></i>Renew Membership
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Statistics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Total Payments -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm mb-1">Total Payments</p>
                    <h3 class="text-3xl font-bold text-primary-700">{{ $stats['total_payments'] }}</h3>
                </div>
                <div class="bg-primary-100 rounded-full p-4">
                    <i class="fas fa-receipt text-primary-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Spent -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm mb-1">Total Spent</p>
                    <h3 class="text-3xl font-bold text-green-600">KSh {{ number_format($stats['total_spent'], 2) }}</h3>
                </div>
                <div class="bg-green-100 rounded-full p-4">
                    <i class="fas fa-money-bill-wave text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Days Remaining -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm mb-1">Days Remaining</p>
                    <h3 class="text-3xl font-bold {{ $stats['days_until_expiry'] <= 7 ? 'text-red-600' : ($stats['days_until_expiry'] <= 30 ? 'text-yellow-600' : 'text-blue-600') }}">
                        {{ $stats['days_until_expiry'] }}
                    </h3>
                </div>
                <div class="bg-blue-100 rounded-full p-4">
                    <i class="fas fa-clock text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <a href="{{ route('books') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-300 text-center">
            <i class="fas fa-book text-primary-600 text-3xl mb-3"></i>
            <p class="font-semibold">Browse Books</p>
        </a>
        <a href="{{ route('videos.index') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-300 text-center">
            <i class="fas fa-video text-primary-600 text-3xl mb-3"></i>
            <p class="font-semibold">Watch Videos</p>
        </a>
        <a href="{{ route('membership.history') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-300 text-center">
            <i class="fas fa-history text-primary-600 text-3xl mb-3"></i>
            <p class="font-semibold">Payment History</p>
        </a>
        <a href="{{ route('user.profile') }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-300 text-center">
            <i class="fas fa-user-cog text-primary-600 text-3xl mb-3"></i>
            <p class="font-semibold">Profile Settings</p>
        </a>
    </div>

    <!-- Recent Payments -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold">Recent Payments</h2>
            <div class="flex items-center space-x-3">
                <a href="{{ route('membership.history') }}" class="text-primary-600 hover:underline text-sm">View All</a>
                <a href="{{ route('account.print-receipts') }}" class="bg-green-100 text-green-800 px-3 py-2 rounded-lg text-sm hover:bg-green-200 transition-colors duration-300 inline-flex items-center">
                    <i class="fas fa-print mr-2"></i>Print Receipts
                </a>
            </div>
        </div>

        @if($recentPayments->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($recentPayments as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm">{{ $payment->created_at->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-sm"><code>{{ $payment->reference }}</code></td>
                            <td class="px-4 py-3 text-sm font-medium">KSh {{ number_format($payment->amount, 2) }}</td>
                            <td class="px-4 py-3 text-sm">
                                <span class="px-3 py-1 rounded-full text-xs font-medium {{ $payment->status === 'completed' ? 'bg-green-100 text-green-800' : ($payment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-inbox text-4xl mb-3"></i>
                <p>No payment history yet</p>
            </div>
        @endif
    </div>

    <!-- Print Reports Section -->
    <div class="mt-8 bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold">Generate Reports</h2>
            <a href="{{ route('account.print-reports') }}" class="bg-blue-100 text-blue-800 px-3 py-2 rounded-lg text-sm hover:bg-blue-200 transition-colors duration-300 inline-flex items-center">
                <i class="fas fa-file-alt mr-2"></i>Generate Comprehensive Report
            </a>
        </div>
        <p class="text-gray-600">Download a detailed report of your account activity, including membership status, payments, and exam results.</p>
    </div>
</div>
@endsection