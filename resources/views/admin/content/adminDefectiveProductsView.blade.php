@extends('admin.dashboard.adminDashboard')

@section('content')

<div class="container mx-auto max-w-full p-4 bg-white" style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">
    <div style="margin-bottom: 20px; font-size: 26px; font-weight: 800; color: #333;" class="border-b border-gray pb-2">
        Defective Products
    </div>

    <div class="flex justify-end space-x-4 mb-4">
        <!-- Date filter input -->
        <input type="date" id="dateFilter" class="px-6 py-2 border rounded-md" placeholder="Filter by Date">
        
        <!-- Search bar -->
        <input type="text" id="searchBar" class="px-6 py-2 border rounded-md" placeholder="Search by Product Name">
    </div>

    <p class="italic text-sm text-gray-600 mb-4">
        The defective products have been recorded based on the most recent order. Please note that the quantity of the products remains unaffected, as this action pertains solely to the separation of defective items for further review.
    </p>

    <table class="min-w-full mt-2" id="productTable">
        <thead>
            <tr>
                <th class="px-6 py-2 bg-gray-100 text-left">Reference ID</th>
                
                <!-- Conditional Variant/Non Variant Header -->
                <th class="px-6 py-2 bg-gray-100 text-left">
                 Product Label
                </th>

                <th class="px-6 py-2 bg-gray-100 text-left">Product Name</th>
                <th class="px-6 py-2 bg-gray-100 text-left">Brand Name</th>
                <th class="px-6 py-2 bg-gray-100 text-left">Quantity</th>
                <th class="px-6 py-2 bg-gray-100 text-left">Price</th>
                <th class="px-6 py-2 bg-gray-100 text-left">Total Price</th>
                <th class="px-6 py-2 bg-gray-100 text-left">Created</th>
                <th class="px-6 py-2 bg-gray-100 text-left hidden">Part ID</th>
                <th class="px-6 py-2 bg-gray-100 text-left hidden">M Part ID</th>
                <th class="px-6 py-2 bg-gray-100 text-left">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($defectiveProducts as $product)
                <tr data-created-at="{{ $product->order->created_at->format('Y-m-d') }}" data-product-name="{{ $product->product_name }}">
                    <td class="px-6 py-2 border-b">
                        @if($product->orderReference)
                            {{ $product->orderReference->reference_id }}
                        @else
                            No reference available
                        @endif
                    </td>
                    
                    <!-- Conditional cell for Variant/Non Variant -->
                    <td class="px-6 py-2 border-b 
                        @if($product->variant_id && $product->variant_id != 0)
                            bg-green-200  <!-- Example background for Variant -->
                        @else
                            bg-yellow-200  <!-- Example background for Non Variant -->
                        @endif
                    ">
                        @if($product->variant_id && $product->variant_id != 0)
                            Variant
                        @else
                            Non Variant
                        @endif
                    </td>
                    <td class="px-6 py-2 border-b">{{ $product->product_name }}</td>
                    <td class="px-6 py-2 border-b">{{ $product->brand_name }}</td>
                    <td class="px-6 py-2 border-b">{{ $product->quantity }}</td>
                    <td class="px-6 py-2 border-b">${{ number_format($product->price, 2) }}</td>
                    <td class="px-6 py-2 border-b">${{ number_format($product->total_price, 2) }}</td>
                    <td class="px-6 py-2 border-b">
                        {{ $product->order->created_at->format('M d, Y H:i:s') }}
                    </td>
                    <td class="px-6 py-2 border-b hidden">{{ $product->part_id }}</td>
                    <td class="px-6 py-2 border-b hidden">{{ $product->m_part_id }}</td>
                    <td class="px-6 py-2 border-b text-sm">
                        @if($product->product_status === 'defective-product')
                            <span class="bg-red-500 text-white rounded-md px-2 py-1">Defective</span>
                        @else
                            {{ $product->product_status }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $defectiveProducts->links() }}
    </div>
    
</div>

<script>
    // Filter table based on search bar and date filter
    document.getElementById('searchBar').addEventListener('input', filterTable);
    document.getElementById('dateFilter').addEventListener('input', filterTable);

    function filterTable() {
        const searchValue = document.getElementById('searchBar').value.toLowerCase();
        const dateValue = document.getElementById('dateFilter').value;
        const rows = document.querySelectorAll('#productTable tbody tr');

        rows.forEach(row => {
            const productName = row.getAttribute('data-product-name').toLowerCase();
            const createdAt = row.getAttribute('data-created-at');
            
            // Filter by both search and date
            const matchesSearch = productName.includes(searchValue);
            const matchesDate = dateValue ? createdAt === dateValue : true;

            // Display row if it matches both search and date filter
            if (matchesSearch && matchesDate) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>

@endsection
