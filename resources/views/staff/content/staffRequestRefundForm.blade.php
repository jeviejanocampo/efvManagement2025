@extends('staff.dashboard.StaffMain')

@section('content')

<style>
    .grid-container {
        display: grid;
        grid-template-columns: 3fr 1fr; /* Left Auto, Right 1/4 */
        gap: 20px;
    }

    .refund-details {
        background: #f8f9fa;
        padding: 16px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .scroll-container {
    max-height: 70vh; /* Adjust based on your layout */
    overflow-y: auto;
    padding-right: 10px; /* Prevents scrollbar overlap */
    border: 1px solid #ddd; /* Optional: Adds border for visibility */
    }

    /* Hide scrollbar for a cleaner look */
    .scroll-container::-webkit-scrollbar {
        width: 8px;
    }

    .scroll-container::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .scroll-container::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .scroll-container::-webkit-scrollbar-thumb:hover {
        background: #555;
    }


    .order-details {
        background: #ffffff;
        padding: 16px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    td {
        text-align: center;
    }
</style>

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="bg-gray-200 p-6" style="zoom: 85%">

        <a href="{{ route('staff.refundRequests') }}" class="text-gray-600 hover:text-gray-900 flex items-center gap-2 mb-4 text-3">
            <i class="fa-solid fa-arrow-left"></i> 
        </a>

        <h1 class="text-4xl font-semibold mb-4">Refund Details</h1>

    <div>
    
        @php
        $total_price = 0;
        @endphp

        <div class="order-details">
            <h2 class="text-3xl font-semibold mb-4">Original Order Details</h2>

            <div class="mb-4 mt-4 space-y-4">
                <p class="text-2xl"><strong>Order ID:</strong> {{ $refund->order_id }}</p>
                <p class="text-sm"><strong>Customer:</strong> {{ $refund->customer->full_name ?? 'Unknown' }}</p>
                <p class="text-sm"><strong>Status:</strong> {{ ucfirst($refund->status) }}</p>
                <p class="text-sm"><strong>Refund Reason:</strong> {{ $refund->refund_reason }}</p>
            </div>

            <form method="POST" action="{{ route('order.updateStatus.refunded') }}">
                @csrf
                <table class="w-full border-collapse border border-gray-300 text-sm">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="p-1 border">Variant ID</th>
                            <th class="p-1 border">Model ID</th>
                            <th class="p-1 border">Product</th>
                            <th class="p-1 border">Brand</th>
                            <th class="p-1 border">Quantity</th>
                            <th class="p-1 border">Price</th>
                            <th class="p-1 border">Subtotal</th>
                            <th class="p-1 border">Status</th>
                            <th class="p-1 border">Action</th> <!-- NEW COLUMN -->
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orderDetails as $detail)
                            @php
                                $imagePath = asset('product-images/default.jpg');

                                if ($detail->model_id && !empty($detail->model->model_img)) {
                                    $imagePath = asset('product-images/' . $detail->model->model_img);
                                } elseif ($detail->variant_id && !empty($detail->variant->variant_img)) {
                                    $imagePath = asset('product-images/' . $detail->variant->variant_img);
                                }

                                $itemTotal = $detail->price * $detail->quantity;

                                // Only add to total price if the status is NOT refunded
                                if ($detail->product_status !== 'refunded') {
                                    $total_price += $itemTotal;
                                }
                            @endphp

                            <tr class="border">
                                <td class="p-1 border">{{ $detail->variant_id }}</td>
                                <td class="p-1 border">{{ $detail->model_id }}</td>
                                <td class="p-1 border flex items-center gap-2">
                                    <img src="{{ $imagePath }}" alt="Product Image" class="w-16 h-16 object-cover rounded-md">
                                    <span class="text-sm">{{ $detail->product_name }}</span>
                                </td>
                                <td class="p-1 border">{{ $detail->brand_name }}</td>
                                <td class="p-1 border">{{ $detail->quantity }}</td>
                                <td class="p-1 border">₱ {{ number_format($detail->price, 2) }}</td>
                                <td class="p-1 border">₱ {{ number_format($detail->price * $detail->quantity, 2) }}</td>
                                <td class="p-1 border 
                                    @if($detail->product_status == 'pending') bg-yellow-200 
                                    @elseif($detail->product_status == 'refunded') bg-red-200 
                                    @elseif($detail->product_status == 'Completed') bg-green-200 
                                    @endif
                                ">
                                    {{ ucfirst($detail->product_status) }}
                                </td>
                                <td class="p-1 border">
                                    <input type="hidden" name="order_id" value="{{ $refund->order_id }}">
                                    <input type="hidden" name="product_id[]" value="{{ $detail->model_id }}">
                                    <input type="hidden" name="product_price[]" value="{{ $itemTotal }}">
                                    
                                    <select name="product_status[]" class="border p-1" 
                                        onchange="confirmRefund(this, '{{ $detail->model_id }}', '{{ $itemTotal }}')">
                                        <option value="pending" {{ $detail->product_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="refunded" {{ $detail->product_status == 'refunded' ? 'selected' : '' }}>Refunded</option>
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </form>


            <!-- Display Total Price -->
            <div class="mt-4 text-right font-semibold text-lg">
                <p>Total Amount: <span id="total_price">₱ {{ number_format($total_price, 2) }}</span></p>
            </div>
        </div>

    </div>

   <br>

    <div class="grid-container" style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 20px;">
        
    <div class="order-details">
        <h2 class="text-xl font-semibold mb-4">Change Product</h2>

        <!-- Search Bar -->
        <input type="text" id="searchInput" placeholder="Search by Model ID, Name, or Brand" 
            class="border rounded p-2 w-full mb-4">

        <!-- Grid Container -->
        <div class="scroll-container">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6" id="cardContainer">
            @foreach ($models as $model)
                @if ($model->w_variant !== 'YES')
                    <!-- Render Model Card -->
                    <div class="bg-white shadow-md rounded-lg overflow-hidden p-4 border card" 
                        data-id="{{ $model->model_id }}" 
                        data-name="{{ $model->model_name }}"
                        data-price="{{ $model->price }}">

                        <!-- Model Image -->
                        <div class="text-center">
                            <img src="{{ asset('product-images/' . ($model->model_img ?? 'default.jpg')) }}" 
                                class="w-32 h-32 object-cover rounded mx-auto" 
                                alt="Model Image">
                        </div>

                        <!-- Model Details -->
                        <div class="mt-3 text-center">
                            <h3 class="text-1xl font-semibold">{{ $model->model_id }}</h3> 
                            <h3 class="text-1xl font-semibold">{{ $model->model_name }}</h3> 
                            <p class="text-gray-600">{{ $model->brand->brand_name ?? 'Unknown' }}</p>
                            <p class="text-gray-800 font-semibold mt-1">Price: ₱{{ $model->price }}</p>
                        </div>

                        <!-- Add Button -->
                        <div class="mt-3 text-center">
                            <button class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800 add-to-details">
                                Add
                            </button>
                        </div>
                    </div>
                @endif
            @endforeach

            <!-- Render Variants Instead of Models if w_variant is YES -->
            @foreach ($variants as $variant)
                <div class="bg-white shadow-md rounded-lg overflow-hidden p-4 border card" 
                    data-id="{{ $variant->model_id }}" 
                    data-name="{{ $variant->product_name }}"
                    data-price="{{ $variant->price }}"
                    data-variant-id="{{ $variant->variant_id }}">  <!-- Added Variant ID -->

                    <!-- Variant Image -->
                    <div class="text-center">
                        <img src="{{ asset('product-images/' . ($variant->variant_image ?? 'default.jpg')) }}" 
                            class="w-32 h-32 object-cover rounded mx-auto" 
                            alt="Variant Image">
                    </div>

                    <!-- Variant Details -->
                    <div class="mt-3 text-center">
                        <h3 class="text-1xl font-semibold">{{ $variant->variant_id }}</h3> 
                        <h3 class="text-1xl font-semibold">{{ $variant->product_name }}</h3> 
                        <p class="text-gray-600">{{ $variant->model->brand->brand_name ?? 'Unknown' }}</p>
                        <p class="text-gray-800 font-semibold mt-1">Price: ₱{{ $variant->price }}</p>
                    </div>

                    <!-- Add Button -->
                    <div class="mt-3 text-center">
                        <button class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800 add-to-details">
                            Add
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
        </div>

    </div>

    <!-- Right Side: Refund Information -->
    <div class="refund-details">
        <div id="detailsContainer"></div> 

        <div class="border-t bg-gray-100 p-4 space-y-6">

            <h2 class="text-2xl font-semibold mb-4 border-b-2 border-gray-300">SUMMARY</h2>

            <div class="flex justify-between">
                <p class="text-1xl ">ORIGINAL ORDER TOTAL AMOUNT:</p>
                <strong id="originalOrderPrice" class="text-right text-2xl">₱ {{ number_format($total_price, 2) }}</strong>
            </div>

            <div id="summaryDetails" class="mt-2 space-y-2 text-gray-700"></div>

            <div>
                <p class="text-1xl hidden">TOTAL PRICE:</p>
                <span id="totalPrice" class="font-bold text-2xl hidden">₱ 0.00</span>
            </div>       
            
            <h2 class="text-2xl font-semibold mb-4 border-b-2 border-gray-300">DIFFERENCE

            </h2>
            <div class="flex justify-between">
                <p class="text-1xl text-red-600">AMOUNT ADDED:</p>
                <strong id="amountAdded" class="text-right text-red-600 text-2xl">₱ 0.00</strong>
            </div>

            <div class="flex justify-between border-b-2 border-gray-300">
                <p class="text-1xl text-green-600 ">CUSTOMER's CHANGE:</p>
                <strong id="customersChange" class="text-right text-green-600 text-2xl">₱ 0.00</strong>
            </div>


            <!-- Updated Total Price -->
            <div class="flex justify-between text-1xl mt-3">
                <p class="font-bold text-2xl">UPDATED TOTAL PRICE:</p>
                <span id="updatedTotalPrice" class="text-blue-600 text-2xl">₱ 0.00</span>
            </div>

          
            <div class="flex justify-between items-center mt-4">
                <button id="confirmButton" class="w-full bg-black text-white py-3 text-2xl font-semibold rounded-lg hover:bg-gray-800 transition">
                    CONFIRM
                </button>
            </div>


        </div>
    </div>


</div>


<script>
    window.processedBy = "{{ Auth::id() }}"; // Authenticated user
    window.userId = "{{ $refund->customer->id ?? 'null' }}"; // Customer's user ID
</script>

<script>
    
document.addEventListener("DOMContentLoaded", function () {
    const totalPriceElement = document.getElementById("totalPrice");
    const updatedTotalPriceElement = document.getElementById("updatedTotalPrice");
    const customersChangeElement = document.getElementById("customersChange");
    const summaryDetailsElement = document.getElementById("summaryDetails");

    const originalOrderPrice = parseFloat(
        document.getElementById("originalOrderPrice").textContent.replace(/[₱,]/g, '')
    ) || 0;

    const orderData = {};
    document.querySelectorAll("tbody tr").forEach(row => {
        const modelId = row.children[0].textContent.trim();
        const productName = row.children[1].querySelector("span").textContent.trim();
        const brand = row.children[2].textContent.trim();
        const quantity = parseInt(row.children[3].textContent.trim());
        const price = parseFloat(row.children[4].textContent.replace(/[₱,]/g, ''));
        const subtotal = parseFloat(row.children[5].textContent.replace(/[₱,]/g, ''));
        if (modelId) {
            orderData[modelId] = { productName, brand, price, quantity, subtotal };
        }
    });

    function updateTotalPrice() {
        let total = originalOrderPrice;
        let summaryDetailsHTML = "";

        document.querySelectorAll("#detailsContainer [data-detail-id]").forEach(item => {
            const oldSubtotal = parseFloat(item.getAttribute("data-old-subtotal")) || 0;
            const newTotalPrice = parseFloat(item.getAttribute("data-price")) || 0;
            const productName = item.querySelector("h3").textContent;

            // Subtract the original subtotal and add the new price for each replaced item
            total = (total - oldSubtotal) + newTotalPrice;

            summaryDetailsHTML += `
            <div class="flex justify-between">
                <p class="text-gray-700">${productName} <span class="text-1xl text-black">(Subtotal)</span></p>
                <strong class="text-gray-900 text-2xl">₱${newTotalPrice.toLocaleString()}</strong>
            </div>`;

        });

        totalPriceElement.textContent = `₱${total.toLocaleString()}`;
        updatedTotalPriceElement.textContent = `₱${total.toLocaleString()}`;
        summaryDetailsElement.innerHTML = summaryDetailsHTML;

        let difference = total - originalOrderPrice;
        
        if (difference > 0) {
            document.getElementById("amountAdded").textContent = `₱${difference.toLocaleString()}`;
            document.getElementById("customersChange").textContent = `₱0.00`;
        } else {
            document.getElementById("customersChange").textContent = `₱${Math.abs(difference).toLocaleString()}`;
            document.getElementById("amountAdded").textContent = `₱0.00`;
        }
    }


    document.getElementById("cardContainer").addEventListener("click", function (event) {
        if (event.target.classList.contains("add-to-details")) {
            const card = event.target.closest(".card");
            const modelId = card.getAttribute("data-id");
            const productName = card.getAttribute("data-name");
            const productType = card.hasAttribute("data-variant-id") ? "variant" : "model";
            const variantId = card.getAttribute("data-variant-id") || null;
            // newPrice from the card is the replacement price you want for models
            const newPrice = parseFloat(card.getAttribute("data-price")) || 0;

            if (!modelId) {
                alert("Error: Model ID not found.");
                return;
            }

            if (document.querySelector(`[data-detail-id="${modelId}"]`)) {
                alert("This product is already added!");
                return;
            }

            let idLabel = productType === "variant" ? "Variant ID" : "Model ID";
            let idValue = productType === "variant" ? variantId : modelId;

            // For models, get original subtotal and quantity from orderData
            let originalSubtotal = orderData[modelId]?.subtotal || 0;
            let quantity = orderData[modelId]?.quantity || 1;
            // For models, the initial replacement price should come from the card (newPrice)
            let newTotalPrice = newPrice * quantity;

            let modelOptions = Object.keys(orderData)
                .map(id => `<option value="${id}">${id}</option>`)
                .join("");

                let detailItem = document.createElement("div");
                detailItem.classList.add("bg-white", "p-6", "rounded-lg", "mb-3", "border", "relative", "shadow-md");
                detailItem.setAttribute("data-detail-id", modelId);
                detailItem.setAttribute("data-old-subtotal", originalSubtotal);
                detailItem.setAttribute("data-price", newTotalPrice);

                if (productType === "model" && modelOptions.length <= 1) {
                    // Separate card for models (no merging)
                    detailItem.innerHTML = `
                        <div class="flex justify-between items-center space-y-4">
                            <div>
                                <h3 class="text-2xl font-semibold">Product</h3>
                                <h3 class="text-lg font-semibold">${productName}</h3>
                                <p class="text-gray-600">Model ID: <span class="font-bold">${modelId}</span></p>
                                <p class="text-blue-500 font-semibold mt-1">This is a direct replacement for Model ID: ${modelId}</p>
                                <p class="text-gray-600 mt-2">Quantity: <span class="quantity-label">${quantity}</span></p>
                            </div>
                             <h3 class="text-lg font-semibold">Subtotal</h3>
                            <p class="text-gray-800 text-2xl font-bold price-label">₱ ${newTotalPrice.toLocaleString()}</p>
                        </div>
                        <button class="absolute top-5 right-5 text-red-500 remove-item font-bold">Remove</button>
                    `;
                } else {
                    // Card for variants or models with multiple choices (merging case)
                    detailItem.innerHTML = `
                        <div class="flex justify-between items-center space-y-4">
                            <div>
                                <h3 class="text-2xl font-semibold mb-2">Product</h3>
                                <h3 class="text-lg font-semibold">${productName}</h3>
                                <p class="text-gray-600">${idLabel}: <span class="font-bold">${idValue}</span></p>
                                <p class="text-gray-600 mt-2">Select New ${productType === "variant" ? "Variant" : "Model"} ID To Replace:</p>
                                <select class="border rounded p-1 w-32 text-center text-sm ${productType === "variant" ? "variant-id-select" : "model-id-select"}">
                                    ${modelOptions}
                                </select>
                                <p class="text-gray-600 mt-2">Quantity: <span class="quantity-label">${quantity}</span></p>
                            </div>
                             <h3 class="text-lg font-semibold">Subtotal</h3>
                            <p class="text-gray-800 text-2xl font-bold price-label">₱ ${newTotalPrice.toLocaleString()}</p>
                        </div>
                        <button class="absolute top-5 right-5 text-red-500 remove-item font-bold">Remove</button>
                    `;
                }

                document.getElementById("detailsContainer").appendChild(detailItem);
                updateTotalPrice();


        detailItem.querySelector("select").addEventListener("change", function () {
            const selectedId = this.value; // New selected Model ID
            const newSelectedQuantity = orderData[selectedId]?.quantity || 1;
            let newSelectedPrice;

            if (productType === "model") {
                const defaultId = detailItem.getAttribute("data-detail-id");

                if (selectedId === defaultId) {
                    // If the selected model ID is the original, use the passed replacement price
                    newSelectedPrice = parseFloat(detailItem.getAttribute("data-default-price"));
                } else {
                    // Otherwise, use the replacement price instead of falling back to orderData
                    newSelectedPrice = newPrice; 
                }

                // Ensure we update the correct old subtotal when switching models
                const newOldSubtotal = selectedId === defaultId 
                    ? (newSelectedPrice * newSelectedQuantity) 
                    : (orderData[selectedId]?.subtotal || 0);
                    
                detailItem.setAttribute("data-old-subtotal", newOldSubtotal);
            } else {
                // For variants, keep the given newPrice
                newSelectedPrice = newPrice;
            }

            // Update the total price calculation
            const newSelectedTotalPrice = newSelectedPrice * newSelectedQuantity;

            detailItem.querySelector(".quantity-label").textContent = newSelectedQuantity;
            detailItem.querySelector(".price-label").textContent = `₱ ${newSelectedTotalPrice.toLocaleString()}`;
            detailItem.setAttribute("data-price", newSelectedTotalPrice);

            updateTotalPrice();
        });
        }
    });

    document.getElementById("detailsContainer").addEventListener("click", function (event) {
    if (event.target.classList.contains("remove-item")) {
        event.target.closest("div").remove();
        updateTotalPrice();
    }
});

document.getElementById("confirmButton").addEventListener("click", function () {
    let insertedData = [];

    let orderIdElement = document.querySelector("p.text-2xl strong");
    let orderIdText = orderIdElement ? orderIdElement.parentElement.textContent.trim() : null;
    let orderId = orderIdText ? orderIdText.replace("Order ID:", "").trim().replace(/^0+/, "") : null;

    if (!orderId) {
        alert("Error: Order ID is missing.");
        return;
    }

    let updatedTotalPriceElement = document.getElementById("updatedTotalPrice");
    let total = updatedTotalPriceElement ? parseFloat(updatedTotalPriceElement.textContent.replace(/[^\d.]/g, '')) || 0 : 0;

    let originalTotalElement = document.getElementById("originalOrderPrice");
    let originalTotal = originalTotalElement ? parseFloat(originalTotalElement.textContent.replace(/[^\d.]/g, '')) || 0 : 0;

    let amountAddedElement = document.getElementById("amountAdded");
    let customersChangeElement = document.getElementById("customersChange");

    let amountAdded = amountAddedElement ? parseFloat(amountAddedElement.textContent.replace(/[^\d.]/g, '')) || 0 : 0;
    let changeGiven = customersChangeElement ? parseFloat(customersChangeElement.textContent.replace(/[^\d.]/g, '')) || 0 : 0;

    document.querySelectorAll("#detailsContainer [data-detail-id]").forEach(item => {
        let originalModelId = item.getAttribute("data-detail-id");
        let selectedModelId = item.querySelector("select") ? item.querySelector("select").value : originalModelId;
        let subtotalText = item.querySelector(".price-label") ? item.querySelector(".price-label").textContent : "₱0";
        let subtotal = parseFloat(subtotalText.replace(/[₱,]/g, '')) || 0;
        let productName = item.querySelector("h3:nth-of-type(2)") ? item.querySelector("h3:nth-of-type(2)").textContent.trim() : "Unknown Product";

        console.log("original_model_id:", originalModelId);
        console.log("selected_model_id:", selectedModelId);
        console.log("subtotal:", subtotal);
        console.log("product_name:", productName);

        insertedData.push({
            original_model_id: originalModelId,
            selected_model_id: selectedModelId,
            subtotal: subtotal,
            product_name: productName
        });
    });

    console.log("Final insertedData:", insertedData);

    fetch('/update-refund', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            order_id: orderId,
            original_total: originalTotal,
            final_total: total,
            change_given: changeGiven,
            amount_added: amountAdded,
            processed_by: window.processedBy,
            user_id: window.userId,
            details_selected: JSON.stringify(insertedData),
            refund_reason: "Customer requested refund",
            refund_method: "Cash",
            status: "pending"
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log("Server Response:", data);
        if (data.success) {
            alert("Refund updated successfully!");
        } else {
            alert("Failed to update refund.");
        }
    })
    .catch(error => console.error("Error:", error));
});



});

</script>



<script>
        document.getElementById('searchInput').addEventListener('keyup', function () {
            let filter = this.value.toLowerCase();
            let cards = document.querySelectorAll('.card');

            cards.forEach(card => {
                let modelName = card.getAttribute('data-name')?.toLowerCase() || '';
                let brandName = card.getAttribute('data-brand')?.toLowerCase() || '';
                let modelId = card.getAttribute('data-id')?.toLowerCase() || '';

                // Check if any of the values match the filter
                if (modelName.includes(filter) || brandName.includes(filter) || modelId.includes(filter)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
 </script>

<script>
    function confirmRefund(selectElement, productId, productPrice) {
        if (selectElement.value === 'refunded') {
            const isConfirmed = confirm("Are you sure you want to mark this product as refunded?");

            if (!isConfirmed) {
                // Reset dropdown selection if user cancels
                selectElement.value = "pending";
                return;
            }

            // Show loading alert (optional)
            alert("Processing refund...");

            // Submit the form
            selectElement.form.submit();
        }
    }

    // Show success or error messages after form submission
    document.addEventListener("DOMContentLoaded", function() {
        @if(session('success'))
            alert("✅ {{ session('success') }}");
        @endif

        @if(session('error'))
            alert("❌ {{ session('error') }}");
        @endif
    });
</script>


</div> 

@endsection
