@extends('manager.dashboard.managerDashboard')

@section('content')
<div class="container mx-auto p-6 bg-white rounded-xl ">
    <div class="mb-4">
        <button onclick="window.history.back()" class="bg-gray-500 text-white px-3 py-1 rounded-lg hover:bg-gray-600">
            ← Back
        </button>
    </div>

    <h2 class="text-2xl font-bold mb-4">View Brands</h2>

    
    @if(session('success'))
        <div id="success-alert" class="mb-4 p-4 bg-green-100 text-green-700 border border-green-400 rounded-lg">
            {{ session('success') }}
        </div>
        <script>
            setTimeout(() => document.getElementById('success-alert').style.display = 'none', 3000);
        </script>
    @endif

    <div class="mb-4">
        <input type="text" id="search" placeholder="Search brands..." class="w-full border border-gray-300 rounded-lg p-2">
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-300 rounded-lg ">
            <thead>
                <tr>
                    <th class="px-1 py-1 border"></th>
                    <th class="px-1 py-1 border"></th>
                    <th class="px-1 py-1 border">Category</th>
                    <th class="px-1 py-1 border">Brand Name</th>
                    <th class="px-1 py-1 border">Status</th>
                </tr>
            </thead>
            <tbody id="brandTableBody">
                @forelse($brands as $index => $brand)
                <tr class="text-center border brand-row">
                    <td class="px-1 py-1 border"></td>
                    <td class="px-1 py-1 border text-center">
                        @if($brand->brand_image)
                            <img src="{{ asset('product-images/' . $brand->brand_image) }}" alt="Brand Image" class="w-16 h-16 rounded mx-auto">
                        @else
                            No Image
                        @endif
                    </td>
                    <td class="px-1 py-1 border brand-name">{{ $brand->brand_name }}</td>
                    <td class="px-1 py-1 border category-name">
                        {{ $brand->category ? $brand->category->category_name : 'N/A' }}
                    </td>
                
                    <td class="px-1 py-1 border">
                        <span class="px-1 py-1 rounded-lg text-white {{ strtolower($brand->status) === 'active' ? 'bg-green-600' : 'bg-red-600' }}">
                            {{ $brand->status }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-4">No brands available.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('search');
        const tableRows = document.querySelectorAll('.brand-row');

        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase();

            tableRows.forEach(row => {
                const brandName = row.querySelector('.brand-name').textContent.toLowerCase();
                const categoryName = row.querySelector('.category-name').textContent.toLowerCase();

                if (brandName.includes(query) || categoryName.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
</script>

@endsection

@section('scripts')

@endsection
