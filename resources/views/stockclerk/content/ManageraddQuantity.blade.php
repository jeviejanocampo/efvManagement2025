@extends('stockclerk.dashboard.stockClerkDashboard')

@section('content')
<div class="container mx-auto p-6 bg-white  ">
    <div class="mb-4">
        <button onclick="window.history.back()" class="bg-gray-500 text-white px-3 py-1 rounded-lg hover:bg-gray-600">
            ← Back
        </button>
    </div>

    <h2 class="text-2xl font-bold mb-4">Add Quantity</h2>

    <form action="{{ route('manager.products.store') }}" method="POST" enctype="multipart/form-data">
    @csrf



    <!-- Submit Button -->
    <div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            Confirm 
        </button>
    </div>
</form>

</div>

@endsection
