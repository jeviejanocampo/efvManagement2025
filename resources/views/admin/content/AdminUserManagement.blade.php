@extends('admin.dashboard.adminDashboard')

@section('content')
<style>
    td{
        text-align: center;
    }
</style>
<div class="bg-white p-4 rounded-md" style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">
    <h1 class="text-4xl font-semibold mb-4">Users</h1>

    <!-- Filters and Search -->
    <div class="mb-4 flex flex-wrap gap-4">
        <!-- Search Bar -->
        <input type="text" id="searchInput" class="px-4 py-2 border rounded-md" placeholder="Search users...">

        <!-- Role Filter -->
        <select id="roleFilter" class="px-4 py-2 border rounded-md">
            <option value="">All Roles</option>
            <option value="admin">Admin</option>
            <option value="user">User</option>
        </select>

        <!-- Status Filter -->
        <select id="statusFilter" class="px-4 py-2 border rounded-md">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>

    <!-- User Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full table-auto border-collapse border border-gray-300">
            <thead class="bg-white">
                <tr class="border-b">
                    <th class="px-4 py-2 text-sm font-bold">ID</th>
                    <th class="px-4 py-2 text-sm font-bold">Name</th>
                    <th class="px-4 py-2 text-sm font-bold">Email</th>
                    <th class="px-4 py-2 text-sm font-bold">Role</th>
                    <th class="px-4 py-2 text-sm font-bold">Created At</th>
                    <th class="px-4 py-2 text-sm font-bold">Status</th>
                    <th class="px-4 py-2 text-sm font-bold">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($users as $user)
                <tr class="border-b">
                    <td class="px-4 py-2 text-sm">00{{ $user->id }}</td>
                    <td class="px-4 py-2 text-sm">{{ $user->name }}</td>
                    <td class="px-4 py-2 text-sm">{{ $user->email }}</td>
                    <td class="px-4 py-2 text-sm">{{ ucfirst($user->role) }}</td>
                    <td class="px-4 py-2 text-sm">{{ $user->created_at }}</td>
                    <td class="px-4 py-2 text-sm font-bold 
                        @if($user->status === 'active') bg-green-500 text-white 
                        @else bg-red-500 text-white 
                        @endif rounded px-2 py-1 text-center">
                        {{ ucfirst($user->status) }}
                    </td>
                    <td class="px-4 py-2 text-sm">
                        @if($user->status === 'active')
                            <button class="px-3 py-1 bg-blue-500 text-white rounded update-status-btn" 
                                data-id="{{ $user->id }}">Update Status</button>
                            <!-- <button class="px-3 py-1 bg-red-500 text-white rounded">Delete</button> -->
                        @else
                            <button class="px-3 py-1 bg-yellow-500 text-white rounded confirm-user-btn" 
                                data-id="{{ $user->id }}">Confirm User</button>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
        document.addEventListener('DOMContentLoaded', function() {
            let searchInput = document.getElementById('searchInput');
            let roleFilter = document.getElementById('roleFilter');
            let statusFilter = document.getElementById('statusFilter');

            function filterUsers() {
                let searchText = searchInput.value.toLowerCase();
                let selectedRole = roleFilter.value.toLowerCase();
                let selectedStatus = statusFilter.value.toLowerCase();

                document.querySelectorAll('tbody tr').forEach(row => {
                    let name = row.cells[1].innerText.toLowerCase();
                    let email = row.cells[2].innerText.toLowerCase();
                    let role = row.cells[3].innerText.toLowerCase();
                    let status = row.cells[5].innerText.toLowerCase();

                    let matchesSearch = name.includes(searchText) || email.includes(searchText);
                    let matchesRole = selectedRole === "" || role === selectedRole;
                    let matchesStatus = selectedStatus === "" || status === selectedStatus;

                    row.style.display = matchesSearch && matchesRole && matchesStatus ? "" : "none";
                });
            }

            searchInput.addEventListener('input', filterUsers);
            roleFilter.addEventListener('change', filterUsers);
            statusFilter.addEventListener('change', filterUsers);
        });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.confirm-user-btn').forEach(button => {
            button.addEventListener('click', function() {
                let userId = this.getAttribute('data-id');

                if (confirm('Are you sure you want to confirm this user?')) {
                    fetch(`/users/confirm/${userId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                        },
                        body: JSON.stringify({})
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('User confirmed successfully!');
                            location.reload(); // Reload the page to update UI
                        } else {
                            alert('Error confirming user.');
                        }
                    })
                    .catch(error => {
                        alert('Something went wrong.');
                    });
                }
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.update-status-btn').forEach(button => {
            button.addEventListener('click', function() {
                let userId = this.getAttribute('data-id');

                if (confirm('Are you sure you want to set this user to inactive?')) {
                    fetch(`/users/update-status/${userId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                        },
                        body: JSON.stringify({ status: 'inactive' })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('User status updated to inactive!');
                            location.reload();
                        } else {
                            alert('Error updating user status.');
                        }
                    })
                    .catch(error => {
                        alert('Something went wrong.');
                    });
                }
            });
        });
    });
</script>

@endsection

@section('scripts')

@endsection
