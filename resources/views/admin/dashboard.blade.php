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

    <!-- Recent Activities -->
    <div class="bg-maroon-50 p-6 rounded-lg shadow-md col-span-2">
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
                </div>
            </div>
        </div>
        
<!-- Financial Overview -->
<div class="mt-8 bg-maroon-50 p-6 rounded-lg shadow-md">
    <h3 class="text-xl font-semibold mb-4 text-maroon-800">Financial Summary</h3>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        @php
            $totalRevenue = \App\Models\Transaction::where('status', 'paid')->sum('amount');
            $bookRevenue = \App\Models\Transaction::where('status', 'paid')->where('content_type', 'book')->sum('amount');
            $videoRevenue = \App\Models\Transaction::where('status', 'paid')->where('content_type', 'video')->sum('amount');
            $membershipRevenue = \App\Models\Transaction::where('status', 'paid')->where('content_type', 'membership')->sum('amount');
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
@endsection
