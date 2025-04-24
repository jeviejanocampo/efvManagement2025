@extends('staff.dashboard.StaffMain')

@section('content')
<style>
  td {
    text-align: center;
  }
</style>

<!-- <h1 class="text-4xl font-semibold pb-2">POS</h1> -->

<div class="max-w-full mx-auto">

    <div class="grid grid-cols-3 gap-0">
        

        <div class="col-span-2 bg-white p-4 w-full" style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.1);">

            <h1 class="text-2xl font-regular pb-2 border-b border-gray">Select Brand</h1>

            <div class="overflow-x-auto overflow-y-hidden whitespace-nowrap flex space-x-4 pb-4 pt-4 border-b border-gray">
            @foreach($brands as $brand)
                <a href="{{ route('staffPOS.view', ['brand_id' => $brand->brand_id]) }}" 
                class="brand-select-box inline-block text-center cursor-pointer border border-gray-300 rounded-md p-2 {{ $selectedBrandId == $brand->brand_id ? 'bg-green-100 ring-2 ring-green-400' : '' }}">
                    <img src="{{ asset('product-images/' . $brand->brand_image) }}" class="h-16 w-16 object-contain mx-auto mb-2" alt="{{ $brand->brand_name }}">
                </a>
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
                    class="p-2 border rounded-md w-full" 
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
                    <div class="bg-white shadow-md rounded-lg p-4 text-center border flex flex-col h-full">
                        <img src="{{ asset('product-images/' . $model->model_img) }}" class="h-24 w-24 object-cover mx-auto mb-2 rounded-md" alt="{{ $model->model_name }}">
                            <h2 class="text-sm font-semibold">{{ $model->model_name }}</h2>
                            <p class="text-green-600 font-medium mt-1">₱{{ number_format($model->price, 2) }}</p>
                            <p class="text-sm">Available Stocks: {{ $stockQuantity }}</p>
                            <p class="text-sm">Model ID: {{ $model->model_id }}</p>
                            <p class="text-sm hidden">With Variant: {{ $model->w_variant }}</p>

                            <button class="mt-auto bg-black text-white px-3 py-1 rounded-md flex items-center justify-center gap-2 w-full">
                                <i class="fas fa-plus text-white"></i> Add
                            </button>

                        </div>
                    @endif

                    {{-- Render variant cards if model has variants --}}
                    @if($model->w_variant === 'YES' && $model->variants)
                        @foreach($model->variants as $variant)
                        <div class="bg-white shadow-md rounded-lg p-4 text-center border flex flex-col h-full">
                                <img src="{{ asset('product-images/' . $variant->variant_image) }}" class="h-24 w-24 object-cover mx-auto mb-2 rounded-md" alt="{{ $variant->product_name }}">
                                <h3 class="text-sm font-semibold">{{ $variant->product_name }}</h3>
                                <p class="text-green-600 font-medium mt-1">₱{{ number_format($variant->price, 2) }}</p>
                                <p class="text-sm">Variant ID: {{ $variant->variant_id }}</p>
                                <p class="text-sm">Available Stocks: {{ $variant->stocks_quantity }}</p>

                                <button class="mt-auto bg-black text-white px-3 py-1 rounded-md flex items-center justify-center gap-2 w-full">
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

        </div>


    </div>
</div>

<script src="{{ asset('js/pos-view-functions.js') }}"></script>

  
@endsection

@section('scripts')
