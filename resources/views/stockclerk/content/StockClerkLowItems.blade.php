@extends('stockclerk.dashboard.stockClerkDashboard')

@section('content')

<div class="container mx-auto max-w-full p-4 bg-white" style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Low Stock Items</h1>

        <!-- JS search input -->
        <input type="text" id="searchInput" placeholder="Search..." 
               class="border border-gray-300 rounded-md px-3 py-1 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" />
    </div>

    <table class="w-full table-auto text-sm border" id="lowItemsTable">
        <thead>
            <tr class="bg-gray-200 text-left">
                <th class="px-4 py-2">Image</th>
                <th class="px-4 py-2">Type</th>
                <th class="px-4 py-2">Name</th>
                <th class="px-4 py-2">Brand</th>
                <th class="px-4 py-2">Price</th>
                <th class="px-4 py-2">Stocks</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                <tr class="border-b">
                      <td class="px-4 py-2">
                        <img src="{{ asset('product-images/' . $item['image']) }}" alt="Image" class="w-24 h-24 object-cover rounded">
                    </td>
                    <td class="px-4 py-2">{{ $item['type'] }}</td>
                    <td class="px-4 py-2">{{ $item['name'] }}</td>
                    <td class="px-4 py-2">{{ $item['brand'] }}</td>
                    <td class="px-4 py-2">₱{{ number_format($item['price'], 2) }}</td>
                    <td class="px-4 py-2 font-bold text-red-600">{{ $item['stocks_quantity'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $items->links() }}
    </div>
</div>

<!-- JS for client-side search -->
<script>
    document.getElementById('searchInput').addEventListener('keyup', function () {
        let searchValue = this.value.toLowerCase();
        let rows = document.querySelectorAll('#lowItemsTable tbody tr');

        rows.forEach(row => {
            let rowText = row.innerText.toLowerCase();
            row.style.display = rowText.includes(searchValue) ? '' : 'none';
        });
    });
</script>

@endsection
