@extends('manager.dashboard.managerDashboard')

@section('content')
<div class="container mx-auto p-6 bg-white rounded-xl shadow-md">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('manager.variantsView', ['model_id' => $model_id]) }}" class="bg-gray-500 text-white px-3 py-1 rounded-lg hover:bg-gray-600">
            ← Back
        </a>
    </div>

    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold">Edit Variant</h2>
    </div>

    <!-- Display Model ID -->
    <div class="mb-4">
        <h3 class="text-lg font-semibold">Model ID: {{ $model_id }}</h3>
    </div>

    <!-- Display Variant ID -->
    <div class="mb-4">
        <h3 class="text-lg font-semibold">Variant ID: {{ $variant_id }}</h3>
    </div>

    <!-- Edit Form -->
    <form id="editVariantForm" action="{{ route('update.variant', ['model_id' => $model_id, 'variant_id' => $variant_id]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block text-gray-700">Product Name</label>
            <input type="text" name="product_name" value="{{ $variant->product_name }}" class="w-full px-3 py-2 border rounded-lg">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700">Variant Image</label>
            <input type="file" name="variant_image" class="w-full px-3 py-2 border rounded-lg">
            <p class="text-sm text-gray-500">Current Image: {{ $variant->variant_image }}</p>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700">Part ID</label>
            <input type="text" name="part_id" value="{{ $variant->part_id }}" class="w-full px-3 py-2 border rounded-lg">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700">Price</label>
            <input type="number" name="price" value="{{ $variant->price }}" class="w-full px-3 py-2 border rounded-lg">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700">Specification</label>
            <input type="text" name="specification" value="{{ $variant->specification }}" class="w-full px-3 py-2 border rounded-lg">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700">Description</label>
            <textarea name="description" class="w-full px-3 py-2 border rounded-lg">{{ $variant->description }}</textarea>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700">Stock Quantity</label>
            <input type="number" name="stocks_quantity" value="{{ $variant->stocks_quantity }}" class="w-full px-3 py-2 border rounded-lg">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700">Status</label>
            <select name="status" class="w-full px-3 py-2 border rounded-lg">
                <option value="active" {{ $variant->status == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ $variant->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <div class="flex justify-between">
            <button type="button" onclick="confirmUpdate()" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">
                Save
            </button>
            <a href="{{ route('variantsView', ['model_id' => $model_id]) }}" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
    function confirmUpdate() {
        if (confirm('Are you sure you want to update this variant?')) {
            document.getElementById('editVariantForm').submit();
        }
    }

    // Show success or error alert
    window.onload = function() {
        @if (session('success'))
            alert("{{ session('success') }}");
        @endif

        @if (session('error'))
            alert("{{ session('error') }}");
        @endif
    };
</script>

@endsection
