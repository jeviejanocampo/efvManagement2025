<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Main</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            zoom: 85%;
        }
    </style>
</head>
<body class="bg-gray-50" >
    <div class="flex h-screen">
        
        <!-- Sidebar -->
        <div id="sidebar" class="bg-gray-800 text-white w-64 space-y-8 px-4 transform -translate-x-full 
            md:translate-x-0 transition-transform duration-300 fixed top-0 bottom-0 z-40 
            rounded-r-2xl shadow-lg" 
            style="margin-right: 14px; margin-top: 14px">


            <p style="display: none">Logged in User ID: {{ Auth::id() }}</p>

            <div class="flex items-center space-x-3 text-white font-bold bg-gray-600 p-3 rounded-lg">
                <!-- Profile Image -->
                <img class="w-12 h-12 rounded-full" src="{{ asset('product-images/adminlogo.png') }}" alt="Profile">

                <!-- Name and Manager -->
                <div class="flex flex-col leading-tight">
                    <span class="text-sm">{{ Auth::user()->name ?? 'Guest' }}</span>

                    <!-- Manager + Dropdown -->
                    <div class="flex items-center space-x-1">
                        <span class="text-sm">Manager</span>
                        <button onclick="toggleDropdown()" class="text-sm">
                            &#9662;
                        </button>
                    </div>

                    <!-- Dropdown Menu -->
                    <div id="dropdownMenu" class="absolute mt-12 bg-white text-black rounded shadow-md hidden z-50">
                        <form action="{{ route('manager.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 hover:bg-gray-200 text-sm">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>


            <!-- Navigation -->
            <nav class="space-y-6">
                <p class="text-white text-sm font-bold">Main</p>

                <a href="{{ route('manager.dashboard.page') }}" class="flex items-center text-white hover:text-white ml-2">
                    <i class="fas fa-dashboard mr-3"></i>
                   <span class="text-sm"> Dashboard </span>
                </a>

                <a href="{{ route('manageroverView') }}" class="flex items-center text-gray hover:text-white ml-2">
                    <i class="fas fa-clipboard-list mr-3"></i>
                    <span class="ml-1 text-sm">Requests</span>
                    @if(session('pendingCount') && session('pendingCount') > 0)
                        <span class="ml-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">
                            {{ session('pendingCount') }}
                        </span>
                    @endif
                </a>

                <p class="text-white text-sm font-bold">Products Management</p>
                <div class="ml-2">
                    <button onclick="toggleProducts()" class="flex items-center justify-between w-full text-white hover:text-white focus:outline-none">
                        <div class="flex items-center">
                            <i class="fas fa-box mr-3 mb-2"></i> 
                            <span class="text-sm mb-2"> View </span>
                        </div>
                            <i id="products-arrow" class="fas fa-chevron-down transition-transform duration-300"></i>
                    </button>

                    <!-- Submenu -->
                    <div id="products-submenu" class=" mt-2 space-y-6 overflow-hidden max-h-0 transition-all duration-300">
                            <a href="{{ route('ManagerproductsView') }}" class="flex items-center text-sm text-white hover:text-white mt-2">
                                <i class="fas fa-box mr-4"></i> 
                                <span class="text-sm ml-1">  Products </span>
                            </a>
                            <a href="{{ route('manager.add.product') }}" class="flex items-center text-sm text-white hover:text-white mt-6 ">
                                <i class="fas fa-plus-square mr-4"></i> 
                                <span class="text-sm ml-1">  Add Product </span>
                            </a>
                            <!-- <a href="{{ route('manager.add.brand') }}" class="flex items-center text-sm text-white hover:text-white mt-6">
                                <i class="fas fa-tags mr-4"></i>
                                <span class="text-sm">  Add New Brand </span>
                            </a>
                            <a href="{{ route('manager.add.category') }}" class="flex items-center text-sm text-white hover:text-white mt-6">
                                <i class="fas fa-folder-plus mr-4"></i> 
                                <span class="text-sm">  Add Category </span>
                            </a> -->
                            <a href="{{ route('manager.view.brands') }}" class="flex items-center text-sm text-white hover:text-white mt-6">
                                <i class="fas fa-eye mr-4"></i>
                                <span class="text-sm">   View Brands </span>
                            </a>
                            <a href="{{ route('manager.view.category') }}" class="flex items-center text-sm text-white hover:text-white mt-6 ">
                                <i class="fas fa-eye mr-4"></i>
                                <span class="text-sm">  View Categories </span>
                            </a>
                    </div>
                </div>

                <a href="{{ route('managerLow') }}" class="flex items-center text-white hover:text-white ml-2 mt-2">
                    <i class="fas fa-exclamation-triangle mr-3"></i>
                    <span class="text-sm">  Low Units </span>
                    @if($lowStockCount > 0)
                        <span class="ml-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">
                            {{ $lowStockCount }}
                        </span>
                    @endif
                    
                </a>

                <!-- <a href="{{ route('staffQueue') }}" class="flex items-center text-white hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h11M9 21h6M4 14h16" />
                    </svg>
                    Orders Queue
                </a> -->
                
                <p class="text-white text-sm font-bold">Reports and Analytics</p>
                <a href="{{ route('manager.salesreport') }}" class="flex items-center text-white hover:text-white ml-2">
                    <i class="fas fa-chart-line mr-3"></i>
                    <span class="text-sm"> Sales </span>
                </a>

                <p class="text-white text-sm font-bold">Activity</p>
                <a href="{{ route('manager.Stocklogs') }}" class="flex items-center text-white hover:text-white ml-2">
                    <i class="fas fa-clipboard-list mr-3"></i>
                    <span class="text-sm"> Activity Log </span>
                </a>

                <a href="{{ route('manager.refund.log') }}" class="flex items-center text-white hover:text-white ml-1 gap-2">
                        <i class="fa-solid fa-book text-gray-200 ml-1"></i>
                    <span class="text-sm">  Refund Activity Log </span>
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
                        <h2 class="text-sm font-medium">Manager Main</h2>
                    </div>
                </div>
                <div class="relative flex items-center space-x-4">
                    
                    <!-- <img src="{{ asset('product-images/efvlogo.png') }}" alt="EFV Logo" class="w-14 ml-2 rounded-full"> -->

                    <!-- Greeting -->
                   

                    <!-- Dropdown -->
                    <div id="dropdownMenu" class="absolute right-0 mt-20 w-48 bg-white text-gray-900 rounded-lg hidden opacity-0 transform scale-95 transition-all duration-200">
                        <form action="{{ route('manager.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 hover:bg-gray-200">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Dynamic Content -->
            <main class="p-6 sm: pt-4">
                @yield('content')
            </main>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const submenu = document.getElementById('products-submenu');
        const arrow = document.getElementById('products-arrow');

        // Load state from localStorage on page load
        const isOpen = localStorage.getItem('productsSubmenuOpen');
        if (isOpen === 'true') {
            submenu.classList.remove('max-h-0');
            submenu.classList.add('max-h-60');
            arrow?.classList.add('rotate-180');
        }

        window.toggleProducts = function () {
            if (submenu.classList.contains('max-h-0')) {
                submenu.classList.remove('max-h-0');
                submenu.classList.add('max-h-60');
                arrow?.classList.add('rotate-180');
                localStorage.setItem('productsSubmenuOpen', 'true');
            } else {
                submenu.classList.add('max-h-0');
                submenu.classList.remove('max-h-60');
                arrow?.classList.remove('rotate-180');
                localStorage.setItem('productsSubmenuOpen', 'false');
            }
        }
    });
</script>
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
