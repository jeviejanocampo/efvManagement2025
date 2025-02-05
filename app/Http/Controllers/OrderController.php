<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;  
use App\Models\Models;  
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

    public function staffOrderOverview()
    {
        // Fetch all orders, customize as needed (e.g., pagination or filters)
        $orders = \App\Models\Order::select('order_id', 'user_id', 'total_items', 'total_price', 'created_at', 'status', 'payment_method', 'overall_status')
            ->orderBy('created_at', 'desc') // Sort by most recent
            ->get();

        // Return the view with the orders data
        return view('staff.content.staffOrderOverview', compact('orders'));
    }

    public function details($order_id)
    {
        $order = Order::find($order_id); // Fetch the order by ID
        if (!$order) {
            abort(404, 'Order not found'); // Handle invalid order ID
        }
    
        // Fetch the order details based on the order_id
        $orderDetails = OrderDetail::where('order_id', $order_id)->get();
    
        // Fetch images for each order detail's model_id
        foreach ($orderDetails as $detail) {
            $detail->model_image = Models::where('model_id', $detail->model_id)->pluck('model_img')->first();
        }
    
        return view('staff.content.staffOverviewDetails', compact('order', 'orderDetails'));
    }

    
    

}
