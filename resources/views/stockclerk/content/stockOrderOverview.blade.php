<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Overview</title>
</head>
<style>
    td {
        font-size: 13px;
    }
</style>
<body>
@extends('stockclerk.dashboard.stockClerkDashboard')
@section('content')

<div class="container mx-auto p-4 bg-white rounded-xl">

    <div style="text-align: center; margin-bottom: 20px; font-size: 26px; font-weight: 800; color: #333;">
        Overview
    </div>

    <div class="flex justify-between items-center mb-4 space-x-4">
        <!-- Search bar -->
        <div class="w-full sm:w-1/3">
            <input 
                type="text" 
                id="search-bar" 
                class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" 
                placeholder="Search by Order ID or User ID">
        </div>

        <!-- Status filter -->
        <div class="w-full sm:w-1/3">
            <select 
                id="status-filter" 
                class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">All Statuses</option>
                <option value="Pending">Pending</option>
                <option value="In Process">In Process</option>
                <option value="Ready to Pickup">Ready to Pickup</option>
                <option value="Completed">Completed</option>
                <option value="Cancelled">Cancelled</option>
            </select>
        </div>
        
        <!-- Date Range Filter -->
        <div class="w-full sm:w-1/3 flex space-x-2">
            <input 
                type="date" 
                id="start-date" 
                class="w-full text-sm px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
            <input 
                type="date" 
                id="end-date" 
                class="w-full text-sm px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="table-auto w-full border-collapse border border-gray-300">
            <thead>
                <tr class="bg-white">
                    <th class="border border-gray-300 px-4 py-2">Order ID</th>
                    <th class="border border-gray-300 px-4 py-2">User ID</th>
                    <th class="border border-gray-300 px-4 py-2">Total Items</th>
                    <th class="border border-gray-300 px-4 py-2">Total</th>
                    <th class="border border-gray-300 px-4 py-2">Created At</th>
                    <th class="border border-gray-300 px-4 py-2">Status</th>
                    <th class="border border-gray-300 px-4 py-2">Action</th>
                </tr>
            </thead>
            <tbody id="order-table">
                @foreach ($orders as $order)
                    <tr class="border border-gray-300 transition-transform duration-300 hover:bg-gray-100">
                        <td class="border border-gray-300 px-4 py-2">{{ $order->order_id }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $order->user_id }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $order->total_items }}</td>
                        <td class="border border-gray-300 px-4 py-2">₱ {{ $order->total_price }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $order->created_at }}</td>
                        <td class="border border-gray-300 px-4 py-2">
                        <span 
                            class="
                                px-4 py-1 rounded-full text-sm text-white
                                @if ($order->status === 'Pending') bg-yellow-500
                                @elseif ($order->status === 'Ready to Pickup') bg-blue-500
                                @elseif ($order->status === 'Cancelled') bg-red-500
                                @elseif ($order->status === 'In Process') bg-orange-500
                                @elseif ($order->status === 'Completed') bg-green-500
                                @else bg-gray-500
                                @endif
                            ">
                            {{ $order->status }}
                        </span>
                        </td>
                        <td class="border border-gray-300 px-4 py-2">
                            <p style="text-align:center"><a href="{{ route('stockclerkoverViewDetails', ['order_id' => $order->order_id]) }}" 
                               class="text-blue-600 hover:underline">view</a></p>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
    const startDate = document.getElementById("start-date");
    const endDate = document.getElementById("end-date");
    const orderTable = document.getElementById("order-table");

    startDate.addEventListener("change", filterByDateRange);
    endDate.addEventListener("change", filterByDateRange);

    function filterByDateRange() {
        const startValue = startDate.value ? new Date(startDate.value) : null;
        const endValue = endDate.value ? new Date(endDate.value) : null;
        const rows = orderTable.querySelectorAll("tr");

        rows.forEach((row) => {
            const createdAtText = row.cells[4].textContent.trim(); // Assuming date is in the 5th column
            const createdAtDate = new Date(createdAtText);

            const matchesDate =
                (!startValue || createdAtDate >= startValue) &&
                (!endValue || createdAtDate <= endValue);

            if (matchesDate) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }
    });

</script>
<script src="{{ asset('js/overview-orders-filter.js') }}"></script>

@endsection

@section('scripts')
@endsection
</body>
</html>
