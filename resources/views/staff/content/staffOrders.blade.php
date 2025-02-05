<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    @extends('staff.dashboard.StaffMain')

    @section('content')
        <div class="container mx-auto px-4 py-1">
            <!-- Table with orders -->
            <table id="orders-table" class="table-auto w-full border-collapse border border-gray-300">
                <thead class="bg-white">
                    <tr>  
                        <th class="px-4 py-2 text-left border-b border-gray-300">Order ID</th>
                        <th class="px-4 py-2 text-left border-b border-gray-300">Scan Status</th>
                        <th class="px-4 py-2 text-left border-b border-gray-300">Action</th>
                    </tr>
                </thead>
                <tbody id="orders-list" class="text-gray-800">
                    <!-- Orders will be appended here -->
                </tbody>
            </table>
        </div>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            // Function to fetch orders and update the table
            function fetchOrders() {
                $.ajax({
                    url: '{{ route("orders.fetch") }}', // URL for fetching orders
                    method: 'GET',
                    success: function(data) {
                        if (data.length > 0) {
                            // Remove all rows before adding new ones
                            $('#orders-list').empty();

                            // Append new orders to the table
                            data.forEach(function(order) {
                                // Define the button visibility based on scan_status
                                let buttonHtml = order.scan_status === 'yes' 
                                    ? `<button onclick="window.location.href='{{ route('orders.show', ':order_id') }}'.replace(':order_id', ${order.order_id})" class="bg-gray-900 text-white py-2 px-4 rounded hover:bg-gray-700 transition-all duration-300">Click Me</button>`
                                    : '';

                                $('#orders-list').append(`
                                    <tr>
                                        <td class="px-4 py-2 border-b border-gray-300">${order.order_id}</td>
                                        <td class="px-4 py-2 border-b border-gray-300">${order.scan_status}</td>
                                        <td class="px-4 py-2 border-b border-gray-300">${buttonHtml}</td>
                                    </tr>
                                `);
                            });
                        }
                    },
                    error: function(error) {
                        console.log('Error fetching orders:', error);
                    }
                });
            }

            // Fetch orders every 5 seconds (polling)
            setInterval(fetchOrders, 5000); // Polling every 5 seconds
        </script>
    @endsection
</body>
</html>
