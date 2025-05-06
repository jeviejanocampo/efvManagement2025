@extends('manager.dashboard.managerDashboard')

@section('content')
<style>
  td {
    text-align: center;
  }
</style>

<div class="bg-white p-4" style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">
    <h1 class="text-4xl font-semibold mb-2">Refund Activity Log</h1>
    <p class="border-b border-b-[1px] border-gray-300 mb-4"></p>

    <!-- Filter Section -->
    <div class="flex gap-2 mb-2">
        <!-- Left: Search Bar -->
        <div class="flex items-center">
            <label for="search" class="mr-2 text-lg">Search User:</label>
            <input type="text" id="search" class="p-1 border rounded" placeholder="Search by name..." onkeyup="filterTable()">
        </div>
        
        <!-- Right: Role and Date Filters -->
        <div class="flex items-center space-x-4">
            <!-- Role Filter -->
            <div>
                <label for="role" class="mr-2 text-lg">Role:</label>
                <select id="role" class="p-1 border rounded" onchange="filterTable()">
                    <option value="">All Roles</option>
                    <option value="staff">Staff</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <!-- Date Filter -->
            <div>
                <label for="date" class="mr-2 text-lg">Date:</label>
                <input type="date" id="date" class="p-1 border rounded" onchange="filterTable()">
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full table-auto">
            <thead class="bg-gray-100">
                <tr class="text-center border-b">
                    <th class="px-4 py-2  text-sm">User</th>
                    <th class="px-4 py-2  text-sm">Role</th>
                    <th class="px-4 py-2  text-sm">Activity</th>
                    <th class="px-4 py-2  text-sm">Date</th>
                </tr>
            </thead>
            <tbody id="logs-table">
                @forelse ($logs as $log)
                    <tr class="border-b log-row">
                        <td class="px-4 py-2 text-sm">{{ $log->user->name ?? 'N/A' }}</td>
                        <td class="px-4 py-2  text-sm">{{ ucfirst($log->role) }}</td>
                        <td class="px-4 py-2  text-sm">{{ $log->activity }}</td>
                        <td class="px-4 py-2  text-sm">{{ \Carbon\Carbon::parse($log->refunded_at)->format('M d, Y h:i A') }}</td>
                    </tr>
                @empty
                    <tr class="border-b">
                        <td colspan="4" class="px-4 py-6 text-gray-500 text-center">No logs found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</div>

<script>
    function filterTable() {
        let searchValue = document.getElementById('search').value.toLowerCase();
        let roleValue = document.getElementById('role').value.toLowerCase();
        let dateValue = document.getElementById('date').value;

        // Get all rows of the table
        let rows = document.querySelectorAll('#logs-table .log-row');

        rows.forEach(row => {
            let userName = row.cells[0].textContent.toLowerCase();
            let role = row.cells[2].textContent.toLowerCase();
            let date = row.cells[3].textContent.toLowerCase();

            let showRow = true;

            // Check if the search text matches the user name
            if (searchValue && !userName.includes(searchValue)) {
                showRow = false;
            }

            // Check if the selected role matches
            if (roleValue && role !== roleValue) {
                showRow = false;
            }

            // Check if the selected date matches
            if (dateValue && !date.includes(dateValue)) {
                showRow = false;
            }

            // Show or hide the row based on conditions
            row.style.display = showRow ? '' : 'none';
        });
    }
</script>

@endsection
