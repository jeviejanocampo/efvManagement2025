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
            zoom: 90%;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        
        <!-- Sidebar -->
        <div id="sidebar" class="bg-gray-900 text-white w-64 space-y-6 py-7 px-4 transform -translate-x-full md:translate-x-0 
        transition-transform duration-300 fixed top-0 bottom-0 z-40">
        
            <p style="display: none">Logged in User ID: {{ Auth::id() }}</p>

            <div class="flex justify-center items-center text-2xl font-bold">
            <img src="{{ asset('product-images/efvlogo.png') }}" alt="EFV Logo" style="width: 128px;" class="ml-2 rounded-full">
                <!-- <p style="margin-top: 8px; text-align: center"><a href="#" class="text-white">Admin Panel</a></p> -->
            </div>
            <!-- Navigation -->
            <nav class="space-y-6">
                <p class="text-white text-sm font-bold">Admin</p>

                <a href="{{ route('admin.dashboard.page') }}" class="flex items-center text-white hover:text-white ml-2">
                    <i class="fas fa-dashboard mr-3"></i>
                   <span class="text-sm"> Dashboard </span>
                </a>

            
                <p class="text-white text-sm font-bold">Reports and Analytics</p>
                <a href="#" class="flex items-center text-white hover:text-white ml-2">
                    <i class="fas fa-chart-line mr-3"></i>
                    <span class="text-sm"> Sales </span>
                </a>

                <p class="text-white text-sm font-bold">Activity</p>
                <a href="{{ route('admin.Stocklogs') }}" class="flex items-center text-white hover:text-white ml-2">
                    <i class="fas fa-clipboard-list mr-3"></i>
                    <span class="text-sm"> Activity Log </span>
                </a>
            </nav>
        </div>

        <!-- Overlay for Sidebar -->
        <div id="sidebarOverlay" class="fixed inset-0 bg-black opacity-50 hidden md:hidden" onclick="toggleSidebar()"></div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col ml-0 md:ml-64">
            <!-- Header -->
            <header class="bg-gray-900 text-white py-2 px-4 flex justify-between items-center   top-0 w-full">
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
                        <div id="dropdownMenu" class="absolute right-0 mt-20 w-48 bg-white text-gray-900 rounded-lg  hidden opacity-0 transform scale-95 transition-all duration-200">
                            <a href="/admin/login" class="block px-4 py-2 hover:bg-gray-200">Logout</a>
                        </div>
                    </div>

            </header>

            <!-- Dynamic Content -->
            <main class="p-4 sm: pt-7">
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
