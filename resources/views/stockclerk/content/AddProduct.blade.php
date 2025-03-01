@extends('stockclerk.dashboard.stockClerkDashboard')

@section('content')
<div class="container mx-auto p-6 bg-white rounded-xl ">
    <div class="mb-4">
        <button onclick="window.history.back()" class="bg-gray-500 text-white px-3 py-1 rounded-lg hover:bg-gray-600">
            ← Back
        </button>
    </div>

    <h2 class="text-2xl font-bold mb-4">Add Product</h2>

    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <!-- Model Name -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Model Name</label>
        <input type="text" name="model_name" class="w-full px-3 py-2 border rounded-lg focus:ring focus:ring-blue-300" required>
    </div>

    <!-- Upload Image -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Upload Image</label>
        <input type="file" name="model_img" class="w-full px-3 py-2 border rounded-lg focus:ring focus:ring-blue-300" id="imageInput" onchange="previewImage(event)" required>
    </div>

    <!-- Image Preview -->
    <div id="imagePreview" class="mb-4 hidden">
        <label class="block text-sm font-medium text-gray-700">Image Preview</label>
        <img id="preview" class="w-32 h-32 object-cover rounded-lg" alt="Image Preview">
    </div>

    <!-- SRP -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">SRP</label>
        <input type="number" name="price" class="w-full px-3 py-2 border rounded-lg focus:ring focus:ring-blue-300" required>
    </div>

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


    <!-- With Variant -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">With Variant?</label>
        <select name="w_variant" class="w-full px-3 py-2 border rounded-lg focus:ring focus:ring-blue-300" required>
            <option value="YES">YES</option>
            <option value="None">None</option>
        </select>
    </div>

    <!-- Status -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Status</label>
        <select name="status" class="w-full px-3 py-2 border rounded-lg focus:ring focus:ring-blue-300" required>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
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

<script>
    function previewImage(event) {
        const file = event.target.files[0]; // Get the file selected by the user
        const reader = new FileReader(); // Create a FileReader object
        
        reader.onload = function() {
            const imagePreview = document.getElementById("imagePreview");
            const previewImage = document.getElementById("preview");

            previewImage.src = reader.result; // Set the src of the image preview to the file content
            imagePreview.classList.remove("hidden"); // Show the image preview div
        };

        if (file) {
            reader.readAsDataURL(file); // Read the file as data URL
        }
    }
</script>
@endsection
