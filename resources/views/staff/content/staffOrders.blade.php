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
    <div class ="bg-white p-4 mt-6 rounded-md">
        <div class="container mx-auto px-4 py-1">
            <!-- Table with orders -->
            <table id="orders-table" class="table-auto w-full">
                <thead class="bg-white">
                    <tr>  
                        <!-- <th class="px-4 py-2  border-b border-gray-300"></th> -->
                        <th class="px-4 py-2 border-b border-gray-300">Reference ID</th>
                        <th class="px-4 py-2  border-b border-gray-300">Scan Status</th>
                        <th class="px-4 py-2  border-b border-gray-300"></th>
                        <th class="px-4 py-2  border-b border-gray-300"></th>
                    </tr>
                </thead>
                <tbody id="orders-list" class="text-gray-800">
                    <!-- Orders will be appended here -->
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
                // Function to fetch orders and update the table
                function fetchOrders() {
                    $.ajax({
                        url: '{{ route("orders.fetch") }}', // URL for fetching orders
                        method: 'GET',
                        success: function(data) {
                            $('#orders-list').empty(); // Clear previous content

                            if (data.length > 0) {
                                // Append new orders to the table
                                data.forEach(function(order) {
                                    let displayId = order.reference_id || order.order_id;

                                    let buttonHtml = order.scan_status === 'yes' 
                                        ? `<button onclick="window.location.href='{{ route('orders.show', ':order_id') }}'.replace(':order_id', ${order.order_id})" class="bg-gray-900 text-white py-2 px-4 rounded hover:bg-gray-700 transition-all duration-300">Click Me</button>`
                                        : '';

                                    $('#orders-list').append(`
                                        <tr>
                                            <td class="px-4 py-2" style="text-align: center"  >${displayId}</td>
                                            <td class="px-4 py-2" style="text-align: center">${order.scan_status}</td>
                                            <td class="px-4 py-2" style="text-align: center">${buttonHtml}</td>
                                        </tr>
                                    `);
                                });
                            } else {
                                // If no orders, show "No Scanned Order" message
                                $('#orders-list').append(`
                                    <tr>
                                        <td colspan="3" class="text-center text-gray-500 py-10 text-lg font-semibold">
                                            No Scanned Order
                                        </td>
                                    </tr>
                                `);
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
