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
        <div class="flex justify-between items-center mb-4">
        <form method="GET" action="{{ route('manager.generateReport') }}" class="flex items-center gap-2">
            <!-- Start Date Filter -->
            <div>
                <label for="start_date" class="text-xs font-medium text-gray-700">Start</label>
                <input type="date" name="start_date" id="start_date" 
                    value="{{ request('start_date') }}" 
                    class="mt-1 block w-full border-b rounded-md shadow-sm p-1 text-sm focus:ring focus:ring-green-200">
            </div>

            <!-- End Date Filter -->
            <div>
                <label for="end_date" class="text-xs font-medium text-gray-700">End</label>
                <input type="date" name="end_date" id="end_date" 
                    value="{{ request('end_date') }}" 
                    class="mt-1 block w-full border-b rounded-md shadow-sm p-1 text-sm focus:ring focus:ring-green-200">
            </div>

            <!-- Monthly Filter -->
            <div>
                <label for="month" class="text-xs font-medium text-gray-700">Month</label>
                <select name="month" id="month" 
                    class="mt-1 block w-full border-b rounded-md shadow-sm p-1 text-sm focus:ring focus:ring-green-200">
                    <option value="">Select Month</option>
                    @foreach(range(1, 12) as $month)
                        <option value="{{ $month }}" {{ request('month') == $month ? 'selected' : '' }}>
                            {{ DateTime::createFromFormat('!m', $month)->format('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Yearly Filter -->
            <div>
                <label for="year" class="text-xs font-medium text-gray-700">Year</label>
                <select name="year" id="year" 
                    class="mt-1 block w-full border-b rounded-md shadow-sm p-1 text-sm focus:ring focus:ring-green-200">
                    <option value="">Select Year</option>
                    @foreach(range(date('Y') - 10, date('Y')) as $year)
                        <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end mt-6">
                <button type="submit" 
                    class="bg-blue-600 text-white px-3 py-1 text-sm rounded-md hover:bg-blue-700 transition duration-300">
                    Filter
                </button>
            </div>

            <a href="{{ route('manager.exportSalesReport', ['start_date' => request('start_date'), 'end_date' => request('end_date')]) }}"
            class="bg-green-600 text-white px-1 py-1 rounded-md hover:bg-green-700 transition duration-300 mt-6">
             <p style="font-size: 14px; padding: 1px"> Export to Excel</p>
            </a>
        </form>



            <button onclick="printReport()" 
                class="bg-black text-white px-1 py-1 rounded-md hover:bg-green-700 transition duration-300 mt-2">
               <p style="font-size: 14px; padding: 4px"> Print Report</p>
            </button>
        </div>
        <table class="w-full border-collapse border-b text-left">
            <thead class="bg-gray-50">
                <tr>
                    <th class="border-b px-4 py-2">REFERENCE ID</th>
                    <th class="border-b px-4 py-2">PRODUCT NAME</th>
                    <th class="border-b px-4 py-2">PRICE</th>
                    <th class="border-b px-4 py-2">QUANTITY</th>
                    <th class="border-b px-4 py-2">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orderDetails as $detail)
                <tr>
                    <td class="border-b px-4 py-2">
                    {{ $detail->reference_id }}
                    </td>
                    <td class="border-b px-4 py-2">{{ $detail->product_name }}</td>
                    <td class="border-b px-4 py-2">₱{{ number_format($detail->price, 2) }}</td>
                    <td class="border-b px-4 py-2">{{ $detail->quantity }}</td>
                    <td class="border-b px-4 py-2">₱{{ number_format($detail->total_price, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="border-b px-4 py-2 text-center">No completed orders found for the selected date range.</td>
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
            <p>Owner: __________________________</p>
            <p>Printed By: {{ Auth::user()->name ?? 'Guest' }}</p>
        </div>
    </div>

</div>

<!-- JavaScript for Print Button -->    
<script>
    function printReport() {
        window.print();
    }
</script>
@endsection
