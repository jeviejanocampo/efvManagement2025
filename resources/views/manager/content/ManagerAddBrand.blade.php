@extends('stockclerk.dashboard.stockClerkDashboard')

@section('content')
<div class="container mx-auto p-6 bg-white " style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">
    <div class="mb-4">
        <button onclick="window.history.back()" class="bg-gray-500 text-white px-3 py-1 rounded-lg hover:bg-gray-600">
            ← Back
        </button>
    </div>

    <h2 class="text-2xl font-bold mb-4">Add Brand</h2>

    <!-- Success Alert -->
    @if(session('success'))
        <div id="success-alert" class="mb-4 p-4 bg-green-100 text-green-700 border border-green-400 rounded-lg">
            {{ session('success') }}
        </div>
        <script>
            setTimeout(() => document.getElementById('success-alert').style.display = 'none', 3000);
        </script>
    @endif

    <form action="{{ route('manager.add.brand.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Category Selection -->
        <div class="mb-4">
            <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
            <select name="category_id" id="category_id" class="w-full border border-gray-300 rounded-lg p-2">
                <option value="">Select Category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->category_id }}">{{ $category->category_name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Brand Name -->
        <div class="mb-4">
            <label for="brand_name" class="block text-sm font-medium text-gray-700">Brand Name</label>
            <input type="text" name="brand_name" id="brand_name" class="w-full border border-gray-300 rounded-lg p-2" required>
        </div>

        <!-- Brand Image -->
        <div class="mb-4">
            <label for="brand_image" class="block text-sm font-medium text-gray-700">Brand Image</label>
            <input type="file" name="brand_image" id="brand_image" class="w-full border border-gray-300 rounded-lg p-2">
        </div>

        <!-- Status -->
        <div class="mb-4">
            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
            <select name="status" id="status" class="w-full border border-gray-300 rounded-lg p-2">
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
            </select>
        </div>

        <!-- Save Button -->
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
            Save Brand
        </button>
    </form>
</div>
@endsection
