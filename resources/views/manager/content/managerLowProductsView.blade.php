@extends('manager.dashboard.managerDashboard')
@section('content')

@php
    use App\Models\Products;
@endphp

<style>
    th {
        font-size: 12px;
    }
    td {
        font-size: 12px;
        style="text-align: center"
    }
</style>

<div class="container mx-auto p-4 bg-white rounded-xl">

    <div style="text-align: center; margin-bottom: 20px; font-size: 26px; font-weight: 800; color: #333;">
         Low Units
    </div>

 <div class="flex justify-between items-center mb-4 space-x-4">
        <div class="w-full sm:w-1/2">
            <input 
                type="text" 
                id="search-bar" 
                class="w-full px-2 py-1 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" 
                placeholder="Search by Product Name">
        </div>

        <div class="w-full sm:w-1/4">
            <select id="category-filter" class="w-full px-2 py-1 border border-gray-300 text-sm rounded-lg">
                <option value="">All Categories</option>
                <option value="Gear Oils">Gear Oils</option>
                <option value="Auto Parts">Auto Parts</option>
            </select>
        </div>

        <div class="w-full sm:w-1/4">
            <select id="brand-filter" class="w-full px-2 py-1 border border-gray-300 text-sm rounded-lg">
                <option value="">All Brands</option>
                @foreach ($brands as $brand)
                    <option value="{{ $brand }}">{{ $brand }}</option>
                @endforeach
            </select>
        </div>

        
        <div class="w-full sm:w-1/4">
            <select id="status-filter" class="w-full px-2 py-1 border border-gray-300 text-sm rounded-lg">
                <option value="">All Status</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center space-x-2">
        <input 
            type="number" 
            id="min-price" 
            class="w-24 px-2 py-1 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" 
            placeholder="Min Price"
        >
        <span>-</span>
        <input 
            type="number" 
            id="max-price" 
            class="w-24 px-2 py-1 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" 
            placeholder="Max Price"
        >
        <button 
            id="apply-price-filter" 
            class="px-3 py-1 text-sm text-white bg-blue-500 rounded-md hover:bg-blue-600 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            Apply
        </button>
        <button 
            id="clear-price" 
            class="px-3 py-1 text-sm text-white bg-red-500 rounded-md hover:bg-red-600 focus:ring-2 focus:ring-red-500 focus:outline-none">
            Clear
        </button>
    </div>
