@extends('staff.dashboard.StaffMain')

@section('content') 
<div class="p-4 rounded-xl">
        <a href="{{ url()->previous() }}" 
            class="bg-gray-800 text-white px-5 py-1 rounded-full hover:bg-white-200 mb-5 custom-arrow">
            Back
        </a>

         <!-- Order Status Dropdown -->
         <div class="flex justify-between items-center mt-4 bg-white p-4 rounded-md">
            <h1 style="font-size: 28px">Order Details</h1>

            <!-- Label and Dropdown for Edit Status for the whole order -->
            <div class="flex items-center">
                <label for="order_status" class="mr-3 text-lg">Edit Status:</label>
                <select class="bg-gray-100 text-gray-700 px-3 py-2 rounded-md" name="order_status" id="order_status">
                    <option value="pending">Pending</option>
                    <option value="ready_to_pickup">Ready to Pickup</option>
                    <option value="in_process">In Process</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
        </div>

        <div class ="bg-white p-4 mt-4 rounded-md">
            <div class="flex justify-between items-center">
            <p style="font-size: 26px; font-weight: 700;">ORDER ID: {{ $order->order_id }}</p>
                <p>STATUS: {{ $order->status }}</p> 
            </div>
            <p style="font-size: 13px">Created At: {{ $order->created_at }} </p> 
            <div class="mt-4">
                
                <p style="font-size: 14px">USER ID: {{ $order->user_id }}</p>
                <p style="font-size: 14px">TOTAL ITEMS: {{ $order->total_items }}</p> 
                <p style="font-size: 14px">PAYMENT METHOD: {{ $order->payment_method }}</p> 
            </div>
        </div>


    
        <!-- Order Details Table -->
        <div class ="bg-white p-4 mt-4 rounded-md">
        <h3 class="text-xl font-semibold border-b-2 border-black-300">Product Details</h3>
        <table class="table-auto w-full border-collapse mt-4">
            <thead>
                <tr class="bg-white">
                   
                </tr>
            </thead>
            <tbody>
                @foreach ($orderDetails as $detail)
                    <tr class="border border-white  ">
                        <td class=" px-5 py-1">
                            <!-- Add badge based on product status -->
                            @if($detail->product_status === 'pending')
                                <span class="bg-red-500 text-white px-3 py-1 rounded-full text-sm">Reserved</span>
                            @elseif($detail->product_status === 'pre-order')
                                <span class="bg-blue-500 text-white px-3 py-1 rounded-full text-sm">PreOrdered</span>
                                                                
                            @else
                                <span class="px-3 py-1 text-sm">N/A</span>
                            @endif
                            {{ $detail->order_detail_id }}
                        </td>
                        <td class=" px-5 py-1">
                            @if ($detail->model_image)
                                <img src="{{ asset('product-images/' . $detail->model_image) }}" alt="{{ $detail->product_name }}" width="200">
                            @else
                                <span>No Image</span>
                            @endif
                        </td>
                        <td class=" px-5 py-1">{{ $detail->model_id }}</td>
                        <td class=" px-5 py-1">{{ $detail->product_name }}</td>
                        <td class=" px-5 py-1">{{ $detail->brand_name }}</td>
                        <td class=" px-5 py-1">{{ $detail->quantity }}</td>
                        <td class=" px-5 py-1">₱{{ $detail->price }}</td>
                        <td class=" px-5 py-1">₱{{ $detail->total_price }}</td>
                       
                        <td class=" px-5 py-1">
                        <div class="mt-2">
                                    <label for="edit_status_{{ $detail->order_detail_id }}" class="text-sm mr-2">Edit Status:</label>
                                    <select class="bg-gray-100 text-gray-700 px-3 py-2 rounded-md text-sm" name="edit_status_{{ $detail->order_detail_id }}" id="edit_status_{{ $detail->order_detail_id }}">
                                        <option value="pending" {{ $detail->product_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="ready_to_pickup" {{ $detail->product_status === 'ready_to_pickup' ? 'selected' : '' }}>Ready to Pickup</option>
                                        <option value="in_process" {{ $detail->product_status === 'in_process' ? 'selected' : '' }}>In Process</option>
                                        <option value="completed" {{ $detail->product_status === 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled" {{ $detail->product_status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </div>                        
                          </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        <div class ="bg-white p-4 mt-4 rounded-md">
            <p class="mt-4" style="font-size: 22px">
                    Total To Pay: ₱ 
                    {{ number_format($orderDetails->sum('total_price'), 2) }}
            </p> 
        </div>
        </div>
    </div>
@endsection
