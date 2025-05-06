<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Main</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            zoom: 80%;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        
        <!-- Sidebar -->
        <div id="sidebar" class="bg-gray-800 text-white w-64 space-y-8 px-4 transform -translate-x-full 
            md:translate-x-0 transition-transform duration-300 fixed top-0 bottom-0 z-40 
            rounded-tr-2xl shadow-lg overflow-y-auto pb-12" 
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
                        <span class="text-sm">Admin</span>
                        <button onclick="toggleDropdown()" class="text-sm">
                            &#9662;
                        </button>
                    </div>

                    <!-- Dropdown Menu -->
                    <div id="dropdownMenu" class="absolute mt-12 bg-white text-black rounded shadow-md hidden z-50">
                        <form action="{{ route('admin.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 hover:bg-gray-200 text-sm">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Navigation -->
            <nav class="space-y-7">
                <p class="text-white text-sm font-bold">Main</p>

                <a href="{{ route('admin.dashboard.page') }}" class="flex items-center text-white hover:text-white ml-2">
                    <i class="fas fa-dashboard mr-3"></i>
                   <span class="text-sm"> Dashboard </span>
                </a>

                <a href="{{ route('AdminoverView') }}" class="flex items-center text-white hover:text-white ml-2"  >
                    <span>
                        <i class="fa-solid fa-box  mr-3"></i>
                    </span>
                    <span class="text-sm"> Order Overview
                   
                </a>

                <a href="{{ route('adminPOS.view') }}" class="flex items-center text-white hover:text-white ml-2"  >
                    <span>
                        <i class="fa-solid fa-list-check  mr-3"></i>
                    </span>
                    <span class="text-sm"> POS
                   
                </a>

                <p class="text-white text-sm font-bold">Products Management</p>

                <a href="{{ route('adminproductsView') }}" class="flex items-center text-white hover:text-white ml-2"  >
                    <span>
                        <i class="fa-solid fa-box  mr-3"></i>
                    </span>
                    <span class="text-sm"> Products
                   
                </a>

                <a href="{{ route('admindefectiveproductsView') }}" class="flex items-center text-white hover:text-white ml-2"  >
                    <span>
                        <i class="fa-solid fa-circle-exclamation mr-3"></i>
                    </span>
                    <span class="text-sm"> Defective Products
                   
                </a>
                
                <a href="{{ route('admin.add.product') }}" class="flex items-center text-white hover:text-white ml-2"  >
                    <span>
                        <i class=" fas fa-plus-square  mr-3"></i>
                    </span>
                    <span class="text-sm"> Add Products
                   
                </a>
                
                <a href="{{ route('admin.view.brands') }}" class="flex items-center text-white hover:text-white ml-2"  >
                    <span>
                        <i class="fa-solid fa-eye  mr-3"></i>
                    </span>
                    <span class="text-sm">  Brands
                   
                </a>

                <a href="{{ route('admin.view.category') }}" class="flex items-center text-white hover:text-white ml-2"  >
                    <span>
                        <i class="fa-solid fa-eye  mr-3"></i>
                    </span>
                    <span class="text-sm">  Categories
                   
                </a>

                <p class="text-white text-sm font-bold">Replacement/Refund</p>

                <a href="{{ route('admin.refundRequests') }}" class="flex items-center text-white hover:text-white ml-2"  >
                    <span>
                        <i class="fa-solid fa-copy  mr-3"></i>
                    </span>
                    <span class="text-sm"> Order Details
                   
                </a>


                <a href="{{ route('admin.refund.report.view') }}" class="flex items-center text-white hover:text-white ml-2"  >
                    <span>
                        <i class="fa-solid fa-copy  mr-3"></i>
                    </span>
                    <span class="text-sm"> Replacement Report
                   
                </a>


                <p class="text-white text-sm font-bold">User Management</p>
                <a href="{{ route('admin.users') }}" class="flex items-center text-white hover:text-white ml-2">
                    <i class="fas fa-users mr-3"></i>
                    <span class="text-sm"> Users </span>
                </a>


            
                <p class="text-white text-sm font-bold">Reports and Analytics</p>
                <a href="{{ route('admin.salesreport') }}" class="flex items-center text-white hover:text-white ml-2">
                    <i class="fas fa-chart-line mr-3"></i>
                    <span class="text-sm"> Sales </span>
                </a>

                <p class="text-white text-sm font-bold">Activity</p>
                <a href="{{ route('admin.Stocklogs') }}" class="flex items-center text-white hover:text-white ml-2">
                    <i class="fas fa-clipboard-list mr-3"></i>
                    <span class="text-sm"> Activity Log </span>
                </a>

                <a href="{{ route('admin.refund.log') }}" class="flex items-center text-white hover:text-white ml-2">
                    <i class="fas fa-clipboard-list mr-3"></i>
                    <span class="text-sm"> Replacement Activity Log </span>
                </a>
            </nav>
        </div>

        <!-- Overlay for Sidebar -->
        <div id="sidebarOverlay" class="fixed inset-0 bg-black opacity-50 hidden md:hidden" onclick="toggleSidebar()"></div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col ml-0 md:ml-64">
            <!-- Header -->
            <header class="text-white py-4 px-4 flex justify-between items-center top-0 w-70 rounded-tl-lg bg-cover bg-center"
            style="margin: 20px; margin-left: 12px; background-image: url('{{ asset('assets/product-images/dashboarddisplay.png') }}');">

                <div class="flex items-center space-x-4">
                    <!-- Hamburger for Small Screens -->
                    <button class="md:hidden focus:outline-none" onclick="toggleSidebar()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div class="space-x-2">
                        <h1 class="text-lg md:text-2xl font-semibold mt-2">EFV AUTO PARTS MANAGEMENT SYSTEM</h1>
                        <h2 class="text-1xl font-medium">Admin Main</h2>
                    </div>                </div>

                <div class="relative flex items-center space-x-4">
                        

                        <!-- Dropdown -->
                        <div id="dropdownMenu" class="absolute right-0 mt-20 w-48 bg-white text-gray-900 rounded-lg  hidden opacity-0 transform scale-95 transition-all duration-200">
                        <form action="{{ route('admin.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 hover:bg-gray-200">
                                Logout
                            </button>
                        </form>                        </div>
                    </div>

            </header>

            <!-- Dynamic Content -->
            <main class="p-6 sm: pt-2">
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
                link.classList.remove("text-white", "bg-gray-100", "shadow-md", "scale-105", "font-bold", "rounded-[12px]", "p-4");
                link.classList.add("text-white", "hover:text-white"); // Add hover effect back to non-active links
            });

            clickedLink.classList.add("text-black", "bg-gray-100", "shadow-md", "scale-105", "font-bold", "rounded-[12px]", "p-4");
            clickedLink.classList.remove("text-white", "hover:text-white"); // Remove hover effect from active link

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
