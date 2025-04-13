@extends('manager.dashboard.managerDashboard')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="bg-white p-6  mb-6" style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">

        <div class="flex items-center justify-between mb-6">
            <!-- Sales Overview Title -->
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Sales Overview</h1>

            
            <!-- Generate Report Button -->
            <a href="{{ route('manager.generateReport') }}" 
            class="bg-blue-800 text-white px-4 py-1 rounded-md hover:bg-blue-700 transition duration-300">
                + Generate Sales Report
            </a>

        </div>

        <!-- <div class="w-full bg-white rounded-md shadow-sm dark:bg-gray-800 p-2">
            <form id="dateRangeForm" class="flex items-center space-x-2">
                <label for="startDate" class="text-gray-700 dark:text-gray-300 text-sm font-medium">Start:</label>
                <input type="date" id="startDate" name="start_date" class="p-1 border rounded-md text-sm">
                <label for="endDate" class="text-gray-700 dark:text-gray-300 text-sm font-medium">End:</label>
                <input type="date" id="endDate" name="end_date" class="p-1 border rounded-md text-sm">
                <button type="submit" class="bg-blue-600 text-white px-2 py-1 text-sm rounded-md hover:bg-blue-700">
                    Filter
                </button>
            </form>
        </div> -->


    <div class="relative h-84 w-full" >
                <!-- <h3 class="text-lg font-semibold mb-4"></h3> -->

                <!-- Date Range Form -->
                <form id="dateRangeForm" class="flex items-center space-x-2 mb-4">
                    <label for="startDate" class="text-gray-700 text-sm font-medium">Start:</label>
                    <input type="date" id="startDate" name="start_date"
                        value="{{ request('start_date', now()->subDays(29)->toDateString()) }}"
                        class="border-gray-300 rounded-md px-2 py-1 focus:ring-blue-500 focus:border-blue-500">
                    
                    <label for="endDate" class="text-gray-700 text-sm font-medium">End:</label>
                    <input type="date" id="endDate" name="end_date"
                        value="{{ request('end_date', now()->toDateString()) }}"
                        class="border-gray-300 rounded-md px-2 py-1 focus:ring-blue-500 focus:border-blue-500">
                    
                    <button type="submit" class="bg-blue-600 text-white px-2 py-1 text-sm rounded-md hover:bg-blue-700">
                        Filter
                    </button>
                </form>

                <label for="salesType" class="text-gray-700 font-medium">Select Type:</label>
                    <select id="salesType" class="border-gray-300 rounded-md px-2 py-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="daily">Daily Sales</option>
                        <option value="monthly">Monthly Sales</option>
                </select>
                
                <div class="relative h-64 w-full">
                    <canvas id="salesChart"></canvas>
                </div>
            <br>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-12 pt-8" style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">

        <div class="space-y-4">
        <div class="bg-white p-4 flex items-center relative">
            <h1 class="absolute top-2 left-4 text-xl font-bold z-10 px-2">Total Orders</h1>
            <br>
            <div class="w-2/3 h-64">
                <canvas id="totalOrdersChart"></canvas>
            </div>
            <div class="w-1/3 pl-4">
                <h5 class="font-semibold mb-2">Order Breakdown</h5>
                <ul class="space-y-2 text-sm ml-6">
                    <li><span class="text-green-600 font-bold">Completed:</span> {{ $orderStatuses['Completed'] }} orders</li>
                    <li><span class="text-yellow-600 font-bold">Pending:</span> {{ $orderStatuses['Pending'] }} orders</li>
                    <li><span class="text-red-600 font-bold">Cancelled:</span> {{ $orderStatuses['Cancelled'] }} orders</li>
                    <li><span class="text-blue-600 font-bold">In Process:</span> {{ $orderStatuses['In Process'] }} orders</li>
                </ul>
            </div>
        </div>


        <div class="bg-white p-4 flex items-center relative">
            <h1 class="absolute top-2 left-2 text-xl font-bold z-10 px-2">Total Sales</h1>
            <br>
            <div class="w-2/3 h-64">
                <canvas id="totalSalesChart"></canvas>
        </div>


        <div class="w-1/3 pl-4">
            <h5 class="font-semibold mb-2">Sales Breakdown</h5>
                <ul class="space-y-2 text-sm ml-2">
                    <!-- Total Sales -->
                    <li><span class="text-blue-600 font-bold">Total Sales:</span> ₱{{ number_format($totalSales, 2) }}</li>
                    
                    <!-- Weekly Breakdown Header -->
                    <li><span class="text-purple-600 font-bold">Weekly Breakdown:</span></li>
                    
                    <!-- Weekly Breakdown: Totals and Percentages -->
                    <ul class="ml-4 space-y-1">
                        @foreach ($percentagePerWeek as $week => $percentage)
                            <li>
                                Week of {{ $week }}: 
                                <span class="text-orange-600 font-bold">₱{{ number_format($weeklySales[$week], 2) }}</span>
                                ({{ number_format($percentage, 2) }}%)
                            </li>
                        @endforeach
                    </ul>
                </ul>
                </div>
            </div>
        </div>

        <div class="bg-white p-4 rounded-md ">
            <h1 style="font-size: 24px; font-weight: Bold">Top Selling</h1>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th class="border-b py-1 px-4"></th>
                        <th class="border-b py-1 px-4">Product</th>
                        <th class="border-b py-1 px-4">Sales per Pieces</th>
                        <th class="border-b py-1 px-4">Sales</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($salesData as $data)
                        <tr>
                            <td class="border-b py-1 px-4">
                                <img src="{{ asset('product-images/' . $data['model_img']) }}" alt="Product Image" class="w-16 h-16 object-cover rounded">
                            </td>
                            <td class="border-b py-1 px-4">{{ $data['product_name'] }}</td>
                            <td class="border-b py-1 px-4">{{ $data['quantity'] }}</td>
                            <td class="border-b py-1 px-4">₱{{ number_format($data['sales'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-gray-500">No sales data available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination links -->
            <div class="mt-4">
                {{ $salesData->links() }}
            </div>
        </div>


    </div>
</div>



<script>
    document.addEventListener('DOMContentLoaded', () => {
        const ctx = document.getElementById('salesChart').getContext('2d');

        // Get Sales Data from Laravel Backend
        const dailySalesLabels = {!! json_encode($days->keys()->map(fn($date) => \Carbon\Carbon::parse($date)->format('M d, Y'))) !!};
        const dailySalesData = {!! json_encode($days->values()) !!};
        const monthlySalesLabels = {!! json_encode($months->keys()->map(fn($date) => \Carbon\Carbon::parse($date)->format('F Y'))) !!};
        const monthlySalesData = {!! json_encode($months->values()) !!};

        // Initialize Chart
        let salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: dailySalesLabels, // Default to daily sales
                datasets: [{
                    label: 'Daily Sales (₱)',
                    data: dailySalesData,
                    borderColor: '#6366f1', // soft indigo
                    backgroundColor: 'transparent', // no fill
                    borderWidth: 1.5, // thinner line
                    tension: 0.3, // slight curve
                    pointRadius: 2, // minimal points
                    pointHoverRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        title: { display: false },
                        grid: {
                            display: false // removes vertical grid lines
                        },
                        ticks: {
                            font: {
                                size: 10
                            }
                        }
                    },
                    y: {
                        title: { display: false },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)' // very light grid
                        },
                        ticks: {
                            font: {
                                size: 10
                            },
                            callback: function(value) {
                                return `₱${value.toLocaleString()}`;
                            }
                        },
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            font: {
                                size: 12
                            },
                            boxWidth: 12,
                            color: '#4B5563'
                        },
                        position: 'top'
                    },
                    tooltip: {
                        enabled: true,
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function (tooltipItem) {
                                return `₱ ${tooltipItem.raw.toLocaleString()}`;
                            }
                        }
                    }
                },
                hover: {
                    mode: 'nearest',
                    intersect: false
                }
            }

        });

        // Event Listener for Dropdown
        document.getElementById('salesType').addEventListener('change', (event) => {
            const selectedType = event.target.value;
            
            if (selectedType === 'daily') {
                salesChart.data.labels = dailySalesLabels;
                salesChart.data.datasets[0].label = 'Daily Sales (₱)';
                salesChart.data.datasets[0].data = dailySalesData;
            } else {
                salesChart.data.labels = monthlySalesLabels;
                salesChart.data.datasets[0].label = 'Monthly Sales (₱)';
                salesChart.data.datasets[0].data = monthlySalesData;
            }

            salesChart.update();
        });
    });
</script>

<script>
    const salesMonths = @json($months->keys()); // Fetch labels (months)
    const salesData = @json($months->values()); // Fetch data (sales)
</script>

<script src="{{ asset('js/sales-report.js') }}"></script>

@endsection
