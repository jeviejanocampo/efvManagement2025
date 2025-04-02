@extends('staff.dashboard.StaffMain')

@section('content')
<div class="bg-white p-4 rounded-md" style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">

    <a href="{{ url()->previous() }}" class="text-gray-600 hover:text-gray-900 text-lg mb-4 flex items-center">
        <i class="fas fa-arrow-left mr-2"></i> Back
    </a>

    <h1 class="text-5xl font-semibold">Edit Product Details</h1>

    <!-- Product Edit Form -->
    <form action="{{ route('update.order.details.preorder') }}" method="POST">
        @csrf

        <!-- Display multiple products in 2 columns -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-4" id="products-container">

            @foreach ($orderDetails as $index => $detail)
                <div class="bg-white p-4 border rounded-md shadow-sm" id="product-{{ $detail->order_detail_id }}">
                    <h3 class="font-semibold text-lg mb-4">Product {{ $index + 1 }}</h3>

                    <input type="hidden" name="order_id" value="{{ $orderDetails->first()->order_id }}">

                    <!-- Hidden Model ID for Reference -->
                    <input name="model_id[{{ $detail->order_detail_id }}]" value="{{ $detail->model_id }}">

                    <!-- Product Name -->
                    <div class="mt-4">
                        <label for="product_name_{{ $detail->order_detail_id }}" class="block text-sm font-medium text-gray-700">Product Name</label>
                        <input type="text" name="product_name[{{ $detail->order_detail_id }}]" id="product_name_{{ $detail->order_detail_id }}" class="mt-1 block w-full px-4 py-2 border rounded-md" value="{{ $detail->product_name }}">
                    </div>

                    <!-- Brand Name -->
                    <div class="mt-4">
                        <label for="brand_name_{{ $detail->order_detail_id }}" class="block text-sm font-medium text-gray-700">Brand</label>
                        <input type="text" name="brand_name[{{ $detail->order_detail_id }}]" id="brand_name_{{ $detail->order_detail_id }}" class="mt-1 block w-full px-4 py-2 border rounded-md" value="{{ $detail->brand_name }}">
                    </div>

                    <!-- Quantity -->
                    <div class="mt-4">
                        <label for="quantity_{{ $detail->order_detail_id }}" class="block text-sm font-medium text-gray-700">Quantity</label>
                        <input type="number" name="quantity[{{ $detail->order_detail_id }}]" id="quantity_{{ $detail->order_detail_id }}" class="mt-1 block w-full px-4 py-2 border rounded-md" value="{{ $detail->quantity }}" data-price="{{ $detail->price }}" data-order-detail-id="{{ $detail->order_detail_id }}" oninput="updateSubtotal(this)">
                    </div>

                    <!-- Price (Read-only) -->
                    <div class="mt-4">
                        <label for="price_{{ $detail->order_detail_id }}" class="block text-sm font-medium text-gray-700">Unit Price</label>
                        <input type="text" name="price[{{ $detail->order_detail_id }}]" id="price_{{ $detail->order_detail_id }}" class="mt-1 block w-full px-4 py-2 border rounded-md" value="₱{{ number_format($detail->price, 2) }}" readonly>
                    </div>

                    <!-- Subtotal (Read-only) -->
                    <div class="mt-4">
                        <label for="subtotal_{{ $detail->order_detail_id }}" class="block text-sm font-medium text-gray-700">Subtotal</label>
                        <input type="text" name="subtotal[{{ $detail->order_detail_id }}]" id="subtotal_{{ $detail->order_detail_id }}" class="mt-1 block w-full px-4 py-2 border rounded-md" value="₱{{ number_format($detail->total_price, 2) }}" readonly>
                    </div>
                </div>
            @endforeach

        </div>

        <!-- Total Amount to Pay -->
        <div class="mt-6 flex justify-between">
            <label for="total_amount" class="text-lg font-medium">Total Amount to Pay</label>
            <input type="text" id="total_amount" name="total_amount" class="px-4 py-2 border rounded-md" value="₱{{ number_format($orderDetails->sum('total_price'), 2) }}" readonly>
        </div>

        <!-- Save Button -->
        <div class="mt-6">
            <button type="submit" class="bg-green-500 text-white px-6 py-3 rounded-md text-lg hover:bg-green-600 transition">
                Save
            </button>
        </div>
    </form>
</div>

<script>
        // Update the subtotal when quantity is changed
        function updateSubtotal(inputElement) {
            let quantity = parseInt(inputElement.value);
            let unitPrice = parseFloat(inputElement.getAttribute('data-price'));
            let orderDetailId = inputElement.getAttribute('data-order-detail-id');
            
            // Ensure quantity and price are numbers before calculating
            if (isNaN(quantity) || isNaN(unitPrice)) {
                return;
            }
            
            let subtotal = (quantity * unitPrice).toFixed(2);
            
            // Update the specific product subtotal field
            document.getElementById('subtotal_' + orderDetailId).value = '₱' + subtotal;
            
            // Recalculate the total amount to pay
            updateTotalAmount();
        }

        // Calculate the total amount to pay for all products
        function updateTotalAmount() {
            let totalAmount = 0;
            let subtotalFields = document.querySelectorAll('input[id^="subtotal_"]');

            // Loop through all the subtotal fields to sum up the total
            subtotalFields.forEach(function(field) {
                let subtotal = parseFloat(field.value.replace('₱', '').replace(',', ''));
                if (!isNaN(subtotal)) {
                    totalAmount += subtotal;
                }
            });

            // Update the total amount input field
            document.getElementById('total_amount').value = '₱' + totalAmount.toFixed(2);
        }
        
        // Initialize the total amount on page load
        window.onload = updateTotalAmount;
</script>

<script>
    // Add a listener to handle form submission
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent default form submission
        
        // Create FormData object to send the form data
        const formData = new FormData(form);
        
        fetch("{{ route('update.order.details.preorder') }}", {
            method: 'POST',
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            if (data.message === 'Order details updated successfully.') {
                alert("Order updated successfully!");
                window.location.href = "{{ url()->current() }}"; // Reload the page to reflect changes
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => {
            alert("Error: " + error.message);
        });
    });
</script>

@if(session('success'))
    <script>
        alert("{{ session('success') }}");
    </script>
@endif

@if(session('error'))
    <script>
        alert("{{ session('error') }}");
    </script>
@endif

@endsection
