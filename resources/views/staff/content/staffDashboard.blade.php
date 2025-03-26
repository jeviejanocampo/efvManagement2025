<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<style>
    body{
        /* zoom: 90%; */
    }
    .highlighted {
        background-color: gray !important; /* Change background color to gray */
        color: white !important; /* Change font color to white */
    }
    td {
        text-align: center;
    }
</style>
<body>
@extends('staff.dashboard.StaffMain')

@section('content')
    <!-- Main Cards 1 -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6" style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">

        <div class="card bg-white text-gray-900 p-6 rounded-2xl 
        hover:bg-gray-900 hover:text-white transition duration-300"
         onclick="highlightCard(this)" style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">
            <div class="flex items-center space-x-4">
                <div class="p-3 bg-gray-100 rounded-full">
                    <!-- Icon (example: user icon) -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 11H3a1 1 0 01-1-1V5a1 1 0 011-1h6a1 1 0 011 1v1m10 8v2a2 2 0 01-2 2h-2m2 2h2a2 2 0 002-2v-2m-8-8H5a2 2 0 00-2 2v2m14 0v-2a2 2 0 00-2-2h-2" />
                    </svg>
                </div>
                <div class="grid grid-cols-2">
                    <h3 class="text-lg font-semibold">Pending Requests</h3>
                    <p id="pendingOrders" class="text-4xl text-right">0</p>
                </div>
            </div>
        </div>
        
        <div class="card bg-white text-gray-900 p-6 rounded-2xl hover:bg-gray-900 hover:text-white transition duration-300" onclick="highlightCard(this)">
            <div class="flex items-center space-x-4">
                <div class="p-3 bg-gray-100 rounded-full">
                    <!-- Icon (example: folder icon) -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h.172a2 2 0 011.414.586l1.828 1.828A2 2 0 009.828 8H17a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                    </svg>
                </div>
                <div class="grid grid-cols-2">
                    <h3 class="text-lg font-semibold">On Queue</h3>
                    <p id="onQueueOrders" class="text-4xl text-right">0</p>
                </div>

            </div>
        </div>

        <div class="card bg-white text-gray-900 p-6 rounded-2xl hover:bg-gray-900 
        hover:text-white transition duration-300" onclick="highlightCard(this)" style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">
            <div class="flex items-center space-x-4">
                <div class="p-3 bg-gray-100 rounded-full">
                    <!-- Icon for QR Code -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 11V7a1 1 0 011-1h4a1 1 0 011 1v4M4 4v16M20 20v-8M8 8v8M12 12v8" />
                    </svg>
                </div>
                <div class="grid grid-cols-2">
                    <h3 class="text-lg font-semibold">In Process</h3>
                    <p id="inProcessOrders" class="text-4xl text-right">0</p>
                    <!-- Display QR Code -->
                </div>
            </div>
        </div>

        <div class="card bg-white text-gray-900 p-6 rounded-2xl hover:bg-gray-900 hover:text-white transition duration-300" onclick="highlightCard(this)">
            <div class="flex items-center space-x-4">
                <div class="p-3 bg-gray-100 rounded-full">
                    <!-- Icon (example: bell icon) -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V4a1 1 0 00-2 0v6a1 1 0 00-2 0v2m8 0v-2a1 1 0 10-2 0v2a1 1 0 001 1h2M4 20h16" />
                    </svg>
                </div>
                <div class="grid grid-cols-2">
                    <h3 class="text-lg font-semibold">Total Sales Today</h3>
                    <p id="totalSalesToday" class="text-2xl text-right">₱ 0.00</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 p-4" style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">
        <h3 class="text-2xl font-semibold mb-2 border-b border-gray-200">Recent Pending</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-300 mt-2">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="px-4 py-2 border">Reference ID</th>
                        <th class="px-4 py-2 border">User</th>
                        <th class="px-4 py-2 border">Total Items</th>
                        <th class="px-4 py-2 border">Total</th>
                        <th class="px-4 py-2 border">Created At</th>
                        <th class="px-4 py-2 border">Status</th>
                    </tr>
                </thead>
                <tbody id="recentPendingTable">
                    <!-- Dynamic content will be inserted here -->
                </tbody>
            </table>
        </div>
    </div>



    <script>
        document.addEventListener("DOMContentLoaded", function () {
            fetch("/staff/dashboard/orders-summary")
                .then(response => response.json())
                .then(data => {
                    document.getElementById("pendingOrders").textContent = data.pending_orders;
                    document.getElementById("onQueueOrders").textContent = data.on_queue_orders;
                    document.getElementById("inProcessOrders").textContent = data.in_process_orders;
                    document.getElementById("totalSalesToday").textContent = `₱ ${data.total_sales_today.toFixed(2)}`;

                    // Update Recent Pending Table
                    let recentPendingTable = document.getElementById("recentPendingTable");
                    recentPendingTable.innerHTML = ""; // Clear existing rows

                    data.recent_pending_orders.forEach(order => {
                        let row = `
                            <tr class="bg-gray-100">
                                <td class="px-4 py-2 border">#${order.order_id}</td>
                                <td class="px-4 py-2 border">${order.customer ? order.customer.name : 'Unknown'}</td>
                                <td class="px-4 py-2 border">${order.total_items}</td>
                                <td class="px-4 py-2 border">₱${parseFloat(order.total_price).toFixed(2)}</td>
                                <td class="px-4 py-2 border">${new Date(order.created_at).toLocaleDateString()}</td>
                                <td class="px-4 py-2 border text-red-500 font-semibold">${order.status}</td>
                            </tr>
                        `;
                        recentPendingTable.innerHTML += row;
                    });
                })
                .catch(error => console.error("Error fetching data:", error));
        });
    </script>
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