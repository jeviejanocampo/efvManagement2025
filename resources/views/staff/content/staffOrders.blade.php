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
    <div class ="bg-white p-4 mt-6 rounded-md" style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">
        <div class="container mx-auto px-4 py-1">
            <h1 style="font-size: 36px; font-weight: bold" class="border-b border-gray-200">Scanned Items Queue</h1>

            <p style="margin-bottom: 12px; font-style: italic;color: gray">
                Note: Scanned qr code will be reflected here
            </p>
            <!-- Table with orders -->
            <table id="orders-table" class="table-auto w-full">
                <thead class="bg-gray-100">
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
        function fetchOrders() {
            $.ajax({
                url: '{{ route("orders.fetch") }}', // URL for fetching orders
                method: 'GET',
                success: function(data) {
                    $('#orders-list').empty(); // Clear previous content

                    if (data.length > 0) {
                        // Append new orders to the table
                        data.forEach(function(order) {
                            let displayId = order.reference_id || order.order_id; // Ensure correct ID is shown

                            let buttonHtml = order.scan_status === 'yes' 
                                ? `<button onclick="window.location.href='{{ route('orders.show', ':order_id') }}'
                                    .replace(':order_id', ${order.order_id}) + '?reference_id=' + encodeURIComponent('${displayId}')"
                                    class="bg-gray-900 text-white py-2 px-4 rounded hover:bg-gray-700 transition-all duration-300">
                                    Click Me
                                </button>`
                                : '';

                            // Append data to the table
                            $('#orders-list').append(`
                                <tr>
                                    <td class="px-4 py-2 text-center">${displayId}</td> <!-- Reference ID Column -->
                                    <td class="px-4 py-2 text-center">${order.scan_status}</td> <!-- Scan Status Column -->
                                    <td class="px-4 py-2 text-center">${buttonHtml}</td> <!-- Button Column -->
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
