<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - @yield('title')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    
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
    </style>
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
                        ['route' => 'videos.index', 'label' => 'Videos'], // Changed from 'videos' to 'videos.index'
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
                            <form method="POST" action="{{ route('logout') }}" class="block">
                                @csrf
                                <button type="submit" class="w-full text-left text-white text-2xl hover:text-primary-100 py-3 border-b border-primary-700 transition-colors duration-300">
                                    Logout
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
                    dropdown.style.visibility = 'hidden';
                });
            });
        });
    </script>
</body>
</html>
