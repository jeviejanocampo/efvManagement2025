@extends('stockclerk.dashboard.stockClerkDashboard')

@section('content') 
<style>
    td {
        text-align: center;
    }
</style>
<div class="p-4 rounded-xl">
    <a href="{{ url('stockclerk/overview') }}" 
        class="bg-gray-800 text-white px-5 py-1 rounded-full hover:bg-gray-700 mb-5 items-center gap-2">
        <i class="fas fa-arrow-left"></i> 
    </a>

    <div style="margin-top: 12px">
        @if($order->status === 'Cancelled')
         <p class="text-white bg-red-500 px-5 py-2 rounded-md text-center font-bold text-lg">CANCELLED </p>
         @elseif($order->status === 'Completed')
         <p class="text-white bg-green-500 px-5 py-2 rounded-md text-center font-bold text-lg">ORDER COMPLETED </p>
         @else
         <p></p>
        @endif
    </div>


    <!-- Order Status Dropdown -->
    <div class="flex justify-between items-center mt-4 bg-white p-4 rounded-md" style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">
        <h1 style="font-size: 34px; font-weight: bold">ORDER DETAILS</h1>

        <p style="display: none">Logged in User ID: {{ Auth::id() }}</p>
        <!-- Label and Dropdown for Edit Status for the whole order -->
        <div class="flex items-center">
            <!-- <label for="order_status" class="mr-3 text-md">Edit Status:</label> -->
            <select class="bg-gray-100 text-black-200 px-5 py-2 rounded-md" name="order_status" id="order_status" onchange="updateOrderStatus({{ $order->order_id }})">
                <option value="pending">Pending</option>
                <option value="Ready to Pickup">Ready to Pickup</option>
                <option value="In Process">In Process</option>
                <!-- <option value="Completed">Completed</option>
                <option value="Cancelled">Cancelled</option> -->
            </select>
        </div>
    </div>

    <div class ="bg-white p-4 mt-6 rounded-md" style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">
        <div class="flex justify-between items-center">
            @php
                $latestOrderDetail = $orderDetails->last(); // Get the last row (latest order detail)
                $referenceId = request()->query('reference_id'); // Retrieve reference_id from URL

                if ($referenceId) {
                    $formattedRefId = substr($referenceId, 0, 3) . '-' . substr($referenceId, 3, -1) . substr($referenceId, -1);
                } else {
                    $formattedRefId = 'N/A';
                }
            @endphp

            @if ($latestOrderDetail)
                <p style="font-size: 28px; font-weight: 700;">
                    REFERENCE ID: {{ $formattedRefId }}-ORD000{{ $order->order_id }}
                </p>
            @else
                <p style="font-size: 28px; font-weight: 700;">ORDER ID: N/A</p>
            @endif
            <p class="text-md">
                <strong>Status: </strong>
                <span class="
                    rounded-lg 
                    @if($order->status === 'Pending')
                        bg-yellow-500
                        text-white
                        m-1
                    @elseif($order->status === 'In Process')
                         bg-orange-500
                        text-white
                        m-1
                    @elseif($order->status === 'Ready to Pickup')
                        bg-green-500
                        text-white
                        m-1
                    @elseif($order->status === 'Completed')
                        bg-green-500
                        text-white
                        m-1
                    @elseif($order->status === 'Cancelled')
                        text-red-700
                        m-1
                    @else
                        text-gray-700
                        m-1
                    @endif
                    px-2 py-1
                ">
                    {{ ucfirst($order->status) }}
                </span>
            </p>
        </div>
        <p class="text-sm text-gray-600 border-b border-gray-200 pb-1">
            Created At: {{ \Carbon\Carbon::parse($order->created_at)->format('F j, Y - h:i A') }}
        </p>
        <div class="mt-4 space-y-4">
            <p class="text-base">
                <a href="#" class="text-white bg-blue-700 px-2 py-1 rounded-md" onclick="openModal({{ $order->user_id }})">
                    View User Details
                </a>
            </p>
            <p style="font-size: 15px">TOTAL ITEMS: {{ $order->total_items }}</p> 
            <p style="font-size: 15px">PAYMENT METHOD: {{ $order->payment_method }}</p> 
        </div>
    </div>

    <!-- Order Details Table -->
    <div class ="bg-white p-4 mt-6 rounded-md" style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">
        <h3 class="text-2xl font-semibold border-b border-gray-200">Product Details</h3>
        <div class="text-gray-500 italic text-sm m-4">
            Note: For the pre-orders products, edit status if ready to pick up status
        </div>
        <table class="table-auto w-full border-collapse mt-4">
            <thead>
                <tr class="bg-white">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-300 px-2 py-1">Status</th>
                        <th class="border border-gray-300 px-2 py-1"></th>
                        <th class="border border-gray-300 px-2 py-1">Product Name</th>
                        <th class="border border-gray-300 px-2 py-1">Brand</th>
                        <th class="border border-gray-300 px-2 py-1">Quantity</th>
                        <th class="border border-gray-300 px-2 py-1">Unit Price</th>
                        <th class="border border-gray-300 px-2 py-1">SubTotal</th>
                    </tr>
            </thead>
            <tbody>
                @foreach ($orderDetails as $detail)
                    <tr class="border border-white  ">
                    <td class=" px-5 py-1">
                        <!-- Add badge based on product status -->
                        @if($order->status !== 'Completed' && $order->status !== 'Cancelled')
                            @if($detail->product_status === 'pending')
                                <span class="bg-red-500 text-white px-5 py-1 rounded-full text-sm">Reserved</span>
                            @elseif($detail->product_status === 'pre-order')
                                <span class="bg-blue-500 text-white px-5 py-1 rounded-full text-sm" style="white-space: nowrap;">Pre Ordered</span>
                            @elseif($detail->product_status === 'Ready to Pickup')
                                <span class="bg-blue-500 text-white px-5 py-1 rounded-full text-sm" style="white-space: nowrap;">Ready to Pickup (Not yet paid)</span>
                            @elseif($detail->product_status === 'Cancelled')
                                <span class="bg-gray-500 text-white px-5 py-1 rounded-full text-sm" style="white-space: nowrap;">Cancelled</span>
                            @else
                                <span class="bg-black text-white px-5 py-1 rounded-full text-sm" style="white-space: nowrap;">Unknown</span>
                            @endif
                        @endif
                        <span style="display: none">{{ $detail->order_detail_id }}</span>
                    </td>
                    <td class=" px-5 py-1">
                        @if ($detail->model_image)
                            <img src="{{ asset('product-images/' . $detail->model_image) }}" alt="{{ $detail->product_name }}" width="200">
                        @else
                            <span>No Image</span>
                        @endif
                    </td>
                    <td class=" px-5 py-1" style="display: none">{{ $detail->model_id }}</td>
                    <td class=" px-5 py-1">{{ $detail->product_name }}</td>
                    <td class=" px-5 py-1">{{ $detail->brand_name }}</td>
                    <td class=" px-5 py-1">{{ $detail->quantity }}x</td>
                    <td class="px-5 py-1">₱{{ number_format($detail->price, 2) }}</td>
                    <td class="px-5 py-1">₱{{ number_format($detail->total_price, 2) }}</td>
                    <td class=" px-5 py-1">
                        <!-- Conditional for Edit Status Dropdown -->
                        @if($detail->product_status !== 'Completed' && $detail->product_status !== 'pending')
                            <div class="mt-2">
                            <!-- <label for="edit_status_{{ $detail->order_detail_id }}" class="text-sm mr-2">Edit Status:</label> -->
                            <select class="bg-gray-100 text-gray-700 px-5 py-2 rounded-md text-sm" name="edit_status_{{ $detail->order_detail_id }}" id="edit_status_{{ $detail->order_detail_id }}" onchange="updateProductStatus({{ $detail->order_detail_id }})">
                                <option value="pending" {{ $detail->product_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="In Process" {{ $detail->product_status === 'Process' ? 'selected' : '' }}>In Process</option>
                                <!-- <option value="Ready to Pickup" {{ $detail->product_status === 'Ready to Pickup' ? 'selected' : '' }}>Ready to Pickup</option>
                                <option value="Cancelled" {{ $detail->product_status === 'Cancelled' ? 'selected' : '' }}>Cancelled</option> -->
                            </select>
                        </div>
                    @else
                        <span class="text-sm text-gray-500" id="status_span_{{ $detail->order_detail_id }}" style="display:none">Status is Pending</span>
                    @endif
                    </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="bg-white p-4 mt-6 rounded-md" style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">
        @if($order->status === 'Completed')
            <p>
            </p>
        @elseif($order->status === 'Cancelled')
            <p>
            </p>
        @else
            <p style="font-size: 28px; font-weight: bold; text-align: right;">
                Total To Pay: ₱ {{ number_format ( $order->total_price, 2 ) }}
            </p> 
        @endif
    </div>

    <!-- User Details Modal -->
    <div id="userModal" class="fixed inset-0 flex justify-center items-center bg-black bg-opacity-50 hidden">
        <div class="bg-white p-6 rounded-lg w-1/3">
            <h2 class="text-xl font-semibold mb-4">User Details</h2>
            <div id="userDetailsContent"></div>
            <button onclick="closeModal()" class="mt-4 bg-gray-800 text-white px-5 py-2 rounded-md">Close</button>
        </div>
    </div>


</div>

<script>
    function openModal(userId) {
        // Fetch user details using the userId
        fetch(`/users/${userId}`)
            .then(response => response.json())
            .then(data => {
                const userDetailsContent = document.getElementById('userDetailsContent');
                userDetailsContent.innerHTML = `
                    <p><strong>Full Name:</strong> ${data.full_name}</p>
                    <p><strong>Email:</strong> ${data.email}</p>
                    <p><strong>Phone Number:</strong> ${data.phone_number}</p>
                    <p><strong>Address:</strong> ${data.address}</p>
                    <p><strong>City:</strong> ${data.city}</p>
                    <p><strong>Status:</strong> ${data.status}</p>
                `;
                document.getElementById('userModal').classList.remove('hidden');
            });
    }

    function closeModal() {
        document.getElementById('userModal').classList.add('hidden');
    }

    function updateOrderStatus(orderId) {
        const newStatus = document.getElementById('order_status').value;

        // Confirm the action
        const confirmUpdate = confirm(`Are you sure you want to update the status to "${newStatus}"?`);

        if (confirmUpdate) {
            // Send the update request via AJAX
            fetch(`/orders/update-status/${orderId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' // Laravel CSRF token
                },
                body: JSON.stringify({
                    status: newStatus
                })
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message); // Display confirmation message from server
                if (data.success) {
                    // Optionally, you can update the page with the new status
                    document.getElementById('order_status').value = newStatus; 

                    // Refresh the page to reflect changes
                    window.location.reload();
                }
            })
            .catch(error => {
                alert("An error occurred while updating the status.");
            });
        }
    }

    function updateProductStatus(orderDetailId) {
        const newStatus = document.getElementById('edit_status_' + orderDetailId).value;

        // Confirm the action
        const confirmUpdate = confirm(`Are you sure you want to update the product status to "${newStatus}"?`);

        if (confirmUpdate) {
            // Send the update request via AJAX
            fetch(`/orders/update-product-status/${orderDetailId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' // Laravel CSRF token
                },
                body: JSON.stringify({
                    status: newStatus
                })
            })
            .then(response => {
                // Check if the response is OK
                if (!response.ok) {
                    throw new Error('Network response was not ok.');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert(data.message); // Display success message

                    // Optional: Update the DOM element with the new status
                    const statusSpan = document.getElementById('status_span_' + orderDetailId);
                    if (statusSpan) {
                        statusSpan.innerText = newStatus.charAt(0).toUpperCase() + newStatus.slice(1); // Capitalize first letter
                    }

                    // Refresh the page
                    location.reload();
                } else {
                    alert(data.message); // Display error message
                }
            })
            .catch(error => {
                console.error("Error updating product status:", error); // Log detailed error in the console
                alert("An error occurred while updating the status. Please try again.");
            });
        }
    }
</script>

@endsection

@section('scripts')

@endsection
