@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard Overview')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Quick Stats -->
    <div class="bg-maroon-50 p-6 rounded-lg shadow-md">
        <div class="flex items-center mb-4">
            <div class="p-3 bg-maroon-100 rounded-full mr-4">
                <i class="fas fa-book text-maroon-600 text-2xl"></i>
            </div>
            <div>
                <h3 class="text-xl font-semibold text-maroon-800">Books</h3>
                <p class="text-maroon-600">Total Books in Library</p>
            </div>
        </div>
        <div class="text-3xl font-bold text-maroon-700">{{ $booksCount }}</div>
    </div>

    <div class="bg-maroon-50 p-6 rounded-lg shadow-md">
        <div class="flex items-center mb-4">
            <div class="p-3 bg-maroon-100 rounded-full mr-4">
                <i class="fas fa-video text-maroon-600 text-2xl"></i>
            </div>
            <div>
                <h3 class="text-xl font-semibold text-maroon-800">Videos</h3>
                <p class="text-maroon-600">Total Video Resources</p>
            </div>
        </div>
        <div class="text-3xl font-bold text-maroon-700">{{ $videosCount }}</div>
    </div>

    <div class="bg-maroon-50 p-6 rounded-lg shadow-md">
        <div class="flex items-center mb-4">
            <div class="p-3 bg-maroon-100 rounded-full mr-4">
                <i class="fas fa-users text-maroon-600 text-2xl"></i>
            </div>
            <div>
                <h3 class="text-xl font-semibold text-maroon-800">Users</h3>
                <p class="text-maroon-600">Total Registered Users</p>
            </div>
        </div>
        <div class="text-3xl font-bold text-maroon-700">{{ \App\Models\User::count() }}</div>
    </div>
</div>

<!-- Membership Statistics -->
<div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-6">
    <div class="bg-green-50 p-6 rounded-lg shadow-md border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-green-800">Active Memberships</h3>
                <p class="text-3xl font-bold text-green-700">{{ \App\Models\User::active()->count() }}</p>
                <p class="text-sm text-green-600">Currently subscribed</p>
            </div>
            <i class="fas fa-check-circle text-green-500 text-3xl"></i>
        </div>
    </div>

    <div class="bg-yellow-50 p-6 rounded-lg shadow-md border-l-4 border-yellow-500">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-yellow-800">Expiring Soon</h3>
                <p class="text-3xl font-bold text-yellow-700">{{ \App\Models\User::expiringWithin(30)->count() }}</p>
                <p class="text-sm text-yellow-600">Within 30 days</p>
            </div>
            <i class="fas fa-exclamation-triangle text-yellow-500 text-3xl"></i>
        </div>
    </div>

    <div class="bg-red-50 p-6 rounded-lg shadow-md border-l-4 border-red-500">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-red-800">Critical (≤7 days)</h3>
                <p class="text-3xl font-bold text-red-700">{{ \App\Models\User::expiringWithin(7)->count() }}</p>
                <p class="text-sm text-red-600">Need immediate attention</p>
            </div>
            <i class="fas fa-times-circle text-red-500 text-3xl"></i>
        </div>
    </div>

    <div class="bg-gray-50 p-6 rounded-lg shadow-md border-l-4 border-gray-500">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Expired</h3>
                <p class="text-3xl font-bold text-gray-700">{{ \App\Models\User::expired()->count() }}</p>
                <p class="text-sm text-gray-600">Need renewal</p>
            </div>
            <i class="fas fa-calendar-times text-gray-500 text-3xl"></i>
        </div>
    </div>
</div>

<!-- Critical Expiration Alert -->
@php
    $criticalUsers = \App\Models\User::expiringWithin(7)->get();
@endphp

