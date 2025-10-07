<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Admin Panel') - {{ config('app.name', 'Inzoberi School') }}</title>
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        admin: {
                            50: '#FDF2F2',
                            100: '#FCE7E7',
                            200: '#FCCACA',
                            300: '#FAA5A5',
                            400: '#F87171',
                            500: '#8C1C13', 
                            600: '#7F1D1D', 
                            700: '#6E1610', 
                            800: '#5C1E10', 
                            900: '#4A1E10'
                        }
                    },
                    boxShadow: {
                        'admin-lg': '0 10px 15px -3px rgba(140, 28, 19, 0.1), 0 4px 6px -2px rgba(140, 28, 19, 0.05)'
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background: linear-gradient(135deg, #FDF2F2 0%, #FCE7E7 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }
        .sidebar-gradient {
            background: linear-gradient(to bottom right, #6E1610, #5C1E10);
        }
        .hover-lift {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(140,28,19,0.1);
        }
    </style>
</head>
<body class="bg-gray-50 antialiased">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 sidebar-gradient text-white p-6 space-y-6 hidden md:block">
            <div class="flex items-center space-x-4 mb-8">
                <img src="{{ asset('images/victoria_logo.jpg') }}" 
                     alt="Admin Logo"
                     class="h-12 w-12 rounded-full object-cover border-2 border-admin-300">
                <div>
                    <h1 class="text-xl font-bold">Admin Panel</h1>
                    <p class="text-xs text-admin-300">Inzoberi School Management</p>
                </div>
            </div>

            <nav class="space-y-2">
            @php
    $adminRoutes = [
        ['route' => 'admin.dashboard', 'icon' => 'fas fa-home', 'label' => 'Dashboard'],
        ['route' => 'admin.books', 'icon' => 'fas fa-book', 'label' => 'Books'], 
        ['route' => 'admin.videos', 'icon' => 'fas fa-video', 'label' => 'Videos'],
        ['route' => 'admin.memberships', 'icon' => 'fas fa-users', 'label' => 'Memberships'],
        ['route' => 'admin.users', 'icon' => 'fas fa-user-cog', 'label' => 'Users'],
        ['route' => 'admin.payments', 'icon' => 'fas fa-credit-card', 'label' => 'Payments'],
        ['route' => 'admin.reports', 'icon' => 'fas fa-chart-line', 'label' => 'Reports'],
        ['route' => 'admin.testimonials.index', 'icon' => 'fas fa-star', 'label' => 'Testimonials']
    ];
@endphp
    
    @foreach($adminRoutes as $route)
        <a href="{{ route($route['route']) }}" 
           class="block px-4 py-2 rounded hover:bg-admin-600 transition-colors 
                  {{ request()->routeIs($route['route']) ? 'bg-admin-700' : '' }}">
            <i class="{{ $route['icon'] }} mr-3"></i>
            {{ $route['label'] }}
        </a>
    @endforeach
</nav>


        </div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            <header class="bg-white shadow-md">
                <div class="container mx-auto px-6 py-4 flex justify-between items-center">
                    <div class="flex items-center">
                        <button class="md:hidden mr-4 text-admin-700" id="mobile-menu-toggle">
                            <i class="fas fa-bars text-2xl"></i>
                        </button>
                        <h2 class="text-2xl font-semibold text-admin-800">@yield('page-title', 'Dashboard')</h2>
                    </div>

                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <div class="relative">
                            <button class="text-admin-600 hover:text-admin-800">
                                <i class="fas fa-bell text-lg"></i>
                                <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">3</span>
                            </button>
                        </div>

                        <!-- Admin Profile -->
                        <div class="flex items-center space-x-3">
                            <div class="text-right">
                                <p class="text-sm font-medium text-admin-800">{{ Auth::guard('admin')->user()->name ?? 'Admin' }}</p>
                                <p class="text-xs text-admin-500">Administrator</p>
                            </div>
                            <div class="h-10 w-10 bg-admin-600 rounded-full flex items-center justify-center text-white">
                                {{ substr(Auth::guard('admin')->user()->name ?? 'A', 0, 1) }}
                            </div>
                        </div>

                        <!-- Logout -->
                        <form method="POST" action="{{ route('admin.logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-red-500 hover:text-red-700">
                                <i class="fas fa-sign-out-alt text-lg"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </header>
            

            <!-- Mobile Sidebar (Overlay) -->
            <div id="mobile-sidebar" class="fixed inset-0 z-50 bg-black bg-opacity-50 hidden md:hidden">
                <div class="w-64 bg-white h-full shadow-lg transform transition-transform duration-300 ease-in-out" id="mobile-sidebar-content">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-bold text-admin-800">Admin Menu</h2>
                            <button id="close-mobile-sidebar" class="text-admin-600 hover:text-admin-800">
                                <i class="fas fa-times text-2xl"></i>
                            </button>
                        </div>
                        <nav class="space-y-2">
                            @foreach($adminRoutes as $route)
                                <a href="{{ route($route['route']) }}" 
                                   class="block px-4 py-2 rounded hover:bg-admin-100 transition-colors 
                                          {{ request()->routeIs($route['route']) ? 'bg-admin-200' : '' }}">
                                    <i class="{{ $route['icon'] }} mr-3 text-admin-600"></i>
                                    {{ $route['label'] }}
                                </a>
                            @endforeach
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 p-4 text-center">
                <p class="text-sm text-admin-500">
                    © {{ date('Y') }} {{ config('app.name', 'Inzoberi School') }} Admin Panel. All rights reserved.
                </p>
            </footer>
        </div>
    </div>

    <script>
        // Mobile Sidebar Toggle
        const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
        const mobileSidebar = document.getElementById('mobile-sidebar');
        const closeMobileSidebar = document.getElementById('close-mobile-sidebar');

        mobileMenuToggle.addEventListener('click', () => {
            mobileSidebar.classList.remove('hidden');
        });

        closeMobileSidebar.addEventListener('click', () => {
            mobileSidebar.classList.add('hidden');
        });

        // Close mobile sidebar when clicking outside
        mobileSidebar.addEventListener('click', (e) => {
            if (e.target === mobileSidebar) {
                mobileSidebar.classList.add('hidden');
            }
        });
    </script>
    @yield('scripts')
</body>
</html>