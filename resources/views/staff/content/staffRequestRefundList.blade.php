@extends('staff.dashboard.StaffMain')

@section('content')
<style>
  td {
    text-align: center;
  }
</style>

<div class="bg-white p-4 rounded-md" style="box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);">
    <h1 class="text-4xl font-semibold mb-4">Request Refund List</h1>
    
    <table class="w-full border-collapse border border-gray-300">
        <thead>
            <tr class="bg-gray-200">
                <!-- <th class="p-2 border">Refund ID</th> -->
                <th class="p-2 border">Order ID</th>
                <th class="p-2 border">User ID</th>
                <th class="p-2 border">Status</th>
                <th class="p-2 border">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($refunds as $refund)
            <tr class="border">
                <!-- <td class="p-2 border">0000{{ $refund->refund_id }}</td> -->
                <td class="p-2 border">0000{{ $refund->order_id }}</td>
                <td class="p-2 border">{{ $refund->customer->full_name ?? 'Unknown' }}</td>
                <td class="p-2 border">{{ ucfirst($refund->status) }}</td>
                <td class="p-2 border">
                    <a href="{{ route('staff.refundRequestForm', ['order_id' => $refund->order_id]) }}" 
                    class="bg-blue-700 text-white px-3 py-1 rounded hover:bg-blue-600">
                        View Details
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