</div>

    <div>
        <a href="{{ route('manager.add.product') }}">
            <button class="bg-violet-700 text-white px-2 py-1 rounded-lg hover:bg-violet-700 mb-4">
                Add Product
            </button>
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="table-auto w-full border-collapse border border-gray-300">
            <thead>
                <tr class="bg-white">
                    <th class="border border-gray-300 px-2 py-1">Id</th>
                    <th class="border border-gray-300 px-2 py-1"></th>
                    <!-- <th class="border border-gray-300 px-2 py-1">Category</th> -->
                    <th class="border border-gray-300 px-2 py-1">Brand Name</th>
                    <th class="border border-gray-300 px-2 py-1">Product Name</th>
                    <th class="border border-gray-300 px-2 py-1">Unit Price</th>
                    <th class="border border-gray-300 px-2 py-1">Quantity</th>
                    <!-- <th class="border border-gray-300 px-2 py-1">W/Variant</th> -->
                    <!-- <th class="border border-gray-300 px-2 py-1">Details</th> -->
                    <th class="border border-gray-300 px-2 py-1">View Variants</th>
                    <th class="border border-gray-300 px-2 py-1">Status</th>
                    <th class="border border-gray-300 px-2 py-1">Action</th>
                </tr>
            </thead>
            <tbody id="order-table">
            @foreach ($products as $product)
                @php
                    $stock = Products::where('model_id', $product->model_id)->sum('stocks_quantity');
                @endphp

                @if ($stock <= 5)
                    <tr data-category="{{ $product->brand->category->category_name ?? 'N/A' }}"
                        data-brand="{{ $product->brand->brand_name ?? 'N/A' }}"
                        data-name="{{ $product->model_name }}">
                        <td class="border border-gray-300 px-2 py-1">{{ $product->model_id }}</td>
                        <td class="border border-gray-300 px-2 py-1">
                            <img src="{{ asset('product-images/' . $product->model_img) }}" alt="Product Image" width="50">
                        </td>
                        <td class="border border-gray-300 px-2 py-1" style="text-align: center">
                            {{ $product->brand->brand_name ?? 'N/A' }}
                        </td>
                        <td class="border border-gray-300 px-2 py-1" style="text-align: center">{{ $product->model_name }}</td>
                        <td class="border border-gray-300 px-2 py-1">{{ $product->price }}</td>
                        <td class="border border-gray-300 px-2 py-1 relative">
                            {{ $stock }}
                            @if ($stock <= 5 && !request()->routeIs('edit.product'))
                                <span class="absolute left-8 bg-red-500 text-white font-semibold px-2 py-1 rounded-md" style="font-size:10px">
                                    Low units
                                </span>
                            @endif
                        </td>
                        <td class="border border-gray-300 px-2 py-1 text-center">
                            @if (strtolower($product->w_variant) === 'yes')
                                <a href="{{ route('manager.variantsView', ['model_id' => $product->model_id]) }}" class="text-blue-500">View</a>
                            @else
                                <span class="text-gray-500">No Variant</span>
                            @endif
                        </td>
                        <td class="border border-gray-300 px-2 py-1 text-center">
                            <span class="px-2 py-1 text-white text-xs font-semibold rounded cursor-pointer update-status 
                                        {{ $product->status == 'active' ? 'bg-green-600' : 'bg-red-500' }}" 
                                data-id="{{ $product->model_id }}" 
                                data-status="{{ $product->status }}">
                                {{ $product->status }}
                            </span>
                        </td>
                        <td class="border border-gray-300 px-2 py-1" style="text-align: center">
                            <a href="{{ route('manager.viewDetails', ['model_id' => $product->model_id]) }}">
                                <img src="{{ asset('product-images/view.png') }}" alt="View Details" class="w-6 h-6 inline mx-1" title="View Details">
                            </a>
                            <a href="{{ route('manager.viewModelDetails', ['model_id' => $product->model_id]) }}">
                                <img src="{{ asset('product-images/edit.png') }}" alt="Edit" class="w-6 h-6 inline mx-1" title="Edit">
                            </a>
                            <a href="#" class="delete-product" data-id="{{ $product->model_id }}">
                                <img src="{{ asset('product-images/trash.png') }}" alt="Delete" class="w-6 h-6 inline mx-1" title="Delete">
                            </a>
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $products->links('pagination::tailwind') }}
</div>

 <!-- Status Update Modal -->
 <div id="statusModal" class="hidden fixed inset-0 bg-gray-800 bg-opacity-20 flex justify-center items-center mb-50">
    <div class="bg-white p-5 rounded-lg shadow-md w-1/3">
        <h2 class="text-lg font-bold mb-4">Update Status</h2>
        
        <input type="hidden" id="model_id">
        
        <label class="block text-sm font-medium text-gray-700">Select Status:</label>
        <select id="statusSelect" class="w-full px-2 py-1 border border-gray-300 rounded-lg">
            <option value="active">Active</option>
            <option value="Inactive">Inactive</option>
            <option value="on order">On Order</option>
        </select>

        <div class="flex justify-end mt-4">
            <button id="closeModal" class="bg-gray-500 text-white px-3 py-1 rounded-lg mr-2">Cancel</button>
            <button id="saveStatus" class="bg-blue-600 text-white px-3 py-1 rounded-lg">Update</button>
        </div>
    </div>
</div>

<script>
   document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("statusModal");
    const statusSelect = document.getElementById("statusSelect");
    const modelIdInput = document.getElementById("model_id");
    const saveButton = document.getElementById("saveStatus");
    const closeButton = document.getElementById("closeModal");

    document.querySelectorAll(".update-status").forEach(item => {
        item.addEventListener("click", function () {
            const modelId = this.getAttribute("data-id");
            const currentStatus = this.getAttribute("data-status");

            modelIdInput.value = modelId;
            statusSelect.value = currentStatus;

            modal.classList.remove("hidden");
        });
    });

    closeButton.addEventListener("click", function () {
        modal.classList.add("hidden");
    });

    saveButton.addEventListener("click", function () {
        const modelId = modelIdInput.value;
        const newStatus = statusSelect.value;

        fetch(`/update-model-status/${modelId}`, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(response => response.json())
    .then(data => {
        console.log("Server Response:", data);
        alert(data.message);
        if (data.success) {
            location.reload();
        }
    })
        .catch(error => console.error("Error:", error));
    });
    });


</script>
<script>
      document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".delete-product").forEach(button => {
            button.addEventListener("click", function (event) {
                event.preventDefault();
                let productId = this.getAttribute("data-id");

                if (confirm("Are you sure you want to delete this product?")) {
                    fetch(`/product/delete/${productId}`, {
                        method: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Content-Type": "application/json"
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.success) {
                            location.reload(); // Refresh the page after successful deletion
                        }
                    })
                    .catch(error => console.error("Error:", error));
                }
            });
        });
    });
</script>
<script src="{{ asset('js/product-filter.js') }}"></script>

@endsection

@section('scripts')

@endsection
