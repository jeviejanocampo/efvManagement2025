@extends('admin.dashboard.adminDashboard')
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

<div class="container mx-auto max-w-full p-4 bg-white " style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">

    <div style=" margin-bottom: 20px; font-size: 26px; font-weight: 800; color: #333;">
        Products 

        <p class="border-b border-b-[1px] border-gray-300 mt-2">
            <!-- Your content here -->
        </p>

    </div>

    <div class="flex justify-between items-center mb-4 space-x-4">

        <div class="w-full sm:w-1/2">
            <input 
                type="text" 
                id="search-bar" 
                class="w-full px-2 py-1 text-sm border-b rounded-lg focus:ring-2 focus:ring-black focus:outline-none" 
                placeholder="Search by Product Name">
        </div>

        <div class="w-full sm:w-1/4">
            <select id="category-filter" class="w-full px-2 py-1 border-b text-sm rounded-lg">
                <option value="">All Categories</option>
                <option value="Gear Oils">Gear Oils</option>
                <option value="Auto Parts">Auto Parts</option>
            </select>
        </div>

        <div class="w-full sm:w-1/4">
            <select id="brand-filter" class="w-full px-2 py-1 border-b text-sm rounded-lg">
                <option value="">All Brands</option>
                @foreach ($brands as $brand)
                    <option value="{{ $brand }}">{{ $brand }}</option>
                @endforeach
            </select>
        </div>

        
        <div class="w-full sm:w-1/4">
            <select id="status-filter" class="w-full px-2 py-1 border-b text-sm rounded-lg">
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
            class="w-24 px-2 py-1 text-sm border-b rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" 
            placeholder="Min Price"
        >
        <span>-</span>
        <input 
            type="number" 
            id="max-price" 
            class="w-24 px-2 py-1 text-sm border-b rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none" 
            placeholder="Max Price"
        >
        <button 
            id="apply-price-filter" 
            class="px-3 py-1 text-sm text-white bg-black rounded-md hover:bg-black focus:ring-2 focus:ring-blue-500 focus:outline-none">
            Apply
        </button>
        <button 
            id="clear-price" 
            class="px-3 py-1 text-sm text-white bg-red-500 rounded-md hover:bg-red-600 focus:ring-2 focus:ring-red-500 focus:outline-none">
            Clear
        </button>
    </div>

    </div>

    <div class="text-gray-500 italic text-sm mb-2">
        Note: Navigate to action to add details for the specific products || To edit primary products status, just click the status text

        <div class="mt-2">
        <!-- <a href="{{ route('manager.add.product') }}" style="margin-left: 4px">
        <button class="bg-white text-black px-2 py-1 rounded-[6px] border border-gray-400 hover:bg-violet-100">
        Add Product
            </button>
        </a>

        <a href="{{ route('manager.add.brand') }}">
        <button class="bg-white text-black px-2 py-1 rounded-[6px] border border-gray-400 hover:bg-violet-100">
                Add New Brand
            </button>
        </a>

        <a href="{{ route('manager.add.category') }}">
        <button class="bg-white text-black px-2 py-1 rounded-[6px] border border-gray-400 hover:bg-violet-100">
                Add Category
            </button>
        </a>

        <a href="{{ route('manager.view.brands') }}">
        <button class="bg-white text-black px-2 py-1 rounded-[6px] border border-gray-400 hover:bg-violet-100">
                View Brands
            </button>
        </a>

        <a href="{{ route('manager.view.category') }}">
        <button class="bg-white text-black px-2 py-1 rounded-[6px] border border-gray-400 hover:bg-violet-100">
                View Categories
            </button>
        </a> -->
    </div>
    </div>


    <div class="overflow-x-auto">
        <table class="table-auto w-full border-collapse border-b">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border-b px-2 py-1"></th>
                    <!-- <th class="border-b px-2 py-1"></th> -->
                    <!-- <th class="border-b px-2 py-1">Category</th> -->
                    <th class="border-b px-2 py-1">Brand</th>
                    <th class="border-b px-2 py-1">Product</th>
                    <th class="border-b px-2 py-1">Price</th>
                    <th class="border-b px-2 py-1">Qty</th>
                    <!-- <th class="border-b px-2 py-1">W/Variant</th> -->
                    <!-- <th class="border-b px-2 py-1">Details</th> -->
                    <th class="border-b px-2 py-1">View Variants</th>
                    <th class="border-b px-2 py-1">Status</th>
                    <th class="border-b px-2 py-1">Action</th>
                </tr>
            </thead>
            <tbody id="order-table">
                @foreach ($products as $product)
                    <tr data-category="{{ $product->brand->category->category_name ?? 'N/A' }}"
                        data-brand="{{ $product->brand->brand_name ?? 'N/A' }}"
                        data-name="{{ $product->model_name }}">
                        <!-- <td class="border-b px-2 py-1">000{{ $product->model_id }}</td> -->
                        <td class="border-b px-2 py-1">
                            <img src="{{ asset('product-images/' . $product->model_img) }}" alt="Product Image" width="50">
                        </td>
                        <!-- <td class="border-b px-2 py-1">
                            {{ $product->brand->category->category_name ?? 'N/A' }}
                        </td> -->
                        <td class="border-b px-2 py-1" style="text-align: center">
                            {{ $product->brand->brand_name ?? 'N/A' }}
                        </td>
                        <td class="border-b px-2 py-1" style="text-align: center">{{ $product->model_name }}</td>
                        <td class="border-b px-2 py-1 text-center">₱ {{ $product->price }}</td>
                        @if (strtolower($product->w_variant) !== 'yes')
                            <td class="border-b px-2 py-1 relative text-center">
                                @php
                                    $stock = Products::where('model_id', $product->model_id)->sum('stocks_quantity');
                                @endphp
                                {{ $stock }}

                                @if ($stock <= 5 && !request()->routeIs('edit.product'))
                                    <span class="absolute right-12 bg-red-500 text-white font-semibold px-2 py-1 bottom-9 rounded-md" style="font-size:10px">
                                        Low
                                    </span>
                                @endif
                            </td>
                        @else
                            <td class="border-b px-2 py-1 text-center text-gray-400 italic">
                                —
                            </td>
                        @endif


                        <!-- <td class="border-b px-2 py-1" style="text-align: center">{{ $product->w_variant }}</td> -->

                        <!-- <td class="border-b px-2 py-1 text-center">
                            @php
                                $hasDetails = Products::where('model_id', $product->model_id)->exists();
                            @endphp
                            @if ($hasDetails)
                                <a href="{{ route('manager.viewDetails', ['model_id' => $product->model_id]) }}" 
                                class="px-2 py-1 text-white text-xs font-semibold rounded bg-green-500">
                                    View Details
                                </a>
                            @else
                                <a href="{{ route('manager.addDetails', ['model_id' => $product->model_id]) }}" 
                                class="px-1 py-1 text-white font-semibold rounded bg-red-500" style="font-size:10px">
                                    No Details | Click to add
                                </a>
                            @endif
                        </td> -->

                        <td class="border-b px-2 py-1 text-center">
                            @if (strtolower($product->w_variant) === 'yes')
                                <a href="{{ route('admin.variantsView', ['model_id' => $product->model_id]) }}" class="text-white bg-blue-500 px-2 py-1 rounded">View</a>
                            @else
                                <span class="text-white bg-gray-500 px-2 py-1 rounded">No Variant</span>
                            @endif
                        </td>


                        <td class="border-b px-2 py-1 text-center rounded-md">
                            <span class="px-2 py-1 text-white text-xs font-semibold rounded-md cursor-pointer update-status 
                                        {{ $product->status == 'active' ? 'bg-green-600' : 'bg-red-500' }}" 
                                data-id="{{ $product->model_id }}" 
                                data-status="{{ $product->status }}">
                                {{ $product->status }}
                            </span>
                        </td>

                        <td class="border-b px-2 py-1 text-center">
                            @if ($hasDetails)
                                <!-- View Details Icon -->
                                <a href="{{ route('admin.viewDetails', ['model_id' => $product->model_id]) }}" title="View Details">
                                    <i class="fas fa-eye text-blue-600 mx-1 text-sm"></i>
                                </a>
                            @else
                                <!-- No Details / Add Details Icon -->
                                <a href="{{ route('admin.addDetails', ['model_id' => $product->model_id]) }}" title="No Details | Add Details">
                                    <i class="fas fa-eye-slash text-gray-400 mx-1 cursor-not-allowed text-sm"></i>
                                </a>
                            @endif

                            <!-- Edit Icon -->
                            <a href="{{ route('admin.viewModelDetails', ['model_id' => $product->model_id]) }}" title="Edit Primary Model Details">
                                <i class="fas fa-edit text-yellow-500 mx-1 text-sm"></i>
                            </a>

                            <!-- Delete Icon -->
                            <!-- <a href="#" class="delete-product" data-id="{{ $product->model_id }}" title="Delete">
                                <i class="fas fa-trash-alt text-red-600 mx-1 text-sm"></i>
                            </a> -->
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $products->links('pagination::tailwind') }}
    </div>
