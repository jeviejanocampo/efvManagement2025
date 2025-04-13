@extends('manager.dashboard.managerDashboard')

@section('content')
<div class="container mx-auto p-6 bg-white " style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">
    
    <div class="mb-4">
        <button onclick="window.history.back()" class="bg-gray-500 text-white px-3 py-1 rounded-lg hover:bg-gray-600">
            ← Back
        </button>
    </div>

    <h2 class="text-3xl font-bold mb-4">Edit Category</h2>

    <form id="editCategoryForm" action="{{ route('manager.update.category', $category->category_id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Category Name:</label>
            <input type="text" name="category_name" value="{{ $category->category_name }}" required 
                   class="w-full border border-gray-300 rounded-lg p-2">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Category Image:</label>
            <input type="file" name="cat_image" class="w-full border border-gray-300 rounded-lg p-2">
            <img src="{{ asset('product-images/' . $category->cat_image) }}" alt="Category Image" class="h-16 w-16 mt-2 rounded">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Status:</label>
            <select name="status" class="w-full border border-gray-300 rounded-lg p-2">
                <option value="active" {{ $category->status == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ $category->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-800">
            Save Changes
        </button>
    </form>
</div>

<script>
    document.getElementById('editCategoryForm').addEventListener('submit', function (event) {
        event.preventDefault();
        
        if (confirm('Are you sure you want to update this category?')) {
            this.submit();
        }
    });

    @if(session('success'))
        alert("{{ session('success') }}");
    @elseif(session('error'))
        alert("{{ session('error') }}");
    @endif
</script>

@endsection
