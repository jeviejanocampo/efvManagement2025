<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\OrderDetail;  
use App\Models\Models;  
use App\Models\Products;  
use App\Models\ActivityLog;  
use Carbon\Carbon;


class OrderController extends Controller
{
    public function fetchOrders()
    {
        // Fetch orders with scan_status "Yes" updated in the last 5 minutes
        $orders = Order::where('scan_status', 'Yes')
            ->where('updated_at', '>=', Carbon::now()->subMinutes(5))
            ->get(['order_id', 'scan_status']); // Retrieve only necessary columns
    
        foreach ($orders as $order) {
            // Fetch the last 2 part_id, variant_id, and brand_name from OrderDetail
            $orderDetails = OrderDetail::where('order_id', $order->order_id)
                ->latest('order_detail_id') // Sort by latest
                ->limit(2) // Get last 2 rows
                ->get(['part_id', 'variant_id', 'brand_name']); // Fetch required columns
    
            $cleanParts = collect();
            $brandNames = collect();
    
            foreach ($orderDetails as $detail) {
                if (!empty($detail->variant_id) && $detail->variant_id != 0) {
                    // Fetch part_id from Variant if variant_id exists
                    $variantPartId = \App\Models\Variant::where('variant_id', $detail->variant_id)
                        ->value('part_id');
    
                    // Trim to first 3 characters
                    $cleanPart = $variantPartId ? substr(preg_replace('/[^A-Za-z0-9]/', '', $variantPartId), 0, 3) : '';
    
                    // Fetch brand_name from OrderDetail
                    $brandName = $detail->brand_name ? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $detail->brand_name), 0, 3)) : '';
    
                    $brandNames->push($brandName);
                } else {
                    // Use part_id directly from OrderDetail
                    $cleanPart = substr(preg_replace('/[^A-Za-z0-9]/', '', $detail->part_id), 0, 3);
                }
    
                $cleanParts->push($cleanPart);
            }
    
            // Fetch the brand_name using m_part_id from Products table
            $productBrandName = \App\Models\Products::whereIn('m_part_id', $orderDetails->pluck('part_id'))
                ->value('brand_name');
    
            // Trim product brand name to first 3 letters
            $shortProductBrand = $productBrandName ? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $productBrandName), 0, 3)) : '';
    
            // If variant_id exists, use brand from OrderDetail, otherwise from Products table
            $finalBrand = $brandNames->isNotEmpty() ? $brandNames->first() : $shortProductBrand;
    
            // Format Reference ID (BRAND + PART_IDs + ORDER_ID)
            if ($cleanParts->count() === 2) {
                $order->reference_id = $finalBrand . '-'. $cleanParts[0] . $cleanParts[1] . '-' . $order->order_id;
            } elseif ($cleanParts->count() === 1) {
                $order->reference_id = $finalBrand . '-' . $cleanParts[0] . '-' . $order->order_id;
            } else {
                $order->reference_id = $finalBrand . '-' . $order->order_id;
            }
        }
    
        return response()->json($orders);
    }
    
    
    
    
    public function show($order_id, Request $request)
    {
        $reference_id = $request->query('reference_id'); // Get reference_id from URL query
    
        $order = Order::find($order_id);
        if (!$order) {
            abort(404, 'Order not found');
        }
    
        // Fetch order details based on order_id
        $orderDetails = OrderDetail::where('order_id', $order_id)->get();
    
        // Fetch images for each order detail's model_id
        foreach ($orderDetails as $detail) {
            $detail->model_image = Models::where('model_id', $detail->model_id)->pluck('model_img')->first();
        }
    
        return view('staff.content.staffOrderDetails', compact('order', 'orderDetails', 'reference_id'));
    }
    


    public function staffOrderOverview()
    {
        $orders = \App\Models\Order::select('order_id', 'user_id', 'total_items', 'total_price', 'created_at', 'status', 'payment_method', 'overall_status')
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($orders as $order) {
            // Fetch the latest 2 part_id, variant_id, and brand_name from OrderDetail
            $orderDetails = OrderDetail::where('order_id', $order->order_id)
                ->latest('order_detail_id')
                ->take(2)
                ->get(['part_id', 'variant_id', 'brand_name']);

            $cleanParts = collect();
            $brandNames = collect();

            foreach ($orderDetails as $detail) {
                if (!empty($detail->variant_id) && $detail->variant_id != 0) {
                    // Fetch part_id from the variants table based on variant_id
                    $variantPartId = \App\Models\Variant::where('variant_id', $detail->variant_id)
                        ->value('part_id');

                    // Trim to first 3 characters
                    $cleanPart = $variantPartId ? substr(preg_replace('/[^A-Za-z0-9]/', '', $variantPartId), 0, 3) : '';

                    // Fetch brand_name from OrderDetail
                    $brandName = $detail->brand_name ? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $detail->brand_name), 0, 3)) : '';

                    $brandNames->push($brandName);
                } else {
                    // Use part_id directly from OrderDetail
                    $cleanPart = substr(preg_replace('/[^A-Za-z0-9]/', '', $detail->part_id), 0, 4);
                }

                $cleanParts->push($cleanPart);
            }

            // Fetch the brand_name using m_part_id from Products table
            $productBrandName = \App\Models\Products::whereIn('m_part_id', $orderDetails->pluck('part_id'))
                ->value('brand_name');

            // Trim brand_name to first 3 letters and convert to uppercase
            $shortProductBrand = $productBrandName ? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '-', $productBrandName), 0, 3)) : '';

            // If variant_id exists, use the brand from OrderDetail, otherwise use from Products table
            $finalBrand = $brandNames->isNotEmpty() ? $brandNames->first() : $shortProductBrand;

            // Format Reference ID
            if ($cleanParts->count() === 2) {
                $order->reference_id = $finalBrand . '-' . $cleanParts[0] . '' . $cleanParts[1];
            } elseif ($cleanParts->count() === 1) {
                $order->reference_id = $finalBrand . '-' . $cleanParts[0];
            } else {
                $order->reference_id = $finalBrand;
            }
        }

        session(['pendingCount' => \App\Models\Order::where('status', 'Pending')->count()]);

        return view('staff.content.staffOrderOverview', compact('orders'));
    }


    public function stockOrderOverview()
    {
        $orders = \App\Models\Order::select('order_id', 'user_id', 'total_items', 'total_price', 'created_at', 'status', 'payment_method', 'overall_status')
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($orders as $order) {
            // Fetch the latest 2 part_id, variant_id, and brand_name from OrderDetail
            $orderDetails = OrderDetail::where('order_id', $order->order_id)
                ->latest('order_detail_id')
                ->take(2)
                ->get(['part_id', 'variant_id', 'brand_name']);

            $cleanParts = collect();
            $brandNames = collect();

            foreach ($orderDetails as $detail) {
                if (!empty($detail->variant_id) && $detail->variant_id != 0) {
                    // Fetch part_id from the variants table based on variant_id
                    $variantPartId = \App\Models\Variant::where('variant_id', $detail->variant_id)
                        ->value('part_id');

                    // Trim to first 3 characters
                    $cleanPart = $variantPartId ? substr(preg_replace('/[^A-Za-z0-9]/', '', $variantPartId), 0, 3) : '';

                    // Fetch brand_name from OrderDetail
                    $brandName = $detail->brand_name ? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $detail->brand_name), 0, 3)) : '';

                    $brandNames->push($brandName);
                } else {
                    // Use part_id directly from OrderDetail
                    $cleanPart = substr(preg_replace('/[^A-Za-z0-9]/', '', $detail->part_id), 0, 4);
                }

                $cleanParts->push($cleanPart);
            }

            // Fetch the brand_name using m_part_id from Products table
            $productBrandName = \App\Models\Products::whereIn('m_part_id', $orderDetails->pluck('part_id'))
                ->value('brand_name');

            // Trim brand_name to first 3 letters and convert to uppercase
            $shortProductBrand = $productBrandName ? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '-', $productBrandName), 0, 3)) : '';

            // If variant_id exists, use the brand from OrderDetail, otherwise use from Products table
            $finalBrand = $brandNames->isNotEmpty() ? $brandNames->first() : $shortProductBrand;

            // Format Reference ID
            if ($cleanParts->count() === 2) {
                $order->reference_id = $finalBrand . $cleanParts[0] . $cleanParts[1];
            } elseif ($cleanParts->count() === 1) {
                $order->reference_id = $finalBrand . $cleanParts[0];
            } else {
                $order->reference_id = $finalBrand;
            }
        }

        session(['pendingCount' => \App\Models\Order::where('status', 'Pending')->count()]);

        return view('stockclerk.content.stockOrderOverview', compact('orders'));
    }    


    public function ManagerstockOrderOverview()
    {
        $orders = \App\Models\Order::select('order_id', 'user_id', 'total_items', 'total_price', 'created_at', 'status', 'payment_method', 'overall_status')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    
        foreach ($orders as $order) {
            $orderDetails = OrderDetail::where('order_id', $order->order_id)
                ->latest('order_detail_id')
                ->take(2)
                ->get(['part_id', 'variant_id', 'brand_name']);
    
            $cleanParts = collect();
            $brandNames = collect();
    
            foreach ($orderDetails as $detail) {
                if (!empty($detail->variant_id) && $detail->variant_id != 0) {
                    $variantPartId = \App\Models\Variant::where('variant_id', $detail->variant_id)
                        ->value('part_id');
                    $cleanPart = $variantPartId ? substr(preg_replace('/[^A-Za-z0-9]/', '', $variantPartId), 0, 3) : '';
                    $brandName = $detail->brand_name ? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $detail->brand_name), 0, 3)) : '';
                    $brandNames->push($brandName);
                } else {
                    $cleanPart = substr(preg_replace('/[^A-Za-z0-9]/', '', $detail->part_id), 0, 4);
                }
                $cleanParts->push($cleanPart);
            }
    
            $productBrandName = \App\Models\Products::whereIn('m_part_id', $orderDetails->pluck('part_id'))
                ->value('brand_name');
            $shortProductBrand = $productBrandName ? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '-', $productBrandName), 0, 3)) : '';
            $finalBrand = $brandNames->isNotEmpty() ? $brandNames->first() : $shortProductBrand;
    
            if ($cleanParts->count() === 2) {
                $order->reference_id = $finalBrand . $cleanParts[0] . $cleanParts[1];
            } elseif ($cleanParts->count() === 1) {
                $order->reference_id = $finalBrand . $cleanParts[0];
            } else {
                $order->reference_id = $finalBrand;
            }
        }
    
        session(['pendingCount' => \App\Models\Order::where('status', 'Pending')->count()]);
    
        return view('manager.content.ManagerstockOrderOverview', compact('orders'));
    }     


    public function details($order_id, Request $request)
    {

        $reference_id = $request->query('reference_id');

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

    public function stockDetails($order_id, Request $request)
    {

        $reference_id = $request->query('reference_id');

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

    public function ManagerstockDetails($order_id, Request $request)
    {

        $reference_id = $request->query('reference_id');

        $order = Order::find($order_id); // Fetch the order by ID
        if (!$order) {
            abort(404, 'Order not found'); // Handle invalid order ID
        }
    
        // Fetch the order details based on the order_id, including part_id
        $orderDetails = OrderDetail::where('order_id', $order_id)->get();
    
        // Fetch images for each order detail's model_id and include part_id
        foreach ($orderDetails as $detail) {
            $detail->model_image = Models::where('model_id', $detail->model_id)->pluck('model_img')->first();
            $detail->part_id = OrderDetail::where('order_detail_id', $detail->order_detail_id)->pluck('part_id')->first();
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

        // If status is "Completed", add the total_price of the product to the order total
        if ($request->status === 'Completed') {
            $order->total_price += $orderDetail->total_price;
            $order->save(); // <-- Fixed! Ensure the new total price is saved
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
