@extends('admin.dashboard.adminDashboard')

@section('content')
<div class="bg-white p-4 rounded-md" style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">

    <a href="{{ route('admin.users') }}" class="inline-flex items-center text-black hover:text-blue-800 pb-4">
            <i class="fas fa-arrow-left mr-2"></i> 
        </a>

    <h1 class="text-4xl font-semibold mb-4 border-b border-gray pb-2">Add User</h1>

    <form id="addUserForm" action="{{ route('admin.users.store.user') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="font-semibold">Name:</label>
            <input type="text" name="name" class="w-full border p-2 rounded" required>
            <small class="text-gray-500">Please enter the full name (first and last).</small>
        </div>

        <div class="mb-3">
            <label class="font-semibold">Email:</label>
            <input type="email" name="email" class="w-full border p-2 rounded" required>
        </div>

        <div class="mb-3">
            <label class="font-semibold">Role:</label>
            <select name="role" class="w-full border p-2 rounded" required>
                <option value="admin">Admin</option>
                <option value="staff">Staff</option>
                <option value="manager">Manager</option>
                <option value="stock-clerk">Stock Clerk</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="font-semibold">Status:</label>
            <select name="status" class="w-full border p-2 rounded" required>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>

        <div class="mb-3 relative">
            <label class="font-semibold">Password:</label>
            <input type="password" name="password" id="password" class="w-full border p-2 rounded pr-10" required>
            <i class="fas fa-eye absolute right-2 bottom-2 transform -translate-y-1/2 cursor-pointer" id="togglePassword"></i>
            <small class="text-gray-500">Password must be at least 8 characters long and contain a mix of letters, numbers, and special characters.</small>
        </div>

        <script>
            // Toggle the visibility of the password
            document.getElementById('togglePassword').addEventListener('click', function (e) {
                // Get the password input element
                var passwordField = document.getElementById('password');
                
                // Toggle the type of the input field
                if (passwordField.type === "password") {
                    passwordField.type = "text";
                } else {
                    passwordField.type = "password";
                }
            });
        </script>

        <div class="mt-4 flex justify-end">
            <button type="submit" onclick="return confirmSubmission()" class="px-4 py-2 bg-black text-white rounded hover:bg-gray-800">Save User</button>
        </div>
    </form>
</div>

<script>
    // Function to confirm form submission
    function confirmSubmission() {
        var userConfirmed = confirm('Are you sure you want to add this user?');
        if (!userConfirmed) {
            return false; // Prevent form submission if not confirmed
        }

        // Optionally, you can add more validation here if necessary

        // If confirmed, show success alert
        alert('User added successfully!');
        return true; // Proceed with form submission
    }

    // Display success or error alerts based on session data
    @if(session('success'))
        alert("{{ session('success') }}");
    @elseif(session('error'))
        alert("{{ session('error') }}");
    @endif
</script>

@endsection
