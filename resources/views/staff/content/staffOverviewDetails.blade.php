@extends('staff.dashboard.staffMain')

@section('content') 
<style>
    td {
     text-align: center;
     font-size: 12px;
    }
</style>

@if (session('success'))
    <script>
        alert('{{ session('success') }}');
    </script>
@endif

@if ($errors->any())
    <script>
        alert('Error: {{ $errors->first() }}');
    </script>
@endif

@php
    use Illuminate\Support\Str;
@endphp


<div">
    <a href="{{ url('staff/overview') }}" 
    class="bg-gray-800 text-white px-2 py-1 rounded-full hover:bg-gray-700 mb-5 items-center gap-2">
    <i class="fas fa-arrow-left"></i> 
    </a>


    <div style="margin-top: 12px">
        @if($order->status === 'Cancelled')
         <p class="text-white bg-red-500 px-5 py-2  text-center font-bold text-lg">CANCELLED </p>
         @elseif($order->status === 'Completed')
         <p class="text-white bg-green-500 px-5 py-2  text-center font-bold text-lg">ORDER COMPLETED </p>
         @else
         <p></p>
        @endif
    </div>


    <div class="flex justify-between items-center mt-4 bg-white p-4 " style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.1);">
      <h1 style="font-size: 24px; font-weight: bold">ORDER DETAILS</h1>

        <p style="display: none">Logged in User ID: {{ Auth::id() }}</p>
        <div class="flex items-center">
            <label for="order_status" class="mr-3 text-md">Edit Status:</label>
            <select class="bg-gray-100 text-black-200 px-5 py-2 " name="order_status" id="order_status" onchange="updateOrderStatus({{ $order->order_id }})">
                <option value="pending">Pending</option>
                <option value="Ready to Pickup">Ready to Pickup</option>
                <option value="In Process">In Process</option>
                <option value="Completed">Completed</option>
                <option value="Cancelled">Cancelled</option>
            </select>
        </div>
    </div>

    <div class ="bg-white p-4 " style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.1);">
        <div class="flex justify-between items-center">
        <!-- @php
    $latestOrderDetail = $orderDetails->last();
    $rawReferenceId = request()->query('reference_id');
    $orderId = $order->order_id ?? null; // <-- Get the order ID

    // Fetch brand_name and get first 3 characters
    $brandName = $latestOrderDetail->brand_name ?? ''; // Get the brand_name
    $brandPrefix = substr($brandName, 0, 3); // Get first 3 characters of brand_name

    $usedPartId = null;

    if ($latestOrderDetail) {
        $hasMpartId = !empty($latestOrderDetail->m_part_id);
        $hasPartId = !empty($latestOrderDetail->part_id);

        if ($hasMpartId && $hasPartId) {
            $usedPartId = $latestOrderDetail->m_part_id . '-' . $latestOrderDetail->part_id;
        } elseif ($hasMpartId) {
            $usedPartId = $latestOrderDetail->m_part_id;
        } elseif ($hasPartId) {
            $usedPartId = $latestOrderDetail->part_id;
        }
    }

    // Now decide final Reference ID
    $referenceId = null;

    if (!empty($rawReferenceId) && !Str::contains($rawReferenceId, 'null')) {
        // If reference_id exists and doesn't contain "null", use it
        $referenceId = $rawReferenceId;

        if (Str::contains($referenceId, '-ORD000')) {
            $referenceId = explode('-ORD000', $referenceId)[0];
        }
    } else {
        // Else fallback to Part ID
        $referenceId = $usedPartId;
    }

    // Add brand prefix and always append "-ORD000{order_id}"
    if (!empty($referenceId) && !empty($orderId)) {
        $formattedRefId = strtoupper($brandPrefix) . '-' . $referenceId . '-ORD000' . $orderId;
    } else {
        $formattedRefId = 'N/A';
    }
