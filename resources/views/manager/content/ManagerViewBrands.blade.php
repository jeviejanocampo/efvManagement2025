@extends('manager.dashboard.managerDashboard')

@section('content')
<div class="container mx-auto p-6 bg-white " style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">
    <!-- <div class="mb-4">
        <button onclick="window.history.back()" class="bg-gray-500 text-white px-3 py-1 rounded-lg hover:bg-gray-600">
            ← Back
        </button>
    </div> -->

    <div class="flex items-center justify-between mb-4">
        <h2 class="text-3xl font-bold">View Brands</h2>
        <a href="{{ route('manager.add.brand') }}" 
        class="bg-violet-800 text-white px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-violet-900">
            <i class="fas fa-plus"></i> Add Brand
        </a>
    </div>


    @if(session('success'))
        <div id="success-alert" class="mb-4 p-4 bg-green-100 text-green-700 border-b border-b-green-400 rounded-lg">
            {{ session('success') }}
        </div>
        <script>
            setTimeout(() => document.getElementById('success-alert').style.display = 'none', 3000);
        </script>
    @endif

    <div class="mb-4">
        <input type="text" id="search" placeholder="Search brands..." class="w-full border rounded-lg p-2 border-gray">
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border-b-b rounded-lg ">
            <thead class="bg-gray-100">
                    <tr class="bg-gray-100">
                    <!-- <th class="px-2 py-1 border-b">Image</th> -->
                    <th class="px-2 py-1 border-b"></th>
                    <th class="px-2 py-1 border-b">Brand Name</th>
                    <th class="px-2 py-1 border-b">Category</th>
                    <th class="px-2 py-1 border-b">Status</th>
                    <th class="px-2 py-1 border-b">Action</th> 
                </tr>
            </thead>
            <tbody id="brandTableBody">
                @forelse($brands as $index => $brand)
                <tr class="text-center border-b brand-row">
                    <!-- <td class="px-2 py-1 border-b">0000{{ $brand->brand_id }}</td> -->
                    <td class="px-2 py-1 border-b text-center">
                        @if($brand->brand_image)
                            <img src="{{ asset('product-images/' . $brand->brand_image) }}" alt="Brand Image" class="w-16 h-16 rounded mx-auto">
                        @else
                            No Image
                        @endif
                    </td>
                    <td class="px-2 py-1 border-b brand-name">{{ $brand->brand_name }}</td>
                    <td class="px-2 py-1 border-b category-name">
                        {{ $brand->category ? $brand->category->category_name : 'N/A' }}
                    </td>         
                    <td class="px-2 py-1 border-b">
                        <span class="px-2 py-1 rounded-lg text-white {{ strtolower($brand->status) === 'active' ? 'bg-green-600' : 'bg-red-600' }}">
                            {{ $brand->status }}
                        </span>
                    </td>
                    <td class="px-2 py-1 border-b">
                        <div class="flex justify-center space-x-2">
                            <a href="{{ route('stockclerk.edit.brand', ['brand_id' => $brand->brand_id]) }}" 
                            class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-edit"></i>
                            </a>
                            <Delete Button>
                            <!-- <form action="{{ route('stockclerk.delete.brand', ['brand_id' => $brand->brand_id]) }}" method="POST" class="inline delete-brand-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 delete-brand-btn">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form> -->
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4">No brands available.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    document.querySelectorAll('.delete-brand-form').forEach(form => {
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            if (confirm("Are you sure you want to delete this brand? This action cannot be undone.")) {
                this.submit();
            }
        });
    });

    @if(session('success'))
        alert("{{ session('success') }}");
    @elseif(session('error'))
        alert("{{ session('error') }}");
    @endif
</script>
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
