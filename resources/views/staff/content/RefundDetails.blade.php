@extends('staff.dashboard.StaffMain')

@section('content')
<!-- Back Navigation Button -->

<style>

    @media print {
        body{
            zoom: 65%;
        }
        .no-print {
        display: none !important;
    }
    .no-print-styles {
        background-color: transparent !important;  /* Remove background */
        box-shadow: none !important;  /* Remove box shadow */
        padding: 0 !important;  /* Remove padding */
        border-radius: 0 !important;  /* Remove border radius */
    }
}

</style>
<a href="{{ url()->previous() }}" class="text-white bg-gray-700 p-2 rounded-lg mb-4 inline-flex items-center hover:bg-gray-600">
    <i class="fa-solid fa-arrow-left"></i>
</a>

<div class="bg-white p-8 rounded-md no-print-styles" style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">

    <div class="flex items-center justify-between mb-4 border-b border-gray pb-2">
        <p class="text-4xl font-semibold">Replacement Report</p>

        <button onclick="window.print()" 
                class="no-print bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-800 transition print:bg-transparent print:text-black print:shadow-none print:px-0 print:py-0 print:rounded-none">
            Print Report
        </button>

    </div>

    <div class="border-b border-gray pb-4">
        <p class="text-2xl font-medium">Reference ID: {{ $reference_id ?? 'N/A' }}</p>
        
        @if ($refund->refund_completed_at)
            <p><span class="font-semibold">Processed Completed:</span> 
            {{ \Carbon\Carbon::parse($refund->refund_completed_at)->format('F j, Y - g:i A') }}
            </p>
        @else
            <p><span class="font-semibold">Processed Completed:</span> N/A</p>
        @endif
    </div>

    <!-- Grid Layout -->
    <div class="grid grid-cols-2 gap-8 pb-4 pt-4">
        <!-- Left Grid: Order and User Information -->
        <div class="space-y-6 mb-4">
            <div>
                <strong class="text-sm">Order ID:</strong>
                <p>{{ $order->order_id }}</p>
            </div>  
            <div>
                <strong class="text-sm">User</strong>
                <p>{{ $refund->customer?->full_name ?? 'Unknown User' }}<p>
                </div>

            <div>
                <strong class="text-sm">Order Created</strong>
                <p>{{ $order->created_at->format('F j, Y - g:i A') }}</p>
            </div>

        </div>

        <!-- Right Grid: Order Details (Total, Payment Method, Status) -->
        <div class="space-y-6">
             
                <div>
                    <strong class="text-sm">Payment Method</strong>
                    <p>{{ $order->payment_method }}</p>
                </div>

                <div>
                    <strong class="text-sm">Payment via</strong>
                    <p>{{ $refund-> refund_method ?? 'NULL' }}</p>
                </div>

                <div>
                    <strong>Processed By:</strong>
                    <p>{{ $refund->processedByUser->name ?? 'Unknown' }} (Staff)</p>
                </div>
                
            </div>
        </div>

        <div class="grid grid-cols-2 gap-8  pb-4 pt-4">
            <div class="space-y-6">
                <div>
                    <strong class="text-sm">Refund Reason:</strong>
                    <p>{{ $refund->refund_reason ?? 'NULL' }}</p>
                </div>
                <div>
                    <strong class="text-sm">Extra Details:</strong>
                    <p>{{ $refund->extra_details ?? 'NULL' }}</p>
                </div>
            </div>
            <div class="space-y-6">
                <div>
                    <strong class="text-sm">Details Selected:</strong>
                    <p>{{ $refund->details_selected ?? 'NULL' }}</p>
                </div>
                <div>
                    <strong class="text-sm">Status:</strong>
                    <p>{{ $order->status }}</p>
                </div>
            </div>
        </div>



        <div class="text-sm text-gray-500 italic mt-4">
            Note: The total price of refunded items has been returned to the customer and is excluded from the updated change given.
        </div>

        <div class="mt-2">
