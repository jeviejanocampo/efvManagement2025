<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Main</title>
    <!-- Import Poppins Font from Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-200">

    <div class="flex h-screen">
        <!-- Sidebar -->
        <div id="sidebar" class="bg-gray-900 text-white w-64 space-y-6 py-7 px-4 transform -translate-x-full md:translate-x-0 transition-transform duration-300 fixed top-0 bottom-0 z-40">
        <!-- Logo -->
            <div class="text-2xl font-bold">
                <a href="#" class="text-white">Staff Panel</a>
            </div>
            <!-- Navigation -->
            <nav class="space-y-4">
                <a href="{{ route('dashboardView') }}" class="flex items-center text-gray-300 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h11M9 21h6M4 14h16" />
                    </svg>
                    Dashboard
                </a>
                <a href="{{ route('ordersView') }}" class="flex items-center text-gray-300 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h11M9 21h6M4 14h16" />
                    </svg>
                    Orders
                </a>
                <a href="{{ route('ordersView') }}" class="flex items-center text-gray-300 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h11M9 21h6M4 14h16" />
                    </svg>
                    Overview Orders
                </a>
                <a href="#" class="flex items-center text-gray-300 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16" />
                    </svg>
                    Products
                </a>
                <a href="#" class="flex items-center text-gray-300 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12h7M7 16h6M5 20h10" />
                    </svg>
                    Customers
                </a>
            </nav>
        </div>

        <!-- Overlay for Sidebar -->
        <div id="sidebarOverlay" class="fixed inset-0 bg-black opacity-50 hidden md:hidden" onclick="toggleSidebar()"></div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col ml-0 md:ml-64">
            <!-- Header -->
            <header class="bg-gray-900 text-white py-4 px-6 flex justify-between items-center shadow-md">
                <div class="flex items-center space-x-4">
                    <!-- Hamburger for Small Screens -->
                    <button class="md:hidden focus:outline-none" onclick="toggleSidebar()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h1 class="text-lg md:text-xl font-semibold">EFV Auto Parts Management System</h1>
                </div>
                <div class="relative">
                    <button onclick="toggleDropdown()" class="flex items-center space-x-2 focus:outline-none">
                        <img class="w-8 h-8 rounded-full" src="https://via.placeholder.com/150" alt="Profile">
                        <span class="hidden sm:inline">Profile</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.292 7.292a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 011.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0-01-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <!-- Dropdown -->
                    <div id="dropdownMenu" class="absolute right-0 mt-2 w-48 bg-white text-gray-900 rounded-lg shadow-md hidden opacity-0 transform scale-95 transition-all duration-200">
                        <a href="/staff/login" class="block px-4 py-2 hover:bg-gray-200">Logout</a>
                    </div>
                </div>
            </header>

            <!-- Dynamic Content -->
            <main class="p-4 sm:p-6">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        function toggleDropdown() {
            const dropdown = document.getElementById('dropdownMenu');
            if (dropdown.classList.contains('hidden')) {
                dropdown.classList.remove('hidden', 'opacity-0', 'scale-95');
                dropdown.classList.add('opacity-100', 'scale-100');
            } else {
                dropdown.classList.add('opacity-0', 'scale-95');
                dropdown.classList.remove('opacity-100', 'scale-100');
                setTimeout(() => dropdown.classList.add('hidden'), 200);
            }
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }
    </script>

</body>
</html>
