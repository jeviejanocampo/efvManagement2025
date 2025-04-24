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

        <div class="overflow-x-auto overflow-y-hidden whitespace-nowrap flex space-x-4 pb-2 pt-4">
            @foreach($brands as $brand)
            <div class="inline-block text-center cursor-pointer border border-gray-300 rounded-md p-2" onclick="showBrandId('{{ $brand->brand_id }}')">
                <input type="hidden" value="{{ $brand->brand_id }}">
                <img src="{{ asset('product-images/' . $brand->brand_image) }}" class="h-12 w-12 object-cover rounded-full mx-auto mb-2" alt="{{ $brand->brand_name }}">
            </div>
            @endforeach
        </div>

        <div class="mt-4">

            <span class="text-sm text-gray-600">Selected Brand ID:</span>
            <p id="selectedBrandId" class="text-lg font-semibold text-gray-800 mt-1">None</p>

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
