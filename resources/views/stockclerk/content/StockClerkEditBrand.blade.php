@extends('stockclerk.dashboard.stockClerkDashboard')

@section('content')
<div class="container mx-auto p-6 bg-white rounded-xl" style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">
    <div class="mb-4">
        <button onclick="window.history.back()" class="bg-gray-500 text-white px-3 py-1 rounded-lg hover:bg-gray-600">
            ← Back
        </button>
    </div>

    <h2 class="text-2xl font-bold mb-4">Edit Brand</h2>

    <form id="editBrandForm" action="{{ route('stockclerk.update.brand', ['brand_id' => $brand->brand_id]) }}" method="POST">
        @csrf
        <div class="mb-4">
            <label class="block font-semibold">Brand Name</label>
            <input type="text" name="brand_name" value="{{ $brand->brand_name }}" class="w-full border border-gray-300 p-2 rounded-lg" required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Category</label>
            <input type="text" name="category_name" value="{{ $brand->category ? $brand->category->category_name : 'N/A' }}" class="w-full border border-gray-300 p-2 rounded-lg" required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold">Status</label>
            <select name="status" class="w-full border border-gray-300 p-2 rounded-lg" required>
                <option value="Active" {{ strtolower($brand->status) === 'active' ? 'selected' : '' }}>Active</option>
                <option value="Inactive" {{ strtolower($brand->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <button type="submit" class="bg-violet-800 text-white px-4 py-2 rounded-lg hover:bg-violet-900">
            Save Changes
        </button>
    </form>

<script>
    document.getElementById('editBrandForm').addEventListener('submit', function(event) {
        event.preventDefault();
        if (confirm("Are you sure you want to update this brand?")) {
            this.submit();
        }
    });

    @if(session('success'))
        alert("{{ session('success') }}");
    @elseif(session('error'))
        alert("{{ session('error') }}");
    @endif
</script>

</div>
@endsection
