@extends('staff.dashboard.StaffMain')

@section('content')
<style>
  td {
    text-align: center;
  }
</style>

<div class ="bg-white p-4 rounded-md" style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">
    <h1 class="text-4xl font-semibold">Activity Logs</h1>
        <p class="border-b border-b-[1px] border-gray-300 mt-2 mb-2">
            <!-- Your content here -->
        </p>

    <p style="margin-bottom: 12px; font-style: italic;color: gray">
        Note: All the activities that have been operated by the staff are logged here.
    </p>
    <!-- Filters Section -->
    <div class="flex space-x-4 mb-4">
        <!-- Search Filter -->
        <div class="flex items-center">
            <!-- <label for="search" class="mr-2 text-sm">Search:</label> -->
            <input id="search" type="text" placeholder="Search activities" class="p-2 border rounded-lg text-sm">
        </div>

        <!-- Date Filter -->
        <div class="flex items-center">
            <label for="start_date" class="mr-2 text-sm">Start Date:</label>
            <input id="start_date" type="date" class="p-2 border rounded-lg text-sm">
        </div>

        <div class="flex items-center">
            <label for="end_date" class="mr-2 text-sm">End Date:</label>
            <input id="end_date" type="date" class="p-2 border rounded-lg text-sm">
        </div>

        <!-- Role Filter -->
        <div class="flex items-center">
            <label for="role" class="mr-2 text-sm">Role:</label>
            <select id="role" class="p-2 border rounded-lg text-sm">
                <option value="">All</option>
                <option value="staff">Staff</option>
                <option value="administrator">Administrator</option>
                <option value="manager">Manager</option>
                <option value="stock clerk">Stock Clerk</option>
            </select>
        </div>
    </div>

    <!-- Activity Log Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full table-auto border-collapse border border-gray-300">
        <thead class="bg-gray-100">
            <tr class="border-b">
                <!-- <th id="column-0" class="px-4 py-2 text-sm font-bold cursor-pointer" onclick="sortTable(0, 'column-0')">
                    ID <span class="sort-icon" id="icon-0"></span>
                </th> -->
                <th id="column-1" class="px-4 py-2 text-sm font-bold cursor-pointer" onclick="sortTable(1, 'column-1')">
                    User <span class="sort-icon" id="icon-1"></span>
                </th>
                <th id="column-2" class="px-4 py-2 text-sm font-bold cursor-pointer" onclick="sortTable(2, 'column-2')">
                    Role <span class="sort-icon" id="icon-2"></span>
                </th>
                <th id="column-3" class="px-4 py-2 text-sm font-bold cursor-pointer" onclick="sortTable(3, 'column-3')">
                    Activity <span class="sort-icon" id="icon-3"></span>
                </th>
                <th id="column-4" class="px-4 py-2 text-sm font-bold cursor-pointer" onclick="sortTable(4, 'column-4')">
                    Created At <span class="sort-icon" id="icon-4"></span>
                </th>
            </tr>
        </thead>
            <tbody id="activityLogsTable">
                <!-- @foreach($activityLogs as $log)
                    <tr class="border-b log-row" data-id="{{ $log->id }}" data-role="{{ $log->role }}" data-activity="{{ $log->activity }}" data-created="{{ $log->created_at }}">
                        <td class="px-4 py-2 text-sm">{{ $log->id }}</td>
                        <td class="px-4 py-2 text-sm">{{ $log->user_id }}</td>
                        <td class="px-4 py-2 text-sm">{{ $log->role }}</td>
                        <td class="px-4 py-2 text-sm">{{ $log->activity }}</td>
                        <td class="px-4 py-2 text-sm">{{ $log->created_at }}</td>
                    </tr>
                @endforeach -->
                @foreach($activityLogs as $log)
                @if($log->role === 'staff')
                    <tr class="border-b log-row" data-id="{{ $log->id }}" data-role="{{ $log->role }}" data-activity="{{ $log->activity }}" data-created="{{ $log->created_at }}">
                        <!-- <td class="px-4 py-2 text-sm">{{ $log->id }}</td> -->
                        <td class="px-4 py-2 text-sm">
                            {{ $log->customer->full_name ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-2 text-sm">{{ $log->role }}</td>
                        <td class="px-4 py-2 text-sm">{{ $log->activity }}</td>
                        <td class="px-4 py-2 text-sm">{{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
        <div class="mt-4">
            {{ $activityLogs->links() }}
        </div>
    </div>
</div>

<script>
    function sortTable(columnIndex, columnId) {
        const table = document.querySelector("table");
        const tbody = table.querySelector("tbody");
        const rows = Array.from(tbody.querySelectorAll("tr"));

        // Determine sorting order
        const isAscending = table.dataset.sortOrder === "asc";
        table.dataset.sortOrder = isAscending ? "desc" : "asc";

        rows.sort((rowA, rowB) => {
            const cellA = rowA.children[columnIndex].textContent.trim().toLowerCase();
            const cellB = rowB.children[columnIndex].textContent.trim().toLowerCase();

            if (!isNaN(cellA) && !isNaN(cellB)) {
                return isAscending ? cellA - cellB : cellB - cellA;
            }
            return isAscending ? cellA.localeCompare(cellB) : cellB.localeCompare(cellA);
        });

        tbody.innerHTML = "";
        rows.forEach(row => tbody.appendChild(row));

        // Reset all header colors and icons
        document.querySelectorAll("th").forEach(th => th.style.color = "");
        document.querySelectorAll(".sort-icon").forEach(icon => icon.textContent = "");

        // Set active column color to black and update sorting icon
        document.getElementById(columnId).style.color = "black";
        document.getElementById(`icon-${columnIndex}`).textContent = isAscending ? " ▲" : " ▼";
    }
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get filter inputs
        const searchInput = document.getElementById('search');
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const roleSelect = document.getElementById('role');
        
        const tableRows = document.querySelectorAll('.log-row'); // All rows in the table

        // Function to filter rows based on inputs
        function filterTable() {
            const searchValue = searchInput.value.toLowerCase();
            const startDateValue = startDateInput.value;
            const endDateValue = endDateInput.value;
            const roleValue = roleSelect.value.toLowerCase();

            tableRows.forEach(row => {
                const activity = row.getAttribute('data-activity').toLowerCase();
                const role = row.getAttribute('data-role').toLowerCase();
                const createdAt = row.getAttribute('data-created');
                const rowTextContent = row.textContent.toLowerCase();

                // Check if row matches the filter criteria
                const matchesSearch = rowTextContent.includes(searchValue);
                const matchesRole = role.includes(roleValue);
                const matchesDateRange = (!startDateValue || createdAt >= startDateValue) && (!endDateValue || createdAt <= endDateValue);

                // Show or hide row based on filter conditions
                if (matchesSearch && matchesRole && matchesDateRange) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Add event listeners to filter inputs
        searchInput.addEventListener('input', filterTable);
        startDateInput.addEventListener('change', filterTable);
        endDateInput.addEventListener('change', filterTable);
        roleSelect.addEventListener('change', filterTable);
    });
</script>
@endsection

@section('scripts')