<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AI PR Inspector Admin') - Admin Panel</title>
    <script src="{{ asset('js/chart.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/app.js') }}" defer></script>
</head>
<body class="bg-gray-50">
    <!-- Mobile sidebar backdrop -->
    <div id="sidebar-backdrop" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden"></div>

    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside id="sidebar" class="fixed lg:static inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
            <!-- Sidebar Header -->
            <div class="flex items-center justify-between h-16 px-6 border-b border-gray-200">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 1 1 0 000 2H6a2 2 0 100 4h2a2 2 0 100-4h-.5a1 1 0 000-2H8a2 2 0 012 2v9a2 2 0 01-2 2H6a2 2 0 01-2-2V5z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <a href="/" class="text-lg font-bold text-gray-900 hover:text-gray-700">AI PR Inspector</a>
                </div>
                <!-- Mobile close button -->
                {{--<button id="close-sidebar" class="lg:hidden text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>--}}
            </div>

            <!-- Sidebar Navigation -->
            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                <!-- Main Navigation -->
                <div class="space-y-1">
                    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4">
                        <svg class="mr-3 h-5 w-5 {{ request()->routeIs('admin.dashboard') ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        Dashboard
                    </a>

                    <a href="{{ route('admin.reviews.index') }}" class="{{ request()->routeIs('admin.reviews.*') ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4">
                        <svg class="mr-3 h-5 w-5 {{ request()->routeIs('admin.reviews.*') ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Reviews
                    </a>
                </div>

                <!-- User Management -->
                @if(Auth::user()->hasPermission('admin.users'))
                <div class="mt-8">
                    <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">User Management</h3>
                    <div class="mt-3 space-y-1">
                        <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4">
                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('admin.users.*') ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            Users
                        </a>

                        <a href="{{ route('admin.roles.index') }}" class="{{ request()->routeIs('admin.roles.*') ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4">
                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('admin.roles.*') ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            Roles
                        </a>
                    </div>
                </div>
                @endif

                <!-- System -->
                <div class="mt-8">
                    <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">System</h3>
                    <div class="mt-3 space-y-1">
                        @if(Auth::user()->hasPermission('admin.settings'))
                        <a href="{{ route('admin.statistics') }}" class="{{ request()->routeIs('admin.statistics') ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4">
                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('admin.statistics') ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Statistics
                        </a>
                        @endif

                        @if(Auth::user()->hasPermission('admin.logs'))
                        <a href="{{ route('admin.logs.index') }}" class="{{ request()->routeIs('admin.logs.*') ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} group flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4">
                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('admin.logs.*') ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Logs
                        </a>
                        @endif
                    </div>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden w-full lg:ml-0">
            <!-- Top Bar -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center">
                        <!-- Mobile menu button -->
                        <button id="open-sidebar" class="lg:hidden text-gray-500 hover:text-gray-700 mr-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                        <h1 class="text-lg font-semibold text-gray-900">@yield('title', 'Dashboard')</h1>
                    </div>

                    <!-- User Profile Dropdown -->
                    <div class="relative">
                        <button id="user-menu-button" class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 lg:hover:bg-gray-50 p-1 lg:p-0">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-medium text-indigo-600">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                    </div>
                                </div>
                                <div class="ml-3 text-left hidden lg:block">
                                    <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                                    @php
                                        $userRoles = Auth::user()->roles->pluck('name')->toArray();
                                    @endphp
                                    @if(count($userRoles) > 0)
                                        <p class="text-xs text-gray-500">{{ implode(', ', array_slice($userRoles, 0, 2)) }}</p>
                                    @else
                                        <p class="text-xs text-gray-500">User</p>
                                    @endif
                                </div>
                                <svg class="ml-2 h-4 w-4 text-gray-400 hidden lg:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </button>

                        <!-- Dropdown menu -->
                        <div id="user-menu" class="hidden absolute right-0 mt-2 w-48 sm:w-56 lg:w-64 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50 max-h-96 overflow-y-auto">
                            <div class="py-1">
                                <!-- User Profile Header -->
                                <div class="px-4 py-3 border-b border-gray-100">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ Auth::user()->name }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                                </div>

                                <!-- Menu Items -->
                                <div class="py-1">
                                    <a href="{{ route('admin.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <div class="flex items-center">
                                            <svg class="mr-3 h-4 w-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            <span class="truncate">Profile</span>
                                        </div>
                                    </a>

                                    <a href="{{ route('admin.settings') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <div class="flex items-center">
                                            <svg class="mr-3 h-4 w-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            <span class="truncate">Settings</span>
                                        </div>
                                    </a>

                                    @if(Auth::user()->hasPermission('admin.settings'))
                                    <a href="{{ route('admin.statistics') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <div class="flex items-center">
                                            <svg class="mr-3 h-4 w-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                            </svg>
                                            <span class="truncate">Statistics</span>
                                        </div>
                                    </a>
                                    @endif
                                </div>

                                <!-- Logout -->
                                <div class="border-t border-gray-100 py-1">
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <div class="flex items-center">
                                                <svg class="mr-3 h-4 w-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                                </svg>
                                                <span class="truncate">Logout</span>
                                            </div>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto bg-gray-50">
                <div class="py-6">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        @if(session('success'))
                            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded relative" role="alert">
                                <span class="block sm:inline">{{ session('success') }}</span>
                                <button type="button" class="absolute top-2 right-2 text-green-600 hover:text-green-800" onclick="this.parentElement.remove()">
                                    <span class="sr-only">Close</span>
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded relative" role="alert">
                                <span class="block sm:inline">{{ session('error') }}</span>
                                <button type="button" class="absolute top-2 right-2 text-red-600 hover:text-red-800" onclick="this.parentElement.remove()">
                                    <span class="sr-only">Close</span>
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded relative" role="alert">
                                <div class="font-medium">Please fix the following errors:</div>
                                <ul class="mt-2 list-disc list-inside text-sm">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="absolute top-2 right-2 text-red-600 hover:text-red-800" onclick="this.parentElement.remove()">
                                    <span class="sr-only">Close</span>
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </div>
                        @endif

                        @yield('content')
                    </div>
                </div>
            </main>

            <!-- Footer -->
            @include('components.footer')
        </div>
    </div>

    <!-- Sidebar JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar toggle functionality
            const mobileSidebar = document.getElementById('mobile-sidebar');
            const sidebarBackdrop = document.getElementById('sidebar-backdrop');
            const openSidebarBtn = document.getElementById('open-sidebar');
            const closeSidebarBtn = document.getElementById('close-mobile-sidebar');

            // User menu dropdown functionality
            const userMenuButton = document.getElementById('user-menu-button');
            const userMenu = document.getElementById('user-menu');

            function openMobileSidebar() {
                console.log('Opening mobile sidebar'); // Debug log
                console.log('Elements:', mobileSidebar, sidebarBackdrop);
                if (mobileSidebar && sidebarBackdrop) {
                    mobileSidebar.classList.remove('hidden');
                    mobileSidebar.classList.remove('-translate-x-full');
                    sidebarBackdrop.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                    console.log('Sidebar opened successfully');
                } else {
                    console.error('Mobile sidebar or backdrop not found');
                }
            }

            function closeMobileSidebar() {
                console.log('Closing mobile sidebar'); // Debug log
                if (mobileSidebar && sidebarBackdrop) {
                    mobileSidebar.classList.add('-translate-x-full');
                    setTimeout(() => {
                        mobileSidebar.classList.add('hidden');
                    }, 300);
                    sidebarBackdrop.classList.add('hidden');
                    document.body.style.overflow = '';
                    console.log('Sidebar closed successfully');
                } else {
                    console.error('Mobile sidebar or backdrop not found');
                }
            }

            function toggleUserMenu() {
                userMenu.classList.toggle('hidden');
            }

            function closeUserMenu() {
                userMenu.classList.add('hidden');
            }

            // Sidebar event listeners
            console.log('Checking elements:', {
                mobileSidebar: !!mobileSidebar,
                sidebarBackdrop: !!sidebarBackdrop,
                openSidebarBtn: !!openSidebarBtn,
                closeSidebarBtn: !!closeSidebarBtn
            });

            if (openSidebarBtn) {
                console.log('Open sidebar button found'); // Debug log
                openSidebarBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Open sidebar clicked!');
                    openMobileSidebar();
                });
            } else {
                console.log('Open sidebar button NOT found'); // Debug log
            }

            if (closeSidebarBtn) {
                console.log('Close sidebar button found'); // Debug log
                closeSidebarBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Close sidebar clicked!');
                    closeMobileSidebar();
                });
            } else {
                console.log('Close sidebar button NOT found'); // Debug log
            }

            if (sidebarBackdrop) {
                sidebarBackdrop.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Backdrop clicked!');
                    closeMobileSidebar();
                });
            }

            // User menu event listeners
            if (userMenuButton) {
                userMenuButton.addEventListener('click', toggleUserMenu);
            }

            // Close user menu when clicking outside
            document.addEventListener('click', (e) => {
                if (!userMenuButton.contains(e.target) && !userMenu.contains(e.target)) {
                    closeUserMenu();
                }
            });

            // Close sidebar on escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !mobileSidebar.classList.contains('hidden')) {
                    closeMobileSidebar();
                }
                if (e.key === 'Escape' && !userMenu.classList.contains('hidden')) {
                    closeUserMenu();
                }
            });
        });

        // Auto-refresh functionality for logs
        let autoRefreshInterval;

        function startAutoRefresh(endpoint, containerId, interval = 5000) {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }

            autoRefreshInterval = setInterval(() => {
                fetch(endpoint)
                    .then(response => response.json())
                    .then(data => {
                        const container = document.getElementById(containerId);
                        if (container && data.logs) {
                            container.innerHTML = data.logs.map(log => `
                                <div class="border-l-4 ${log.level === 'ERROR' ? 'border-red-500' : log.level === 'WARNING' ? 'border-yellow-500' : 'border-blue-500'} pl-4 py-2">
                                    <div class="flex items-center">
                                        <span class="text-sm text-gray-500">${log.timestamp}</span>
                                        <span class="ml-2 px-2 py-1 text-xs font-medium rounded ${log.level_class}">${log.level}</span>
                                    </div>
                                    <div class="mt-1 text-sm text-gray-900">${log.message}</div>
                                </div>
                            `).join('');
                        }
                    })
                    .catch(error => console.error('Auto-refresh error:', error));
            }, interval);
        }

        function stopAutoRefresh() {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }
        }
    </script>
</body>
</html>
