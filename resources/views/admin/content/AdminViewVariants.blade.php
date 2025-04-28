@extends('admin.dashboard.adminDashboard')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container mx-auto p-6 bg-white " style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">
    <!-- Back Button -->
    <div class="mb-4">
    <a href="{{ route('adminproductsView') }}" class="bg-gray-500 text-white px-3 py-1 rounded-lg hover:bg-gray-600">
        ← Back
    </a>
    </div>

    <div class="flex justify-between items-center mb-4">
        
        <!-- <h2 class="text-2xl font-bold">Model ID: {{ $model->model_id }}</h2> -->
        <h2 class="text-2xl font-bold">Primary Product Name: {{ $model->model_name }}</h2>

        <a href="{{ route('admin.add.variant', ['model_id' => $model_id]) }}">
            <button class="bg-violet-700 text-white px-2 py-1 rounded-lg hover:bg-violet-800 mb-4" title="Add Variant">
                <i class="fas fa-plus"></i>
            </button>
        </a>


    </div>




    <div class="overflow-x-auto">
        <table class="table-auto w-full border-collapse border-b">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border-b px-2 py-1">Product ID</th>
                    <th class="border-b px-2 py-1">Part ID</th>
                    <th class="border-b px-2 py-1"></th>
                    <th class="border-b px-2 py-1">Variant Name</th>
                    <th class="border-b px-2 py-1">Unit Price</th>
                    <th class="border-b px-2 py-1">Stock Quantity</th>
                    <th class="border-b px-2 py-1">Status</th>
                    <th class="border-b px-2 py-1">Edit Status</th>
                    <th class="border-b px-2 py-1">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($variants as $variant)
                    <tr class="text-center">
                        <td class="border-b px-2 py-1">0000{{ $variant->variant_id }}</td>
                        <td class="border-b px-2 py-1">{{ $variant->part_id }}</td>
                        <td class="border-b px-2 py-1">
                            <img src="{{ asset('product-images/' . $variant->variant_image) }}" alt="Variant Image" class="w-16 h-16 object-cover rounded">
                        </td>
                        <td class="border-b px-2 py-1">{{ $variant->product_name }}</td>
                        <td class="border-b px-2 py-1">{{ $variant->price }}</td>
                        <td class="border-b px-2 py-1">{{ $variant->stocks_quantity }}</td>
                        <td class="border-b px-2 py-1 text-center
                            {{ $variant->status == 'active' ? 'bg-green-500 text-white' : 'bg-red-500 text-white' }} 
                            text-center" style="margin: 12px">
                            {{ ucfirst($variant->status) }}
                        </td>
                        <td class="border-b px-2 py-1 text-center">
                            <!-- Edit Status Button -->
                            <button onclick="openModal({{ $variant->variant_id }})" 
                                    class="bg-yellow-500 text-white px-2 py-1 rounded-lg hover:bg-yellow-600">
                                Edit Status
                            </button>
                        </td>

                        <td class="border-b px-2 py-1 text-center gap-2">

                            <a href="{{ route('admin.edit.variant', ['model_id' => $variant->model_id, 'variant_id' => $variant->variant_id]) }}" 
                            class="text-blue-500 hover:underline">
                                <i class="fas fa-edit"></i> 
                            </a>
                            
                                <form action="{{ route('admin.delete.variant', ['id' => $variant->variant_id]) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('POST')
                                    <button type="submit" class="text-red-500 hover:underline bg-transparent border-none cursor-pointer"
                                            onclick="return confirm('Are you sure you want to delete this variant?');">
                                        <i class="fas fa-trash-alt"></i> 
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
