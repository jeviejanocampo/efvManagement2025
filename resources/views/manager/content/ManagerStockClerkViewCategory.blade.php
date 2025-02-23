@extends('manager.dashboard.managerDashboard')

@section('content')
<div class="container mx-auto p-6 bg-white rounded-xl shadow-md">
    <div class="mb-4">
        <button onclick="window.history.back()" class="bg-gray-500 text-white px-3 py-1 rounded-lg hover:bg-gray-600">
            ← Back
        </button>
    </div>

    <h2 class="text-2xl font-bold mb-4">View Categories</h2>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-300">
            <thead>
                <tr>
                    <!-- <th class="border border-gray-300 px-1 py-1"></th> -->
                    <th class="border border-gray-300 px-1 py-1">Category Name</th>
                    <th class="border border-gray-300 px-1 py-1">Image</th>
                    <th class="border border-gray-300 px-1 py-1">Status</th>
                    <!-- <th class="border border-gray-300 px-1 py-1"></th> -->
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $index => $category)
                <tr class="text-center">
                    <!-- <td class="border border-gray-300 px-1 py-1">{{ $index + 1 }}</td> -->
                    <td class="border border-gray-300 px-1 py-1">{{ $category->category_name }}</td>
                    <td class="border border-gray-300 px-1 py-1 flex justify-center items-center">
                        <img src="{{ asset('product-images/' . $category->cat_image) }}" alt="Category Image" class="h-16 w-16 rounded">
                    </td>
                    <td class="border border-gray-300 px-1 py-1">
                        <span class="px-3 py-1 rounded text-white 
                            {{ $category->status == 'active' ? 'bg-green-500' : 'bg-red-500' }}">
                            {{ ucfirst($category->status) }}
                        </span>
                    </td>
                    <!-- <td class="border border-gray-300 px-1 py-1">
                        <button class="bg-blue-500 text-white px-3 py-1 rounded-lg hover:bg-blue-600">
                            Edit
                        </button>
                        <button class="bg-red-500 text-white px-3 py-1 rounded-lg hover:bg-red-600">
                            Delete
                        </button>
                    </td> -->
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
