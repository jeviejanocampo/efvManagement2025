@extends('stockclerk.dashboard.stockClerkDashboard')

@section('content')
<div class="container mx-auto p-6 bg-white rounded-xl" style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">
    <div class="mb-4">
        <button onclick="window.history.back()" class="bg-gray-500 text-white px-3 py-1 rounded-lg hover:bg-gray-600">
            ← Back
        </button>
    </div>

    <div class="flex items-center justify-between mb-4">
        <h2 class="text-3xl font-bold">View Categories</h2>
        <a href="{{ route('stockclerk.add.category') }}" 
        class="bg-violet-800 text-white px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-violet-900">
            <i class="fas fa-plus"></i> Add Category
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-300">
            <thead>
                <tr>
                    <th class="border border-gray-300 px-1 py-1">ID</th>
                    <th class="border border-gray-300 px-1 py-1">Category Name</th>
                    <th class="border border-gray-300 px-1 py-1">Image</th>
                    <th class="border border-gray-300 px-1 py-1">Status</th>
                    <th class="px-2 py-1 border">Action</th> 
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $index => $category)
                <tr class="text-center">
                    <td class="border border-gray-300 px-1 py-1">{{ $category->category_id }}</td>
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
                    <td class="px-2 py-1 border">
                        <div class="flex justify-center space-x-2">
                            <!-- Edit Button -->
                            <a href="{{ route('stockclerk.edit.category', ['category_id' => $category->category_id]) }}" 
                                class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-edit"></i>
                                </a>
                            <!-- Delete Button -->
                            <form action="{{ route('stockclerk.delete.category', $category->category_id) }}" method="POST" class="inline delete-category-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 delete-category-btn">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const deleteForms = document.querySelectorAll('.delete-category-form');
        
        deleteForms.forEach(form => {
            form.addEventListener('submit', function (event) {
                event.preventDefault();

                if (confirm('Are you sure you want to delete this category? This action cannot be undone.')) {
                    this.submit();
                }
            });
        });

        @if(session('success'))
            alert("{{ session('success') }}");
        @elseif(session('error'))
            alert("{{ session('error') }}");
        @endif
    });
</script>
@endsection
