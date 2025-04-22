@extends('admin.dashboard.adminDashboard')

@section('content')
<div class="bg-white p-4 rounded-md" style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">

    <div class="flex justify-between items-center mb-4">
        <h1 class="text-4xl font-semibold mb-4 border-b border-gray pb-2">Users</h1>

        <a href="{{ route('admin.users.create') }}" class="flex items-center bg-black text-white px-4 py-2 rounded hover:bg-gray-800" title="Add User">
            <i class="fas fa-plus mr-2"></i> Add User
        </a>

    </div>

    <table class="min-w-full border-b border-gray-300">
        <thead>
            <tr class="bg-gray-100 text-left text-sm font-medium text-gray-700">
                <th class="px-4 py-2">Name</th>
                <th class="px-4 py-2">Email</th>
                <th class="px-4 py-2">Role</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr class="text-sm text-gray-800">
                    <td class="px-4 py-2">
                    <i class="fas fa-user mr-2 text-gray-600"></i>{{ $user->name }}
                </td>
                <td class="px-4 py-2">{{ $user->email }}</td>
                <td class="px-4 py-2">
                    <span class="px-2 py-1 rounded text-white text-xs font-semibold
                        @if($user->role === 'admin') bg-purple-600
                        @elseif($user->role === 'staff') bg-blue-500
                        @elseif($user->role === 'stock-clerk') bg-yellow-500
                        @elseif($user->role === 'manager') bg-green-600
                        @else bg-gray-500 @endif">
                        {{ ucfirst($user->role) }}
                    </span>
                </td>
                <td class="px-4 py-2">
                    <span class="px-2 py-1 rounded text-white text-xs font-semibold
                        {{ $user->status === 'active' ? 'bg-green-500' : 'bg-red-500' }}">
                        {{ ucfirst($user->status) }}
                    </span>
                </td>
                <td class="px-4 py-2">
                    <a href="{{ route('admin.users.edit', $user->id) }}" class="text-blue-500 hover:text-blue-700" title="Edit User">
                        <i class="fas fa-edit"></i>
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>
@endsection
