@extends('manager.dashboard.managerDashboard')

@section('content')
<style>
    td {
        text-align: center;
        font-size: 12px;
    }
    th {
        text-align: center;
        font-size: 13px;
    }

    @media print {
        button {
            display: none !important;
        }

        /* Force hide header during printing */
        /* header {
            display: none !important;
        } */

        /* Optionally hide other elements */
        .no-print {
            display: none !important;
        }
    }

    /* Watermark background style */
    .watermark-container {
        position: relative;
        z-index: 1; /* Content sits above the watermark */
    }

    .watermark-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        /* background: url('{{ asset('product-images/efvlogo.png') }}') no-repeat center center; */
        background-size: 50%; /* Adjust size of the watermark */
        opacity: 0.2; /* Opacity of the watermark (20%) */
        z-index: -1; /* Pushes the watermark behind content */
    }
</style>

<div class="watermark-container bg-white p-6 rounded-md" style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">
    <!-- Header Section -->
    <button onclick="window.history.back()" 
        class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition duration-300">
        Back
    </button>

    <!-- Date Range Filter -->
    <div class="mt-4 mb-4">
        <h1 class="text-lg font-semibold mb-4" style="font-size:32px">Sales Report</h1>
    </div>

   


    <!-- Sales Report Table -->
    <div>
        <!-- Keyword Search Filter -->

       <div class="flex justify-between items-start flex-wrap gap-4 mb-4">

            <!-- LEFT: Search + Filters -->
            <div class="flex flex-wrap items-end gap-3">

                <!-- Search -->
                <div>
                    <label for="search" class="text-xs font-medium text-gray-700 block">Search</label>
                    <input type="text" name="search" id="search"
                        value="{{ request('search') }}"
                        placeholder="Search reference, user, or product"
                        class="block w-64 border border-gray-300 rounded-md shadow-sm p-1 text-sm focus:ring focus:ring-green-200">
                </div>

                <!-- Filter Form -->
                <form method="GET" action="{{ route('manager.generateReport') }}" class="flex flex-wrap items-end gap-3">
                    <div>
                        <label for="start_date" class="text-xs font-medium text-gray-700 block">Start</label>
                        <input type="date" name="start_date" id="start_date" 
                            value="{{ request('start_date') }}" 
                            class="block w-full border border-gray-300 rounded-md shadow-sm p-1 text-sm focus:ring focus:ring-green-200">
                    </div>

                    <div>
                        <label for="end_date" class="text-xs font-medium text-gray-700 block">End</label>
                        <input type="date" name="end_date" id="end_date" 
                            value="{{ request('end_date') }}" 
                            class="block w-full border border-gray-300 rounded-md shadow-sm p-1 text-sm focus:ring focus:ring-green-200">
                    </div>

                    <div>
                        <label for="month" class="text-xs font-medium text-gray-700 block">Month</label>
                        <select name="month" id="month" 
                            class="block w-full border border-gray-300 rounded-md shadow-sm p-1 text-sm focus:ring focus:ring-green-200">
                            <option value="">Select Month</option>
                            @foreach(range(1, 12) as $month)
                                <option value="{{ $month }}" {{ request('month') == $month ? 'selected' : '' }}>
                                    {{ DateTime::createFromFormat('!m', $month)->format('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="year" class="text-xs font-medium text-gray-700 block">Year</label>
                        <select name="year" id="year" 
                            class="block w-full border border-gray-300 rounded-md shadow-sm p-1 text-sm focus:ring focus:ring-green-200">
                            <option value="">Select Year</option>
                            @foreach(range(date('Y') - 10, date('Y')) as $year)
                                <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Button -->
                    <div>
                        <button type="submit" 
                            class="bg-blue-600 text-white px-3 py-1 text-sm rounded-md hover:bg-blue-700 transition duration-300 mt-1">
                            Filter
                        </button>
                    </div>

                    <!-- Export to Excel -->
                    <div>
                        <a href="{{ route('manager.exportSalesReport', ['start_date' => request('start_date'), 'end_date' => request('end_date')]) }}"
                            class="bg-green-600 text-white px-2 py-1 rounded-md hover:bg-green-700 transition duration-300 mt-1 text-sm">
                            Export to Excel
                        </a>
                    </div>
                </form>
            </div>

            <!-- RIGHT: Print Button -->
            <div class="mt-6 sm:mt-0">
                <button onclick="printReport()" 
                    class="bg-green-600 text-white px-3 py-2 rounded-md hover:bg-green-700 transition duration-300 text-sm">
                    Print Report
                </button>
            </div>

        </div>

        <table class="w-full border-collapse border border-gray-300 text-left">
        <p class="italic text-gray-600 text-sm mt-4">
            Each product's unit price has been calculated to include a 12% VAT and a markup based on the original cost price, ensuring appropriate pricing for resale.
        </p>
            <thead class="bg-gray-100">
                <tr>
                    <th class="border border-gray-300 px-4 py-2">PRODUCT REFERENCE ID</th>
                    <th class="border border-gray-300 px-4 py-2">USER</th>
                    <th class="border border-gray-300 px-4 py-2">PRODUCT NAME</th>
                    <th class="border border-gray-300 px-4 py-2 hidden">MARKUP</th>
                    <th class="border border-gray-300 px-4 py-2 hidden">MODEL ID</th>
                    <th class="border border-gray-300 px-4 py-2 hidden">VARIANT ID</th>
                    <th class="border border-gray-300 px-4 py-2">CREATED</th>
                    <th class="border border-gray-300 px-4 py-2">PRICE</th>
                    <th class="border border-gray-300 px-4 py-2">QUANTITY</th>
                    <th class="border border-gray-300 px-4 py-2">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orderDetails as $detail)
                <tr>
                    <td class="border border-gray-300 px-4 py-2">
                        {{ $detail->orderReference->reference_id ?? 'N/A' }}
                    </td>
                    <td class="border border-gray-300 px-4 py-2">
                        {{ $detail->order->customer->full_name ?? 'Guest' }}
                    </td>
                    <td class="border border-gray-300 px-4 py-2">{{ $detail->product_name }}</td>
                    <td class="border border-gray-300 px-4 py-2 hidden">{{ $detail->markup_percentage }}%</td>
                    <td class="border border-gray-300 px-4 py-2 hidden">{{ $detail->model_id }}</td>
                    <td class="border border-gray-300 px-4 py-2">
                        {{ \Carbon\Carbon::parse($detail->created_at)->format('F j, Y, g:i A') }}
                    </td>
                    <td class="border border-gray-300 px-4 py-2 hidden">{{ $detail->variant_id }}</td>
                    <td class="border border-gray-300 px-4 py-2">₱{{ number_format($detail->price, 2) }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $detail->quantity }}</td>
                    <td class="border border-gray-300 px-4 py-2">₱{{ number_format($detail->total_price, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="border border-gray-300 px-4 py-2 text-center">No completed orders found for the selected date range.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Summary Section -->
        <div class="flex justify-end mt-6">
            <div class="w-1/3 text-right">
            <div style="margin-top: 20px; width: 250px; margin-left: auto; font-size: 0.875rem;">
            <p class="flex justify-between mb-6">
                        <strong>SUMMARY</strong> 
                    </p>
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <div><strong>Total Items:</strong></div>
                    <div>{{ number_format($salesTotal, 0) }}</div>
                </div>

                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <div><strong>Total:</strong></div>
                    <div>₱{{ number_format($salesAmount, 2) }}</div>
                </div>

                <br>

                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <div><strong>VAT Amount (12%):</strong></div>
                    <div>₱{{ number_format($salesAmount * 0.12, 2) }}</div>
                </div>
                
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <div><strong>VATable Sales:</strong></div>
                    <div>₱{{ number_format($salesAmount - ($salesAmount * 0.12), 2) }}</div>
                </div>

                @php
                    $vatAmount = $salesAmount * 0.12;
                    $vatableSales = $salesAmount - $vatAmount;


                    // Determine effective average markup rate
                    $markupIncome = 0;

                    foreach ($orderDetails as $detail) {
                        $markupRate = 0;

                        if ($detail->price >= 1 && $detail->price <= 500) {
                            $markupRate = 0.02;
                        } elseif ($detail->price >= 501 && $detail->price <= 1000) {
                            $markupRate = 0.05;
                        } elseif ($detail->price > 1000) {
                            $markupRate = 0.10;
                        }

                        // Cost before VAT and markup
                        $cost = $detail->price / (1 + $markupRate) / 1.12;

                        // Revenue = total_price
                        $total = $detail->total_price;

                        // VAT = 12% of price
                        $vat = $detail->price * 0.12;

                        // Income = markup only
                        $incomePerUnit = $detail->price - ($cost + $vat);
                        $markupIncome += $incomePerUnit * $detail->quantity;
                    }
                @endphp

                <br>

                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <div><strong>Revenue:</strong></div>
                    <div>₱{{ number_format($salesAmount, 2) }}</div>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <div><strong>Income:</strong></div>
                    <div>₱{{ number_format($markupIncome, 2) }}</div>
                </div>

        
            </div>
        </div>

    </div>

    <!-- Notes Section -->
    <div class="mt-8 text-gray-700 text-sm italic">
        <p>Note: Based on the summary, the sales amount represents the total revenue generated during the selected date range, while the total items sold indicates the overall quantity of products purchased by customers.</p>
        <div class="mt-4 text-right">
            <p>Owner: ___Ernany Fabor Verdadero__</p>
            <p>Printed By: {{ Auth::user()->name ?? 'Guest' }}</p>
        </div>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('search');
        const tableRows = document.querySelectorAll('tbody tr');

        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase();

            tableRows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                if (rowText.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
</script>
<script>
    function printReport() {
        window.print();
    }
</script>
@endsection
