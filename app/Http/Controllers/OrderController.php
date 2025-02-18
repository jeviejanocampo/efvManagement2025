<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\OrderDetail;  
use App\Models\Models;  
use App\Models\ActivityLog;  
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
    
        return view('staff.content.staffOrderDetails', compact('order', 'orderDetails'));
    }

    public function staffOrderOverview()
    {
        // Fetch all orders, customize as needed
        $orders = \App\Models\Order::select('order_id', 'user_id', 'total_items', 'total_price', 'created_at', 'status', 'payment_method', 'overall_status')
            ->orderBy('created_at', 'desc')
            ->get();
    
        // Count the number of orders with status 'Pending'
        $pendingCount = \App\Models\Order::where('status', 'Pending')->count();
    
        // Store the pending count in the session
        session(['pendingCount' => $pendingCount]);
    
        // Return the view with orders data and the pending count
        return view('staff.content.staffOrderOverview', compact('orders'));
    }

    public function stockOrderOverview()
    {
        // Fetch all orders, customize as needed
        $orders = \App\Models\Order::select('order_id', 'user_id', 'total_items', 'total_price', 'created_at', 'status', 'payment_method', 'overall_status')
            ->orderBy('created_at', 'desc')
            ->get();
    
        // Count the number of orders with status 'Pending'
        $pendingCount = \App\Models\Order::where('status', 'Pending')->count();
    
        // Store the pending count in the session
        session(['pendingCount' => $pendingCount]);
    
        // Return the view with orders data and the pending count
        return view('stockclerk.content.stockOrderOverview', compact('orders'));
    }
    


    public function ManagerstockOrderOverview()
    {
        // Fetch all orders, customize as needed
        $orders = \App\Models\Order::select('order_id', 'user_id', 'total_items', 'total_price', 'created_at', 'status', 'payment_method', 'overall_status')
            ->orderBy('created_at', 'desc')
            ->get();
    
        // Count the number of orders with status 'Pending'
        $pendingCount = \App\Models\Order::where('status', 'Pending')->count();
    
        // Store the pending count in the session
        session(['pendingCount' => $pendingCount]);
    
        // Return the view with orders data and the pending count
        return view('manager.content.ManagerstockOrderOverview', compact('orders'));
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

    public function stockDetails($order_id)
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
    
        return view('stockclerk.content.stockOverviewDetails', compact('order', 'orderDetails'));
    }

    public function ManagerstockDetails($order_id)
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
    
        return view('manager.content.managerstockOverviewDetails', compact('order', 'orderDetails'));
    }

    public function updateStatus($order_id, Request $request)
    {
        // Validate the request
        $request->validate([
            'status' => 'required|in:pending,Ready to Pickup,In Process,Completed,Cancelled',
        ]);
    
        // Find the order by order_id
        $order = Order::find($order_id);
    
        // Check if the order exists
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ]);
        }
    
        // Update the order status
        $order->status = $request->input('status');
    
        // If status is "Completed", set scan_status to "Completed" as well
        if ($order->status === 'Completed') {
            $order->scan_status = 'Completed';
    
            // Use the OrderDetail model to update product_status
            $orderDetails = OrderDetail::where('order_id', $order_id)->get();
    
            foreach ($orderDetails as $orderDetail) {
                $orderDetail->product_status = 'Completed';
                $orderDetail->save(); // Save each updated order detail
            }
        }
    
        $order->save(); // Save the updated order
    
        // Get the role of the user
        $user = Auth::user();  // Get the currently authenticated user
        $role = $user->role;   // Get the role of the user
    
        // Log the activity (user_id, role, and activity)
        ActivityLog::create([
            'user_id' => Auth::id(),
            'role' => $role, // Insert the user's role
            'activity' => "Updated order #$order_id status to {$order->status}",
        ]);
    
        // Return response
        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully and product statuses.',
        ]);
    }
    
    
    public function updateProductStatus(Request $request, $orderDetailId)
    {
        // Check if the order detail exists
        $orderDetail = OrderDetail::find($orderDetailId);

        if (!$orderDetail) {
            return response()->json(['message' => 'Order Detail not found.'], 404);
        }

        // Validate the status update
        $validStatuses = ['pending', 'Ready to Pickup', 'In Process', 'Completed', 'Cancelled'];
        if (!in_array($request->status, $validStatuses)) {
            return response()->json(['message' => 'Invalid status.'], 400);
        }

        // Get the order associated with this order detail
        $order = Order::find($orderDetail->order_id);

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        // If status is "Cancelled", subtract the total_price of the product from the order total
        if ($request->status === 'Cancelled') {
            $order->total_price -= $orderDetail->total_price;
            $order->save();
        }

        // Update the product status
        $orderDetail->product_status = $request->status;
        $orderDetail->save();

        // Get the role of the user
        $user = Auth::user();
        $role = $user->role;

        // Log the activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'role' => $role,
            'activity' => "Updated product status for order detail #$orderDetailId to {$orderDetail->product_status}",
        ]);

        return response()->json(['message' => 'Product status updated successfully.', 'success' => true]);
    }

    
    
    

}
