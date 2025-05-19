@extends('admin.dashboard.adminDashboard')

@section('content')
<div class="container mx-auto p-6 bg-white " style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">
    <!-- Back Button -->
     
    <div class="mb-4">
        <a href="{{ route('admin.variantsView', ['model_id' => $model_id]) }}" class="bg-gray-500 text-white px-3 py-1 rounded-lg hover:bg-gray-600">
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

    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Variant Gallery:</label>
        <div class="flex space-x-4 mt-2 overflow-x-auto">
            @php
                // Assuming you passed $galleryImages to the view:
                // $galleryImages = GalleryImage::where('variant_id', $variant_id)->get();
            @endphp
            @forelse($variantImages as $image)
                <div class="relative w-32 h-32">
                    <img src="{{ asset('product-images/' . $image->image) }}" 
                        alt="Variant Image"
                        class="w-full h-full object-cover border rounded-lg">
                    <button 
                        onclick="deleteVariantGalleryImage({{ $image->id }})"
                        class="absolute top-0 right-0 bg-red-600 text-white text-xs px-2 py-1 rounded-bl hover:bg-red-700"
                        title="Delete">
                        ✕
                    </button>
                </div>
            @empty
                <p class="text-gray-500 italic">No variant images available.</p>
            @endforelse
        </div>
    </div>

    <form action="{{ route('admin.uploadVariantGalleryImage', ['variant_id' => $variant_id]) }}" 
        method="POST" enctype="multipart/form-data" class="mt-6">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Upload Variant Gallery Image (1 only):</label>
            <input type="file" name="gallery_image" required class="px-3 py-2 border rounded-lg w-full">
        </div>
        <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded-lg hover:bg-indigo-600">
            Upload Variant Gallery Image
        </button>
    </form>


    <!-- Edit Form -->
    <form id="editVariantForm" action="{{ route('admin.update.variant', ['model_id' => $model_id, 'variant_id' => $variant_id]) }}" method="POST" enctype="multipart/form-data">
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

        <!-- Markup Percentage -->
        <div class="mb-4">
            <label class="block text-gray-700">Markup Percentage</label>
            <input type="number" name="markup_percentage" id="markup_percentage" value="{{ $variant->markup_percentage ?? '' }}" readonly class="w-full px-3 py-2 border rounded-lg">
        </div>

        <!-- VAT Inclusive Price -->
        <div class="mb-4">
            <label class="block text-gray-700">VAT Inclusive Price (After Markup)</label>
            <input type="number" name="vat_inclusive" id="vat_inclusive" value="{{ $variant->vat_inclusive ?? '' }}" readonly class="w-full px-3 py-2 border rounded-lg">
        </div>


        <div class="mb-4">
            <label class="block text-gray-700">Cost Price</label>
            <input type="number" name="price" id="price" value="{{ $variant->price }}" class="w-full px-3 py-2 border rounded-lg" oninput="calculateVariantMarkupVAT()">
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
            <p id="stockChangeLabel" class="text-sm text-gray-500 mb-1">No changes yet</p>
            <div class="flex gap-2">
                <input type="number" name="stocks_quantity" id="stocks_quantity" value="{{ $variant->stocks_quantity }}" class="w-full px-3 py-2 border rounded-lg" readonly>
                <button type="button" onclick="openStockModal('add')" class="bg-green-500 text-white px-3 py-2 rounded hover:bg-green-600">
                    <i class="fas fa-plus"></i>
                </button>
                <button type="button" onclick="openStockModal('subtract')" class="bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
            <input type="hidden" id="original_stock" value="{{ $variant->stocks_quantity }}">
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
            <a href="{{ route('admin.variantsView', ['model_id' => $model_id]) }}" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">
                Cancel
            </a>
        </div>
    </form>

   <!-- Stock Update Modal -->
    <div id="stockModal" class="fixed inset-0 bg-black bg-opacity-40 hidden items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-sm">
            <h2 class="text-xl font-semibold mb-4" id="modalTitle">Adjust Stock</h2>
            <input type="number" id="stock_adjust_value" class="w-full px-3 py-2 border rounded mb-4" placeholder="Enter quantity">
            <div class="flex justify-end space-x-2">
                <button onclick="closeStockModal()" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Cancel</button>
                <button onclick="applyStockAdjustment()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Apply</button>
            </div>
        </div>
    </div>