@if($criticalUsers->count() > 0)
<div class="mt-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
    <div class="flex">
        <div class="flex-shrink-0">
            <i class="fas fa-exclamation-triangle text-red-400 text-xl"></i>
        </div>
        <div class="ml-3 flex-1">
            <h3 class="text-red-800 font-semibold">Critical Membership Expirations</h3>
            <p class="text-red-700 text-sm mb-3">{{ $criticalUsers->count() }} users have memberships expiring within 7 days:</p>
            <div class="space-y-2">
                @foreach($criticalUsers->take(5) as $user)
                    <div class="flex items-center justify-between bg-white p-2 rounded border-l-2 border-red-300">
                        <div>
                            <span class="font-medium text-gray-900">{{ $user->name }}</span>
                            <span class="text-gray-600">({{ $user->email }})</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-red-600 font-bold">{{ $user->days_until_expiry }} days</span>
                            <a href="{{ route('admin.users.details', $user->id) }}" 
                               class="text-blue-600 hover:text-blue-800 text-sm">View</a>
                        </div>
                    </div>
                @endforeach
                @if($criticalUsers->count() > 5)
                    <p class="text-red-600 text-sm">
                        And {{ $criticalUsers->count() - 5 }} more... 
                        <a href="{{ route('admin.memberships') }}?filter=critical" class="underline">View all</a>
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

