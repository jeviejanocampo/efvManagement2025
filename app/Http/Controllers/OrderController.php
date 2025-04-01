<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\OrderDetail;  
use App\Models\Models;  
use App\Models\Products;  
use App\Models\ActivityLog;  
use App\Models\Variant;  
use Carbon\Carbon;
use App\Models\RefundOrder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;



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
    
    public function refundRequests()
    {
        // Fetch all refund requests    
        $refunds = RefundOrder::select('refund_id', 'order_id', 'user_id', 'status')->get();

       // Fetch refund requests with pagination (10 per page)
        $refunds = RefundOrder::with('customer')
        ->orderBy('created_at', 'desc') // Sort by newest first
        ->paginate(10); // Show 10 per page

        return view('staff.content.staffRequestRefundList', compact('refunds'));
    }
    
    public function showRefundRequestForm($order_id)
    {
        $refund = RefundOrder::where('order_id', $order_id)
            ->with('customer')
            ->first();

        $orderDetails = OrderDetail::where('order_id', $order_id)->get();

        if (!$refund) {
            return redirect()->route('staff.refundRequests')->with('error', 'Refund request not found.');
        }

        // Fetch all models where w_variant is NOT "YES" and include stock count
        $models = Models::where('status', 'active')
            ->where('w_variant', '!=', 'YES')
            ->with(['products' => function ($query) {
                $query->selectRaw('model_id, SUM(stocks_quantity) as total_quantity')->groupBy('model_id');
            }])
            ->get(); // Removed pagination

        // Fetch all variants where the related model has w_variant = "YES"
        $variants = Variant::whereHas('model', function ($query) {
            $query->where('w_variant', 'YES')->where('status', 'active');
        })->get(); // Removed pagination

        return view('staff.content.staffRequestRefundForm', compact('refund', 'orderDetails', 'models', 'variants'));
    }


    public function updateProductStatusRefunded(Request $request)
    {
        $order_id = $request->input('order_id');
        $product_ids = $request->input('product_id');
        $product_statuses = $request->input('product_status');
        $product_prices = $request->input('product_price');
        $variant_ids = $request->input('variant_id');
    
        $total_adjustment = 0;
    
        foreach ($product_ids as $index => $product_id) {
            $status = $product_statuses[$index];
            $price = $product_prices[$index];
            $variant_id = $variant_ids[$index];
    
            if ($variant_id != 0) {
                $orderDetail = OrderDetail::where('order_id', $order_id)
                    ->where('variant_id', $variant_id)
                    ->first();
            } else {
                $orderDetail = OrderDetail::where('order_id', $order_id)
                    ->where('model_id', $product_id)
                    ->first();
            }
    
            if ($orderDetail) {
                if ($orderDetail->product_status !== $status) {
                    if ($status === 'refunded') {
                        $total_adjustment -= $price;
    
                        if ($variant_id != 0) {
                            $variant = Variant::find($variant_id);
                            if ($variant) {
                                $variant->stocks_quantity += $orderDetail->quantity;
                                $variant->save();
                            }
                        } else {
                            $product = Products::where('model_id', $product_id)->first();
                            if ($product) {
                                $product->stocks_quantity += $orderDetail->quantity;
                                $product->save();
                            }
                        }
                    } elseif ($orderDetail->product_status === 'refunded' && $status === 'pending') {
                        $total_adjustment += $price;
    
                        if ($variant_id != 0) {
                            $variant = Variant::find($variant_id);
                            if ($variant) {
                                $variant->stocks_quantity -= $orderDetail->quantity;
                                $variant->save();
                            }
                        } else {
                            $product = Products::where('model_id', $product_id)->first();
                            if ($product) {
                                $product->stocks_quantity -= $orderDetail->quantity;
                                $product->save();
                            }
                        }
                    }
    
                    $orderDetail->product_status = $status;
                    $orderDetail->save();
                }
            }
        }
    
        $order = Order::find($order_id);
        if ($order && $total_adjustment !== 0) {
            $order->total_price += $total_adjustment;
            $order->save();
            return back()->with('success', 'Product status updated successfully.');
        }
    
        return back()->with('error', 'No changes were made.');
    }    
    


    public function updateRefund(Request $request)
    {
        try {
            // Log the received data
            Log::info("✅ Refund Update Request Received:", $request->all());
    
            // Extract main order data
            $orderId = $request->order_id;
            $userId = $request->user_id;
            $processedBy = $request->processed_by;
            $originalTotal = $request->original_total;
            $updatedTotalPrice = $request->updated_total_price;
            $amountAdded = $request->amount_added;
            $changeGiven = $request->change_given;
            $status = "Completed";
            $detailsSelected = $request->details_selected;
    
            Log::info("🔍 Order Update Info", compact(
                'orderId', 'userId', 'processedBy', 'originalTotal', 
                'updatedTotalPrice', 'amountAdded', 'changeGiven', 'status'
            ));
    
            // Update orders table
            $orderUpdated = DB::table('orders')
                ->where('order_id', $orderId)
                ->update([
                    'total_price' => $updatedTotalPrice,
                    'status' => $status,
                    'updated_at' => now()
                ]);
    
            Log::info("✅ Orders table updated:", ['status' => $orderUpdated]);
    
            // Update refund_order table
            $refundUpdated = DB::table('refund_order')
                ->where('order_id', $orderId)
                ->where('user_id', $userId)
                ->update([
                    'original_total' => $originalTotal,
                    'final_total' => $updatedTotalPrice,
                    'amount_added' => $amountAdded,
                    'change_given' => $changeGiven,
                    'status' => $status,
                    'processed_by' => $processedBy,
                    'updated_at' => now()
                ]);
    
            Log::info("✅ refund_order updated:", ['status' => $refundUpdated]);
    
           // Loop through details and update order_details table
           foreach ($detailsSelected as $detail) {
            $type = $detail['type'];
            $subtotal = $detail['subtotal'];
            $productName = $detail['product_name'];
            
            if ($type === "model" && isset($detail['model_original_id'], $detail['model_passed_id'])) {
                $originalId = $detail['model_original_id'];
                $passedId = $detail['model_passed_id'];
                
                Log::info("🛠 Checking for existing model order_details entry", [
                    'order_id' => $orderId,
                    'model_id' => $originalId
                ]);
                
                $existingModel = DB::table('order_details')
                    ->where('order_id', $orderId)
                    ->where('model_id', $originalId)
                    ->first();

                Log::info("🔍 Fetched Order Details Row:", ['existingModel' => $existingModel]);

                
                if ($existingModel) {

                    Log::info("🔍 Fetched Order Details Fields:", [
                        'quantity' => $existingModel->quantity ?? 'N/A',  // Check if quantity exists
                        'product_name' => $existingModel->product_name ?? 'N/A', // Check if product_name exists
                        'subtotal' => $existingModel->subtotal ?? 'N/A' // Check if subtotal exists
                    ]);

                    // Fetch the model_name and price from the products table (not models)
                    $modelName = DB::table('products')
                        ->where('model_id', $passedId)  // fetch based on passed model_id
                        ->value('model_name');  // Fetch the model_name, not product_name
                    
                    // Fetch the price from the models table
                    $modelPrice = DB::table('models')
                        ->where('model_id', $passedId)
                        ->value('price');
                    
                    Log::info("💡 Fetched New Model Name and Price:", ['model_name' => $modelName, 'price' => $modelPrice]);
                
                    // Proceed with the update
                    if (!is_null($modelName) && !is_null($modelPrice)) {
                        $modelUpdated = DB::table('order_details')
                            ->where('order_id', $orderId)
                            ->where('model_id', $originalId)
                            ->update([
                                'model_id' => $passedId,
                                'product_name' => $modelName,  // Update product_name based on the correct table (model_name)
                                'price' => $modelPrice,  // Update price based on the correct table (models)
                                'updated_at' => now()
                            ]);
                        Log::info("✅ Model Update Result:", ['status' => $modelUpdated]);

                        $quantity = $existingModel->quantity;  // Use the quantity from the order_details row

                        $stockDeducted = DB::table('products')
                        ->where('model_id', $passedId)
                        ->decrement('stocks_quantity', $quantity);  // Deduct the quantity from passed model
        
                        Log::info("🔽 Deducted stock from model_id $passedId (quantity: $quantity).");
            
                        // Add stock to the original model
                        $stockAdded = DB::table('products')
                            ->where('model_id', $originalId)
                            ->increment('stocks_quantity', $quantity);  // Add the quantity to original model
        
                        Log::info("🔼 Added stock to model_id $originalId (quantity: $quantity).");
                        
                    } else {
                        Log::warning("⚠️ No model_name or price found for model_id $passedId in products or models table.");
                    }
                } else {
                    Log::warning("⚠️ No matching model_id found in order_details. Skipping update.");
                }
            }
        
            // For the variant-based updates
            elseif ($type === "variant" && isset($detail['variant_original_id'], $detail['variant_passed_id'])) {
                $originalId = $detail['variant_original_id'];
                $passedId = $detail['variant_passed_id'];
                
                Log::info("🛠 Checking for existing variant order_details entry", [
                    'order_id' => $orderId,
                    'variant_id' => $originalId
                ]);
                
                $existingVariant = DB::table('order_details')
                    ->where('order_id', $orderId)
                    ->where('variant_id', $originalId)
                    ->first();
                
                if ($existingVariant) {
                    // Fetch the product_name and price from the variants table

                    Log::info("🔍 Fetched Order Details Row:", ['existingVariant' => $existingVariant]);

                    Log::info("🔍 Fetched Order Details Fields:", [
                        'quantity' => $existingVariant->quantity ?? 'N/A',  // Check if quantity exists
                        'product_name' => $existingVariant->product_name ?? 'N/A', // Check if product_name exists
                        'subtotal' => $existingVariant->subtotal ?? 'N/A' // Check if subtotal exists
                    ]);


                    $variantName = DB::table('variants')
                        ->where('variant_id', $passedId)  // Fetch based on passed variant_id
                        ->value('product_name');  // Fetch the product_name from variants table
                    
                    $variantPrice = DB::table('variants')
                        ->where('variant_id', $passedId)
                        ->value('price');  // Fetch the price from variants table
                    
                    Log::info("💡 Fetched New Variant Name and Price:", ['variant_name' => $variantName, 'price' => $variantPrice]);
                
                    // Proceed with the update
                    if (!is_null($variantName) && !is_null($variantPrice)) {
                        $variantUpdated = DB::table('order_details')
                            ->where('order_id', $orderId)
                            ->where('variant_id', $originalId)
                            ->update([
                                'variant_id' => $passedId,
                                'product_name' => $variantName,  // Update product_name based on the correct table (variant_name)
                                'price' => $variantPrice,  // Update price based on the correct table (variants)
                                'updated_at' => now()
                            ]);
                        Log::info("✅ Variant Update Result:", ['status' => $variantUpdated]);

                        // Fetch the current stock levels of the original and passed variants
                        $originalStockQuantity = DB::table('variants')->where('variant_id', $originalId)->value('stocks_quantity');
                        $passedStockQuantity = DB::table('variants')->where('variant_id', $passedId)->value('stocks_quantity');

                        Log::info("🔍 Original Variant Stock Quantity: $originalStockQuantity, Passed Variant Stock Quantity: $passedStockQuantity");

                         // Deduct from passed variant and add to original variant in the variants table
                        $stockDeducted = DB::table('variants')
                        ->where('variant_id', $passedId)
                        ->decrement('stocks_quantity', $existingVariant->quantity);  // Deduct the quantity from passed variant

                        Log::info("🔽 Deducted stock from variant_id $passedId (quantity: " . $existingVariant->quantity . "). New stock quantity for passed variant: " . ($passedStockQuantity - $existingVariant->quantity));

                        $stockAdded = DB::table('variants')
                            ->where('variant_id', $originalId)
                            ->increment('stocks_quantity', $existingVariant->quantity);  // Add the quantity to original variant

                        Log::info("🔼 Added stock to variant_id $originalId (quantity: " . $existingVariant->quantity . "). New stock quantity for original variant: " . ($originalStockQuantity + $existingVariant->quantity));

                    } else {
                        Log::warning("⚠️ No product_name or price found for variant_id $passedId in variants table.");
                    }
                } else {
                    Log::warning("⚠️ No matching variant_id found in order_details. Skipping update.");
                }
            }
        }
               
    
            return response()->json([
                'success' => true,
                'message' => 'Refund updated successfully!',
                'data' => $request->all()
            ]);
    
        } catch (\Exception $e) {
            Log::error("❌ Refund Update Failed:", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update refund.',
                'error' => $e->getMessage()
            ]);
        }
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
            ->paginate(10); // Add pagination, showing 10 orders per page
    
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
            ->paginate(8); // Add pagination

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
