@extends('stockclerk.dashboard.stockClerkDashboard')

@section('content')
<div class="container mx-auto p-6 bg-white rounded-xl" style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">
    <!-- Back Button -->
    <div class="mb-4">
        <button onclick="window.history.back()" class="bg-gray-500 text-white px-3 py-1 rounded-lg hover:bg-gray-600">
            ← Back
        </button>
    </div>

    <div class="flex justify-between items-center ">
        <h2 class="text-2xl font-bold">Product Details</h2>
        
        <!-- Edit Button -->
        <button id="editButton" class="bg-blue-500 text-white px-3 py-1 rounded-lg hover:bg-blue-600">
            Edit
        </button>
    </div>

    <p style="font-size: 16px; margin-top: 4px">To add stocks just simply click edit</p>


    <!-- Product Form -->
    <form id="editForm" action="{{ route('updateProduct', ['model_id' => $product->model_id]) }}" 
        method="POST" enctype="multipart/form-data" class="hidden">
        @csrf

        <!-- Model Name -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Model Name:</label>
            <input type="text" name="model_name" value="{{ $product->model_name }}" class="px-3 py-2 border rounded-lg w-full">
        </div>

        <!-- Image Upload -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Upload Image:</label>
            <input type="file" name="model_img" class="px-3 py-2 border rounded-lg w-full">
        </div>


        <!-- Brand Name -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Brand Name:</label>
            <input type="text" name="brand_name" value="{{ $product->brand_name }}" class="px-3 py-2 border rounded-lg w-full">
        </div>

        <!-- Price -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Price:</label>
            <input type="number" name="price" value="{{ $product->price }}" class="px-3 py-2 border rounded-lg w-full">
        </div>

        <!-- Description -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Description:</label>
            <textarea name="description" class="px-3 py-2 border rounded-lg w-full">{{ $product->description }}</textarea>
        </div>

        <!-- Part ID -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">M Part ID:</label>
            <input type="text" name="m_part_id" value="{{ $product->m_part_id }}" class="px-3 py-2 border rounded-lg w-full">
        </div>

        <!-- Stock Quantity -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Stocks Quantity:</label>
            <input type="number" name="stocks_quantity" value="{{ $product->stocks_quantity }}" class="px-3 py-2 border rounded-lg w-full">
        </div>

        <!-- Status -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Status:</label>
            <select name="status" class="px-3 py-2 border rounded-lg w-full">
                <option value="active" {{ $product->status == 'active' ? 'selected' : '' }}>active</option>
                <option value="inactive" {{ $product->status == 'inactive' ? 'selected' : '' }}>inactive</option>
            </select>
        </div>

        <!-- Save Button -->
        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">
            Save Changes
        </button>

        <button type="button" id="cancelButton" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 ml-2">
            Cancel
        </button>

    </form>

    <!-- Display Static Data (Hidden When Editing) -->
    <div id="productDetails">
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Model Name:</label>
            <p class="px-3 py-2 border bg-gray-100 rounded-lg">{{ $product->model_name }}</p>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Image:</label>
            <img src="{{ asset('product-images/' . $product->model_img) }}" 
                alt="Product Image" 
                class="w-32 h-32 object-cover border rounded-lg">
        </div>


        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">PART ID:</label>
            <p class="px-3 py-2 border bg-gray-100 rounded-lg">{{ $product->m_part_id }}</p>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Brand Name:</label>
            <p class="px-3 py-2 border bg-gray-100 rounded-lg">{{ $product->brand_name }}</p>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Price:</label>
            <p class="px-3 py-2 border bg-gray-100 rounded-lg">{{ $product->price }}</p>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Description:</label>
            <p class="px-3 py-2 border bg-gray-100 rounded-lg">{{ $product->description }}</p>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Stocks Quantity:</label>
            <p class="px-3 py-2 border bg-gray-100 rounded-lg">{{ $product->stocks_quantity }}</p>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Status:</label>
            <p class="px-3 py-2 border bg-gray-100 rounded-lg capitalize">{{ $product->status }}</p>
        </div>
    </div>
</div>

<script>
      document.getElementById("editButton").addEventListener("click", function() {
        document.getElementById("editForm").classList.remove("hidden");
        document.getElementById("productDetails").classList.add("hidden");
    });

    document.getElementById("cancelButton").addEventListener("click", function() {
        document.getElementById("editForm").classList.add("hidden");
        document.getElementById("productDetails").classList.remove("hidden");
    });
</script>

@endsection