<!-- Recent Activities and Quick Actions Row -->
<div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Recent Activities -->
    <div class="lg:col-span-2 bg-maroon-50 p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-semibold mb-4 text-maroon-800">Recent System Activities</h3>
        <div class="divide-y divide-maroon-200">
            @php
                $recentActivities = \App\Models\Transaction::with('user')
                    ->latest()
                    ->take(5)
                    ->get();
            @endphp
            @forelse($recentActivities as $activity)
                <div class="py-3 flex justify-between items-center hover:bg-maroon-100 transition-colors">
                    <div class="flex items-center">
                        <div class="w-2 h-2 bg-maroon-600 rounded-full mr-3"></div>
                        <div>
                            <p class="text-sm text-maroon-700">
                                {{ $activity->user->name }} 
                                @switch($activity->content_type)
                                    @case('book')
                                        purchased a book
                                        @break
                                    @case('video')
                                        purchased a video
                                        @break
                                    @case('membership')
                                        renewed membership
                                        @break
                                    @default
                                        made a transaction
                                @endswitch
                            </p>
                            <p class="text-xs text-maroon-500">
                                {{ $activity->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                    <span class="text-sm text-maroon-600 font-semibold">
                        KSh {{ number_format($activity->amount, 2) }}
                    </span>
                </div>
            @empty
                <p class="text-center text-maroon-500 py-4">No recent activities</p>
            @endforelse
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-maroon-50 p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-semibold mb-4 text-maroon-800">Quick Actions</h3>
        <div class="grid grid-cols-2 gap-4">
            <a href="{{ route('admin.books.create') }}" class="bg-maroon-100 text-maroon-700 p-4 rounded-lg text-center hover:bg-maroon-200 transition-colors">
                <i class="fas fa-plus-circle text-2xl mb-2 block text-maroon-600"></i>
                <span class="text-sm">Add Book</span>
            </a>
            <a href="{{ route('admin.videos.create') }}" class="bg-maroon-100 text-maroon-700 p-4 rounded-lg text-center hover:bg-maroon-200 transition-colors">
                <i class="fas fa-upload text-2xl mb-2 block text-maroon-600"></i>
                <span class="text-sm">Upload Video</span>
            </a>
            <a href="{{ route('admin.users') }}" class="bg-maroon-100 text-maroon-700 p-4 rounded-lg text-center hover:bg-maroon-200 transition-colors">
                <i class="fas fa-users-cog text-2xl mb-2 block text-maroon-600"></i>
                <span class="text-sm">Manage Users</span>
            </a>
            <a href="{{ route('admin.memberships') }}" class="bg-maroon-100 text-maroon-700 p-4 rounded-lg text-center hover:bg-maroon-200 transition-colors">
                <i class="fas fa-id-card text-2xl mb-2 block text-maroon-600"></i>
                <span class="text-sm">Memberships</span>
            </a>
            <a href="{{ route('admin.reports') }}" class="bg-maroon-100 text-maroon-700 p-4 rounded-lg text-center hover:bg-maroon-200 transition-colors">
                <i class="fas fa-chart-bar text-2xl mb-2 block text-maroon-600"></i>
                <span class="text-sm">Reports</span>
            </a>
        </div>
    </div>
</div>

<!-- Financial Overview -->
<div class="mt-8 bg-maroon-50 p-6 rounded-lg shadow-md">
    <h3 class="text-xl font-semibold mb-4 text-maroon-800">Financial Summary</h3>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        @php
            $totalRevenue = \App\Models\Transaction::where('status', 'paid')->sum('amount') + 
                           \App\Models\MembershipPayment::where('status', 'completed')->sum('amount');
            $bookRevenue = \App\Models\Transaction::where('status', 'paid')->where('content_type', 'book')->sum('amount');
            $videoRevenue = \App\Models\Transaction::where('status', 'paid')->where('content_type', 'video')->sum('amount');
            $membershipRevenue = \App\Models\MembershipPayment::where('status', 'completed')->sum('amount');
        @endphp
        <div class="bg-white p-4 rounded-lg text-center border-l-4 border-maroon-500">
            <h4 class="text-sm text-maroon-600 mb-2">Total Revenue</h4>
            <p class="text-2xl font-bold text-maroon-800">KSh {{ number_format($totalRevenue, 2) }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg text-center border-l-4 border-maroon-500">
            <h4 class="text-sm text-maroon-600 mb-2">Book Sales</h4>
            <p class="text-2xl font-bold text-maroon-800">KSh {{ number_format($bookRevenue, 2) }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg text-center border-l-4 border-maroon-500">
            <h4 class="text-sm text-maroon-600 mb-2">Video Sales</h4>
            <p class="text-2xl font-bold text-maroon-800">KSh {{ number_format($videoRevenue, 2) }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg text-center border-l-4 border-maroon-500">
            <h4 class="text-sm text-maroon-600 mb-2">Membership Revenue</h4>
            <p class="text-2xl font-bold text-maroon-800">KSh {{ number_format($membershipRevenue, 2) }}</p>
        </div>
    </div>
</div>

<!-- Membership Expiration Chart (if you want to add a visual representation) -->
<div class="mt-8 bg-white p-6 rounded-lg shadow-md">
    <h3 class="text-lg font-semibold mb-4 text-gray-800">Membership Expiration Breakdown</h3>
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        @php
            $expirationData = [
                'expired' => \App\Models\User::expired()->count(),
                '1_7_days' => \App\Models\User::expiringWithin(7)->count(),
                '8_30_days' => \App\Models\User::where('subscription_end_date', '>', now()->addDays(7))
                               ->where('subscription_end_date', '<=', now()->addDays(30))
                               ->count(),
                '31_90_days' => \App\Models\User::where('subscription_end_date', '>', now()->addDays(30))
                                ->where('subscription_end_date', '<=', now()->addDays(90))
                                ->count(),
                'more_90_days' => \App\Models\User::where('subscription_end_date', '>', now()->addDays(90))
                                  ->count()
            ];
        @endphp
        
        <div class="text-center p-4 bg-red-50 rounded-lg border-l-4 border-red-500">
            <div class="text-2xl font-bold text-red-600">{{ $expirationData['expired'] }}</div>
            <div class="text-sm text-red-700">Expired</div>
        </div>
        
        <div class="text-center p-4 bg-red-50 rounded-lg border-l-4 border-red-400">
            <div class="text-2xl font-bold text-red-600">{{ $expirationData['1_7_days'] }}</div>
            <div class="text-sm text-red-700">1-7 days</div>
        </div>
        
        <div class="text-center p-4 bg-yellow-50 rounded-lg border-l-4 border-yellow-500">
            <div class="text-2xl font-bold text-yellow-600">{{ $expirationData['8_30_days'] }}</div>
            <div class="text-sm text-yellow-700">8-30 days</div>
        </div>
        
        <div class="text-center p-4 bg-blue-50 rounded-lg border-l-4 border-blue-500">
            <div class="text-2xl font-bold text-blue-600">{{ $expirationData['31_90_days'] }}</div>
            <div class="text-sm text-blue-700">31-90 days</div>
        </div>
        
        <div class="text-center p-4 bg-green-50 rounded-lg border-l-4 border-green-500">
            <div class="text-2xl font-bold text-green-600">{{ $expirationData['more_90_days'] }}</div>
            <div class="text-sm text-green-700">90+ days</div>
        </div>
    </div>
</div>
@endsection