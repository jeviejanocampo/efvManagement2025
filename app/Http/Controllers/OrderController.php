<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Carbon\Carbon;

class OrderController extends Controller
{
    public function fetchOrders()
    {
        // Fetch orders with scan_status "Yes" that have been updated in the last 5 minutes
        $orders = Order::where('scan_status', 'Yes')
            ->where('updated_at', '>=', Carbon::now()->subMinutes(5))
            ->get(['order_id', 'scan_status']); // Only retrieve necessary columns

        return response()->json($orders);
    }
    
    public function show($order_id)
    {
        // Fetch the order using the order_id
        $order = Order::findOrFail($order_id); // Replace this with your model's logic to fetch the order by ID
    
        return view('staff.content.staffOrderDetails', compact('order'));
    }
    
}