</div>

<script>
    function deleteVariantGalleryImage(imageId) {
        if (confirm("Are you sure you want to delete this variant image?")) {
            fetch(`/admin-delete-variant-gallery-image/${imageId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (response.ok) return response.json();
                throw new Error('Network response was not ok.');
            })
            .then(data => {
                alert(data.message);
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => {
                console.error(error);
                alert('Failed to delete variant image.');
            });
        }
    }
</script>
<script>
    let currentAction = 'add'; // 'add' or 'subtract'

    function openStockModal(action) {
        currentAction = action;
        document.getElementById('modalTitle').textContent = action === 'add' ? 'Add Stock Quantity' : 'Subtract Stock Quantity';
        document.getElementById('stock_adjust_value').value = '';
        document.getElementById('stockModal').classList.remove('hidden');
        document.getElementById('stockModal').classList.add('flex');
    }

    function closeStockModal() {
        document.getElementById('stockModal').classList.add('hidden');
        document.getElementById('stockModal').classList.remove('flex');
    }

    function applyStockAdjustment() {
        const value = parseInt(document.getElementById('stock_adjust_value').value);
        const stockInput = document.getElementById('stocks_quantity');
        const originalStock = parseInt(document.getElementById('original_stock').value);
        const label = document.getElementById('stockChangeLabel');

        if (isNaN(value) || value <= 0) {
            alert('Please enter a valid number greater than 0.');
            return;
        }

        let currentStock = parseInt(stockInput.value);
        let newStock = currentAction === 'add' ? currentStock + value : currentStock - value;

        if (newStock < 0) {
            alert('Stock cannot go below 0.');
            return;
        }

        stockInput.value = newStock;

        const diff = newStock - originalStock;
        label.textContent = diff > 0
            ? `Increased by +${diff}`
            : diff < 0
                ? `Decreased by ${diff}`
                : 'No changes yet';

        label.className = diff > 0
            ? "text-sm text-green-600 mb-1"
            : diff < 0
                ? "text-sm text-red-600 mb-1"
                : "text-sm text-gray-500 mb-1";

        closeStockModal();
    }
</script>
<script>
    function displayStockChange() {
        const originalStock = parseInt(document.getElementById('original_stock').value);
        const currentStock = parseInt(document.getElementById('stocks_quantity').value);
        const label = document.getElementById('stockChangeLabel');

        if (isNaN(currentStock)) {
            label.textContent = 'Invalid input';
            return;
        }

        const difference = currentStock - originalStock;

        if (difference > 0) {
            label.textContent = `Increased by +${difference}`;
            label.className = "text-sm text-green-600 mb-1";
        } else if (difference < 0) {
            label.textContent = `Decreased by ${difference}`;
            label.className = "text-sm text-red-600 mb-1";
        } else {
            label.textContent = 'No changes yet';
            label.className = "text-sm text-gray-500 mb-1";
        }
    }
</script>
<script>
    window.onload = function() {
        @if (session('success'))
            alert("{{ session('success') }}");
        @endif

        @if (session('error'))
            alert("{{ session('error') }}");
        @endif
    };
</script>
<script>
    function calculateVariantMarkupVAT() {
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

        if (price >= 1 && price <= 500) {
            markup = 2;
        } else if (price >= 501 && price <= 1000) {
            markup = 5;
        } else if (price > 1000) {
            markup = 10;
        }

        markupInput.value = markup;

        const sellingPrice = price * (1 + (markup / 100));
        const vatInclusivePrice = sellingPrice * 1.12;

        let mod5 = vatInclusivePrice % 5;
        let mod10 = vatInclusivePrice % 10;
        let roundedPrice = vatInclusivePrice;

        if (mod10 <= 2) {
            roundedPrice = vatInclusivePrice - mod10 - 1;
        } else if (mod5 <= 2.5) {
            roundedPrice = vatInclusivePrice - mod5;
        } else {
            roundedPrice = vatInclusivePrice - mod5;
        }

        vatInclusiveInput.value = Math.round(roundedPrice);
    }
</script>
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
