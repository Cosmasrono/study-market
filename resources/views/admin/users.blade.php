@extends('layouts.admin')

@section('title', 'Manage Users')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">User Management</h1>
            <p class="text-gray-600 mt-1">Manage all registered users and their accounts</p>
        </div>
        
        <div class="flex space-x-3">
            <a href="{{ route('admin.dashboard') }}" 
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded transition-colors">
                Back to Dashboard
            </a>
        </div>
    </div>



    <!-- Quick Reports Section -->
<div class="bg-white rounded-lg shadow-md p-6 mb-8">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Quick Reports</h3>
            <p class="text-sm text-gray-500">Generate user-related reports</p>
        </div>
        <a href="{{ route('admin.reports') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
            View All Reports →
        </a>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- User Activity Report -->
        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
            <div class="flex items-center mb-3">
                <div class="bg-blue-500 rounded-full p-2 mr-3">
                    <i class="fas fa-chart-bar text-white"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900">User Activity</h4>
                    <p class="text-xs text-gray-500">Engagement & spending</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.reports.download') }}">
                @csrf
                <input type="hidden" name="report_type" value="user_activity">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm transition-colors">
                    <i class="fas fa-download mr-2"></i>Download CSV
                </button>
            </form>
        </div>

        <!-- User Registrations Report -->
        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
            <div class="flex items-center mb-3">
                <div class="bg-green-500 rounded-full p-2 mr-3">
                    <i class="fas fa-user-plus text-white"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900">Registrations</h4>
                    <p class="text-xs text-gray-500">Monthly sign-ups</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.reports.download') }}">
                @csrf
                <input type="hidden" name="report_type" value="user_registrations">
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-sm transition-colors">
                    <i class="fas fa-download mr-2"></i>Download CSV
                </button>
            </form>
        </div>

        <!-- Membership Status Report -->
        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
            <div class="flex items-center mb-3">
                <div class="bg-purple-500 rounded-full p-2 mr-3">
                    <i class="fas fa-id-card text-white"></i>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900">Membership Status</h4>
                    <p class="text-xs text-gray-500">All users overview</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.reports.download') }}">
                @csrf
                <input type="hidden" name="report_type" value="membership_status">
                <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded text-sm transition-colors">
                    <i class="fas fa-download mr-2"></i>Download CSV
                </button>
            </form>
        </div>
    </div>
</div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Users</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $users->total() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Active Members</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        {{ $users->filter(function($user) { return $user->hasMembership(); })->count() }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Pending Members</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        {{ $users->where('membership_status', 'pending')->count() }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">New This Month</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        {{ $users->filter(function($user) { return $user->created_at->isCurrentMonth(); })->count() }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6 alert-auto-hide">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 alert-auto-hide">
            {{ session('error') }}
        </div>
    @endif

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">All Users</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Membership</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center">
                                            <span class="text-sm font-medium text-white">
                                                {{ substr($user->name, 0, 1) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    @if($user->hasMembership())
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Active until {{ $user->membership_expires_at->format('M d, Y') }}
                                        </span>
                                    @elseif($user->membershipPending())
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Pending Payment
                                        </span>
                                    @elseif($user->membershipExpired())
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Expired
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            No Membership
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $user->created_at->format('M d, Y') }}
                                <div class="text-xs text-gray-500">
                                    {{ $user->created_at->diffForHumans() }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($user->transactions->count() > 0)
                                    <div>
                                        <span class="font-medium">{{ $user->transactions->count() }}</span> transactions
                                        <div class="text-xs text-gray-500">
                                            Last: {{ $user->transactions->first()->created_at->format('M d') }}
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-400">No activity</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <a href="{{ route('admin.users.details', $user) }}" 
                                   class="text-indigo-600 hover:text-indigo-900">View Details</a>
                                
                                @if(!$user->hasMembership())
                                    <form method="POST" action="{{ route('admin.memberships.activate', $user) }}" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="text-green-600 hover:text-green-900"
                                                onclick="return confirm('Activate membership for {{ $user->name }}?')">
                                            Activate
                                        </button>
                                    </form>
                                @endif
                                
                                @if($user->hasMembership())
                                    <form method="POST" action="{{ route('admin.memberships.suspend', $user) }}" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="text-orange-600 hover:text-orange-900"
                                                onclick="return confirm('Suspend membership for {{ $user->name }}?')">
                                            Suspend
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No users found</h3>
                                <p class="mt-1 text-sm text-gray-500">Get started by having users register on your platform.</p>
                            </div>
                        </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection