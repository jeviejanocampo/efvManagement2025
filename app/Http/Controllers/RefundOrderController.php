<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Products;
use App\Models\Variant;
use App\Models\User;
use App\Models\RefundOrder;
use App\Models\OrderReference;
use App\Models\RefundLog;
use App\Models\OrderDetail;
use Illuminate\Http\Request;

class RefundOrderController extends Controller
{
    public function createForm()
    {
        // Get all orders (or limit it as needed)
        $orders = Order::with('customer')->get();

        return view('staff.content.RequestForm', compact('orders'));
    }

    public function store(Request $request)
    {
        // Set user_id to 0 if null
        $request->merge([
            'user_id' => $request->input('user_id') ?? 0,
        ]);
    
        // Validate the input data
        $validated = $request->validate([
            'order_id' => 'required|integer|exists:orders,order_id',
            'user_id' => 'required|integer|min:0', // allow 0 (means guest)
            'refund_reason' => 'required|string',
            'processed_by' => 'required|integer|exists:users,id',
            'status' => 'required|string',
        ]);
    
        // Create the refund order
        RefundOrder::create($validated);
    
        return redirect()->back()->with('success', 'Refund request submitted successfully!');
    }    

    public function editDetails($order_id)
    {
        $orderDetails = OrderDetail::where('order_id', $order_id)->get();
    
        foreach ($orderDetails as $detail) {
            // Try to find the product in Variant first
            $variant = \App\Models\Variant::where('model_id', $detail->model_id)
                                          ->where('product_name', $detail->product_name)
                                          ->first();
    
            if ($variant) {
                $detail->stocks_quantity = $variant->stocks_quantity;
            } else {
                // Fallback to Products table
                $product = \App\Models\Products::where('model_id', $detail->model_id)
                                               ->where('model_name', $detail->product_name)
                                               ->first();
    
                $detail->stocks_quantity = $product ? $product->stocks_quantity : 0;
            }
        }
    
        return view('staff.content.staffEditDetailsRefund', compact('orderDetails'));
    }

    public function updateOrderDetails(Request $request)
    {
        try {
            // Start a transaction to ensure data consistency
            \DB::beginTransaction();
        
            // Loop through the order details and update each one
            foreach ($request->product_name as $orderDetailId => $productName) {
                $orderDetail = OrderDetail::findOrFail($orderDetailId);
        
                // Update quantity and calculate new total_price (quantity * unit_price)
                $quantity = $request->quantity[$orderDetailId];
                $unitPrice = $orderDetail->price;
                $totalPrice = $quantity * $unitPrice;
        
                // Update the order detail record
                $orderDetail->update([
                    'quantity' => $quantity,
                    'total_price' => $totalPrice,
                ]);
            }
        
            // After updating all order details, update the total amount in the orders table
            $orderId = $request->order_id;
            $order = Order::findOrFail($orderId);
        
            // Recalculate the total amount to pay for the order
            $totalAmount = OrderDetail::where('order_id', $orderId)->sum('total_price');
            
            // Update total_amount and original_total_amount in the orders table
            $order->update([
                'total_price' => $totalAmount,               // New total amount to pay
                'original_total_amount' => $totalAmount,       // Assuming original_total_amount is also updated here
            ]);
        
            // Commit the transaction
            \DB::commit();
    
            // Redirect back with success message
        return redirect()->route('edit.product', ['order_id' => $orderId])->with('success', 'Order details updated successfully.');
        } catch (\Exception $e) {
            // Rollback in case of error
            \DB::rollBack();
    
            // Redirect back with error message
            return redirect()->route('edit.product', ['order_id' => $request->order_id])->with('error', 'Error updating order details: ' . $e->getMessage());
        }
    }

    public function editDetailsQueue($order_id)
    {
        $order = Order::find($order_id); // Get the order details
        $orderDetails = OrderDetail::where('order_id', $order_id)->get(); // Get the associated order details
    
        // Pass the details to the view
        return view('staff.content.staffEditDetailsRefundQueue', compact('orderDetails'));
    }

    public function updateOrderDetailsQueue(Request $request)
    {
        try {
            // Start a transaction to ensure data consistency
            \DB::beginTransaction();
        
            // Loop through the order details and update each one
            foreach ($request->product_name as $orderDetailId => $productName) {
                $orderDetail = OrderDetail::findOrFail($orderDetailId);
        
                // Update quantity and calculate new total_price (quantity * unit_price)
                $quantity = $request->quantity[$orderDetailId];
                $unitPrice = $orderDetail->price;
                $totalPrice = $quantity * $unitPrice;
        
                // Update the order detail record
                $orderDetail->update([
                    'quantity' => $quantity,
                    'total_price' => $totalPrice,
                ]);
            }
        
            // After updating all order details, update the total amount in the orders table
            $orderId = $request->order_id;
            $order = Order::findOrFail($orderId);
        
            // Recalculate the total amount to pay for the order
            $totalAmount = OrderDetail::where('order_id', $orderId)->sum('total_price');
            
            // Update total_amount and original_total_amount in the orders table
            $order->update([
                'total_price' => $totalAmount,               // New total amount to pay
                'original_total_amount' => $totalAmount,       // Assuming original_total_amount is also updated here
            ]);
        
            // Commit the transaction
            \DB::commit();
    
            // Redirect back with success message
        return redirect()->route('edit.product.queue', ['order_id' => $orderId])->with('success', 'Order details updated successfully.');
        } catch (\Exception $e) {
            // Rollback in case of error
            \DB::rollBack();
    
            // Redirect back with error message
            return redirect()->route('edit.product.queue', ['order_id' => $request->order_id])->with('error', 'Error updating order details: ' . $e->getMessage());
        }
    }

    public function viewRefundLog()
    {
        $logs = RefundLog::with('user')->orderBy('refunded_at', 'desc')->paginate(11); // 10 items per page
        return view('staff.content.RefundLog', compact('logs'));
    }
    

    public function RefundViewList()
    {
        $refunds = RefundOrder::orderBy('created_at', 'desc')->paginate(10);
        return view('staff.content.RefundView', compact('refunds'));
    }

    public function RefundDetailsView($order_id, $reference_id = null)
    {

        $orderDetails = OrderDetail::where('order_id', $order_id)->get();

        $refund = RefundOrder::where('order_id', $order_id)->first();
        $orderReference = OrderReference::where('order_id', $order_id)->first();
        
        // Fetch order details from the 'orders' table
        $order = Order::where('order_id', $order_id)->first();

        // Pass refund, orderReference, reference_id, and order details to the view
        return view('staff.content.RefundDetails', compact('refund', 'orderReference', 'reference_id', 'order', 'orderDetails'));
    }

        
    
    

        

}
