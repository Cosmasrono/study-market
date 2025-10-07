@extends('layouts.admin')

@section('title', 'Manage Memberships')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Membership Management</h1>
            <p class="text-gray-600 mt-1">Manage user memberships and monitor expiration dates</p>
        </div>
        
        <div class="flex space-x-3">
            <a href="{{ route('admin.dashboard') }}" 
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded transition-colors">
                Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Filter Options -->
    <div class="mb-6 bg-white p-4 rounded-lg shadow-md">
        <div class="flex flex-wrap items-center gap-2">
            <span class="font-medium text-gray-700">Filter by:</span>
            <a href="{{ route('admin.memberships') }}" 
               class="px-3 py-1 rounded-full text-sm {{ !request('filter') ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                All
            </a>
            <a href="{{ route('admin.memberships') }}?filter=active" 
               class="px-3 py-1 rounded-full text-sm {{ request('filter') == 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                Active
            </a>
            <a href="{{ route('admin.memberships') }}?filter=expiring_soon" 
               class="px-3 py-1 rounded-full text-sm {{ request('filter') == 'expiring_soon' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                Expiring Soon (30 days)
            </a>
            <a href="{{ route('admin.memberships') }}?filter=critical" 
               class="px-3 py-1 rounded-full text-sm {{ request('filter') == 'critical' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                Critical (7 days)
            </a>
            <a href="{{ route('admin.memberships') }}?filter=expired" 
               class="px-3 py-1 rounded-full text-sm {{ request('filter') == 'expired' ? 'bg-gray-100 text-gray-800' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                Expired
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        @php
            $activeCount = $users->filter(function($user) { return $user->hasMembership(); })->count();
            $pendingCount = $users->where('membership_status', 'pending')->count();
            $expiredCount = $users->filter(function($user) { return $user->membershipExpired(); })->count();
            $expiringSoonCount = $users->filter(function($user) { return $user->membershipExpiresWithin(30) && !$user->membershipExpired(); })->count();
        @endphp

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
                    <p class="text-2xl font-semibold text-gray-900">{{ $activeCount }}</p>
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
                    <p class="text-sm font-medium text-gray-500">Expiring Soon</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $expiringSoonCount }}</p>
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
                    <p class="text-sm font-medium text-gray-500">Expired</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $expiredCount }}</p>
                </div>
            </div>
        </div>

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

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">User Memberships</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Membership Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subscription Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days Remaining</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expires On</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payments</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                        @php
                            $daysRemaining = $user->days_until_expiry;
                            $membershipInfo = $user->membership_status_with_days;
                        @endphp
                        <tr class="hover:bg-gray-50 {{ $daysRemaining <= 7 && $daysRemaining > 0 ? 'bg-red-25' : ($daysRemaining <= 30 && $daysRemaining > 7 ? 'bg-yellow-25' : '') }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center">
                                            <span class="text-sm font-medium text-white">
                                                {{ substr($user->name, 0, 1) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                        <div class="text-xs text-gray-400">Joined {{ $user->created_at->format('M d, Y') }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->hasMembership())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Active
                                    </span>
                                @elseif($user->membershipPending())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-clock mr-1"></i>
                                        Pending
                                    </span>
                                @elseif($user->membershipExpired())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-1"></i>
                                        Expired
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <i class="fas fa-user mr-1"></i>
                                        None
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($user->current_subscription_type)
                                    <div class="flex items-center">
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                                            @switch($user->current_subscription_type)
                                                @case('3_months')
                                                    3 Months
                                                    @break
                                                @case('6_months')
                                                    6 Months
                                                    @break
                                                @case('9_months')
                                                    9 Months
                                                    @break
                                                @case('1_year')
                                                    1 Year
                                                    @break
                                                @default
                                                    {{ ucfirst(str_replace('_', ' ', $user->current_subscription_type)) }}
                                            @endswitch
                                        </span>
                                    </div>
                                    @if($user->current_subscription_price)
                                        <div class="text-xs text-gray-500 mt-1">
                                            KES {{ number_format($user->current_subscription_price) }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($daysRemaining > 0)
                                    <div class="flex items-center">
                                        <div class="text-center">
                                            <div class="text-lg font-bold 
                                                @if($daysRemaining <= 7) text-red-600 
                                                @elseif($daysRemaining <= 30) text-yellow-600 
                                                @else text-green-600 @endif">
                                                {{ $daysRemaining }}
                                            </div>
                                            <div class="text-xs text-gray-500">days</div>
                                        </div>
                                        <div class="ml-3">
                                            <div class="w-16 h-2 bg-gray-200 rounded-full">
                                                @php
                                                    $totalDays = match($user->current_subscription_type) {
                                                        '3_months' => 90,
                                                        '6_months' => 180,
                                                        '9_months' => 270,
                                                        default => 365
                                                    };
                                                    $percentage = min(100, ($daysRemaining / $totalDays) * 100);
                                                    $barColor = $daysRemaining <= 7 ? 'red' : ($daysRemaining <= 30 ? 'yellow' : 'green');
                                                @endphp
                                                <div class="h-2 bg-{{ $barColor }}-500 rounded-full" style="width: {{ $percentage }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                    @if($daysRemaining <= 7)
                                        <div class="text-xs text-red-600 font-medium mt-1">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Critical!
                                        </div>
                                    @elseif($daysRemaining <= 30)
                                        <div class="text-xs text-yellow-600 font-medium mt-1">
                                            <i class="fas fa-clock mr-1"></i>
                                            Expiring Soon
                                        </div>
                                    @endif
                                @elseif($user->subscription_end_date)
                                    <div class="text-center">
                                        <div class="text-lg font-bold text-red-600">0</div>
                                        <div class="text-xs text-red-600 font-medium">EXPIRED</div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ $user->subscription_end_date->diffForHumans() }}
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($user->subscription_end_date)
                                    <div>
                                        <div class="font-medium">{{ $user->subscription_end_date->format('M d, Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ $user->subscription_end_date->format('g:i A') }}</div>
                                        @if($user->subscription_end_date->isPast())
                                            <div class="text-xs text-red-600 font-medium mt-1">
                                                {{ $user->subscription_end_date->diffForHumans() }}
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-400">Not set</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($user->membershipPayments->count() > 0)
                                    <div>
                                        <div class="font-medium">{{ $user->membershipPayments->count() }} payments</div>
                                        <div class="text-xs text-gray-500">
                                            Last: {{ $user->membershipPayments->first()->created_at->format('M d, Y') }}
                                        </div>
                                        <div class="text-xs">
                                            Total: KES {{ number_format($user->membershipPayments->where('status', 'completed')->sum('amount')) }}
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center">
                                        <span class="text-gray-400">No payments</span>
                                        <div class="text-xs text-gray-400">Never paid</div>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex flex-col space-y-1">
                                    <a href="{{ route('admin.users.details', $user) }}" 
                                       class="text-blue-600 hover:text-blue-900 text-xs">
                                        <i class="fas fa-eye mr-1"></i>View Details
                                    </a>
                                    
                                    @if(!$user->hasMembership())
                                        <form method="POST" action="{{ route('admin.memberships.activate', $user) }}" class="inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="text-green-600 hover:text-green-900 text-xs"
                                                    onclick="return confirm('Activate membership for {{ $user->name }}?')">
                                                <i class="fas fa-check mr-1"></i>Activate
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if($user->hasMembership())
                                        <button onclick="showExtendModal({{ $user->id }}, '{{ $user->name }}', {{ $daysRemaining }})" 
                                                class="text-blue-600 hover:text-blue-900 text-xs">
                                            <i class="fas fa-plus mr-1"></i>Extend
                                        </button>
                                        
                                        <form method="POST" action="{{ route('admin.memberships.suspend', $user) }}" class="inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-900 text-xs"
                                                    onclick="return confirm('Suspend membership for {{ $user->name }}?')">
                                                <i class="fas fa-ban mr-1"></i>Suspend
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-users fa-3x text-gray-300 mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No users found</h3>
                                <p class="text-sm">No users match the current filter criteria.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $users->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    <!-- Summary Statistics Footer -->
    <div class="mt-6 bg-gray-50 p-4 rounded-lg">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-center">
            <div>
                <div class="text-lg font-bold text-gray-900">{{ $users->filter(function($user) { return $user->days_until_expiry <= 7 && $user->days_until_expiry > 0; })->count() }}</div>
                <div class="text-sm text-red-600">Critical (≤7 days)</div>
            </div>
            <div>
                <div class="text-lg font-bold text-gray-900">{{ $users->filter(function($user) { return $user->days_until_expiry <= 30 && $user->days_until_expiry > 7; })->count() }}</div>
                <div class="text-sm text-yellow-600">Warning (8-30 days)</div>
            </div>
            <div>
                <div class="text-lg font-bold text-gray-900">{{ $users->filter(function($user) { return $user->days_until_expiry > 30; })->count() }}</div>
                <div class="text-sm text-green-600">Healthy (>30 days)</div>
            </div>
            <div>
                <div class="text-lg font-bold text-gray-900">KES {{ number_format($users->sum(function($user) { return $user->membershipPayments->where('status', 'completed')->sum('amount'); })) }}</div>
                <div class="text-sm text-blue-600">Total Revenue</div>
            </div>
        </div>
    </div>
</div>

<!-- Extend Membership Modal -->
<div id="extendModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <h3 class="text-lg font-semibold mb-4">Extend Membership</h3>
            <form id="extendForm" method="POST" action="">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">User</label>
                    <div id="extendUserName" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></div>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Days Remaining</label>
                    <div id="extendCurrentDays" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></div>
                </div>
                <div class="mb-4">
                    <label for="months" class="block text-sm font-medium text-gray-700 mb-2">Extend by (months)</label>
                    <select name="months" id="months" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
                        <option value="1">1 Month</option>
                        <option value="3">3 Months</option>
                        <option value="6">6 Months</option>
                        <option value="12" selected>1 Year</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideExtendModal()" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Extend Membership
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showExtendModal(userId, userName, currentDays) {
    document.getElementById('extendForm').action = `/admin/memberships/${userId}/extend`;
    document.getElementById('extendUserName').textContent = userName;
    document.getElementById('extendCurrentDays').textContent = `${currentDays} days`;
    document.getElementById('extendModal').classList.remove('hidden');
}

function hideExtendModal() {
    document.getElementById('extendModal').classList.add('hidden');
}

// Auto-hide alerts
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert-auto-hide');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 5000);
    });
});
</script>
@endsection