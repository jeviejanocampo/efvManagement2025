@extends('manager.dashboard.managerDashboard')

@section('content')
<div class="container mx-auto p-6 bg-white rounded-xl shadow-md">
    <div class="mb-4">
        <button onclick="window.history.back()" class="bg-gray-500 text-white px-3 py-1 rounded-lg hover:bg-gray-600">
            ← Back
        </button>
    </div>

    <h2 class="text-2xl font-bold mb-4">Add Details Based on the Primary roduct</h2>

    <form action="{{ route('manager.add.details.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Model ID (Read-only) -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Model ID</label>
            <input type="text" name="model_id" value="{{ $model_id }}" readonly 
                class="w-full px-3 py-2 border bg-gray-100 rounded-lg focus:ring focus:ring-blue-300">
        </div>

        <!-- Model Name (Read-only, fetched from models table) -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Model Name</label>
            <input type="text" name="model_name" value="{{ $model_name }}" readonly 
                class="w-full px-3 py-2 border bg-gray-100 rounded-lg focus:ring focus:ring-blue-300">
        </div>

        <!-- Upload Image -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Upload Image</label>
            <input type="file" name="model_img" id="imageUpload" class="w-full px-3 py-2 border rounded-lg focus:ring focus:ring-blue-300" required>
            <div class="mt-2">
                <img id="imagePreview" src="#" alt="Image Preview" class="hidden w-32 h-32 object-cover border rounded-lg">
            </div>
        </div>

        <!-- JavaScript for Image Preview -->
        <script>
            document.getElementById('imageUpload').addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = document.getElementById('imagePreview');
                        preview.src = e.target.result;
                        preview.classList.remove('hidden'); // Show preview
                    };
                    reader.readAsDataURL(file);
                }
            });
        </script>


        <!-- SRP -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">SRP</label>
            <input type="number" name="price" value="{{ $price }}" readonly 
            class="w-full px-3 py-2 border bg-gray-100 rounded-lg focus:ring focus:ring-blue-300">        </div>

        <!-- Select Brand -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Select Brand</label>
            <select name="brand_id" class="w-full px-3 py-2 border rounded-lg focus:ring focus:ring-blue-300" required>
                <option value="">Choose a brand</option>
                @foreach ($brands as $brand)
                    <option value="{{ $brand->brand_id }}">{{ $brand->brand_name }}</option>
                @endforeach
            </select>
        </div>


        <!-- Description -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" class="w-full px-3 py-2 border rounded-lg focus:ring focus:ring-blue-300" required></textarea>
        </div>

        <!-- Part ID -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">M Part ID</label>
            <input type="text" name="m_part_id" class="w-full px-3 py-2 border rounded-lg focus:ring focus:ring-blue-300" required>
        </div>

        <!-- Stocks Quantity -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Stocks Quantity</label>
            <input type="number" name="stocks_quantity" class="w-full px-3 py-2 border rounded-lg focus:ring focus:ring-blue-300" required>
        </div>

        <!-- Status -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Status</label>
            <select name="status" class="w-full px-3 py-2 border rounded-lg focus:ring focus:ring-blue-300" required>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="on_order">On Order</option>
            </select>
        </div>

        <!-- Submit Button -->
        <div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                Save Product
            </button>
        </div>
    </form>
</div>
@if ($errors->any())
    <script>
        let errors = "";
        @foreach ($errors->all() as $error)
            errors += "{{ $error }}\n";
        @endforeach
        alert(errors);
    </script>
@endif

@if (session('success'))
    <script>
        alert("{{ session('success') }}");
    </script>
@endif

@endsection
