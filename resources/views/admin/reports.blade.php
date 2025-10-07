@extends('layouts.admin')

@section('title', 'Admin Reports')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Reports Center</h1>
            <p class="text-gray-600 mt-1">Generate and download comprehensive reports</p>
        </div>
        
        <div class="flex space-x-3">
            <a href="{{ route('admin.dashboard') }}" 
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded transition-colors">
                Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            {{ session('error') }}
        </div>
    @endif

    <!-- Reports Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Membership Reports -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-semibold mb-4 text-maroon-800">Membership Reports</h3>
            <div class="space-y-4">
                <div class="bg-maroon-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium mb-2">Membership Status</h4>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Active, Expiring, Expired</span>
                        <form method="POST" action="{{ route('admin.reports.download') }}">
                            @csrf
                            <input type="hidden" name="report_type" value="membership_status">
                            <button type="submit" class="bg-maroon-600 text-white px-3 py-1 rounded text-sm hover:bg-maroon-700">
                                Download CSV
                            </button>
                        </form>
                    </div>
                </div>

                <div class="bg-maroon-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium mb-2">Membership Expirations</h4>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Users expiring in next 30 days</span>
                        <form method="POST" action="{{ route('admin.reports.download') }}">
                            @csrf
                            <input type="hidden" name="report_type" value="expiring_memberships">
                            <button type="submit" class="bg-maroon-600 text-white px-3 py-1 rounded text-sm hover:bg-maroon-700">
                                Download CSV
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Reports -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-semibold mb-4 text-maroon-800">Financial Reports</h3>
            <div class="space-y-4">
                <div class="bg-green-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium mb-2">Revenue Summary</h4>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Monthly & Yearly Revenue</span>
                        <form method="POST" action="{{ route('admin.reports.download') }}">
                            @csrf
                            <input type="hidden" name="report_type" value="revenue_summary">
                            <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                                Download CSV
                            </button>
                        </form>
                    </div>
                </div>

                <div class="bg-green-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium mb-2">Payment Transactions</h4>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">All completed transactions</span>
                        <form method="POST" action="{{ route('admin.reports.download') }}">
                            @csrf
                            <input type="hidden" name="report_type" value="payment_transactions">
                            <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                                Download CSV
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Reports -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-semibold mb-4 text-maroon-800">User Reports</h3>
            <div class="space-y-4">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium mb-2">User Registration</h4>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Monthly user registrations</span>
                        <form method="POST" action="{{ route('admin.reports.download') }}">
                            @csrf
                            <input type="hidden" name="report_type" value="user_registrations">
                            <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                                Download CSV
                            </button>
                        </form>
                    </div>
                </div>

                <div class="bg-blue-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium mb-2">User Activity</h4>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">User engagement metrics</span>
                        <form method="POST" action="{{ route('admin.reports.download') }}">
                            @csrf
                            <input type="hidden" name="report_type" value="user_activity">
                            <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                                Download CSV
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Box -->
    <div class="mt-8 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    All reports are generated in CSV format and include data up to the current date. Revenue reports only include completed transactions.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection