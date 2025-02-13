@extends('manager.dashboard.managerDashboard')

@section('content')

<div class="bg-white p-6 rounded-md">

    <div class="flex items-center justify-between mb-6">
        <!-- Sales Overview Title -->
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Sales Overview</h1>
        
        <!-- Generate Report Button -->
        <button class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-300">
            Generate Report
        </button>
    </div>

    <!-- Full-Width Line Chart Section -->
    <div class="w-full bg-white rounded-md shadow-sm dark:bg-gray-800 p-4">
        <h5>Sales Data (Last 6 Months)</h5>
        <div class="w-full h-92">
            <canvas id="salesLineChart"></canvas>
        </div>
    </div>

    <!-- Secondary Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">

        <!-- First Column -->
        <div class="space-y-4">

        <!-- Total Orders -->
        <div class="bg-white p-4 rounded-md shadow-md flex items-center relative">
            <h1 class="absolute top-2 left-4 text-xl font-bold z-10 px-2">Total Orders</h1>
            <br>
            <div class="w-2/3 h-64">
                <canvas id="totalOrdersChart"></canvas>
            </div>
            <div class="w-1/3 pl-4">
                <h5 class="font-semibold mb-2">Order Breakdown</h5>
                <ul class="space-y-2 text-sm">
                    <li><span class="text-green-600 font-bold">Completed:</span> {{ $orderStatuses['Completed'] }} orders</li>
                    <li><span class="text-yellow-600 font-bold">Pending:</span> {{ $orderStatuses['Pending'] }} orders</li>
                    <li><span class="text-red-600 font-bold">Cancelled:</span> {{ $orderStatuses['Cancelled'] }} orders</li>
                    <li><span class="text-blue-600 font-bold">In Process:</span> {{ $orderStatuses['In Process'] }} orders</li>
                </ul>
            </div>
        </div>


        <div class="bg-white p-4 rounded-md shadow-md flex items-center relative">
            <h1 class="absolute top-2 left-4 text-xl font-bold z-10 px-2">Total Sales</h1>
            <br>
            <div class="w-2/3 h-64">
                <canvas id="totalSalesChart"></canvas>
            </div>
            <div class="w-1/3 pl-4">
                <h5 class="font-semibold mb-2">Sales Breakdown</h5>
                <ul class="space-y-2 text-sm">
                    <li><span class="text-blue-600 font-bold">Total Sales:</span> ₱{{ number_format($totalSales, 2) }}</li>
                    <li><span class="text-purple-600 font-bold">Weekly Breakdown:</span></li>
                    <ul class="ml-4 space-y-1">
                        @foreach ($percentagePerWeek as $week => $percentage)
                            <li>
                                Week of {{ $week }}: <span class="text-orange-600 font-bold">{{ number_format($percentage, 2) }}%</span>
                            </li>
                        @endforeach
                    </ul>
                </ul>
            </div>
        </div>



        </div>

        <!-- Second Column -->
        <div class="bg-white p-4 rounded-md shadow-md mt-6">
            <h1 style="font-size: 24px; font-weight: Bold">Top Selling</h1>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th class="border-b py-2 px-4">Product</th>
                        <th class="border-b py-2 px-4">Sales per Pieces</th>
                        <th class="border-b py-2 px-4">Sales</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($salesData as $data)
                        <tr>
                            <td class="border-b py-2 px-4">{{ $data['product_name'] }}</td>
                            <td class="border-b py-2 px-4">{{ $data['quantity'] }}</td>
                            <td class="border-b py-2 px-4">₱{{ number_format($data['sales'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-gray-500">No sales data available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Include Chart.js and custom script -->
<script>
    const salesMonths = @json($months->keys()); // Fetch labels (months)
    const salesData = @json($months->values()); // Fetch data (sales)
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/sales-report.js') }}"></script>

@endsection
