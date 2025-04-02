@extends('staff.dashboard.StaffMain')

@section('content')
<style>
   
  td {
    text-align: center;
  }
  .filter-container {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
    align-items: center;
  }
  .filter-container select, .filter-container input {
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 5px;
  }
</style>

<div class="bg-white p-4 rounded-md" style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">

    <div class="p-4 bg-white flex items-center justify-between">
        <h1 class="text-4xl font-semibold border-b border-gray-300 pb-2">Order Details</h1>

        <a href="{{ route('request.replacement.form') }}">
            <button class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 flex items-center gap-1">
                <i class="fas fa-plus"></i> Add Replacement
            </button>
        </a>

    </div>

    <!-- FILTERS -->
    <div class="filter-container">
        <!-- Date Filter -->
        <select id="dateFilter">
            <option value="">Filter by Date</option>
            <option value="today">Today</option>
            <option value="week">This Week</option>
            <option value="month">This Month</option>
        </select>

        <!-- Status Filter -->
        <select id="statusFilter">
            <option value="">Filter by Status</option>
            <option value="pending">Pending</option>
            <option value="completed">Completed</option>
            <option value="refunded">Refunded</option>
        </select>

        <!-- Search Filter -->
        <input type="text" id="searchFilter" placeholder="Search by Order ID or User">
    </div>

    <p class="text-gray-500 italic text-sm">
        Note: Requested refund order is also viewed here for a change, please view details.
    </p>


    <!-- TABLE -->
    <table class="w-full border-collapse border border-gray-200">
        <thead>
            <tr class="bg-gray-200">
                <th class="p-1 border">Order ID</th>
                <th class="p-1 border">User</th>
                <th class="p-1 border">Created</th>
                <th class="p-1 border">Status</th>
                <th class="p-1 border">Action</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            @foreach ($refunds as $refund)
            <tr class="border">
                <td class="p-1 border">OR0000{{ $refund->order_id }}</td>
                <td class="p-1 border">{{ $refund->customer->full_name ?? 'Unknown' }}</td>
                <td class="p-1 border created-date" data-date="{{ $refund->created_at }}">{{ $refund->created_at->format('M d, Y - h:i A') }}</td>
                <td class="p-1 border text-center status-cell" data-status="{{ strtolower($refund->status) }}">
                    <span class="px-2 py-1 border rounded-full text-white 
                        @if(strtolower($refund->status) == 'pending') bg-yellow-500 
                        @elseif(strtolower($refund->status) == 'completed') bg-green-600 
                        @elseif(strtolower($refund->status) == 'refunded') bg-red-600 
                        @endif 
                        w-fit inline-block"
                    >
                        {{ ucfirst($refund->status) }}
                    </span>
                </td>
                <td class="p-1 border">
                    <a href="{{ route('staff.refundRequestForm', ['order_id' => $refund->order_id]) }}" 
                        class="bg-blue-400 text-white px-3 py-1 rounded hover:bg-blue-600 items-center gap-1">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">
            {{ $refunds->links() }} 
        </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const dateFilter = document.getElementById("dateFilter");
        const statusFilter = document.getElementById("statusFilter");
        const searchFilter = document.getElementById("searchFilter");
        const tableRows = document.querySelectorAll("#tableBody tr");

        function filterTable() {
            const dateValue = dateFilter.value;
            const statusValue = statusFilter.value.toLowerCase();
            const searchValue = searchFilter.value.toLowerCase();

            tableRows.forEach(row => {
                const createdDate = new Date(row.querySelector(".created-date").dataset.date);
                const status = row.querySelector(".status-cell").dataset.status;
                const orderId = row.children[0].textContent.toLowerCase();
                const userName = row.children[1].textContent.toLowerCase();

                let isDateMatch = true;
                let isStatusMatch = true;
                let isSearchMatch = true;

                // Date Filter Logic
                if (dateValue) {
                    const now = new Date();
                    if (dateValue === "today") {
                        isDateMatch = createdDate.toDateString() === now.toDateString();
                    } else if (dateValue === "week") {
                        const weekAgo = new Date();
                        weekAgo.setDate(now.getDate() - 7);
                        isDateMatch = createdDate >= weekAgo;
                    } else if (dateValue === "month") {
                        const monthAgo = new Date();
                        monthAgo.setMonth(now.getMonth() - 1);
                        isDateMatch = createdDate >= monthAgo;
                    }
                }

                // Status Filter Logic
                if (statusValue && status !== statusValue) {
                    isStatusMatch = false;
                }

                // Search Filter Logic
                if (searchValue && !orderId.includes(searchValue) && !userName.includes(searchValue)) {
                    isSearchMatch = false;
                }

                // Show or hide row based on filters
                row.style.display = (isDateMatch && isStatusMatch && isSearchMatch) ? "" : "none";
            });
        }

        // Attach event listeners to filters
        dateFilter.addEventListener("change", filterTable);
        statusFilter.addEventListener("change", filterTable);
        searchFilter.addEventListener("input", filterTable);
    });
</script>

@endsection
