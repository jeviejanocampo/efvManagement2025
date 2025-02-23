@extends('stockclerk.dashboard.stockClerkDashboard')

@section('content')

<div class="container mx-auto p-6 bg-white rounded-xl shadow-md">
    
    <a href="{{ route('productsView') }}" class="bg-gray-500 text-white px-3 py-1 rounded-lg hover:bg-gray-600">
        ← Back
    </a>


    <!-- Success & Error Messages -->
    @if(session('success'))
        <script>
            alert("{{ session('success') }}");
        </script>
    @endif

    @if(session('error'))
        <script>
            alert("{{ session('error') }}");
        </script>
    @endif

    <!-- Header Section with Model Details and Edit Button -->
    <div class="flex justify-between items-center my-4">
        <h2 class="text-2xl font-bold">Primary Product Details</h2>

        <!-- Edit Button (Toggle Form) -->
        <button onclick="toggleEditForm()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            Edit
        </button>
    </div>

    <!-- Model Details Display -->
    <div id="modelDetails">
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Model ID:</label>
            <p class="px-3 py-2 border bg-gray-100 rounded-lg">{{ $model->model_id }}</p>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Model Name:</label>
            <p class="px-3 py-2 border bg-gray-100 rounded-lg">{{ $model->model_name }}</p>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Model Image:</label>
            <img src="{{ asset('product-images/' . $model->model_img) }}" 
                alt="Model Image" 
                class="w-32 h-32 object-cover border rounded-lg">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Price:</label>
            <p class="px-3 py-2 border bg-gray-100 rounded-lg">${{ $model->price }}</p>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Brand Name:</label>
            <p class="px-3 py-2 border bg-gray-100 rounded-lg">{{ $model->brand->brand_name ?? 'N/A' }}</p>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">W Variant:</label>
            <p class="px-3 py-2 border bg-gray-100 rounded-lg">{{ $model->w_variant }}</p>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Status:</label>
            <p class="px-3 py-2 border bg-gray-100 rounded-lg capitalize">{{ $model->status }}</p>
        </div>
    </div>

    <!-- Edit Form (Initially Hidden) -->
    <div id="editForm" class="hidden mt-6 p-6 border rounded-lg bg-gray-100">
        <h3 class="text-lg font-semibold mb-4">Edit Product</h3>
        <form action="{{ route('updateModel', ['model_id' => $model->model_id]) }}" method="POST" enctype="multipart/form-data" onsubmit="return confirmUpdate()">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Model Name:</label>
                <input type="text" name="model_name" value="{{ $model->model_name }}" required class="w-full px-3 py-2 border rounded-lg">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Price:</label>
                <input type="number" name="price" value="{{ $model->price }}" required class="w-full px-3 py-2 border rounded-lg">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Model Image (Upload New):</label>
                <input type="file" name="model_img" class="w-full px-3 py-2 border rounded-lg" id="imageInput" onchange="previewImage(event)">
            </div>


            <!-- Image Preview for Upload -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Image Preview:</label>
                <div id="imagePreview" class="mb-4 hidden">
                    <img id="preview" class="w-32 h-32 object-cover border rounded-lg" alt="Image Preview">
                </div>
            </div>


            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Status:</label>
                <select name="status" required class="w-full px-3 py-2 border rounded-lg">
                    <option value="active" {{ $model->status == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $model->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="flex justify-end">
                <button type="button" onclick="toggleEditForm()" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 mr-2">Cancel</button>
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">Save Changes</button>
            </div>
        </form>
    </div>

</div>

<script>
    function previewImage(event) {
        const file = event.target.files[0]; // Get the selected file
        const reader = new FileReader(); // Create a FileReader object
        
        reader.onload = function() {
            const imagePreview = document.getElementById("imagePreview");
            const previewImage = document.getElementById("preview");

            previewImage.src = reader.result; // Set the src of the image preview
            imagePreview.classList.remove("hidden"); // Show the image preview div
        };

        if (file) {
            reader.readAsDataURL(file); // Read the file as data URL
        }
    }
</script>
<script>
    function toggleEditForm() {
        document.getElementById('editForm').classList.toggle('hidden');
        document.getElementById('modelDetails').classList.toggle('hidden');
    }

    function confirmUpdate() {
        return confirm("Are you sure you want to update this model?");
    }
</script>

@endsection