@endphp -->




            @if ($latestOrderDetail)
                <p style="font-size: 18px; font-weight: 700;">
                    REFERENCE ID: {{ $reference_id ? $reference_id : 'Not Available' }}
                </p>

                <!-- <p style="font-size: 18px; font-weight: 700;">
                    REFERENCE ID: {{ $formattedRefId }}
                </p> -->
                <!-- <p style="font-size: 18px; font-weight: 700;">
                    REFERENCE ID: {{ $formattedRefId }}-ORD000{{ $order->order_id }}
                </p> -->
            @else
                <p style="font-size: 28px; font-weight: 700;">ORDER ID: N/A</p>
            @endif
            <p class="text-md">
                <!-- <strong>Status: </strong> -->
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
        <p style="font-size: 12px">
            Created At: {{ \Carbon\Carbon::parse($order->created_at)->format('F d, Y h:i A') }}
        </p>
        <div class="mt-4 grid grid-cols-2 gap-6">
        <!-- Left Grid - User Details and Payment Method -->

        <div class="space-y-6">
            <p style="font-size: 14px">
                USER DETAILS:
                <a href="#" 
                class="bg-blue-700 text-white font-bold px-2 py-1 hover:bg-blue-700 transition"
                onclick="openModal({{ $order->user_id }})">
                    View Details
                </a>
            </p>

            <p style="font-size: 14px; padding-top: 4px;"> 
                <div class="flex items-center space-x-4">
                    PAYMENT METHOD: {{ $order->payment_method }}
                    
                    <!-- Conditional Image Display for Payment Method -->
                    @php
                        $paymentMethod = Str::lower($order->payment_method);
                    @endphp

                    @if($paymentMethod == 'gcash')
                        <img src="{{ asset('product-images/gcashlogo.png') }}" alt="GCash Logo" style="width: 52px; height: 52px; margin-left: 10px;">
                    @elseif($paymentMethod == 'pnb')
                        <img src="{{ asset('product-images/pnblogo.png') }}" alt="PNB Logo" style="width: 52px; height: 52px; margin-left: 10px;">
                    @endif

                    <!-- Edit Payment Method Icon -->
                    <span class="cursor-pointer" onclick="openPaymentMethodModal()" title="Edit Payment Method">
                        <i class="fas fa-edit text-yellow-800"></i> 
                    </span>
                </div>
            </p>

           
        </div>

        <!-- Right Grid - View Gcash/Pnb Payment Method -->
        <div class="flex justify-end items-center">
            @php
                $paymentMethod = Str::lower($order->payment_method);
            @endphp

            @if($paymentMethod == 'gcash')
                <button class="bg-blue-700 text-white font-bold px-2 py-1 hover:bg-blue-700 transition" onclick="viewGcashPayment({{ $order->order_id }})">
                    View Gcash Payment
                </button>
            @elseif($paymentMethod == 'pnb')
                <button class="bg-blue-700 text-white font-bold px-2 py-1 hover:bg-blue-700 transition" onclick="viewPnbPayment({{ $order->order_id }})">
                    View PNB Payment
                </button>
            @endif
        </div>

        <!-- Modal for Viewing Payment Images -->
        <div id="paymentModal" class="hidden fixed inset-0 flex justify-center items-center bg-black bg-opacity-50 z-50">
            <div class="bg-white p-4 rounded shadow-lg max-w-4xl w-full relative">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold">Payment Screenshots</h3>
                    <button onclick="closePaymentModal()" class="text-red-600 font-bold">Close</button>
                </div>

                <!-- SCROLLABLE IMAGES -->
                <div id="paymentImageContainer" 
                    class="flex overflow-x-auto space-x-4 p-4 scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-200" 
                    style="scrollbar-width: thin; scrollbar-color: #cbd5e0 #edf2f7;">
                    <!-- Images will be dynamically inserted here -->
                </div>
            </div>
        </div>



        <!-- Payment Method Modal -->
        <div id="paymentMethodModal" class="fixed inset-0 flex justify-center items-center bg-black bg-opacity-50 hidden">
            <div class="bg-white p-6 w-1/3">
                <h2 class="text-xl font-semibold mb-4">Edit Payment Method</h2>

                <form id="gcashPaymentForm" method="POST" action="{{ route('admin.saveGcashPayment') }}" enctype="multipart/form-data" onsubmit="return handlePaymentSubmit(event)">
                    @csrf
                    <input type="hidden" id="order_id" name="order_id" value="{{ $order->order_id }}">

                    <div class="mb-4">
                        <label for="paymentMethod" class="block text-md mb-2">Select Payment Method</label>
                        <select id="paymentMethod" name="paymentMethod" class="bg-gray-100 text-gray-700 px-5 py-2 w-full" onchange="showPaymentFields(this.value)">
                            <option value="">-- Select Method --</option>
                            <option value="gcash">GCash</option>
                            <option value="pnb">PNB</option>
                        </select>
                    </div>

                    <!-- GCash Fields -->
                    <div id="gcashFields" class="hidden">

                        <img id="gcashQRCode" src="{{ asset('product-images/gcashqrcode.webp') }}" 
                        alt="GCash QR Code" class="mb-4 w-80 h-auto">


                        <h2 class="text-lg font-semibold mb-4">Account Number: 094532445021 </h2>
                        <h2 class="text-lg font-semibold mb-4">Account Name: Antinio Efro Montero</h2>

                        <div class="mt-4">
                            <label for="gcashUpload" class="block text-md mb-2">Upload GCash Receipt</label>
                            <input type="file" id="gcashUpload" name="gcash_image" class="border p-2 w-full" />
                        </div>
                    </div>

                    <!-- PNB Fields -->
                    <div id="pnbFields" class="hidden">

                        <img id="gcashQRCode" src="{{ asset('product-images/pnbqrcode.png') }}" 
                        alt="GCash QR Code" class="mb-4 w-60 h-auto">

                        <p class="text-sm">Account Name: Eforo Volante Montero</p>
                        <p class="text-sm">Account Number: 2034201993322</p>
                        <div class="mt-4">
                            <label for="pnbUpload" class="block text-md mb-2">Upload PNB Receipt</label>
                            <input type="file" id="pnbUpload" name="pnb_image" class="border p-2 w-full" />
                        </div>
                    </div>

                    <div class="flex justify-end gap-4 mt-4">
                        <button type="button" onclick="closePaymentMethodModal()" class="bg-gray-800 text-white px-5 py-2 rounded">Cancel</button>
                        <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded">Save</button>
                    </div>
                </form>

            </div>
        </div>

    </div>

    <!-- Order Details Table -->
    <div class ="bg-white p-4 border-b border-gray pb-4 " style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.1);">

        <div class="flex justify-between items-center">
            <h3 class="text-l font-semibold">Product Details</h3>
            
            <!-- One button for the entire order -->
            <a href="{{ route('edit.product', ['order_id' => $orderDetails->first()->order_id]) }}" class="bg-black text-white px-2 py-2 text-sm hover:bg-blue-600 transition">
                Edit Product Details for Order #{{ $formattedRefId }}
            </a>

        </div>

        <div class="text-gray-500 italic text-sm m-4">
            Note: For the pre-orders products, edit status if ready to pick up status
        </div>
        
        <table class="table-auto w-full border-collapse mt-4">
            <thead>
                <thead>
                    <tr class="bg-gray-50 text-sm">
                        <th class="border-b border-gray-300 px-2 py-1"></th>
                        <th class="border-b border-gray-300 px-2 py-1">Product Name</th>
                        <th class="border-b border-gray-300 px-2 py-1">Brand</th>
                        <th class="border-b border-gray-300 px-2 py-1">Quantity</th>
                        <th class="border-b border-gray-300 px-2 py-1">Unit Price</th>
                        <th class="border-b border-gray-300 px-2 py-1">SubTotal</th>
                        @if($order->status !== 'Cancelled' && $order->status !== 'Completed')
                            <th class="border-b border-gray-300 px-2 py-1">Status</th>
                            <th class="border-b border-gray-300 px-2 py-1">Action</th>
                        @endif
                    </tr>
            </thead>
            <tbody>
                @foreach ($orderDetails as $detail)
                    <tr class="border border-white  ">
                    <td class=" px-5 py-1">
                        @if ($detail->model_image)
                            <img src="{{ asset('product-images/' . $detail->model_image) }}" alt="{{ $detail->product_name }}" width="100">
                        @else
                            <span>No Image</span>
                        @endif
                    </td>
                    <td class=" px-5 py-1" style="display: none">{{ $detail->model_id }}</td>
                    <td class=" px-5 py-1">{{ $detail->product_name }}</td>
                    <td class=" px-5 py-1">{{ $detail->brand_name }}</td>
                    <td class=" px-5 py-1">{{ $detail->quantity }}x</td>
                    <td class="px-5 py-1">₱{{ number_format($detail->price, 2) }}</td>
                    <td class="px-5 py-1">₱{{ number_format($detail->quantity * $detail->price, 2) }}</td>
                    <td class="px-5 py-1">
                        @if($order->status !== 'Cancelled')
                        @if($detail->product_status === 'pending')
                                <span class="bg-red-500 text-white px-5 py-1 rounded-full text-sm">Reserved</span>
                            @elseif($detail->product_status === 'pre-order')
                                <span class="bg-blue-500 text-white px-5 py-1 rounded-full text-sm" style="white-space: nowrap;">Pre Ordered</span>
                            @elseif($detail->product_status === 'to be refunded')
                                <span class="bg-violet-800 text-white px-5 py-1 rounded-full text-sm" style="white-space: nowrap;">To be removed</span>
                            @elseif($detail->product_status === 'refunded')
                                <span class="bg-violet-500 text-white px-5 py-1 rounded-full text-sm" style="white-space: nowrap;">Refunded</span>
                            @elseif($detail->product_status === 'Ready to Pickup')
                                <span class="bg-blue-500 text-white px-5 py-1 rounded-full text-sm" style="white-space: nowrap;">Ready to Pickup (Not yet paid)</span>
                            @elseif($detail->product_status === 'Completed')
                                <span class="bg-green-500 text-white px-5 py-1 rounded-full text-sm" style="white-space: nowrap;">Purchased</span>
                            @else
                                <span class="bg-black text-white px-5 py-1 rounded-full text-sm" style="white-space: nowrap;">Unknown</span>
                            @endif
                        @endif
                        <span style="display: none">{{ $detail->order_detail_id }}</span>
                    </td>
                    <td class=" px-5 py-1">
                        <!-- Conditional for Edit Status Dropdown -->
                        @if($detail->product_status !== 'Completed' && $detail->product_status !== 'pending' && $detail->product_status !== 'refunded')
                        <div class="mt-2">
                                <select class="bg-gray-100 text-gray-700 px-5 py-2  text-sm" name="edit_status_{{ $detail->order_detail_id }}" id="edit_status_{{ $detail->order_detail_id }}" onchange="updateProductStatus({{ $detail->order_detail_id }})">
                                    <option value="pending" {{ $detail->product_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="In Process" {{ $detail->product_status === 'In Process' ? 'selected' : '' }}>In Process</option>
                                    <option value="pending" {{ $detail->product_status === 'Ready to Pickup' ? 'selected' : '' }}>Ready to Pickup</option>
                                    <option value="Completed" {{ $detail->product_status === 'Completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="refunded" {{ $detail->product_status === 'refunded' ? 'selected' : '' }}>Confirmed Removed</option>
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

    <div class="bg-white p-6 shadow-lg rounded-lg">
        <div class="flex justify-end">

            <!-- Content inside the parent div -->
            <div class="space-y-4 gap-10">

                <div class="text-2xl font-bold text-black pb-2">Overview</div>


                <div class="flex justify-between">
                    <p class="text-lg font-semibold">
                    <span class="text-black mr-28"> TOTAL ITEMS: </span>
                    <span class="text-gray-700"> {{ $order->total_items }}</span>
                    </p>
                </div>

                @if($order->status === 'Completed')
                    <div class="flex justify-between">
                        <p class="text-lg font-semibold">
                        <span class="text-black mr-24"> Total: </span>
                        <span class="text-gray-700"> ₱ {{ number_format($order->total_price, 2) }}</span>
                        </p>
                    </div>
                @elseif($order->status === 'Cancelled')
                    <div class="flex justify-between">
                        <p class="text-lg">
                            (Cancelled)
                        </p>
                    </div>
                @else
                    <div class="flex justify-between">
                        <p class="text-lg font-semibold">
                        <span class="text-gray-400 mr-24">   Total To Pay: </span>
                        <span class="text-gray-700"> ₱ {{ number_format($order->total_price, 2) }}</span>
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>




    <!-- User Details Modal -->
    <div id="userModal" class="fixed inset-0 flex justify-center items-center bg-black bg-opacity-50 hidden">
        <div class="bg-white p-6 rounded-lg w-1/3">
            <h2 class="text-xl font-semibold mb-4">User Details</h2>
            <div id="userDetailsContent"></div>
            <button onclick="closeModal()" class="mt-4 bg-gray-800 text-white px-5 py-2 ">Close</button>
        </div>
    </div>


</div>

<script>
    function viewGcashPayment(order_id) {
        fetchPaymentImage(order_id, 'gcash');
    }

    function viewPnbPayment(order_id) {
        fetchPaymentImage(order_id, 'pnb');
    }

    function closePaymentModal() {
        document.getElementById('paymentModal').classList.add('hidden');
    }


    function fetchPaymentImage(order_id, payment_method) {
        fetch(`/staff/payment-image/${order_id}/${payment_method}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let container = document.getElementById('paymentImageContainer');
                    container.innerHTML = ''; // Clear old images first

                    data.images.forEach(image => {
                        let img = document.createElement('img');
                        img.src = `/onlinereceipts/${image}`;
                        img.alt = 'Payment Image';
                        img.classList.add('w-80', 'h-80', 'object-contain', 'm-2', 'border', 'rounded');
                        container.appendChild(img);
                    });

                    document.getElementById('paymentModal').classList.remove('hidden');
                } else {
                    alert('No payment images found.');
                }
            });
    }

</script>

<script>
    function openPaymentMethodModal() {
        document.getElementById('paymentMethodModal').classList.remove('hidden');
    }

    function closePaymentMethodModal() {
        document.getElementById('paymentMethodModal').classList.add('hidden');
    }

    function showPaymentFields(paymentMethod) {
        document.getElementById('gcashFields').classList.add('hidden');
        document.getElementById('pnbFields').classList.add('hidden');

        let form = document.getElementById('gcashPaymentForm');

        if (paymentMethod === 'gcash') {
            document.getElementById('gcashFields').classList.remove('hidden');
            form.action = "{{ route('admin.saveGcashPayment') }}";
        } else if (paymentMethod === 'pnb') {
            document.getElementById('pnbFields').classList.remove('hidden');
            form.action = "{{ route('admin.savePnbPayment') }}";
        }
    }


    function handlePaymentSubmit(event) {
        event.preventDefault(); // Stop normal form submit

        let paymentMethod = document.getElementById('paymentMethod').value;
        let gcashUpload = document.getElementById('gcashUpload').files.length;
        let pnbUpload = document.getElementById('pnbUpload').files.length;

        if (!paymentMethod) {
            alert('Please select a payment method.');
            return false;
        }

        if (paymentMethod === 'gcash' && gcashUpload === 0) {
            alert('Please upload your GCash receipt.');
            return false;
        }

        if (paymentMethod === 'pnb' && pnbUpload === 0) {
            alert('Please upload your PNB receipt.');
            return false;
        }

        // Confirm before submitting
        if (confirm('Are you sure you want to submit this payment?')) {
            document.getElementById('gcashPaymentForm').submit();
        } else {
            return false;
        }
    }
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let referenceIdForOrder = "{{ $formattedRefId }}";
        console.log("REFERENCE ID:", referenceIdForOrder);
    });
</script>


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
        let referenceId = null;

        // Get the referenceId from the Blade template
        const referenceIdForOrder = "{{ $formattedRefId }}";
        console.log("REFERENCE ID:", referenceIdForOrder);

        // If the status is "In Process", set the referenceId
        if (newStatus === "In Process") {
            referenceId = referenceIdForOrder;  // Use the referenceId generated by Blade
        }

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
                    status: newStatus,
                    reference_id: referenceId // Send the reference_id if it's set
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
