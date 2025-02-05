<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<style>
    .highlighted {
    background-color: gray !important; /* Change background color to gray */
    color: white !important; /* Change font color to white */
}
</style>
<body>
@extends('staff.dashboard.StaffMain')

@section('content')
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1 -->
        <div class="card bg-white text-gray-900 p-6 rounded-2xl hover:bg-gray-900 hover:text-white transition duration-300" onclick="highlightCard(this)">
            <div class="flex items-center space-x-4">
                <div class="p-3 bg-gray-100 rounded-full">
                    <!-- Icon (example: user icon) -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 11H3a1 1 0 01-1-1V5a1 1 0 011-1h6a1 1 0 011 1v1m10 8v2a2 2 0 01-2 2h-2m2 2h2a2 2 0 002-2v-2m-8-8H5a2 2 0 00-2 2v2m14 0v-2a2 2 0 00-2-2h-2" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold">Content Name 1</h3>
                    <p class="text-sm">123</p>
                </div>
            </div>
        </div>
        
        <!-- Card 2 -->
        <div class="card bg-white text-gray-900 p-6 rounded-2xl hover:bg-gray-900 hover:text-white transition duration-300" onclick="highlightCard(this)">
            <div class="flex items-center space-x-4">
                <div class="p-3 bg-gray-100 rounded-full">
                    <!-- Icon (example: folder icon) -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h.172a2 2 0 011.414.586l1.828 1.828A2 2 0 009.828 8H17a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold">Content Name 2</h3>
                    <p class="text-sm">456</p>
                </div>
            </div>
        </div>

        <!-- QR Code Card -->
        <div class="card bg-white text-gray-900 p-6 rounded-2xl hover:bg-gray-900 hover:text-white transition duration-300" onclick="highlightCard(this)">
            <div class="flex items-center space-x-4">
                <div class="p-3 bg-gray-100 rounded-full">
                    <!-- Icon for QR Code -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 11V7a1 1 0 011-1h4a1 1 0 011 1v4M4 4v16M20 20v-8M8 8v8M12 12v8" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold">Generated QR Code</h3>
                    <p class="text-sm">Scan the QR code below:</p>
                    <!-- Display QR Code -->
                </div>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="card bg-white text-gray-900 p-6 rounded-2xl hover:bg-gray-900 hover:text-white transition duration-300" onclick="highlightCard(this)">
            <div class="flex items-center space-x-4">
                <div class="p-3 bg-gray-100 rounded-full">
                    <!-- Icon (example: bell icon) -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V4a1 1 0 00-2 0v6a1 1 0 00-2 0v2m8 0v-2a1 1 0 10-2 0v2a1 1 0 001 1h2M4 20h16" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold">Content Name 4</h3>
                    <p class="text-sm">101</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function highlightCard(card) {
            // Remove the 'highlighted' class from all cards
            document.querySelectorAll('.card').forEach(c => c.classList.remove('highlighted'));
            // Add the 'highlighted' class to the clicked card
            card.classList.add('highlighted');
        }
    </script>
@endsection
</body>
</html>