<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Main</title>
    <!-- Import Poppins Font from Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            zoom: 90%;
        }
    </style>
</head>
<body>
    <div class="flex h-screen" >
        
    <div id="sidebar" class="bg-gray-800 text-white w-64 space-y-8 px-4 transform -translate-x-full 
            md:translate-x-0 transition-transform duration-300 fixed top-0 bottom-0 z-40 
            rounded-r-2xl shadow-lg" 
            style="margin-right: 14px; margin-top: 14px">


        <p style="display: none">Logged in User ID: {{ Auth::id() }}</p>
        
            <div class="flex justify-center items-center text-2xl font-bold">
                <img src="{{ asset('product-images/efvlogo.png') }}" alt="EFV Logo" class="w-32 ml-1 rounded-full">
            </div>

    
        <nav class="space-y-4">
            <p class="text-white text-1xl font-bold">Main</p>
            <a href="{{ route('staff.dashboard.page') }}" class="flex items-center text-white hover:text-white ml-1 gap-2">
                <span class="bg-gray-600 p-2 rounded-lg">
                    <i class="fa-solid fa-dashboard text-gray-200"></i>
                </span>
                Dashboard
            </a>
            <a href="{{ route('overView') }}" class="flex items-center text-white hover:text-white ml-1 gap-2">
                <span class="bg-gray-600 p-2 rounded-lg">
                    <i class="fa-solid fa-box text-gray-200"></i>
                </span>
                 Request
                @if(session('pendingCount') && session('pendingCount') > 0)
                    <span class="ml-1 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">
                        {{ session('pendingCount') }}
                    </span>
                @endif
            </a>

            <p class="text-white text-1xl font-bold">Queue</p>
            <a href="{{ route('staffQueue') }}" class="flex items-center text-gray hover:text-white ml-1 gap-2">
                <span class="bg-gray-600 p-2 rounded-lg">
                    <i class="fa-solid fa-list-check text-gray-200"></i>
                </span>
                Orders Queue
            </a>

            <a href="{{ route('staff.refundRequests') }}" class="flex items-center text-gray hover:text-white ml-1 gap-2">
                <span class="bg-gray-600 p-2 rounded-lg">
                    <i class="fa-solid fa-file text-gray-200"></i>
                </span>
                Refund Request
            </a>

            
            <p class="text-white text-1xl font-bold">Activity</p>
            <a href="{{ route('logs') }}" class="flex items-center text-white hover:text-white ml-1 gap-2">
                <span class="bg-gray-600 p-2 rounded-lg">
                    <i class="fa-solid fa-clipboard-list text-gray-200"></i>
                </span>
                Staff Activity Log
            </a>
        </nav>
    </div>

        <!-- Overlay for Sidebar -->
        <div id="sidebarOverlay" class="fixed inset-0 bg-gray-800 opacity-50 hidden md:hidden" onclick="toggleSidebar()"></div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col ml-0 md:ml-64 mt-1">
            <!-- Header -->
            <header class="text-white py-4 px-4 flex justify-between items-center top-0 w-70 rounded-tl-lg bg-cover bg-center"
            style="margin: 12px; margin-left: 12px; background-image: url('{{ asset('assets/product-images/dashboarddisplay.png') }}');">

            <div class="flex items-start space-x-2">
                    <!-- Hamburger for Small Screens -->
                    <button class="md:hidden focus:outline-none" onclick="toggleSidebar()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <i class="fa-solid fa-screwdriver-wrench text-xl md:text-4xl text-white mt-3"></i>
                    <div class="space-x-2">
                        <h1 class="text-lg md:text-2xl font-semibold mt-2">EFV AUTO PARTS MANAGEMENT SYSTEM</h1>
                        <h2 class="text-1xl font-medium">Staff Main</h2>
                    </div>
                </div>
                <div class="relative flex items-center space-x-4">
                    <!-- Greeting -->
                    <div class="text-white">
                        <h2 class="text-1xl font-Regular text-right">{{ Auth::user()->name ?? 'Guest' }}</h2>
                        <h2 class="text-1xl font-semibold">Staff</h2>
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
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 hover:bg-gray-200">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Dynamic Content -->
            <main class="p-4 sm: pt-4">
                @yield('content')
            </main>
        </div>
    </div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const navLinks = document.querySelectorAll("#sidebar nav a");

        function setActiveLink(clickedLink) {
            navLinks.forEach(link => {
                link.classList.remove("text-white", "bg-gray-600", "shadow-md", "scale-105", "font-bold", "rounded-[12px]", "p-4");
                link.classList.add("text-white", "hover:text-white"); 

                const icon = link.querySelector("i");
                if (icon) {
                    icon.classList.remove("text-black");
                    icon.classList.add("text-white");
                }
            });

            clickedLink.classList.add("text-white", "bg-gray-600", "shadow-md", "scale-105", "font-bold", "rounded-[12px]", "p-4");
            clickedLink.classList.remove("text-white", "hover:text-white");

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
