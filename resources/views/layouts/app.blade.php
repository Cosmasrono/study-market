<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - @yield('title')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- Flash message meta tags for notifications --}}
    @if(session('success'))
        <meta name="flash-success" content="{{ session('success') }}">
    @endif
    @if(session('error'))
        <meta name="flash-error" content="{{ session('error') }}">
    @endif
    @if(session('warning'))
        <meta name="flash-warning" content="{{ session('warning') }}">
    @endif
    @if(session('info'))
        <meta name="flash-info" content="{{ session('info') }}">
    @endif
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    
    {{-- SweetAlert2 CSS --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.10.1/sweetalert2.min.css" rel="stylesheet">
    
    {{-- Alpine.js for notification dropdown --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#FEF3F2', 
                            100: '#FEE2E2', 
                            200: '#FECACA', 
                            300: '#FCA5A5', 
                            400: '#F87171', 
                            500: '#8C1C13', 
                            600: '#7F1D1D', 
                            700: '#6E1610', 
                            800: '#5C1E10', 
                            900: '#4A1E10'
                        },
                        maroon: {
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
                        'primary-lg': '0 10px 15px -3px rgba(140, 28, 19, 0.1), 0 4px 6px -2px rgba(140, 28, 19, 0.05)'
                    }
                }
            }
        }
    </script>
    
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            background-image: linear-gradient(to right, rgba(249,250,251,0.9), rgba(249,250,251,0.9)), url('{{ asset('images/background-subtle.jpg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        
        .nav-link {
            transition: all 0.3s ease;
            position: relative;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -4px;
            left: 50%;
            background-color: white;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
        
        .dropdown-menu {
            display: none;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
        }
        
        .dropdown-toggle:focus + .dropdown-menu,
        .dropdown-toggle:hover + .dropdown-menu,
        .dropdown-menu:hover {
            display: block;
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        /* Notification Bell Animation */
        .notification-bell {
            position: relative;
            animation: none;
        }

        .notification-bell.has-unread {
            animation: bell-shake 2s ease-in-out infinite;
        }

        @keyframes bell-shake {
            0%, 50%, 100% { transform: rotate(0deg); }
            10%, 30% { transform: rotate(-15deg); }
            20%, 40% { transform: rotate(15deg); }
        }

        .notification-pulse {
            animation: pulse-red 2s infinite;
        }

        @keyframes pulse-red {
            0%, 100% { background-color: #ef4444; }
            50% { background-color: #dc2626; }
        }
    </style>

    @stack('styles')
</head>
<body class="bg-primary-50 text-gray-900 flex flex-col min-h-screen">
    <header class="bg-primary-800 text-white shadow-md sticky top-0 z-50">
        <nav class="container mx-auto flex items-center justify-between p-4 relative">
            <div class="flex items-center space-x-4">
                <img src="{{ asset('images/victoria_logo.jpg') }}" 
                     alt="Inzoberi Logo"
                     class="h-12 w-12 rounded-full object-cover border-4 border-white shadow-lg transition-all duration-300 hover:scale-110">
                
                <a href="/" class="font-bold text-white hover:text-primary-100 transition-colors duration-300 text-xl">
                    <span class="hidden lg:inline">Inzoberi School of Professionals</span>
                    <span class="lg:hidden">Inzoberi</span>
                </a>
            </div>

            <!-- Desktop Navigation -->
            <ul class="hidden lg:flex space-x-6">
            @php
    $navItems = [
        ['route' => 'home', 'label' => 'Home'],
        ['route' => 'about', 'label' => 'About'],
        ['route' => 'program', 'label' => 'Program'],
        ['route' => 'books', 'label' => 'Books'],
        ['route' => 'videos.index', 'label' => 'Videos'],
        ['route' => 'testimonials.index', 'label' => 'Testimonials'],
        ['route' => 'faq', 'label' => 'FAQ'],
        ['route' => 'contact', 'label' => 'Contact'],
        ['route' => 'cbt', 'label' => 'CBT']
    ];
@endphp
                @foreach($navItems as $item)
                    <li>
                        <a href="{{ route($item['route']) }}" class="nav-link text-white hover:text-primary-100 transition-colors duration-300">
                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach
                <!-- Notifications Link -->
                
            </ul>

            <!-- Authentication & User Actions -->
            <div class="flex items-center space-x-4">
                @auth('admin')
                    <div class="relative group">
                        <button class="dropdown-toggle flex items-center focus:outline-none text-white hover:text-primary-100 transition-colors duration-300">
                            <span class="hidden sm:inline">Admin Menu</span>
                            <i class="fas fa-chevron-down ml-2"></i>
                        </button>
                        <div class="dropdown-menu absolute right-0 mt-2 w-48 bg-white shadow-lg rounded-lg py-2 z-50">
                            <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 hover:bg-primary-50 text-primary-700 hover:text-primary-900 transition-colors duration-200">
                                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                            </a>
                            <a href="{{ route('admin.books') }}" class="block px-4 py-2 hover:bg-primary-50 text-primary-700 hover:text-primary-900 transition-colors duration-200">
                                <i class="fas fa-book mr-2"></i>Manage Books
                            </a>
                            <a href="{{ route('admin.videos') }}" class="block px-4 py-2 hover:bg-primary-50 text-primary-700 hover:text-primary-900 transition-colors duration-200">
                                <i class="fas fa-video mr-2"></i>Manage Videos
                            </a>
                            <div class="border-t border-gray-200 my-2"></div>
                            <form method="POST" action="{{ route('admin.logout') }}" class="block">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 hover:bg-red-50 text-red-600 hover:text-red-800 transition-colors duration-200">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @elseauth('web')
                    <div class="flex items-center space-x-3">
                        {{-- Notification Bell --}}
                        <div class="relative" x-data="{ open: false }" @click.away="open = false">
                            <button @click="open = !open" 
                                    class="notification-bell relative p-2 text-white hover:text-primary-100 focus:outline-none transition-colors duration-300 {{ auth()->user()->unreadNotifications->count() > 0 ? 'has-unread' : '' }}">
                                <i class="fas fa-bell text-lg"></i>
                                @if(auth()->user()->unreadNotifications->count() > 0)
                                    <span id="notification-count" 
                                          class="notification-pulse absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold">
                                        {{ auth()->user()->unreadNotifications->count() > 9 ? '9+' : auth()->user()->unreadNotifications->count() }}
                                    </span>
                                @else
                                    <span id="notification-count" class="hidden absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold"></span>
                                @endif
                            </button>

                            <!-- Notification Dropdown -->
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border z-50"
                                 style="display: none;">
                                
                                <div class="p-4 border-b border-gray-200">
                                    <div class="flex items-center justify-between">
                                        <h3 class="font-semibold text-gray-900">Notifications</h3>
                                        <a href="{{ route('notifications.index') }}" class="text-sm text-primary-600 hover:underline">View All</a>
                                    </div>
                                </div>

                                <div class="max-h-80 overflow-y-auto">
                                    @forelse(auth()->user()->unreadNotifications->take(5) as $notification)
                                    <div class="p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors duration-200" 
                                         onclick="markAsRead('{{ $notification->id }}')">
                                        @php $data = $notification->data; @endphp
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0">
                                                @switch($data['icon'] ?? 'info')
                                                    @case('success')
                                                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                                            <i class="fas fa-check text-green-600"></i>
                                                        </div>
                                                        @break
                                                    @case('warning')
                                                        <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                                            <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                                                        </div>
                                                        @break
                                                    @case('error')
                                                        <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                                            <i class="fas fa-times text-red-600"></i>
                                                        </div>
                                                        @break
                                                    @case('celebration')
                                                        <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                                            <span class="text-purple-600">🎉</span>
                                                        </div>
                                                        @break
                                                    @default
                                                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                            <i class="fas fa-info text-blue-600"></i>
                                                        </div>
                                                @endswitch
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-900">{{ $data['title'] ?? 'Notification' }}</p>
                                                <p class="text-xs text-gray-600 mt-1">{{ Str::limit($data['message'], 60) }}</p>
                                                <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="p-4 text-center text-gray-500">
                                        <i class="fas fa-bell-slash text-gray-400 text-2xl mb-2"></i>
                                        <p class="text-sm">No new notifications</p>
                                    </div>
                                    @endforelse
                                </div>

                                @if(auth()->user()->unreadNotifications->count() > 0)
                                <div class="p-3 border-t border-gray-200 bg-gray-50">
                                    <button onclick="markAllAsRead()" class="w-full text-sm text-primary-600 hover:underline">
                                        Mark All as Read
                                    </button>
                                </div>
                                @endif
                            </div>
                        </div>

                        <a href="{{ url('/account') }}" class="text-white hover:text-primary-100 transition-colors duration-300 flex items-center">
                            <i class="fas fa-user-circle mr-2"></i>
                            <span class="hidden sm:inline">Dashboard</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-white hover:text-primary-100 transition-colors duration-300 px-4 py-2 rounded hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500 flex items-center">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </button>
                        </form>
                    </div>
                @else
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('login') }}" class="text-white hover:text-primary-100 transition-colors duration-300 px-3 py-1 rounded border border-transparent hover:border-primary-100 flex items-center">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </a>
                        <a href="{{ route('register') }}" class="bg-primary-500 text-white px-4 py-2 rounded hover:bg-primary-600 transition-colors duration-300 font-medium flex items-center">
                            <i class="fas fa-user-plus mr-2"></i>Register
                        </a>
                    </div>
                @endauth

                <!-- Mobile Menu Toggle -->
                <button class="lg:hidden text-white hover:text-primary-100 p-2" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </nav>

        <!-- Mobile Menu (Overlay) -->
        <div id="mobile-menu" class="fixed inset-0 bg-primary-900 bg-opacity-95 z-50 transform -translate-x-full transition-transform duration-300 ease-in-out">
            <div class="container mx-auto px-4 py-8 relative">
                <button class="absolute top-4 right-4 text-white text-3xl" onclick="toggleMobileMenu()">
                    <i class="fas fa-times"></i>
                </button>
                
                <ul class="space-y-6 mt-12">
                    @foreach($navItems as $item)
                        <li>
                            <a href="{{ route($item['route']) }}" class="block text-white text-2xl hover:text-primary-100 py-3 border-b border-primary-700 transition-colors duration-300">
                                {{ $item['label'] }}
                            </a>
                        </li>
                    @endforeach
                    
                    @auth
                        <li>
                            <a href="{{ route('notifications.index') }}" class="block text-white text-2xl hover:text-primary-100 py-3 border-b border-primary-700 transition-colors duration-300">
                                <i class="fas fa-bell mr-3"></i>Notifications
                                @if(auth()->user()->unreadNotifications->count() > 0)
                                    <span class="bg-red-500 text-white text-sm rounded-full px-2 py-1 ml-2">
                                        {{ auth()->user()->unreadNotifications->count() }}
                                    </span>
                                @endif
                            </a>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="block">
                                @csrf
                                <button type="submit" class="w-full text-left text-white text-2xl hover:text-primary-100 py-3 border-b border-primary-700 transition-colors duration-300">
                                    <i class="fas fa-sign-out-alt mr-3"></i>Logout
                                </button>
                            </form>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </header>

    <main class="container mx-auto py-6 bg-white shadow-md rounded-lg mt-4 p-6 flex-grow">
        @yield('content')
    </main>

    <footer class="bg-primary-800 text-white text-center py-6 mt-auto">
        <div class="container mx-auto">
            <p>&copy; {{ date('Y') }} Inzoberi School of Professionals. All Rights Reserved.</p>
            <div class="mt-4 flex justify-center space-x-6">
                <a href="#" class="hover:text-primary-100 transition-colors"><i class="fab fa-facebook"></i></a>
                <a href="#" class="hover:text-primary-100 transition-colors"><i class="fab fa-twitter"></i></a>
                <a href="#" class="hover:text-primary-100 transition-colors"><i class="fab fa-linkedin"></i></a>
                <a href="#" class="hover:text-primary-100 transition-colors"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
    </footer>

    {{-- Hidden data for JavaScript --}}
    @auth
        <div data-user-authenticated style="display: none;"></div>
    @endauth

    {{-- SweetAlert2 JS --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.10.1/sweetalert2.all.min.js"></script>

    {{-- Notification System JavaScript --}}
    <script>
        // Notification Management System
        class NotificationManager {
            constructor() {
                this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                this.initializeSystem();
            }

            initializeSystem() {
                // Check for session alerts
                this.checkSessionAlerts();
                
                // Auto-update notification count
                if (document.querySelector('[data-user-authenticated]')) {
                    setInterval(() => this.updateNotificationCount(), 60000);
                }
            }

            checkSessionAlerts() {
                // Success alerts
                const successMeta = document.querySelector('meta[name="flash-success"]');
                if (successMeta) {
                    this.showSuccess(successMeta.getAttribute('content'));
                }

                // Error alerts  
                const errorMeta = document.querySelector('meta[name="flash-error"]');
                if (errorMeta) {
                    this.showError(errorMeta.getAttribute('content'));
                }

                // Warning alerts
                const warningMeta = document.querySelector('meta[name="flash-warning"]');
                if (warningMeta) {
                    this.showWarning(warningMeta.getAttribute('content'));
                }

                // Info alerts
                const infoMeta = document.querySelector('meta[name="flash-info"]');
                if (infoMeta) {
                    this.showInfo(infoMeta.getAttribute('content'));
                }

                // Special alerts
                if (window.showWelcomeAlert) {
                    this.showWelcomeAlert();
                }
            }

            showSuccess(message) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: message,
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    toast: true,
                    position: 'top-end'
                });
            }

            showError(message) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message,
                    confirmButtonColor: '#ef4444'
                });
            }

            showWarning(message) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning',
                    text: message,
                    confirmButtonColor: '#f59e0b'
                });
            }

            showInfo(message) {
                Swal.fire({
                    icon: 'info',
                    title: 'Information',
                    text: message,
                    confirmButtonColor: '#8C1C13'
                });
            }

            showWelcomeAlert() {
                Swal.fire({
                    title: 'Welcome to Inzoberi!',
                    html: `
                        <div class="text-center">
                            <div class="text-6xl mb-4">🎉</div>
                            <p class="mb-4">Your membership is now active!</p>
                            <div class="bg-primary-50 p-4 rounded-lg">
                                <p class="text-sm text-primary-800">
                                    <strong>What's next?</strong><br>
                                    📚 Browse our premium books<br>
                                    🎥 Watch exclusive videos<br>
                                    📧 Check your email for details
                                </p>
                            </div>
                        </div>
                    `,
                    showConfirmButton: true,
                    confirmButtonText: 'Start Exploring!',
                    confirmButtonColor: '#8C1C13',
                    allowOutsideClick: false
                });
            }

            updateNotificationCount() {
                if (!this.csrfToken) return;

                fetch('/notifications/unread-count', {
                    headers: { 'X-CSRF-TOKEN': this.csrfToken }
                })
                .then(response => response.json())
                .then(data => {
                    const countElement = document.getElementById('notification-count');
                    const bellElement = document.querySelector('.notification-bell');
                    
                    if (countElement && bellElement) {
                        countElement.textContent = data.count > 9 ? '9+' : data.count;
                        
                        if (data.count === 0) {
                            countElement.classList.add('hidden');
                            bellElement.classList.remove('has-unread');
                        } else {
                            countElement.classList.remove('hidden');
                            bellElement.classList.add('has-unread');
                        }
                    }
                })
                .catch(error => console.error('Notification count update failed:', error));
            }
        }

        // Global notification functions
        function markAsRead(notificationId) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            fetch(`/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function markAllAsRead() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            fetch('/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // Initialize notification system when DOM loads
        document.addEventListener('DOMContentLoaded', function() {
            new NotificationManager();
        });

        // Session-based alert data
        @if(session('show_welcome_alert'))
            window.showWelcomeAlert = true;
        @endif
    </script>

    <script>
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('-translate-x-full');
        }

        // Dropdown menu functionality
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
            
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const dropdownMenu = this.nextElementSibling;
                    
                    // Close all other dropdowns
                    document.querySelectorAll('.dropdown-menu').forEach(menu => {
                        if (menu !== dropdownMenu) {
                            menu.style.display = 'none';
                            menu.style.opacity = '0';
                            menu.style.visibility = 'hidden';
                        }
                    });
                    
                    // Toggle current dropdown
                    if (dropdownMenu.style.display === 'block') {
                        dropdownMenu.style.display = 'none';
                        dropdownMenu.style.opacity = '0';
                        dropdownMenu.style.visibility = 'hidden';
                    } else {
                        dropdownMenu.style.display = 'block';
                        dropdownMenu.style.opacity = '1';
                        dropdownMenu.style.visibility = 'visible';
                    }
                });
            });
            
            // Close dropdowns when clicking outside
            document.addEventListener('click', function() {
                const dropdowns = document.querySelectorAll('.dropdown-menu');
                dropdowns.forEach(dropdown => {
                    dropdown.style.display = 'none';
                    dropdown.style.opacity = '0';
                });
            });
        });
    </script>

    @stack('scripts')
</body>
</html>