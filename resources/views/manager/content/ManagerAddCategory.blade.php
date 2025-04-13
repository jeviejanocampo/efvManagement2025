@extends('manager.dashboard.managerDashboard')

@section('content')
<div class="container mx-auto p-6 bg-white " style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">
    <div class="mb-4">
        <button onclick="window.history.back()" class="bg-gray-500 text-white px-3 py-1 rounded-lg hover:bg-gray-600">
            ← Back
        </button>
    </div>

    <h2 class="text-2xl font-bold mb-4">Add Category</h2>

    <form id="categoryForm" action="{{ route('manager.store.category') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2" for="category_name">Category Name</label>
            <input type="text" name="category_name" id="category_name" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2" for="cat_image">Category Image</label>
            <input type="file" name="cat_image" id="cat_image" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2" for="status">Status</label>
            <select name="status" id="status" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Select Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">Submit</button>
    </form>
</div>

<script>
    document.getElementById('categoryForm').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent default form submission

        let categoryName = document.getElementById('category_name').value.trim();
        let categoryImage = document.getElementById('cat_image').value;
        let status = document.getElementById('status').value;

        if (!categoryName || !categoryImage || !status) {
            alert("Please fill in all fields.");
            return;
        }

        this.submit(); // Submit form if validation passes
    });

    @if(session('success'))
        alert("{{ session('success') }}");
    @endif

    @if(session('error'))
        alert("{{ session('error') }}");
    @endif
</script>
@endsection
