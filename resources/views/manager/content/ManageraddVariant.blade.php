@extends('manager.dashboard.managerDashboard')

@section('content')
<div class="container mx-auto p-6 bg-white rounded-xl shadow-md">
    <!-- Back Button -->
    <div class="mb-4">
        <button onclick="window.history.back()" class="bg-gray-500 text-white px-3 py-1 rounded-lg hover:bg-gray-600">
            ← Back
        </button>
    </div>

    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold">Add Variant</h2>
    </div>

    <form id="variantForm" action="{{ route('manager.store.variant', ['model_id' => $model_id]) }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Model ID (Read-only) -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Model ID:</label>
            <input type="text" name="model_id" value="{{ $model_id }}" readonly class="w-full px-3 py-2 border rounded-lg bg-gray-100 cursor-not-allowed">
        </div>

        <!-- Product Name -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Product Name:</label>
            <input type="text" name="product_name" id="product_name" class="w-full px-3 py-2 border rounded-lg">
        </div>

        <!-- Variant Image with Preview -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Variant Image:</label>
            <input type="file" name="variant_image" id="variant_image" class="w-full px-3 py-2 border rounded-lg">
            <div class="mt-2">
                <img id="imagePreview" src="#" alt="Image Preview" class="hidden w-32 h-32 object-cover border rounded-lg">
            </div>
        </div>

        <!-- JavaScript for Image Preview -->
        <script>
            document.getElementById('variant_image').addEventListener('change', function(event) {
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


        <!-- Part ID -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Part ID:</label>
            <input type="text" name="part_id" id="part_id" class="w-full px-3 py-2 border rounded-lg">
        </div>

        <!-- Price -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Price:</label>
            <input type="number" name="price" id="price" step="0.01" class="w-full px-3 py-2 border rounded-lg">
        </div>

        <!-- Specification -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Specification:</label>
            <textarea name="specification" id="specification" class="w-full px-3 py-2 border rounded-lg"></textarea>
        </div>

        <!-- Description -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Description:</label>
            <textarea name="description" id="description" class="w-full px-3 py-2 border rounded-lg"></textarea>
        </div>

        <!-- Stock Quantity -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Stock Quantity:</label>
            <input type="number" name="stocks_quantity" id="stocks_quantity" class="w-full px-3 py-2 border rounded-lg">
        </div>

        <!-- Status -->
        <div class="mb-4">
            <label class="block text-gray-700 font-semibold">Status:</label>
            <select name="status" id="status" class="w-full px-3 py-2 border rounded-lg">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>

        <!-- Save Button -->
        <button type="submit" class="bg-violet-700 text-white px-4 py-2 rounded-lg hover:bg-violet-800">
            Save Variant
        </button>
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

<script>
    document.getElementById('variantForm').addEventListener('submit', function(event) {
        let productName = document.getElementById('product_name').value.trim();
        let variantImage = document.getElementById('variant_image').value.trim();
        let partId = document.getElementById('part_id').value.trim();
        let price = document.getElementById('price').value.trim();
        let stockQuantity = document.getElementById('stocks_quantity').value.trim();

        if (!productName || !variantImage || !partId || !price || !stockQuantity) {
            alert("Please fill out all required fields.");
            event.preventDefault();
            return;
        }

        if (!confirm("Are you sure you want to save this variant?")) {
            event.preventDefault();
        }
    });
</script>

@endsection
