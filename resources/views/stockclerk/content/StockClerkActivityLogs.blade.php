@extends('stockclerk.dashboard.stockClerkDashboard')

@section('content')

<div class ="bg-white p-4 rounded-md" style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">
    <h1 class="text-4xl font-semibold mb-4">Activity Log</h1>

    <p class="border-b border-b-[1px] border-gray-300 mb-4">
        <!-- Your content here -->
    </p>


    <!-- Filters Section -->
    <div class="flex space-x-4 mb-2">
        <!-- Search Filter -->
        <div class="flex items-center">
            <label for="search" class="mr-2 text-sm">Search:</label>
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
        <!-- <div class="flex items-center">
            <label for="role" class="mr-2 text-sm">Role:</label>
            <select id="role" class="p-2 border rounded-lg text-sm">
                <option value="">All</option>
                <option value="staff">Staff</option>
                <option value="administrator">Administrator</option>
                <option value="manager">Manager</option>
                <option value="stock clerk">Stock Clerk</option>
            </select>
        </div> -->
    </div>

    <!-- Activity Log Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full table-auto border-collapse border border-gray-300">
            <thead class="bg-gray-100">
                <tr class="border-b">
                    <!-- <th class="px-4 py-2  text-sm font-bold">ID</th> -->
                    <th class="px-4 py-2  text-sm font-bold">User</th>
                    <th class="px-4 py-2  text-sm font-bold">Role</th>
                    <th class="px-4 py-2  text-sm font-bold">Activity</th>
                    <th class="px-4 py-2  text-sm font-bold">Created At</th>
                    <th class="px-4 py-2  text-sm font-bold">Updated At</th>
                </tr>
            </thead>
            <tbody id="activityLogsTable">
            @foreach($activityLogs as $log)
                @if($log->role === 'stock clerk')
                    <tr class="border-b log-row" data-id="{{ $log->id }}" data-role="{{ $log->role }}" data-activity="{{ $log->activity }}" data-created="{{ $log->created_at }}">
                        <!-- <td class="px-4 py-2 text-sm">{{ $log->id }}</td> -->
                        <td class="px-4 py-2 text-sm">
                            {{ $log->employee->name ?? 'N/A' }}
                        </td> 
                        <td class="px-4 py-2 text-sm">{{ $log->role }}</td>
                        <td class="px-4 py-2 text-sm">{{ $log->activity }}</td>
                        <td class="px-4 py-2 text-sm">{{ $log->created_at->format('F d, Y - h:i A') }}</td>
                        <td class="px-4 py-2 text-sm">{{ $log->updated_at->format('F d, Y - h:i A') }}</td>
                    </tr>
                @endif
            @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="mt-4">
        {{ $activityLogs->links() }}
    </div>

</div>

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
                const startDateValue = startDateInput.value ? new Date(startDateInput.value) : null;
                const endDateValue = endDateInput.value ? new Date(endDateInput.value) : null;
                const roleValue = roleSelect ? roleSelect.value.toLowerCase() : "";

                tableRows.forEach(row => {
                    const activity = row.getAttribute('data-activity').toLowerCase();
                    const role = row.getAttribute('data-role').toLowerCase();
                    const createdAt = new Date(row.getAttribute('data-created'));
                    const rowTextContent = row.textContent.toLowerCase();

                    // Check if row matches search input
                    const matchesSearch = rowTextContent.includes(searchValue);

                    // Check if row matches role (if role filtering exists)
                    const matchesRole = !roleValue || role.includes(roleValue);

                    // Check if row matches date range
                    const matchesDateRange =
                        (!startDateValue || createdAt >= startDateValue) &&
                        (!endDateValue || createdAt <= endDateValue);

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

@endsection
