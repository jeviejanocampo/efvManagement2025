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
            zoom: 90%;
        }
    </style>
</head>
<body class="bg-gray-200">
    <div class="flex h-screen">
        
        <!-- Sidebar -->
        <div id="sidebar" class="bg-gray-900 text-white w-64 space-y-6 py-7 px-4 transform -translate-x-full md:translate-x-0 transition-transform duration-300 fixed top-0 bottom-0 z-40">
        <p style="display: none">Logged in User ID: {{ Auth::id() }}</p>
            <div class="text-2xl font-bold">
                <img src="{{ asset('product-images/efvlogo.png') }}" alt="EFV Logo" class="w-25 h-25">
                <p style="margin-top: 8px"><a href="#" class="text-white">Stock Clerk Panel</a></p>
            </div>
            <!-- Navigation -->
            <nav class="space-y-4">
                <a href="{{ route('stockoverView') }}" class="flex items-center text-gray-300 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h11M9 21h6M4 14h16" />
                    </svg>
                    Reserve and Pre-Orders
                </a>
                <a href="{{ route('productsView') }}" class="flex items-center text-gray-300 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h11M9 21h6M4 14h16" />
                    </svg>
                    Products
                </a>
                <a href="{{ route('stockclerkLow') }}" class="flex items-center text-gray-300 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h11M9 21h6M4 14h16" />
                    </svg>
                    Low Units
                    @if($lowStockCount > 0)
                        <span class="ml-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">
                            {{ $lowStockCount }}
                        </span>
                    @endif
                </a>
                <!-- <a href="{{ route('staffQueue') }}" class="flex items-center text-gray-300 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h11M9 21h6M4 14h16" />
                    </svg>
                    Orders Queue
                </a> -->
                
                <a href="{{ route('Stocklogs') }}" class="flex items-center text-gray-300 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16" />
                    </svg>
                    Activity Log
                </a>
                <!-- <a href="#" class="flex items-center text-gray-300 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12h7M7 16h6M5 20h10" />
                    </svg>
                    Customers
                </a> -->
            </nav>
        </div>

        <!-- Overlay for Sidebar -->
        <div id="sidebarOverlay" class="fixed inset-0 bg-black opacity-50 hidden md:hidden" onclick="toggleSidebar()"></div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col ml-0 md:ml-64">
            <!-- Header -->
            <header class="bg-gray-900 text-white py-2 px-4 flex justify-between items-center shadow-md  top-0 w-full">
                <div class="flex items-center space-x-4">
                    <!-- Hamburger for Small Screens -->
                    <button class="md:hidden focus:outline-none" onclick="toggleSidebar()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h1 class="text-lg md:text-xl font-semibold">EFV Auto Parts Management System</h1>
                </div>

                <div class="relative flex items-center space-x-4">
                        <!-- Greeting -->
                        <div class="text-white">
                            <h2 class="text-lg font-semibold">
                                Good day, {{ Auth::user()->name ?? 'Guest' }}!
                            </h2>
                        </div>

                        <!-- Profile Button -->
                        <button onclick="toggleDropdown()" class="flex items-center space-x-2 focus:outline-none">
                            <img class="w-8 h-8 rounded-full" src="{{ asset('product-images/adminlogo.png') }}" alt="Profile">
                            <!-- <span class="hidden sm:inline">Profile</span> -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.292 7.292a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 011.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0-01-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <!-- Dropdown -->
                        <div id="dropdownMenu" class="absolute right-0 mt-20 w-48 bg-white text-gray-900 rounded-lg shadow-md hidden opacity-0 transform scale-95 transition-all duration-200">
                            <a href="/stock-clerk/login" class="block px-4 py-2 hover:bg-gray-200">Logout</a>
                        </div>
                    </div>

            </header>

            <!-- Dynamic Content -->
            <main class="p-12 sm: pt-7">
                @yield('content')
            </main>
        </div>
    </div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const navLinks = document.querySelectorAll("#sidebar nav a");

        // Function to update active link state
        function setActiveLink(clickedLink) {
            navLinks.forEach(link => {
                link.classList.remove("text-white", "scale-105", "font-bold");
                link.classList.add("text-gray-300");
            });

            clickedLink.classList.add("text-white", "scale-105", "font-bold");
            clickedLink.classList.remove("text-gray-300");

            // Store the active link in localStorage to persist highlight
            localStorage.setItem("activeNav", clickedLink.getAttribute("href"));
        }

        // Check if there is a stored active link in localStorage
        const storedActiveLink = localStorage.getItem("activeNav");
        if (storedActiveLink) {
            const activeElement = [...navLinks].find(link => link.getAttribute("href") === storedActiveLink);
            if (activeElement) {
                setActiveLink(activeElement);
            }
        }

        // Add click event listener to each nav link
        navLinks.forEach(link => {
            link.addEventListener("click", function () {
                setActiveLink(this);
            });
        });
    });
</script>
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
