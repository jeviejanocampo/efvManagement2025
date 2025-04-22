@extends('admin.dashboard.adminDashboard')

@section('content')

@php
    $editMode = request()->get('edit') === 'true';
@endphp

<div class="bg-white p-4 rounded-md" style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">

    <div class="flex justify-between items-center mb-4">
        <a href="{{ route('admin.users') }}" class="inline-flex items-center text-black hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i> 
        </a>

        @if(!$editMode)
            <a href="{{ route('admin.users.edit', ['id' => $user->id, 'edit' => 'true']) }}"
               class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">
                Edit
            </a>
        @endif
    </div>

    <h1 class="text-3xl font-semibold mb-4 border-b border-gray pb-2">Edit User</h1>

    <div class="bg-white p-4 rounded shadow">
        @if($editMode)
            <form id="editUserForm" method="POST" action="{{ route('admin.users.update', $user->id) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="font-semibold">Name:</label>
                    <input type="text" name="name" class="w-full border p-2 rounded" value="{{ $user->name }}" required>
                </div>

                <div class="mb-3">
                    <label class="font-semibold">Email:</label>
                    <input type="email" name="email" class="w-full border p-2 rounded" value="{{ $user->email }}" required>
                </div>

                <div class="mb-3">
                    <label class="font-semibold">Status:</label>
                    <input type="text" name="status" class="w-full border p-2 rounded" value="{{ $user->status }}" required>
                </div>

                <div class="mb-3">
                    <label class="font-semibold">Role:</label>
                    <select name="role" class="w-full border p-2 rounded" required>
                        <option value="staff" {{ $user->role == 'staff' ? 'selected' : '' }}>Staff</option>
                        <option value="manager" {{ $user->role == 'manager' ? 'selected' : '' }}>Manager</option>
                        <option value="stock-clerk" {{ $user->role == 'stock-clerk' ? 'selected' : '' }}>Stock Clerk</option>
                        <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>


                <div class="mb-3 relative">
                    <label class="font-semibold">New Password:</label>
                    <input type="password" id="password" name="password" class="w-full border p-2 rounded pr-10" placeholder="Leave empty to keep current">
                    <i class="fas fa-eye absolute top-1/2 right-2 transform -translate-y-1/2 cursor-pointer" id="togglePassword" onclick="togglePasswordVisibility()"></i>
                </div>


                <div class="flex justify-end space-x-2">
                    <a href="{{ route('admin.users.edit', $user->id) }}" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Cancel</a>
                    <button type="submit" onclick="return confirmEdit()" class="px-4 py-2 bg-black text-white rounded hover:bg-gray-800">Save</button>
                </div>
            </form>
        @else
            <p><strong>Name:</strong> {{ $user->name }}</p>
            <p><strong>Email:</strong> {{ $user->email }}</p>
            <p><strong>Status:</strong> {{ $user->status }}</p>
            <p><strong>Role:</strong> {{ $user->role }}</p>
        @endif
    </div>
</div>

<script>
    function confirmEdit() {
        return confirm('Are you sure you want to update this user?');
    }

    @if(session('success'))
        alert("{{ session('success') }}");
    @elseif(session('error'))
        alert("{{ session('error') }}");
    @endif
</script>

@endsection
