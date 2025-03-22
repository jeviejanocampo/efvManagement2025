<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Clerk Main</title>
    <!-- Import Poppins Font from Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            zoom: 90%;
        }
        td{
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="flex h-screen">
        
        <!-- Sidebar -->
        <div id="sidebar" class="bg-black text-white w-64 space-y-6 px-4 transform -translate-x-full 
            md:translate-x-0 transition-transform duration-300 fixed top-0 bottom-0 z-40"
             style="margin: 10px; border-radius: 24px; box-shadow: 4px 4px 12px rgba(0, 0, 0, 0.1);">

            <p style="display: none">Logged in User ID: {{ Auth::id() }}</p>
            <div class="text-2xl font-bold">
            <div class="flex justify-center items-center text-2xl font-bold">
                <img src="{{ asset('product-images/efvlogo.png') }}" alt="EFV Logo" class="w-25 h-25 ml-8">
            </div>
             <!-- <p style="margin-top: 8px; text-align: center"><a href="#" class="text-white">Stock Clerk Panel</a></p> -->
            </div>
            <!-- Navigation -->
            <nav class="space-y-4">
            <p class="text-white text-1xl font-bold">Main</p>
            <a href="{{ route('stockoverView') }}" class="flex items-center text-gray-300 hover:text-white relative ml-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h11M9 21h6M4 14h16" />
                </svg>
                Pending Reserved Orders/Pre-orders

                @if(session('pendingCount') && session('pendingCount') > 0)
                    <span class="ml-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">
                        {{ session('pendingCount') }}
                    </span>
                @endif
            </a>

                <p class="text-white text-1xl font-bold">Products Management</p>
                <a href="{{ route('productsView') }}" class="flex items-center text-gray-300 hover:text-white ml-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h11M9 21h6M4 14h16" />
                    </svg>
                    Products
                    <!-- @if($lowStockCount > 0)
                        <span class="ml-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">
                            {{ $lowStockCount }}
                        </span>
                    @endif -->
                </a>
                <a href="{{ route('stockclerkLow') }}" class="flex items-center text-gray-300 hover:text-white ml-4">
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
                
                <p class="text-white text-1xl font-bold">Activity Log</p>
                <a href="{{ route('Stocklogs') }}" class="flex items-center text-gray-300 hover:text-white ml-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16" />
                    </svg>
                    Stock Clerk Log
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
        <div class="flex-1 flex flex-col ml-0 md:ml-64 mt-1">
            <!-- Header -->
            <header class="bg-black text-white py-6 px-8 flex justify-between items-center top-0 w-70" style="margin: 10px; border-radius: 24px; margin-left: 24px">
                <div class="flex items-start space-x-4">
                    <!-- Hamburger for Small Screens -->
                    <button class="md:hidden focus:outline-none" onclick="toggleSidebar()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <i class="fa-solid fa-screwdriver-wrench text-xl md:text-6xl text-white mt-2"></i>
                    <div class="space-x-2">
                        <h1 class="text-lg md:text-4xl font-semibold mt-4">EFV AUTO PARTS MANAGEMENT SYSTEM</h1>
                        <!-- <h2 class="text-1xl font-medium">Here are your daily tasks for today</h2> -->
                    </div>
                </div>
                <div class="relative flex items-center space-x-4">
                    <!-- Greeting -->
                    <div class="text-white">
                        <h2 class="text-1xl font-semibold">GOOD DAY!,  {{ Auth::user()->name ?? 'Guest' }}!</h2>
                    </div>

                    <!-- Profile Button -->
                    <button onclick="toggleDropdown()" class="flex items-center space-x-2 focus:outline-none">
                        <img class="w-10 h-10 rounded-full" src="{{ asset('product-images/adminlogo.png') }}" alt="Profile">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-6" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.292 7.292a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 011.414 
                            1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0-01-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <!-- Dropdown -->
                    <div id="dropdownMenu" class="absolute right-0 mt-20 w-48 bg-white text-gray-900 rounded-lg hidden opacity-0 transform scale-95 transition-all duration-200">
                        <a href="/stock-clerk/login" class="block px-4 py-2 hover:bg-gray-200">Logout</a>
                    </div>
                </div>
            </header>

            <!-- Dynamic Content -->
            <main class="p-12 sm: pt-4">
                @yield('content')
            </main>
        </div>
    </div>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        const navLinks = document.querySelectorAll("#sidebar nav a");

        function setActiveLink(clickedLink) {
            navLinks.forEach(link => {
                link.classList.remove("text-white", "bg-gray-800", "shadow-md", "scale-105", "font-bold", "rounded-[12px]", "p-4");
                link.classList.add("text-gray-300", "hover:text-white"); 

                const icon = link.querySelector("i");
                if (icon) {
                    icon.classList.remove("text-black");
                    icon.classList.add("text-white");
                }
            });

            clickedLink.classList.add("text-white", "bg-gray-800", "shadow-md", "scale-105", "font-bold", "rounded-[12px]", "p-4");
            clickedLink.classList.remove("text-gray-300", "hover:text-white");

            const activeIcon = clickedLink.querySelector("i");
            if (activeIcon) {
                activeIcon.classList.remove("text-white");
                activeIcon.classList.add("text-white");
            }

            localStorage.setItem("activeNav", clickedLink.getAttribute("href"));
        }

        const storedActiveLink = localStorage.getItem("activeNav");
        if (storedActiveLink) {
            const activeElement = [...navLinks].find(link => link.getAttribute("href") === storedActiveLink);
            if (activeElement) {
                setActiveLink(activeElement);
            }
        }

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
