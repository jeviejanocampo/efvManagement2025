@extends('staff.dashboard.StaffMain')

@section('content')
    <div class="p-4">
        <h2 class="text-2xl font-semibold mb-4">Order Details</h2>
        <a href="{{ url()->previous() }}" 
        class="bg-blue-500 text-white px-5 py-1 rounded hover:bg-blue-600 mb-5">Back</a>

        <div class="mt-4">
            <strong>Order ID:</strong> {{ $order->order_id }}<br>
            <strong>User ID:</strong> {{ $order->user_id }}<br>
            <strong>Total Items:</strong> {{ $order->total_items }}<br>
            <strong>Total Price:</strong> ${{ $order->total_price }}<br>
            <strong>Status:</strong> {{ $order->status }}<br>
            <strong>Payment Method:</strong> {{ $order->payment_method }}<br>
            <strong>Created At:</strong> {{ $order->created_at }}<br>
        </div>

        <!-- Order Details Table -->
        <h3 class="text-xl font-semibold mt-6">Product Details</h3>
        <table class="table-auto w-full border-collapse border border-gray-300 mt-4">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border border-gray-300 px-5 py-1">Order Detail ID</th>
                    <th class="border border-gray-300 px-5 py-1">Model ID</th>
                    <th class="border border-gray-300 px-5 py-1">Variant ID</th>
                    <th class="border border-gray-300 px-5 py-1">Product Name</th>
                    <th class="border border-gray-300 px-5 py-1">Brand Name</th>
                    <th class="border border-gray-300 px-5 py-1">Quantity</th>
                    <th class="border border-gray-300 px-5 py-1">Price</th>
                    <th class="border border-gray-300 px-5 py-1">Total Price</th>
                    <th class="border border-gray-300 px-5 py-1">Product Status</th>
                    <th class="border border-gray-300 px-5 py-1">Created At</th>
                    <th class="border border-gray-300 px-5 py-1">Image</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orderDetails as $detail)
                    <tr class="border border-gray-300">
                        <td class="border border-gray-300 px-5 py-1">{{ $detail->order_detail_id }}</td>
                        <td class="border border-gray-300 px-5 py-1">{{ $detail->model_id }}</td>
                        <td class="border border-gray-300 px-5 py-1">{{ $detail->variant_id }}</td>
                        <td class="border border-gray-300 px-5 py-1">{{ $detail->product_name }}</td>
                        <td class="border border-gray-300 px-5 py-1">{{ $detail->brand_name }}</td>
                        <td class="border border-gray-300 px-5 py-1">{{ $detail->quantity }}</td>
                        <td class="border border-gray-300 px-5 py-1">${{ $detail->price }}</td>
                        <td class="border border-gray-300 px-5 py-1">${{ $detail->total_price }}</td>
                        <td class="border border-gray-300 px-5 py-1">{{ $detail->product_status }}</td>
                        <td class="border border-gray-300 px-5 py-1">{{ $detail->created_at }}</td>
                        <td class="border border-gray-300 px-5 py-1">
                            @if ($detail->model_image)
                                <img src="{{ asset('product-images/' . $detail->model_image) }}" alt="{{ $detail->product_name }}" width="300">
                            @else
                                <span>No Image</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
