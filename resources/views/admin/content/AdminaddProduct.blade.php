@extends('admin.dashboard.adminDashboard')

@section('content')
<div class="container mx-auto max-w-full p-6 bg-white " style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">
    <!-- <div class="mb-4">
        <button onclick="window.history.back()" class="bg-gray-500 text-white px-3 py-1 rounded-lg hover:bg-gray-600">
            ← Back
        </button>
    </div> -->

    <h2 class="text-2xl font-bold mb-4">Add Product</h2>

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
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

  <!-- Unit Price -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Unit Price (Cost Price)</label>
        <input type="number" name="price" id="price" class="w-full px-3 py-2 border rounded-lg focus:ring focus:ring-blue-300" required oninput="calculateMarkupAndVAT()">
    </div>

    <!-- Auto-calculated Markup Percentage -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Markup Percentage</label>
        <input type="number" name="markup_percentage" id="markup_percentage" class="w-full px-3 py-2 border rounded-lg focus:ring focus:ring-blue-300" readonly>
    </div>

    <!-- Auto-calculated VAT Inclusive Price -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">VAT Inclusive Price (After Markup)</label>
        <input type="number" name="vat_inclusive" id="vat_inclusive" class="w-full px-3 py-2 border rounded-lg focus:ring focus:ring-blue-300" readonly>
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
    function calculateMarkupAndVAT() {
        const priceInput = document.getElementById('price');
        const markupInput = document.getElementById('markup_percentage');
        const vatInclusiveInput = document.getElementById('vat_inclusive');

        const price = parseFloat(priceInput.value);

        if (!price || price <= 0) {
            markupInput.value = '';
            vatInclusiveInput.value = '';
            return;
        }

        let markup = 0;

        // Set markup based on price range
        if (price >= 1 && price <= 500) {
            markup = 2;
        } else if (price >= 501 && price <= 1000) {
            markup = 5;
        } else if (price > 1000) {
            markup = 10;
        }

        markupInput.value = markup; // Set markup %

        // Calculate selling price after markup
        const sellingPrice = price * (1 + (markup / 100));

        // Add VAT
        const vatInclusivePrice = sellingPrice * 1.12;

        // Now custom rounding logic
        let roundedPrice = 0;
        const mod5 = vatInclusivePrice % 5;
        const mod10 = vatInclusivePrice % 10;

        if (mod10 <= 2) {
            // example 701, 711
            roundedPrice = vatInclusivePrice - mod10 - 1; // move to nearest ending with 9 (like 699, 709, 719)
        } else if (mod5 <= 2.5) {
            // Close to lower 5
            roundedPrice = vatInclusivePrice - mod5;
        } else {
            // Otherwise lower to nearest multiple of 5
            roundedPrice = vatInclusivePrice - mod5;
        }

        vatInclusiveInput.value = Math.round(roundedPrice); // show whole number
    }
</script>


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