</div>



 <!-- Status Update Modal -->
 <div id="statusModal" class="hidden fixed inset-0 bg-gray-800 bg-opacity-20 flex justify-center items-center mb-50">
    <div class="bg-white p-5 rounded-lg  w-1/3">
        <h2 class="text-lg font-bold mb-4">Update Status</h2>
        
        <input type="hidden" id="model_id">
        
        <label class="block text-sm font-medium text-gray-700">Select Status:</label>
        <select id="statusSelect" class="w-full px-2 py-1 border-b rounded-lg">
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
    const minPriceInput = document.getElementById("min-price");
    const maxPriceInput = document.getElementById("max-price");
    const applyPriceFilterButton = document.getElementById("apply-price-filter");
    const productRows = document.querySelectorAll("#order-table tr");

    applyPriceFilterButton.addEventListener("click", function () {
        const minPrice = parseFloat(minPriceInput.value) || 0;
        const maxPrice = parseFloat(maxPriceInput.value) || Infinity;

        productRows.forEach(row => {
            const priceCell = row.querySelector("td:nth-child(5)");
            const price = parseFloat(priceCell.textContent);

            if (price >= minPrice && price <= maxPrice) {
                row.style.display = ""; // Show row
            } else {
                row.style.display = "none"; // Hide row
            }
        });
    });
    });

    document.getElementById('clear-price').addEventListener('click', () => {
        document.getElementById('min-price').value = '';
        document.getElementById('max-price').value = '';
        
        // Optionally, refresh your table or apply filters again here
        console.log('Price range cleared');
    });


</script>
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
