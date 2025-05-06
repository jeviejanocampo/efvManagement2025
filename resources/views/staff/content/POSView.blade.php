@extends('staff.dashboard.StaffMain')

@section('content')
<style>
  td {
    text-align: center;
  }
  
</style>

<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<!-- SweetAlert2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.10/dist/sweetalert2.min.css" rel="stylesheet">

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.10/dist/sweetalert2.all.min.js"></script>

<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="max-w-full mx-auto">

    <div class="grid grid-cols-12 gap-4">
        

        <div class="col-span-6 bg-white p-4 w-full" style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.1);">

            <h1 class="text-2xl font-semibold pb-2 border-b border-gray">Select Brand</h1>

            <div class="overflow-x-auto overflow-y-hidden whitespace-nowrap flex scroll-smooth no-scrollbar space-x-4 pb-4 pt-4 border-b border-gray">
                @foreach($brands as $brand)
                    <div 
                        class="brand-select-box flex-none w-1/5 text-center cursor-pointer p-2 {{ $selectedBrandId == $brand->brand_id ? 'bg-green-100 ring-2 ring-green-400' : '' }}" 
                        data-brand-id="{{ $brand->brand_id }}"
                    >
                        <img 
                            src="{{ asset('product-images/' . $brand->brand_image) }}" 
                            class="h-24 w-24 object-contain mx-auto mb-2 rounded-md shadow" 
                            alt="{{ $brand->brand_name }}">
                    </div>
                @endforeach
            </div>



            
            <div class="mt-4 hidden">
                <span class="text-sm text-gray-600">Selected Brand ID:</span>
                <p id="selectedBrandId" class="text-lg font-semibold text-gray-800 mt-1">None</p>
            </div>

            <div class="mb-4 mt-4">
                <input 
                    type="text" 
                    id="modelSearchInput" 
                    placeholder="Search Products" 
                    class="p-2 border  w-full" 
                    onkeyup="filterModels()"
                />
            </div>

            <div id="modelsContainer" class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
             @forelse($models as $model)
                @php
                    $stockQuantity = $model->products->sum('stocks_quantity');
                @endphp

                {{-- Render model card ONLY if w_variant is not YES --}}
                @if($model->w_variant !== 'YES')
                <div class="bg-white  rounded-lg p-4 text-center border flex flex-col h-full">
                    <img src="{{ asset('product-images/' . $model->model_img) }}" class="h-24 w-24 object-cover mx-auto mb-2 " alt="{{ $model->model_name }}">
                        <h2 class="text-sm font-semibold">{{ $model->model_name }}</h2>
                        <h3 class="text-sm font-semibold">{{ $model->brand_id }}</h3>
                        <p class="text-green-600 font-medium mt-1">₱{{ number_format($model->price, 2) }}</p>
                        <p class="text-sm hidden">Available Stocks: {{ $stockQuantity }}</p>
                        <p class="text-sm">Model ID: {{ $model->model_id }}</p>
                        <p class="text-sm hidden">Part IDs: {{ $model->products->pluck('m_part_id')->unique()->implode(', ') }}</p>

                        <p class="text-sm hidden">With Variant: {{ $model->w_variant }}</p>

                        <button 
                            class="add-to-order mt-auto bg-black text-white px-3 py-1  flex items-center justify-center gap-2 w-full"
                            data-name="{{ $model->model_name }}" 
                                data-price="{{ $model->price }}" 
                                data-id="{{ $model->model_id }}" 
                                data-stocks="{{ $stockQuantity }}"
                                data-type="model"
                                data-part-id="{{ $model->m_part_id }}"
                                >
                            <i class="fas fa-plus text-white"></i> Add
                        </button>


                    </div>
                @endif

                {{-- Render variant cards if model has variants --}}
                @if($model->w_variant === 'YES' && $model->variants)
                    @foreach($model->variants as $variant)
                    <div class="bg-white  rounded-lg p-4 text-center border flex flex-col h-full">
                        <img src="{{ asset('product-images/' . $variant->variant_image) }}" class="h-24 w-24 object-cover mx-auto mb-2 " alt="{{ $variant->product_name }}">
                        <h3 class="text-sm font-semibold">{{ $variant->product_name }}</h3>
                        <h3 class="text-sm font-semibold">{{ $variant->brand_id }}</h3>
                        <p class="text-sm">Part ID: {{ $variant->part_id }}</p>
                        <p class="text-green-600 font-medium mt-1">₱{{ number_format($variant->price, 2) }}</p>
                        <p class="text-sm">model ID: {{ $variant->model_id }}</p>
                        <p class="text-sm">Variant ID: {{ $variant->variant_id }}</p>
                        <p class="text-sm hidden">Available Stocks: {{ $variant->stocks_quantity }}</p>

                        <button 
                            class="add-to-order mt-auto bg-black text-white px-3 py-1  flex items-center justify-center gap-2 w-full"
                            data-name="{{ $variant->product_name }}"
                            data-price="{{ str_replace(',', '', number_format($variant->price, 2)) }}" 
                            data-id="{{ $variant->variant_id }}"
                            data-type="variant"
                            data-model-id="{{ $variant->model_id }}"
                            data-stocks="{{ $variant->stocks_quantity }}"
                            data-part-id="{{ $variant->part_id }}"
                        >
                            <i class="fas fa-plus text-white"></i> Add
                        </button>
                    </div>
                    @endforeach
                @endif
                @empty
                    <p class="col-span-2 text-gray-500">No models found for this brand.</p>
                @endforelse
            </div>


        </div>

        
        <div class="col-span-6 bg-white p-4 w-full" style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.1);">

            <h1 class="text-2xl font-semibold pb-2 border-b border-gray">Order Details</h1>

            <ul id="orderList" class="mt-4 space-y-2 text-sm max-h-[600px] overflow-y-auto pr-4 border-b border-gray-300 pb-4">
            <!-- Items will appear here -->
            </ul>

            <div class="pt-2 border-b border-gray pb-4">
                <h3 for="customerSelect" class="font-semibold text-1xl mb-2">Select Customer</h3>
                <div class="flex items-center gap-2">
                    <select id="customerSelect" class="w-full border px-3 py-2">
                        <option value="">Select a customer</option> {{-- No `disabled`, no `selected` --}}
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" data-name="{{ $customer->full_name }}">{{ $customer->full_name }}</option>
                        @endforeach
                    </select>

                    <!-- Add Customer Button -->
                    <button class="bg-black text-white px-3 py-3 flex items-center justify-center" title="Add Customer" onclick="document.getElementById('addCustomerModal').classList.remove('hidden')">
                        <i class="fas fa-plus"></i>
                    </button>

                    <!-- Remove Customer Button -->
                    <button class="bg-gray-600 text-white px-3 py-3 flex items-center justify-center" title="Remove Customer" onclick="removeCustomerSelection()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>


            <div id="chosen" class="hidden">
                <p class="font-medium text-md">Chosen Customer:</p>
                <p id="chosenCustomerId" class="text-gray-600 text-sm">ID: N/A</p>
                <p id="chosenCustomer" class="text-blue-600 font-semibold">None</p>
            </div>

            <div class="bg-white mt-2 border-b border-gray space-y-4">

                <h3 class="font-semibold text-1xl mb-2">Payment Method</h3>

                <div class="flex items-center gap-6 mb-4">
                <label class="flex items-center gap-2">
                    <input type="radio" name="paymentMethod" value="cash" checked onchange="togglePaymentInput()" />
                    <!-- <span>Cash</span> -->
                    <img src="{{ asset('product-images/cashlogo.png') }}" alt="Cash Logo" class="w-12 h-12">
                </label>
                <label class="flex items-center gap-2">
                    <input type="radio" name="paymentMethod" value="gcash" onchange="togglePaymentInput()" />
                    <!-- <span>GCash</span> -->
                    <img src="{{ asset('product-images/gcashlogo.png') }}" alt="GCash Logo" class="w-12 h-12">
                </label>
                <label class="flex items-center gap-2">
                    <input type="radio" name="paymentMethod" value="pnb" onchange="togglePaymentInput()" />
                    <!-- <span>PNB</span> -->
                    <img src="{{ asset('product-images/pnblogo.png') }}" alt="PNB Logo" class="w-16 h-16">
                </label>
            </div>


                <!-- GCash Payment Modal -->
                <div id="gcashModal" class="fixed inset-0 bg-gray-500 bg-opacity-50 flex justify-center items-center hidden">
                    <div class="bg-white p-6 rounded-lg shadow-lg w-96">
                        <div class="flex justify-center">
                            <img src="{{ asset('product-images/gcashlogo.png') }}" alt="GCash Logo" class="w-12 h-12">
                            <h3 class="text-lg font-semibold mb-4 text-center mt-2 ml-2">GCASH Payment</h3>
                        </div>
                        
                        <!-- Display the default QR code image -->
                        <img id="gcashQRCode" src="{{ asset('product-images/gcashqrcode.webp') }}" alt="GCash QR Code" class="mb-4 w-full h-auto">

                        <h2 class="text-lg font-semibold mb-4">Account Number: 094532445021 </h2>
                        <h2 class="text-lg font-semibold mb-4">Account Name: Antinio Efro Montero</h2>
                        
                        <!-- Image Upload Section -->
                        <div class="mb-4">
                            <label for="uploadImage" class="block text-sm">Upload Screenshot</label>
                            <input type="file" id="uploadImage" class="w-full border px-3 py-2">
                        </div>
                        
                        <button onclick="saveGCashPayment()" class="bg-green-600 text-white px-4 py-2 rounded w-full mb-2">Save</button>
                        <button onclick="closeModal('gcashModal')" class="bg-red-600 text-white px-4 py-2 rounded w-full">Close</button>
                    </div>
                </div>

                <!-- GCash Payment Information Display (Initially Hidden) -->
                <div id="gcashPaymentInfo" class="hidden">
                    <div class="p-4 bg-green-200 rounded-lg mb-4">
                        <p class="text-green-800">GCash payment saved.</p>
                        <button onclick="editGCashPayment()" class="text-blue-600">Edit</button>
                        <button onclick="saveGCashImage()" class="text-green-600 ml-20">Save</button>
                    </div>
                </div>

                <!-- PNB Payment Modal -->
                <div id="pnbModal" class="fixed inset-0 bg-gray-500 bg-opacity-50 flex justify-center items-center hidden">
                    <div class="bg-white p-6 rounded-lg shadow-lg w-96">
                        <div class="flex justify-center pb-4">
                            <img src="{{ asset('product-images/pnblogo.png') }}" alt="PNB Logo" class="w-16 h-16">
                            <h3 class="text-lg font-semibold mb-4 text-center mt-4 ml-2">PNB Payment</h3>
                        </div>

                        <img id="gcashQRCode" src="{{ asset('product-images/pnbqrcode.png') }}" alt="PNB QR Code" class="mb-4 w-full h-auto">
                                                
                        <h2 class="text-lg font-semibold mb-4">Account Number: 392310196887 </h2>
                        <h2 class="text-lg font-semibold mb-4">Account Name: Antinio Efro Montero</h2>

                        <!-- Image Upload Section -->
                        <div class="mb-4">
                            <label for="uploadPNBImage" class="block text-sm">Upload Screenshot</label>
                            <input type="file" id="uploadPNBImage" class="w-full border px-3 py-2">
                        </div>
                        
                        <button onclick="savePNBPayment()" class="bg-blue-600 text-white px-4 py-2 rounded w-full mb-2">Save</button>
                        <button onclick="closeModal('pnbModal')" class="bg-red-600 text-white px-4 py-2 rounded w-full">Close</button>
                    </div>
                </div>

                <!-- PNB Payment Info Display -->
                <div id="pnbPaymentInfo" class="hidden">
                    <div class="p-4 bg-blue-200 rounded-lg mb-4">
                        <p class="text-blue-800">PNB payment saved.</p>
                        <button onclick="editPNBPayment()" class="text-blue-600">Edit</button>
                        <button onclick="savePNBImage()" class="text-blue-600 ml-4">Save</button>
                    </div>
                </div>


                <div id="cashInputSection" class="mb-2 border-b border-gray">
                    <label for="cashInput" class="block text-1xl font-semibold">Payment Received</label>
                    <input 
                        type="text" 
                        id="cashInput" 
                        class="w-full border  px-3 py-2" 
                        placeholder="Enter cash amount" 
                        oninput="formatCashInput(this); calculateChange()" 
                    />
                </div>

                <div class="mt-2">
                    <label class="block text-1xl font-semibold text-black">Change</label>
                    <p id="changeAmount" class="text-xl font-bold text-green-600">₱0.00</p>
                </div>
            </div>

            <div id="orderSummary" class="bg-white mt-6">
                <h3 class="font-medium text-2xl border-b border-gray pb-2">Summary</h3>
                <div class="flex justify-between mt-2">
                    <p class="text-1xl">Total Items</p>
                    <p id="totalItems" class="text-1xl font-medium text-blue-600">0</p>
                </div>
                <div class="flex justify-between mt-2">
                    <p class="text-1xl">Sub Total</p>
                    <p id="subTotal" class="text-1xl font-medium text-blue-600">₱0.00</p>
                </div>
                <div class="flex justify-between mt-2">
                    <p class="text-1xl">VAT Amount (12%)</p>
                    <p id="vatAmount" class="text-1xl font-medium text-blue-600">₱0.00</p>
                </div>

                <!-- VATable Sales -->
                <div class="flex justify-between mt-2 border-b border-gray pb-2">
                    <p class="text-1xl">VATable Sales</p>
                    <p id="vatableSales" class="text-1xl font-medium text-blue-600">₱0.00</p>
                </div>
                <!-- <div class="flex justify-between mt-2">
                    <p class="text-1xl">Discount</p>
                    <p class="text-1xl font-medium text-blue-600">₱0.00</p>
                </div>
                <div class="flex justify-between mt-2">
                    <p class="text-1xl">Service Charge</p>
                    <p class="text-1xl font-medium text-blue-600">₱0.00</p>
                </div>
                <div class="flex justify-between mt-2 border-b border-gray pb-4">
                    <p class="text-1xl">Tax</p>
                    <p class="text-1xl font-medium text-blue-600">₱0.00</p>
                </div> -->
                <div class="flex justify-between mt-2">
                    <p class="text-2xl">Total</p>
                    <p id="totalAmount" class="text-2xl font-medium text-blue-600">₱0.00</p>
                </div>
            </div>
            <script>
               function updateSummaryFromTotalAmount() {
                    const totalAmountElement = document.getElementById("totalAmount");

                    if (!totalAmountElement) return;

                    let totalAmountText = totalAmountElement.textContent.replace('₱', '').replace(/,/g, '').trim();
                    let totalAmount = parseFloat(totalAmountText);

                    if (isNaN(totalAmount)) totalAmount = 0;

                    const vatAmount = totalAmount * 0.12;
                    const vatableSales = totalAmount - vatAmount;

                    // ✅ Use toLocaleString for formatting with commas
                    document.getElementById("subTotal").textContent = `₱${totalAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                    document.getElementById("vatAmount").textContent = `₱${vatAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                    document.getElementById("vatableSales").textContent = `₱${vatableSales.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                }


                // MutationObserver to watch totalAmount changes
                const totalAmountObserver = new MutationObserver(() => {
                    updateSummaryFromTotalAmount();
                });

                const totalAmountTarget = document.getElementById("totalAmount");

                if (totalAmountTarget) {
                    totalAmountObserver.observe(totalAmountTarget, { childList: true, characterData: true, subtree: true });
                }

                // Also run once on page load
                window.onload = updateSummaryFromTotalAmount;
            </script>



            <!-- Customer Select Error -->
            <p id="customerError" class="text-red-600 text-sm mt-2 hidden">Please select a customer.</p>

            <!-- Cash Received Error -->
            <p id="cashError" class="text-red-600 text-sm mt-2 hidden">Cash received must be greater than or equal to the total amount.</p>



            <button onclick="confirmSaveOrder()" class="mt-8 bg-green-600 text-white px-4 py-3 w-full font-semibold">
                CONFIRM PURCHASE
            </button>

           <!-- Restart Purchase Button -->
            <button 
                class="mt-2 bg-red-600 text-white px-4 py-3 w-full font-semibold"
                onclick="confirmReload();">
                RESTART PURCHASE
            </button>

        </div>


    </div>
    
</div>

<div id="addCustomerModal" class="fixed inset-0 bg-black bg-opacity-50 {{ $errors->any() ? 'flex' : 'hidden' }} justify-center items-center z-50 flex">
    <div class="m-auto bg-white rounded-lg p-6 w-full max-w-md shadow-lg">
        <h2 class="text-xl font-semibold mb-4">Add New Customer</h2>
        <form id="addCustomerForm" method="POST" action="{{ route('staff.customers.store.new') }}">
            @csrf
            <div class="mb-3">
                <label class="block font-medium mb-1">Full Name</label>
                <input type="text" name="full_name" class="w-full border rounded px-3 py-2" required value="{{ old('full_name') }}">
                @error('full_name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-3">
                <label class="block font-medium mb-1">Email</label>
                <input type="email" name="email" class="w-full border rounded px-3 py-2" required value="{{ old('email') }}">
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-3">
                <label class="block font-medium mb-1">Password (default)</label>
                <input type="text" value="customer12345678" class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-500 hidden" disabled>
                <p class="text-sm">Staff will gave you the password once adding customer is complete</p>
                <input type="hidden" name="password" value="customer123">
            </div>
            <button type="submit" class="bg-black text-white px-4 py-2 rounded">Save</button>
            <button type="button"  class="bg-red-500 text-white px-4 py-2 rounded" id="closeModal" class="ml-2 text-gray-600" onclick="document.getElementById('addCustomerModal').classList.add('hidden')" >Cancel</button>
        </form>
    </div>
</div>


<script>
    window.customerSelectInstance = new TomSelect("#customerSelect", {
        placeholder: "Select a customer",
        allowEmptyOption: true,
        maxOptions: 100,
        create: false,
        sortField: {
            field: "text",
            direction: "asc"
        }
    });
</script>
<script>
function removeCustomerSelection() {
    if (window.customerSelectInstance) {
        customerSelectInstance.clear(); // Reset TomSelect
    }

    // Clear chosen customer UI
    document.getElementById('chosen').classList.add('hidden');
    document.getElementById('chosenCustomerId').textContent = 'ID: N/A';
    document.getElementById('chosenCustomer').textContent = 'None';

    // ✅ Confirmation prompt
    if (window.Swal) {
        Swal.fire({
            icon: 'success',
            title: 'Customer Removed',
            text: 'The selected customer has been successfully removed.',
            timer: 1500,
            showConfirmButton: false
        });
    } else {
        alert('Customer successfully removed.');
    }
}

</script>

</script>
<script>
    function confirmSaveOrder() {
        Swal.fire({
            title: 'Confirming Purchase',
            text: 'Select confirm of the purchase after payment',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Confirm',
            cancelButtonText: 'Cancel',
            reverseButtons: true  // Reverses the order of buttons, so 'Cancel' comes first
        }).then((result) => {
            if (result.isConfirmed) {
                // If confirmed, save the order
                saveOrder();
            }
        });
    }
</script>
<script>
    function confirmReload() {
        Swal.fire({
            title: 'Restart Purchase?',
            text: 'This will reset your current order. Do you want to continue?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Restart',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                location.reload(); // Reload the page
            }
        });
    }
</script>
<script>
    // Check if there is a success or error message in the session
    @if (session('success'))
        alert("{{ session('success') }}");
    @elseif (session('error'))
        alert("{{ session('error') }}");
    @endif
</script>


<script src="{{ asset('js/pos-view-functions.js') }}"></script>

  
@endsection

@section('scripts')
