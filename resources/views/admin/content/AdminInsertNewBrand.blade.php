@extends('admin.dashboard.adminDashboard')

@section('content')
<div class="container mx-auto max-w-full p-6 bg-white " style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">

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

    <form action="{{ route('admin.add.brand.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <!-- Category Dropdown -->
    <div class="mb-4">
        <label for="category_id" class="block text-gray-700 font-semibold mb-2">Category</label>
        <select name="category_id" id="category_id" class="w-full border border-gray-300 rounded-lg p-2" required>
            <option value="">-- Select Category --</option>
            @foreach($categories as $category)
                <option value="{{ $category->category_id }}" {{ old('category_id') == $category->category_id ? 'selected' : '' }}>
                    {{ $category->category_name }}
                </option>
            @endforeach
        </select>
        @error('category_id')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <!-- Brand Name -->
    <div class="mb-4">
        <label for="brand_name" class="block text-gray-700 font-semibold mb-2">Brand Name</label>
        <input type="text" name="brand_name" id="brand_name" value="{{ old('brand_name') }}" class="w-full border border-gray-300 rounded-lg p-2" required>
        @error('brand_name')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <!-- Brand Image -->
    <div class="mb-4">
        <label for="brand_image" class="block text-gray-700 font-semibold mb-2">Brand Image</label>
        <input type="file" name="brand_image" id="brand_image" accept="image/*" class="w-full border border-gray-300 rounded-lg p-2">
        @error('brand_image')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <!-- Status -->
    <div class="mb-4">
        <label for="status" class="block text-gray-700 font-semibold mb-2">Status</label>
        <select name="status" id="status" class="w-full border border-gray-300 rounded-lg p-2" required>
            <option value="">-- Select Status --</option>
            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
        @error('status')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <!-- Save Button -->
    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
        Save Brand
    </button>
</form>


</div>
@endsection
