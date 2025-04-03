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
<meta name="user-id" content="{{ $refund->user_id ?? '' }}">
<meta name="auth-id" content="{{ Auth::id() ?? '' }}">


<div class="bg-gray-200 p-6" style="zoom: 85%">

        <a href="{{ route('staff.refundRequests') }}" class="text-gray-600 hover:text-gray-900 flex items-center gap-2 mb-4 text-3">
            <i class="fa-solid fa-arrow-left"></i> 
        </a>

    <div>
    
    @php
        $total_price = 0;

        // Initialize variables
        $brandCode = '';
        $partCode1 = '';
        $partCode2 = '';

        // Ensure we have enough rows to work with
        $lastRow = null;
        $secondLastRow = null;

        // Loop through orderDetails to find the last and second-last row with valid data
        foreach ($orderDetails as $detail) {
            if (!empty($detail->brand_name) && !empty($detail->part_id)) {
                $secondLastRow = $lastRow;
                $lastRow = $detail;
            }
        }

        // Extract brand code (first 3 uppercase letters of brand_name from the last row)
        if ($lastRow && isset($lastRow->brand_name)) {
            $brandCode = strtoupper(substr($lastRow->brand_name, 0, 3)); 
        }

        // Extract part_code1 (last 2 characters of part_id from the second last row)
        if ($secondLastRow && isset($secondLastRow->part_id)) {
            $partIdParts = explode('-', $secondLastRow->part_id);
            $partCode1 = substr(end($partIdParts), -4);
        }

        // Extract part_code2 (last 4 characters of part_id from the last row)
        if ($lastRow && isset($lastRow->part_id)) {
            $partIdParts = explode('-', $lastRow->part_id);
            $partCode2 = substr(end($partIdParts), -4);
        }

        // Generate the reference ID
        $newReferenceId = "{$brandCode}-{$partCode1}{$partCode2}-ORD" . str_pad($refund->order_id, 5, '0', STR_PAD_LEFT);

        Log::info("Generated Reference ID: " . $newReferenceId);

    @endphp


        <h2 class="text-3xl font-semibold mb-4">Original Order Details</h2>

        <div class="order-details">

            <div class="mb-4 space-y-6">
            <div class="flex justify-between items-center border-b border-gray-300 pb-4">
                <p class="text-2xl" style="display: none;"><strong>Order ID:</strong> {{ $refund->order_id }}</p>

                <p class="text-3xl font-bold">
                    Reference ID: {{ $newReferenceId ?? $reference_id }}
                </p>

                <div class="flex items-center space-x-4">
                    <strong class="text-2xl">Status:</strong>
                    <form action="{{ route('staff.updateRefundStatus', $refund->order_id) }}" method="POST" class="flex items-center space-x-2">
                        @csrf
                        <select name="overall_status" id="overall_status" class="px-4 py-4 rounded-lg border bg-white" onchange="toggleReferenceId()">
                            <option value="Pending" {{ $refund->overall_status === 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Processing" {{ $refund->overall_status === 'Processing' ? 'selected' : '' }}>Processing</option>
                            <option value="Completed - with changes" {{ $refund->overall_status === 'Completed - with changes' ? 'selected' : '' }}>Completed - with changes</option>
                            <option value="Completed - no changes" {{ $refund->overall_status === 'Completed - no changes' ? 'selected' : '' }}>Completed - no changes</option>
                            <option value="Complete Refund" {{ $refund->overall_status === 'Complete Refund' ? 'selected' : '' }}>Complete Refund</option>
                        </select>

                        <!-- Hidden input for newReferenceId (only needed when "Completed - with changes" is selected) -->
                        <input type="hidden" name="new_reference_id" id="new_reference_id" value="{{ $newReferenceId ?? '' }}">

                        <button type="submit" class="px-3 py-1 bg-blue-700 text-white rounded-lg">Update</button>
                    </form>

                   
                </div>
            </div>
                <p class="text-1xl"><strong>Customer:</strong> {{ $refund->customer->full_name ?? 'Unknown' }}</p>
                <p class="text-1xl"><strong>Refund Reason:</strong> {{ $refund->refund_reason }}</p>
                <p class="text-1xl"><strong>Created:</strong> {{ $refund->created_at->format('F j, Y g:i A') }}</p>
                </div>

            <h2 class="text-3xl font-semibold mb-4 border-b border-gray pb-4 pt-2">Product Details</h2>

            <form method="POST" action="{{ route('order.updateStatus.refunded') }}">
                @csrf
                <table class="w-full border-collapse border border-gray-300 text-sm">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="p-1 ">Variant ID</th>
                            <th class="p-1 ">Model ID</th>
                            <th class="p-1 ">Product</th>
                            <th class="p-1 ">Brand</th>
                            <th class="p-1 ">Quantity</th>
                            <th class="p-1 ">Price</th>
                            <th class="p-1 ">Subtotal</th>
                            <th class="p-1 ">Status</th>
                            <th class="p-1 ">Action</th> <!-- NEW COLUMN -->
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

                            <input type="hidden" name="variant_id[]" value="{{ $detail->variant_id }}">
                            <tr class="">
                                <td class="p-1 ">{{ $detail->variant_id }}</td>
                                <td class="p-1 ">{{ $detail->model_id }}</td>
                                <td class="p-1  flex items-center gap-2">
                                    <img src="{{ $imagePath }}" alt="Product Image" class="w-16 h-16 object-cover rounded-md">
                                    <span class="text-sm">{{ $detail->product_name }}</span>
                                </td>
                                <td class="p-1 ">{{ $detail->brand_name }}</td>
                                <td class="p-1 ">{{ $detail->quantity }}</td>
                                <td class="p-1 ">₱ {{ number_format($detail->price, 2) }}</td>
                                <td class="p-1 ">₱ {{ number_format($detail->price * $detail->quantity, 2) }}</td>
                                <td class="p-1   
                                    @if(strtolower($detail->product_status) == 'pending') bg-yellow-200 
                                    @elseif(strtolower($detail->product_status) == 'refunded') bg-red-200 
                                    @elseif(strtolower($detail->product_status) == 'completed') bg-green-200 
                                    @endif
                                ">
                                    {{ strtolower($detail->product_status) == 'pending' ? 'In Process' : ucfirst($detail->product_status) }}
                                </td>
                                <td class="p-1 ">
                                    <input type="hidden" name="order_id" value="{{ $refund->order_id }}">
                                    <input type="hidden" name="product_id[]" value="{{ $detail->model_id }}">
                                    <input type="hidden" name="product_price[]" value="{{ $itemTotal }}">
                                    
                                    <select name="product_status[]" class=" p-1" 
                                            onchange="confirmRefund(this, '{{ $detail->model_id }}', '{{ $itemTotal }}')">
                                        <option value="pending" {{ $detail->product_status == 'pending' ? 'selected' : '' }}>Undo Refunded</option>
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

   <h2 class="text-3xl font-semibold mb-4 border-b border-gray pt-6">Replacement Process Section</h2>

    <div class="grid-container" style="display: grid; grid-template-columns: 1.9fr 1fr; gap: 20px;">
        
    <div class="order-details">
        <h2 class="text-xl font-semibold mb-4">Change Product</h2>

        <!-- Search Bar -->
        <input type="text" id="searchInput" placeholder="Search by Model ID, Name, or Brand" 
            class="border rounded p-2 w-full mb-4">

        <!-- Grid Container -->
        <div class="scroll-container">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6" id="cardContainer">
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
                            <h3 class="text-1xl font-semibold">Single Product</h3> 
                            <h3 class="text-1xl font-semibold">{{ $model->model_name }}</h3> 
                            <p class="text-gray-600">{{ $model->brand->brand_name ?? 'Unknown' }}</p>
                            <p class="text-gray-800 font-semibold mt-1">Price: ₱{{ $model->price }}</p>
                            <p class="text-red-600 font-semibold mt-1">Qty: {{ $model->total_stock_quantity }}</p>
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
                        <h3 class="text-1xl font-semibold">Variant Product</h3> 
                        <h3 class="text-1xl font-semibold">{{ $variant->product_name }}</h3> 
                        <p class="text-gray-600">{{ $variant->model->brand->brand_name ?? 'Unknown' }}</p>
                        <p class="text-gray-800 font-semibold mt-1">Price: ₱{{ $variant->price }}</p>
                        <p class="text-red-600 font-semibold mt-1">Qty: {{ $variant->stocks_quantity }}</p>
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
            
            <h2 class="text-2xl font-semibold mb-4 border-b-2 border-gray-300">

            <div class="flex justify-between items-center p-3">
                <p class="text-lg font-semibold text-gray-700">Chosen Model/Variant Subtotal:</p>
                <strong id="chosenSubtotal" class="text-2xl font-bold text-gray-900">₱ 0.00</strong>
            </div>



            </h2>
            
            <!-- Updated Total Price -->
            <div class="flex justify-between text-1xl mt-3 bg-white">
                <p class="font-bold text-1xl">Updated Total Amount:</p>
                <span id="updatedTotalPrice" class="text-blue-600 text-2xl font-bold">₱ 0.00</span>
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

<input type="hidden" id="refundUserId" value="{{ $refund->user_id }}">
<input type="hidden" id="authUserId" value="{{ Auth::id() }}">

<script>
    
document.addEventListener("DOMContentLoaded", function () {
    const totalPriceElement = document.getElementById("totalPrice");
    const updatedTotalPriceElement = document.getElementById("updatedTotalPrice");
    const summaryDetailsElement = document.getElementById("summaryDetails");

    const originalOrderPrice = parseFloat(
        document.getElementById("originalOrderPrice").textContent.replace(/[₱,]/g, '')
    ) || 0;

    const orderData = {};
    document.querySelectorAll("tbody tr").forEach(row => {
        const modelId = row.children[1].textContent.trim(); // Model ID
        const variantId = row.children[0].textContent.trim(); // Variant ID
        const productName = row.children[2].querySelector("span").textContent.trim();
        const brand = row.children[3].textContent.trim();
        const quantity = parseInt(row.children[4].textContent.trim());
        const price = parseFloat(row.children[5].textContent.replace(/[₱,]/g, ''));
        const subtotal = parseFloat(row.children[6].textContent.replace(/[₱,]/g, ''));

        if (modelId || variantId) {
            let uniqueKey = `${modelId}-${variantId}`; // Ensures unique storage
            orderData[uniqueKey] = { modelId, variantId, productName, brand, price, quantity, subtotal };
        }

        console.log("Stored Order Data:", orderData);
        console.log("Available Model IDs:", Object.values(orderData).map(item => item.modelId));
        console.log("Available Variant IDs:", Object.values(orderData).map(item => item.variantId));


    });

    document.getElementById("cardContainer").addEventListener("click", function (event) {
        if (event.target.classList.contains("add-to-details")) {
            const card = event.target.closest(".card");
            const productType = card.hasAttribute("data-variant-id") ? "variant" : "model";
            const modelId = card.getAttribute("data-id");
            const variantId = card.getAttribute("data-variant-id") || null;
            const productName = card.getAttribute("data-name");
            const newPrice = parseFloat(card.getAttribute("data-price")) || 0;

            if (!modelId) {
                alert("Error: Model ID not found.");
                return;
            }

            let label = productType === "variant" ? "Variant Product" : "Model Product";
            let idLabel = productType === "variant" ? "Variant ID" : "Model ID";
            let availableIds = productType === "variant"
                ? Object.values(orderData).filter(item => item.variantId).map(item => item.variantId)
                : Object.values(orderData).filter(item => item.modelId).map(item => item.modelId);

            let selectedId = availableIds.length ? availableIds[0] : "";
            let oldSubtotal = orderData[selectedId]?.subtotal || 0;
            let newTotalPrice = newPrice;

            let detailItem = document.createElement("div");
            detailItem.classList.add("bg-white", "p-6", "rounded-lg", "mb-3", "border", "relative", "shadow-md");
            detailItem.setAttribute("data-detail-id", selectedId);
            detailItem.setAttribute("data-old-subtotal", oldSubtotal);
            detailItem.setAttribute("data-price", newTotalPrice);

            let dropdownOptions = availableIds.map(id => `<option value="${id}">${id}</option>`).join("");

            detailItem.innerHTML = `
            <div class="flex justify-between items-center space-y-4">
                <div>
                    <h3 class="text-2xl font-semibold">${label}</h3>
                    <h3 class="text-lg font-semibold">${productName}</h3>
                    <p class="text-gray-600">${idLabel}: 
                        <span class="font-bold text-black">${productType === "variant" ? variantId : modelId}</span>
                    </p>
                    <p class="text-gray-600 mt-2">Choose ${productType === "variant" ? "Variant ID" : "Model ID"} to replace:  
                        <input type="text" class="border rounded p-1 id-selector" placeholder="Enter ID" />
                        <span class="text-red-500 text-sm hidden invalid-id-msg">Invalid ID</span>
                    </p>
                    <p class="text-gray-600 mt-2">Quantity: 
                        <input type="number" class="border rounded p-1 w-20 text-center quantity-input" value="1" min="1">
                    </p>
                </div>
                <h3 class="text-lg font-semibold"></h3>
                <p class="text-gray-800 text-2xl font-bold price-label">₱ ${newTotalPrice.toLocaleString()}</p>
            </div>
            <button class="absolute top-5 right-5 text-red-500 remove-item font-bold">Remove</button>
        `;


            document.getElementById("detailsContainer").appendChild(detailItem);
            updateTotalPrice();

            const modelOrderData = {};   // Store only model products
            const variantOrderData = {}; // Store only variant products

            // ✅ Event: Validate Input Model/Variant ID (Handles multiple matching IDs)
            detailItem.querySelector(".id-selector").addEventListener("input", function () {
                let enteredId = this.value.trim();
                let invalidMsg = this.nextElementSibling;

                let matchingItems = Object.values(orderData).filter(item => {
                    // 🔥 FIXED: Now checking the correct type (modelId or variantId)
                    return productType === "variant" ? item.variantId === enteredId : item.modelId === enteredId;
                });

                console.log("Matching Items Found:", matchingItems);


                if (matchingItems.length > 0) {
                    this.classList.remove("border-red-500");
                    this.classList.add("border-green-500");
                    invalidMsg.classList.add("hidden");

                    // 🔥 FIXED: Get correct subtotal based on the selected type
                    let newSubtotal = matchingItems.reduce((sum, item) => sum + item.subtotal, 0);
                    console.log("✅ New Subtotal Found:", newSubtotal);
                    detailItem.setAttribute("data-old-subtotal", newSubtotal);
                    updateTotalPrice();
                } else {
                    this.classList.remove("border-green-500");
                    this.classList.add("border-red-500");
                    invalidMsg.classList.remove("hidden");
                }

                console.log("Available Model IDs:", Object.values(orderData).map(item => item.modelId));
                console.log("Available Variant IDs:", Object.values(orderData).map(item => item.variantId));
            });






            detailItem.querySelector(".quantity-input").addEventListener("input", function () {
                let newQuantity = parseInt(this.value) || 1;
                let updatedPrice = newPrice * newQuantity;

                detailItem.querySelector(".price-label").textContent = `₱ ${updatedPrice.toLocaleString()}`;
                detailItem.setAttribute("data-price", updatedPrice);
                updateTotalPrice();
            });
        }
    });

    function updateTotalPrice() {
        let newSubtotal = 0;
        let replacedSubtotal = 0; // The subtotal of the item being replaced
        let remainingSubtotal = 0;
        let replacedIds = new Set();

        // ✅ Step 1: Get the chosen new subtotal
        document.querySelectorAll("#detailsContainer [data-detail-id]").forEach(item => {
            let selectedId = item.querySelector(".id-selector")?.value.trim(); // Selected Model ID
            let oldSubtotal = parseFloat(item.getAttribute("data-old-subtotal")) || 0;
            let newTotalPrice = parseFloat(item.getAttribute("data-price")) || 0;

            if (selectedId) {
                replacedIds.add(selectedId); // Track replaced IDs
                replacedSubtotal += oldSubtotal; // Add the old subtotal of the replaced item
            }

            newSubtotal += newTotalPrice;
        });

        // ✅ Step 2: Calculate remaining subtotal from non-replaced items
        document.querySelectorAll("tbody tr").forEach(row => {
            let variantId = row.children[0].textContent.trim(); // Variant ID
            let modelId = row.children[1].textContent.trim(); // Model ID
            let subtotal = parseFloat(row.children[6].textContent.replace(/[₱,]/g, '')) || 0;

            // ✅ Keep only the subtotal of non-replaced items
            if (!replacedIds.has(variantId) && !replacedIds.has(modelId)) {
                remainingSubtotal += subtotal;
            }
        });

        // ✅ Step 3: Calculate the correct updated total price
        let totalWithReplacement = (originalOrderPrice - replacedSubtotal) + newSubtotal;
        let difference = originalOrderPrice - totalWithReplacement;
        let amountAdded = difference < 0 ? Math.abs(difference) : 0;
        let customerChange = difference > 0 ? difference : 0;

        // ✅ Step 4: Update the UI
        let summaryDetailsHTML = `
            <div class="flex justify-between">
                <p class="text-gray-700">Original Order Total Amount:</p>
                <strong class="text-gray-900 text-2xl">₱ ${originalOrderPrice.toLocaleString()}</strong>
            </div>
            <div class="flex justify-between">
                <p class="text-gray-700">Chosen Model/Variant Subtotal:</p>
                <strong class="text-gray-900 text-2xl">₱ ${newSubtotal.toLocaleString()}</strong>
            </div>
            <div class="flex justify-between">
                <p class="text-gray-700">Remaining Subtotal:</p>
                <strong class="text-gray-900 text-2xl">₱ ${remainingSubtotal.toLocaleString()}</strong>
            </div>
            <p class="font-bold text-2xl pb-2 border-b border-gray">Difference</p>

             <div class="flex justify-between ${amountAdded > 0 ? 'text-red-600' : ''}">
                <p class="text-red font-bold">Amount Added:</p>
                <strong class="text-gray-900 text-2xl">₱ ${amountAdded.toLocaleString()}</strong>
            </div>

            <div class="flex justify-between ${customerChange > 0 ? 'text-green-600' : ''}">
                <p class=" text-green font-bold">Customer's Change:</p>
                <strong class="text-green text-2xl">₱ ${customerChange.toLocaleString()}</strong>
            </div>
            <div class="flex justify-between mt-4 border-t pt-2">
                <p class="text-gray-700 font-bold">Updated Total Price:</p>
                <strong class="text-gray-900 text-2xl">₱ ${totalWithReplacement.toLocaleString()}</strong>
            </div>`;

        // ✅ Update the displayed values correctly
        updatedTotalPriceElement.textContent = `₱${totalWithReplacement.toLocaleString()}`;
        summaryDetailsElement.innerHTML = summaryDetailsHTML;
    }



    document.getElementById("detailsContainer").addEventListener("change", function (event) {
        if (event.target.classList.contains("id-selector")) {
            let selectedId = event.target.value.trim(); // Get entered Model/Variant ID
            let detailItem = event.target.closest("[data-detail-id]");
            let productType = detailItem.querySelector("h3").textContent.includes("Model") ? "model" : "variant";
            let chosenSubtotalElement = document.getElementById("chosenSubtotal");

            // Find the correct subtotal based on the type (Model ID or Variant ID)
            let matchingRow = [...document.querySelectorAll("tbody tr")].find(row => {
                let variantIdCell = row.children[0].textContent.trim(); // Variant ID
                let modelIdCell = row.children[1].textContent.trim();   // Model ID
                return productType === "variant" ? variantIdCell === selectedId : modelIdCell === selectedId;
            });

            if (matchingRow) {
                let subtotalText = matchingRow.children[6].textContent.replace(/[₱,]/g, '').trim(); // Subtotal
                let correctSubtotal = parseFloat(subtotalText) || 0;
                
                // ✅ Update the correct subtotal in the selected detail item
                chosenSubtotalElement.textContent = `₱ ${correctSubtotal.toLocaleString()}`;
                detailItem.setAttribute("data-old-subtotal", correctSubtotal);

                updateTotalPrice();
            } else {
                chosenSubtotalElement.textContent = "₱ 0.00"; // Reset if not found
            }
        }
    });

    document.getElementById("detailsContainer").addEventListener("click", function (event) {
    if (event.target.classList.contains("remove-item")) {
        event.target.closest("div").remove();
        updateTotalPrice();
    }
});


document.getElementById("confirmButton").addEventListener("click", function () {

    if (!confirm("Are you sure you want to proceed with updating the product?")) {
        return; 
    }

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

    let amountAddedElement = document.querySelector(".text-red-600 strong");
    let customersChangeElement = document.querySelector(".text-green-600 strong");

    let amountAdded = amountAddedElement ? parseFloat(amountAddedElement.textContent.replace(/[^\d.]/g, '')) || 0 : 0;
    let changeGiven = customersChangeElement ? parseFloat(customersChangeElement.textContent.replace(/[^\d.]/g, '')) || 0 : 0;

    let userIdElement = document.querySelector("meta[name='user-id']");
    let processedByElement = document.querySelector("meta[name='auth-id']");

    let userId = userIdElement ? userIdElement.getAttribute("content") : null;
    let processedBy = processedByElement ? processedByElement.getAttribute("content") : null;

    console.log("✅ User ID:", userId);
    console.log("✅ Processed By:", processedBy);

    document.querySelectorAll("#detailsContainer").forEach(item => {
        let idElement = item.querySelector("p.text-gray-600 span.font-bold.text-black"); 
        let selectedId = idElement ? idElement.textContent.trim() : "N/A";

        let selectedIdElement = item.querySelector("input.id-selector");
        let originalId = selectedIdElement && selectedIdElement.value.trim() !== "" 
            ? selectedIdElement.value.trim() 
            : selectedId;

        let subtotalText = item.querySelector(".price-label") ? item.querySelector(".price-label").textContent : "₱0";
        let subtotal = parseFloat(subtotalText.replace(/[₱,]/g, '')) || 0;
        let productName = item.querySelector("h3:nth-of-type(2)") ? item.querySelector("h3:nth-of-type(2)").textContent.trim() : "Unknown Product";

        let isVariant = item.querySelector("p.text-gray-600").textContent.includes("Variant ID");
        
        let dataEntry = {
            subtotal: subtotal,
            product_name: productName,
            type: isVariant ? "variant" : "model"
        };

        if (isVariant) {
            dataEntry.variant_original_id = originalId;
            dataEntry.variant_passed_id = selectedId;
        } else {
            dataEntry.model_original_id = originalId;
            dataEntry.model_passed_id = selectedId;
        }

        insertedData.push(dataEntry);

        console.log("🔍 Original ID:", selectedId);
        console.log("✅ Selected ID:", originalId);
        console.log("🛒 Product Name:", productName);
        console.log("💰 Subtotal:", subtotal);
        console.log("📌 Type:", isVariant ? "Variant" : "Model");
    });

    let finalData = {
        order_id: orderId,
        original_total: originalTotal,
        updated_total_price: total,
        change_given: changeGiven,
        amount_added: amountAdded,
        processed_by: processedBy,
        user_id: userId,
        details_selected: insertedData,
        status: "Completed"
    };

    console.log("📝 Final Data Sent:", finalData);

    fetch('/update-refund', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(finalData)
    })
    .then(response => response.json())
    .then(data => {
        console.log("✅ Server Response:", data);
        if (data.success) {
            alert("Refund updated successfully!");
            location.reload(); 
        } else {
            alert("Failed to update refund.");
        }
    })
    .catch(error => console.error("❌ Error:", error));
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
        let newStatus = selectElement.value;
        let confirmationMessage = "";

        if (newStatus === "refunded") {
            confirmationMessage = "Are you sure you want to mark this product as REFUNDED?";
        } else if (newStatus === "pending") {
            confirmationMessage = "Are you sure you want to UNDO the refund?";
        }

        const isConfirmed = confirm(confirmationMessage);

        if (!isConfirmed) {
            // If user cancels, revert to the previous selection
            selectElement.value = selectElement.dataset.previousValue;
            return;
        }

        // Save the new value as the previous value
        selectElement.dataset.previousValue = newStatus;

        // Show processing message
        alert("Processing...");

        // Submit the form
        selectElement.form.submit();
    }

    document.addEventListener("DOMContentLoaded", function() {
        // Store initial value of select elements
        document.querySelectorAll("select[name='product_status[]']").forEach(select => {
            select.dataset.previousValue = select.value;
        });

        // Show success or error messages after form submission
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
