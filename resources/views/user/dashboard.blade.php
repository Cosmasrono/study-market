@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-lg p-8 mb-8 shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">Welcome back, {{ explode(' ', $user->name)[0] }}!</h1>
                <p class="text-primary-100 text-lg">Continue your learning journey with Inzoberi School of Professionals</p>
            </div>
            <div class="hidden md:flex items-center">
                <div class="h-20 w-20 bg-white/20 rounded-full flex items-center justify-center text-2xl font-bold">
                    {{ substr($user->name, 0, 2) }}
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        @php
            $enrollments = \App\Models\Enrollment::where('user_id', $user->id)->get();
            $transactions = \App\Models\Transaction::where('user_id', $user->id)->get();
            $testAttempts = \App\Models\TestAttempt::where('user_id', $user->id)->get();
            $completedCourses = $enrollments->where('status', 'completed')->count();
            $totalSpent = $transactions->where('status', 'completed')->sum('amount');
            $averageScore = $testAttempts->avg('score') ?? 0;
        @endphp
        
        <!-- Total Enrollments -->
        <div class="bg-white rounded-lg p-6 shadow-md hover:shadow-lg transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Total Courses</h3>
                    <p class="text-3xl font-bold text-gray-900">{{ $enrollments->count() }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <i class="fas fa-graduation-cap text-blue-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-green-500 text-sm font-medium">
                    <i class="fas fa-arrow-up"></i>
                    {{ $completedCourses }} completed
                </span>
            </div>
        </div>

        <!-- Total Spent -->
        <div class="bg-white rounded-lg p-6 shadow-md hover:shadow-lg transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Total Spent</h3>
                    <p class="text-3xl font-bold text-gray-900">KSh {{ number_format($totalSpent) }}</p>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <i class="fas fa-coins text-green-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-blue-500 text-sm font-medium">
                    <i class="fas fa-shopping-cart"></i>
                    {{ $transactions->count() }} transactions
                </span>
            </div>
        </div>

        <!-- Test Attempts -->
        <div class="bg-white rounded-lg p-6 shadow-md hover:shadow-lg transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Test Attempts</h3>
                    <p class="text-3xl font-bold text-gray-900">{{ $testAttempts->count() }}</p>
                </div>
                <div class="p-3 bg-purple-100 rounded-full">
                    <i class="fas fa-clipboard-check text-purple-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-purple-500 text-sm font-medium">
                    <i class="fas fa-chart-line"></i>
                    {{ number_format($averageScore, 1) }}% avg score
                </span>
            </div>
        </div>

        <!-- Account Status -->
        <div class="bg-white rounded-lg p-6 shadow-md hover:shadow-lg transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Member Since</h3>
                    <p class="text-3xl font-bold text-gray-900">{{ $user->created_at->format('M Y') }}</p>
                </div>
                <div class="p-3 bg-yellow-100 rounded-full">
                    <i class="fas fa-user-check text-yellow-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-green-500 text-sm font-medium">
                    <i class="fas fa-check-circle"></i>
                    Active Member
                </span>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid lg:grid-cols-3 gap-8">
        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Current Courses -->
            <div class="bg-white rounded-lg p-6 shadow-md">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-book-open mr-3 text-primary-600"></i>
                        Current Courses
                    </h2>
                    <a href="{{ route('program') }}" class="text-primary-600 hover:text-primary-800 font-medium">
                        Browse All <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                
                @if($enrollments->where('status', '!=', 'completed')->count() > 0)
                    <div class="space-y-4">
                        @foreach($enrollments->where('status', '!=', 'completed')->take(3) as $enrollment)
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-primary-300 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-gray-800">{{ $enrollment->course->title ?? 'Course Title' }}</h3>
                                        <p class="text-sm text-gray-600 mt-1">{{ $enrollment->course->description ?? 'Course description...' }}</p>
                                        <div class="mt-3">
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-primary-600 h-2 rounded-full" style="width: {{ $enrollment->progress ?? 0 }}%"></div>
                                            </div>
                                            <span class="text-xs text-gray-500 mt-1 inline-block">{{ $enrollment->progress ?? 0 }}% Complete</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <button class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition-colors">
                                            Continue
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-graduation-cap text-gray-300 text-5xl mb-4"></i>
                        <p class="text-gray-500 mb-4">You haven't enrolled in any courses yet.</p>
                        <a href="{{ route('program') }}" class="bg-primary-600 text-white px-6 py-2 rounded-lg hover:bg-primary-700 transition-colors">
                            Explore Courses
                        </a>
                    </div>
                @endif
            </div>

            <!-- Recent Test Results -->
            <div class="bg-white rounded-lg p-6 shadow-md">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-chart-line mr-3 text-primary-600"></i>
                        Recent Test Results
                    </h2>
                    <a href="{{ route('user.results') }}" class="text-primary-600 hover:text-primary-800 font-medium">
                        View All <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                
                @if($testAttempts->count() > 0)
                    <div class="space-y-4">
                        @foreach($testAttempts->take(5) as $attempt)
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-800">{{ $attempt->exam->title ?? 'Test Name' }}</h3>
                                    <p class="text-sm text-gray-600">{{ $attempt->created_at->format('M d, Y g:i A') }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="text-2xl font-bold 
                                        {{ $attempt->score >= 80 ? 'text-green-600' : ($attempt->score >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                                        {{ $attempt->score }}%
                                    </span>
                                    <p class="text-xs text-gray-500">
                                        {{ $attempt->score >= 80 ? 'Excellent' : ($attempt->score >= 60 ? 'Good' : 'Needs Improvement') }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-clipboard-list text-gray-300 text-5xl mb-4"></i>
                        <p class="text-gray-500 mb-4">No test attempts yet.</p>
                        <a href="{{ route('cbt') }}" class="bg-primary-600 text-white px-6 py-2 rounded-lg hover:bg-primary-700 transition-colors">
                            Take a Test
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-8">
            <!-- Quick Actions -->
            <div class="bg-white rounded-lg p-6 shadow-md">
                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-bolt mr-3 text-primary-600"></i>
                    Quick Actions
                </h2>
                <div class="space-y-3">
                    <a href="{{ route('program') }}" class="block w-full bg-primary-600 text-white py-3 text-center rounded-lg hover:bg-primary-700 transition-colors">
                        <i class="fas fa-search mr-2"></i>Browse Courses
                    </a>
                    <a href="{{ route('books') }}" class="block w-full bg-blue-600 text-white py-3 text-center rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-book mr-2"></i>Explore Books
                    </a>
                    <a href="{{ route('videos.index') }}" class="block w-full bg-purple-600 text-white py-3 text-center rounded-lg hover:bg-purple-700 transition-colors">
                        <i class="fas fa-video mr-2"></i>Watch Videos
                    </a>
                    <a href="{{ route('cbt') }}" class="block w-full bg-green-600 text-white py-3 text-center rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-clipboard-check mr-2"></i>Take Test
                    </a>
                </div>
            </div>

            <!-- Profile Overview -->
            <div class="bg-white rounded-lg p-6 shadow-md">
                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-user mr-3 text-primary-600"></i>
                    Profile Overview
                </h2>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Email:</span>
                        <span class="font-medium">{{ $user->email }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Phone:</span>
                        <span class="font-medium">{{ $user->phone ?? 'Not provided' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Status:</span>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">
                            Active
                        </span>
                    </div>
                    <div class="pt-4">
                        <a href="{{ route('user.profile') }}" class="block w-full bg-gray-100 text-gray-800 py-2 text-center rounded-lg hover:bg-gray-200 transition-colors">
                            <i class="fas fa-edit mr-2"></i>Edit Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Purchases -->
            <div class="bg-white rounded-lg p-6 shadow-md">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-shopping-cart mr-3 text-primary-600"></i>
                        Recent Purchases
                    </h2>
                    <a href="{{ route('user.transactions') }}" class="text-primary-600 hover:text-primary-800 font-medium text-sm">
                        View All
                    </a>
                </div>
                
                @if($transactions->count() > 0)
                    <div class="space-y-3">
                        @foreach($transactions->take(3) as $transaction)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex-1">
                                    <p class="font-medium text-sm">
                                        @switch($transaction->content_type)
                                            @case('book')
                                                {{ $transaction->book->title ?? 'Book Purchase' }}
                                                @break
                                            @case('video')
                                                {{ $transaction->video->title ?? 'Video Purchase' }}
                                                @break
                                            @case('membership')
                                                Membership
                                                @break
                                            @default
                                                Transaction
                                        @endswitch
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $transaction->created_at->format('M d, Y') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-sm">KSh {{ number_format($transaction->amount) }}</p>
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold 
                                        {{ $transaction->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }} 
                                        rounded-full">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6">
                        <i class="fas fa-shopping-cart text-gray-300 text-4xl mb-3"></i>
                        <p class="text-gray-500 text-sm">No purchases yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Achievement Badges (Optional) -->
    <div class="mt-8 bg-white rounded-lg p-6 shadow-md">
        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
            <i class="fas fa-trophy mr-3 text-primary-600"></i>
            Achievements
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @php
                $achievements = [
                    ['name' => 'First Course', 'icon' => 'graduation-cap', 'earned' => $enrollments->count() > 0],
                    ['name' => 'Test Taker', 'icon' => 'clipboard-check', 'earned' => $testAttempts->count() > 0],
                    ['name' => 'High Scorer', 'icon' => 'star', 'earned' => $testAttempts->where('score', '>=', 90)->count() > 0],
                    ['name' => 'Regular Learner', 'icon' => 'calendar-check', 'earned' => $enrollments->count() >= 3]
                ];
            @endphp
            
            @foreach($achievements as $achievement)
                <div class="text-center p-4 rounded-lg {{ $achievement['earned'] ? 'bg-yellow-50 border-2 border-yellow-200' : 'bg-gray-50 border-2 border-gray-200' }}">
                    <i class="fas fa-{{ $achievement['icon'] }} text-3xl {{ $achievement['earned'] ? 'text-yellow-600' : 'text-gray-400' }} mb-2"></i>
                    <p class="text-sm font-medium {{ $achievement['earned'] ? 'text-gray-800' : 'text-gray-500' }}">
                        {{ $achievement['name'] }}
                    </p>
                    @if($achievement['earned'])
                        <span class="inline-block mt-1 px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">
                            Earned
                        </span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection