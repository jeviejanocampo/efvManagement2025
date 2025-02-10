@extends('stockclerk.dashboard.stockClerkDashboard')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container mx-auto p-6 bg-white rounded-xl shadow-md">
    <!-- Back Button -->
    <div class="mb-4">
    <a href="{{ route('productsView') }}" class="bg-gray-500 text-white px-3 py-1 rounded-lg hover:bg-gray-600">
        ← Back
    </a>
    </div>

    <div class="flex justify-between items-center mb-4">
        <!-- <h2 class="text-2xl font-bold">Model ID: {{ $model->model_id }}</h2> -->
        <h2 class="text-2xl font-bold">Product Name: {{ $model->model_name }}</h2>
    </div>

    <div>
        <a href="{{ route('add.variant', ['model_id' => $model_id]) }}">
            <button class="bg-violet-700 text-white px-2 py-1 rounded-lg hover:bg-violet-700 mb-4">
                Add Variant
            </button>
        </a>
    </div>



    <div class="overflow-x-auto">
        <table class="table-auto w-full border-collapse border border-gray-300">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border border-gray-300 px-2 py-1">Part ID</th>
                    <th class="border border-gray-300 px-2 py-1"></th>
                    <th class="border border-gray-300 px-2 py-1">Product Name</th>
                    <th class="border border-gray-300 px-2 py-1">Price</th>
                    <th class="border border-gray-300 px-2 py-1">Stock Quantity</th>
                    <th class="border border-gray-300 px-2 py-1">Status</th>
                    <th class="border border-gray-300 px-2 py-1">Edit Status</th>
                    <th class="border border-gray-300 px-2 py-1">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($variants as $variant)
                    <tr>
                        <td class="border border-gray-300 px-2 py-1">{{ $variant->part_id }}</td>
                        <td class="border border-gray-300 px-2 py-1">
                            <img src="{{ asset('product-images/' . $variant->variant_image) }}" alt="Variant Image" class="w-16 h-16 object-cover rounded">
                        </td>
                        <td class="border border-gray-300 px-2 py-1">{{ $variant->product_name }}</td>
                        <td class="border border-gray-300 px-2 py-1">{{ $variant->price }}</td>
                        <td class="border border-gray-300 px-2 py-1">{{ $variant->stocks_quantity }}</td>
                        <td class="border border-gray-300 px-2 py-1 text-center
                            {{ $variant->status == 'active' ? 'bg-green-500 text-white' : 'bg-red-500 text-white' }} 
                            text-center" style="margin: 12px">
                            {{ ucfirst($variant->status) }}
                        </td>
                        <td class="border border-gray-300 px-2 py-1 text-center">
                            <!-- Edit Status Button -->
                            <button onclick="openModal({{ $variant->variant_id }})" 
                                    class="bg-yellow-500 text-white px-2 py-1 rounded-lg hover:bg-yellow-600">
                                Edit Status
                            </button>
                        </td>

                        <td class="border border-gray-300 px-2 py-1 text-center">
                        <a href="{{ route('edit.variant', ['model_id' => $variant->model_id, 'variant_id' => $variant->variant_id]) }}" class="text-blue-500 hover:underline">
                            Edit
                        </a>
                            | 
                            <form action="{{ route('delete.variant', ['id' => $variant->variant_id]) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:underline bg-transparent border-none cursor-pointer"
                                        onclick="return confirm('Are you sure you want to delete this variant?');">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Status Update Modal -->
    <div id="statusModal" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 w-80">
            <h2 class="text-xl font-bold mb-4">Update Status</h2>
            <p class="mb-4">Change the status for <strong>Variant ID: <span id="variantIdText"></span></strong>?</p>

            <select id="statusSelect" class="w-full border p-2 rounded mb-4">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>

            <div class="flex justify-between">
                <button onclick="closeModal()" 
                        class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Cancel
                </button>
                <button onclick="updateStatus()" 
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Save
                </button>
            </div>
        </div>
    </div>


<script>
        let selectedVariantId = null; // Store the selected Variant ID

        function openModal(variantId) {
            selectedVariantId = variantId; // Save the variant ID
            document.getElementById("variantIdText").innerText = variantId; // Show in modal
            document.getElementById("statusModal").classList.remove("hidden"); // Show modal
        }

        function closeModal() {
            document.getElementById("statusModal").classList.add("hidden"); // Hide modal
        }

        function updateStatus() {
            let status = document.getElementById("statusSelect").value;

            fetch(`/update-variant-status/${selectedVariantId}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ status: status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Status updated successfully!");
                    location.reload(); // Refresh to reflect the change
                } else {
                    alert("Failed to update status.");
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("Something went wrong.");
            });

            closeModal(); // Close modal after request
        }
</script>
</div>


@endsection