<table class="w-full text-sm text-center text-gray-700 border-b border-gray mb-4">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2"></th> 
                    <th class="p-2">Product Name</th>
                    <th class="p-2">Brand Name</th>
                    <th class="p-2">Quantity</th>
                    <th class="p-2">Price</th>
                    <th class="p-2">SubTotal</th>
                    <th class="p-2">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orderDetails as $detail)
                    <tr>
                        <td class="p-2">
                            @php
                                $imagePath = null;
                                if ($detail->variant_id && $detail->variant_id != 0 && $detail->variant) {
                                    $imagePath = $detail->variant->variant_image;
                                } elseif ($detail->model && $detail->model->model_img) {
                                    $imagePath = $detail->model->model_img;
                                }
                            @endphp
                            @if ($imagePath)
                                <img src="{{ asset('product-images/' . $imagePath) }}" alt="Product Image" class="w-24 h-24 object-cover mx-auto rounded-md">
                            @else
                                <span class="text-gray-400 italic">No image</span>
                            @endif
                        </td>

                        <td class="p-2">
                            <!-- Original Product Name -->
                            @if ($detail->product_status !== 'refunded')
                                <div>
                                    <span class="text-blue-600 text-xs font-semibold">Updated Product:</span>
                                    <span class="text-gray-700 ml-1">{{ $detail->product_name }}</span>
                                </div>
                            @else
                                <div>
                                    <span class="text-gray-700">{{ $detail->product_name }}</span>
                                </div>
                            @endif

                            @php
                                $replacementLabel = null;

                                // Check if there's a replacement variant or model
                                if ($detail->variant_id && $detail->variant_id != 0 && $detail->changed_variant_id) {
                                    $changedVariant = \App\Models\Variant::find($detail->changed_variant_id);
                                    if ($changedVariant) {
                                        $replacementLabel = $changedVariant->product_name;
                                    }
                                } elseif ($detail->changed_model_id) {
                                    $changedModel = \App\Models\Products::where('model_id', $detail->changed_model_id)->first();
                                    if ($changedModel) {
                                        $replacementLabel = $changedModel->model_name;
                                    }
                                }
                            @endphp

                            <!-- Display Replacement Product -->
                            @if ($replacementLabel && $detail->product_status !== 'refunded')
                                <div class="mt-1">
                                    <span class="text-red-600 text-xs font-semibold">Old Product:</span>
                                    <span class="text-gray-700 underline line-through ml-1 text-1xl">{{ $replacementLabel }}</span>
                                </div>
                            @endif
                        </td>

                        <td class="p-2">{{ $detail->brand_name }}</td>
                        <td class="p-2">{{ $detail->quantity }}</td>
                        <td class="p-2">₱ {{ number_format($detail->price, 2) }}</td>
                        <td class="p-2">₱ {{ number_format($detail->total_price, 2) }}</td>
                        <td class="p-2 
                            @if($detail->product_status === 'Completed') bg-green-600 text-white 
                            @elseif($detail->product_status === 'refunded') bg-red-600 text-white 
                            @endif
                        ">
                            {{ $detail->product_status }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>        

        @php
            $refundedTotal = 0;
            foreach ($orderDetails as $detail) {
                if ($detail->product_status === 'refunded') {
                    $refundedTotal += $detail->total_price;
                }
            }

            $adjustedChangeGiven = $refund->change_given + $refundedTotal;
        @endphp

        <div class="flex justify-end p-4">  

                <div class="space-y-4 gap-10">

                <div class="text-2xl font-bold text-gray-700 pb-2">Overview</div>

                <!-- <div class="flex justify-between">
                    <span class="text-gray-400">Processed By:</span>
                    <span class="text-gray-700">{{ $refund->processedByUser->name ?? 'Unknown' }} (Staff)</span>
                </div> -->

                <!-- Original Total Amount -->
                <div class="flex justify-between">
                    <span class="text-gray-400 mr-24">Original Total Amount:</span>
                    <span class="text-gray-700">₱ {{ number_format($order->original_total_amount, 2) }}</span>
                </div>

                <!-- Change Given -->
                <div class="flex justify-between">
                    <span class="text-gray-400">Change Given:</span>
                    <span class="text-gray-700">₱ {{ number_format($refund->change_given, 2) }}</span>
                </div>

                <!-- Amount Added -->
                <div class="flex justify-between border-b border-gray pb-4">
                    <span class="text-gray-400">Amount Added:</span>
                    <span class="text-gray-700">₱ {{ number_format($refund->amount_added, 2) }}</span>
                </div>

                @php
                    $refundedTotal = $orderDetails->where('product_status', 'refunded')->sum('total_price');
                    $completedTotal = $orderDetails->where('product_status', 'Completed')->sum('total_price');
                @endphp


                <div class="flex justify-between">
                    <span class="text-gray-800">Updated Total Amount:</span>
                    <span class="text-gray-700">
                        ₱ {{ number_format($completedTotal, 2) }}
                    </span>
                </div>

               
            </div>
    </div>

    <div class="text-center text-sm text-gray-600 mt-16">
        Processed by EFV AUTO PARTS MANAGEMENT, all rights reserved © 2025
    </div>

</div>


</div>
@endsection

@section('scripts')
<!-- Add any specific scripts if needed -->
@endsection
