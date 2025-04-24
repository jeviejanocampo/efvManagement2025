@extends('staff.dashboard.StaffMain')

@section('content')
<style>
  td {
    text-align: center;
  }
</style>

<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="max-w-full mx-auto">

    <div class="grid grid-cols-3 gap-4">
        

        <div class="col-span-2 bg-white p-4 w-full" style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.1);">

            <h1 class="text-2xl font-regular pb-2 border-b border-gray">Select Brand</h1>

            <div class="overflow-x-auto overflow-y-hidden whitespace-nowrap flex space-x-4 pb-4 pt-4 border-b border-gray">
            @foreach($brands as $brand)
                <div 
                    class="brand-select-box inline-block text-center cursor-pointer border border-gray-300 p-2 {{ $selectedBrandId == $brand->brand_id ? 'bg-green-100 ring-2 ring-green-400' : '' }}" 
                    data-brand-id="{{ $brand->brand_id }}"
                >
                    <img src="{{ asset('product-images/' . $brand->brand_image) }}" class="h-16 w-16 object-contain mx-auto mb-2" alt="{{ $brand->brand_name }}">
                </div>
            @endforeach
            </div>


            
            <div class="mt-4 hidden">
                <span class="text-sm text-gray-600">Selected Brand ID:</span>
                <p id="selectedBrandId" class="text-lg font-semibold text-gray-800 mt-1">None</p>
            </div>

            <div class="mb-4">
                <input 
                    type="text" 
                    id="modelSearchInput" 
                    placeholder="Search Products" 
                    class="p-2 border  w-full" 
                    onkeyup="filterModels()"
                />
            </div>

            <div id="modelsContainer" class="grid grid-cols-2 md:grid-cols-5 gap-4 mt-6">
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

        
        <div class="col-span-1 bg-white p-4 w-full" style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.1);">

            <h1 class="text-2xl font-regular pb-2 border-b border-gray">Order Details</h1>

            <ul id="orderList" class="mt-4 space-y-2 text-sm max-h-96 overflow-y-auto pr- border-b border-gray pb-4">
                <!-- Items will appear here -->
            </ul>

            <div class="pt-2 border-b border-gray pb-4">
                <h3 for="customerSelect" class="font-semibold text-1xl mb-2">Payment Method</h3>
                <div class="flex items-center gap-2">
                    <select id="customerSelect" class="w-full border  px-3 py-2">
                        <option value="" disabled selected>Select a customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" data-name="{{ $customer->full_name }}">{{ $customer->full_name }}</option>
                        @endforeach
                    </select>
                    <button class="bg-black text-white px-3 py-3 flex items-center justify-center" title="Add Customer" onclick="document.getElementById('addCustomerModal').classList.remove('hidden')">
                        <i class="fas fa-plus"></i>
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

                <div class="flex items-center gap-4 mb-4">
                    <label class="flex items-center gap-2">
                        <input type="radio" name="paymentMethod" value="cash" checked onchange="togglePaymentInput()" />
                        Cash
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="radio" name="paymentMethod" value="gcash" onchange="togglePaymentInput()" />
                        GCash
                    </label>
                </div>

                <div id="cashInputSection" class="mb-2 border-b border-gray">
                    <label for="cashInput" class="block text-sm font-medium">Cash Received</label>
                    <input 
                        type="text" 
                        id="cashInput" 
                        class="w-full border  px-3 py-2" 
                        placeholder="Enter cash amount" 
                        oninput="formatCashInput(this); calculateChange()" 
                    />
                </div>

                <div class="mt-2">
                    <label class="block text-sm font-medium text-gray-600">Change</label>
                    <p id="changeAmount" class="text-xl font-bold text-green-600">₱0.00</p>
                </div>
            </div>

            <div id="orderSummary" class="bg-white p-4 mt-4">
                <h3 class="font-semibold text-1xl">Summary</h3>
                <div class="flex justify-between mt-2">
                    <p class="text-2xl">Total:</p>
                    <p id="totalAmount" class="text-2xl font-medium text-blue-600">₱0.00</p>
                </div>
                <div class="flex justify-between mt-2">
                    <p class="text-2xl">Total Items:</p>
                    <p id="totalItems" class="text-2xl font-medium text-blue-600">0</p>
                </div>
            </div>


            <button onclick="saveOrder()" class="mt-4 bg-green-600 text-white px-4 py-2  w-full">
                Save Order
            </button>



        </div>


    </div>
</div>

<div id="addCustomerModal" class="fixed inset-0 bg-black bg-opacity-50 {{ $errors->any() ? 'flex' : 'hidden' }} justify-center items-center z-50 flex">
    <div class="m-auto bg-white rounded-lg p-6 w-full max-w-md shadow-lg">
        <h2 class="text-xl font-semibold mb-4">Add New Customer</h2>
        <form id="addCustomerForm" method="POST" action="{{ route('customers.store.new') }}">
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
                <input type="text" value="customer12345678" class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-500" disabled>
                <input type="hidden" name="password" value="customer123">
            </div>
            <button type="submit" class="bg-black text-white px-4 py-2 rounded">Save</button>
            <button type="button" id="closeModal" class="ml-2 text-gray-600" onclick="document.getElementById('addCustomerModal').classList.add('hidden')">Cancel</button>
        </form>
    </div>
</div>


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
