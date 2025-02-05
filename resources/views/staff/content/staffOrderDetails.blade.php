<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    @extends('staff.dashboard.StaffMain')

    @section('content')
        <div class="container mx-auto px-4 py-6">
            <!-- Back Button to go back to previous page -->
            <a href="{{ url()->previous() }}" class="text-gray-900 hover:text-gray-700 text-lg mb-4 inline-block">← Back</a>

            <!-- Display Order Details Here -->
            <div class="bg-white p-6 rounded-lg shadow-md mt-6">
                <h2 class="text-xl font-semibold">Order Details</h2>
                <!-- Display order details dynamically -->
                <p class="mt-4">Order ID: {{ $order->order_id }}</p>
                <p class="mt-4">Scan Status: {{ $order->scan_status }}</p>
                <p class="mt-4">Total Price: ${{ $order->total_price }}</p>
                <p class="mt-4">Notes: {{ $order->order_notes }}</p>
                <p class="mt-4">Pickup Date: {{ $order->pickup_date }}</p>
                <p class="mt-4">Pickup Location: {{ $order->pickup_location }}</p>
            </div>
        </div>
    @endsection
</body>
</html>
