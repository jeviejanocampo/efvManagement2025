<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\RefundLog;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\OrderDetail;  
use App\Models\Models;  
use App\Models\Products;  
use App\Models\ActivityLog;  
use App\Models\Variant;  
use Carbon\Carbon;
use App\Models\RefundOrder;
use Illuminate\Support\Facades\DB;
use App\Models\OrderReference;
use App\Http\Controllers\Controller;
use App\Models\PnbPayment;
use App\Models\GcashPayment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;




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

        $refunds = RefundOrder::with(['customer', 'orderReference']) // Eager load orderReference
        ->orderBy('created_at', 'desc') // Sort by newest first
        ->paginate(9); // Show 9 per page

        return view('staff.content.staffRequestRefundList', compact('refunds'));
    }
    
    public function StaffshowRefundRequestForm($order_id)
    {
        $reference_id = request('reference_id');  // Retrieve the reference_id from the request

        // Fetch refund details along with the customer data
        $refund = RefundOrder::where('order_id', $order_id)
            ->with('customer')
            ->first();

        // Fetch order details
        $orderDetails = OrderDetail::where('order_id', $order_id)
        ->with(['model', 'variant']) // <-- important
        ->get();
    
        if (!$refund) {
            return redirect()->route('staff.refundRequests')->with('error', 'Refund request not found.');
        }

        // Fetch all models where w_variant is NOT "YES" and include stock count
        $models = Models::where('status', 'active')
            ->where('w_variant', '!=', 'YES')
            ->with(['products' => function ($query) {
                $query->select('model_id', 'stocks_quantity'); // Fetch stocks_quantity from products
            }])
            ->get()
            ->map(function ($model) {
                // Sum up the stocks_quantity for each model
                $model->total_stock_quantity = $model->products->sum('stocks_quantity');
                return $model;
            });

        // Fetch all variants where the related model has w_variant = "YES"
        $variants = Variant::whereHas('model', function ($query) {
            $query->where('w_variant', 'YES')->where('status', 'active');
        })->get(); // Removed pagination

        // Fetch GCash payment status from the 'gcash_payment' table based on order_id
        $gcashPaymentStatus = GcashPayment::where('order_id', $order_id)->value('status');
        $gcashPaymentStatus = $gcashPaymentStatus ?? 'Pending';

        // Fetch PNB payment status from the 'pnb_payment' table based on order_id
        $pnbPaymentStatus = PnbPayment::where('order_id', $order_id)->value('status');
        $pnbPaymentStatus = $pnbPaymentStatus ?? 'Pending';  // Default to 'Pending' if not found

        // Pass reference_id, refund, orderDetails, models, variants, gcashPaymentStatus, and pnbPaymentStatus to the view
        return view('staff.content.StaffRequestRefundForm', compact('refund', 'orderDetails', 'models', 'variants', 'reference_id', 'gcashPaymentStatus', 'pnbPaymentStatus'));
    }
    

    public function updateProductStatusRefunded(Request $request)
    {
        try {
            
            Log::info('updateProductStatusRefunded function triggered', ['request' => $request->all()]);

            $order_id = $request->input('order_id');
            $product_ids = $request->input('product_id');
            $product_statuses = $request->input('product_status');
            $product_prices = $request->input('product_price');
            $variant_ids = $request->input('variant_id');
    
            $total_adjustment = 0;
            $refunded_items = [];
    
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
                            $refunded_items[] = "Product ID: $product_id, Variant ID: $variant_id, Price: $price";
                
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
                
                            // Insert into RefundLog when status is updated to 'refunded'
                            try {
                                RefundLog::create([
                                    'user_id' => Auth::id(),
                                    'activity' => "Product ID: $product_id, Variant ID: $variant_id marked as refunded. Price: $price",
                                    'role' => Auth::user()->role,
                                    'refunded_at' => now(),
                                ]);
                                Log::info("Refund log created for Product ID: $product_id, Variant ID: $variant_id.");
                            } catch (\Exception $e) {
                                Log::error("Error inserting refund log for Product ID: $product_id, Variant ID: $variant_id: " . $e->getMessage());
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
                
                            // Insert into RefundLog when status is updated from 'refunded' to 'pending'
                            try {
                                RefundLog::create([
                                    'user_id' => Auth::id(),
                                    'activity' => "Product ID: $product_id, Variant ID: $variant_id changed from refunded to pending. Price: $price",
                                    'role' => Auth::user()->role,
                                    'refunded_at' => now(),
                                ]);
                                Log::info("Refund log created for Product ID: $product_id, Variant ID: $variant_id.");
                            } catch (\Exception $e) {
                                Log::error("Error inserting refund log for Product ID: $product_id, Variant ID: $variant_id: " . $e->getMessage());
                            }
                        }
                
                        $orderDetail->product_status = $status;
                        $orderDetail->save();
                    }
                }                
            }
    
            $order = Order::find($order_id);
            if ($order) {
                if ($total_adjustment !== 0) {
                    $order->total_price += $total_adjustment;
                    $order->save();
                }
    
                if ($order->total_price == 0.00) {
                    $order->status = 'Refunded';
                    $order->save();
    
                    try {
                        RefundLog::create([
                            'user_id' => Auth::id(),
                            'activity' => "Order ID: $order_id marked as Refunded. Items: " . implode(', ', $refunded_items),
                            'role' => Auth::user()->role,
                            'refunded_at' => now(),
                        ]);
                        
                        Log::info("Refund log created successfully for Order ID: $order_id");
    
                    } catch (\Exception $e) {
                        Log::error("Error inserting refund log: " . $e->getMessage());
                    }
                }
    
                return back()->with('success', 'Product status updated successfully.');
            }
    
            return back()->with('error', 'No changes were made.');
        
        } catch (\Exception $e) {
            Log::error("Error in updateProductStatusRefunded: " . $e->getMessage());
            return back()->with('error', 'An error occurred. Check logs.');
        }
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

            try {
                RefundLog::create([
                    'user_id' => Auth::id(),
                    'activity' => "Refund updated for Order ID: $orderId. Status: Completed. Total Price: $updatedTotalPrice",
                    'role' => Auth::user()->role,
                    'refunded_at' => now(),
                ]);
                Log::info("Refund log created for Order ID: $orderId");
            } catch (\Exception $e) {
                Log::error("Error inserting refund log for Order ID: $orderId: " . $e->getMessage());
            }
    
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
                                'changed_model_id' => $originalId,
                                'product_name' => $modelName,
                                'price' => $modelPrice,
                                'updated_at' => now()
                            ]);
                            
                        // Insert refund log for model update
                        try {
                            RefundLog::create([
                                'user_id' => Auth::id(),
                                'activity' => "Model ID $originalId updated to Model ID $passedId for Order ID: $orderId. New Price: $modelPrice. Updated Total Price: $updatedTotalPrice",
                                'role' => Auth::user()->role,
                                'refunded_at' => now(),
                            ]);
                            Log::info("Refund log created for Model update in Order ID: $orderId");
                        } catch (\Exception $e) {
                            Log::error("Error inserting refund log for Model update in Order ID: $orderId: " . $e->getMessage());
                        }                        

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
                                'changed_variant_id' => $originalId, // ✅ Save original ID
                                'product_name' => $variantName,
                                'price' => $variantPrice,
                                'updated_at' => now()
                            ]);       

                        Log::info("✅ Variant Update Result:", ['status' => $variantUpdated]);

                        try {
                            RefundLog::create([
                                'user_id' => Auth::id(),
                                'activity' => "Variant ID $originalId updated to Variant ID $passedId for Order ID: $orderId. New Price: $variantPrice. Updated Total Price: $updatedTotalPrice",
                                'role' => Auth::user()->role,
                                'refunded_at' => now(),
                            ]);
                            Log::info("Refund log created for Variant update in Order ID: $orderId");
                        } catch (\Exception $e) {
                            Log::error("Error inserting refund log for Variant update in Order ID: $orderId: " . $e->getMessage());
                        }                        

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

    public function updateRefundStatusOverall(Request $request, $order_id)
    {
        $request->validate([
            'overall_status' => 'required|string|in:Pending,Processing,Completed - with changes,Complete Refund,Completed - no changes',
        ]);
    
        $refund = RefundOrder::where('order_id', $order_id)->first();
    
        if (!$refund) {
            return redirect()->back()->with('error', 'Refund request not found.');
        }
    
        // Generate new reference ID before updating
        $orderDetails = \DB::table('order_details')->where('order_id', $order_id)->get();
        
        $brandCode = '';
        $partCode1 = '';
        $partCode2 = '';
        $lastRow = null;
        $secondLastRow = null;
    
        // Find last and second last valid rows
        foreach ($orderDetails as $detail) {
            if (!empty($detail->brand_name) && !empty($detail->part_id)) {
                $secondLastRow = $lastRow;
                $lastRow = $detail;
            }
        }
    
        // Extract necessary codes
        if ($lastRow) {
            $brandCode = strtoupper(substr($lastRow->brand_name, 0, 3));
            $partCode2 = substr(explode('-', $lastRow->part_id)[1] ?? '', -4);
        }
    
        if ($secondLastRow) {
            $partCode1 = substr(explode('-', $secondLastRow->part_id)[1] ?? '', -4);
        }
    
        // Generate new reference ID
        $newReferenceId = "{$brandCode}-{$partCode1}{$partCode2}" .'OR00'. str_pad($order_id, 5, '0', STR_PAD_LEFT);
    
        // Handle "Completed - with changes"
        if ($request->overall_status === 'Completed - with changes') {
            // Update refund order status
            $refund->update(['overall_status' => 'Completed - with changes', 'status' => 'Completed']);
    
            // Update reference_id in order_reference table
            \DB::table('order_reference')
            ->updateOrInsert(
                ['order_id' => $order_id], // match by order_id
                ['reference_id' => $newReferenceId] // insert the new reference_id
            );

    
            // Log the update
            \Log::info("Updated order_reference: Order ID: $order_id, New Reference ID: $newReferenceId");
        }
        // Handle "Completed - no changes"
        elseif ($request->overall_status === 'Completed - no changes') {
            $refund->update(['overall_status' => 'Completed - no changes', 'status' => 'Completed']);
        }
        // Handle "Complete Refund"
        elseif ($request->overall_status === 'Complete Refund') {
            $refund->update(['overall_status' => 'Complete Refund', 'status' => 'Refunded']);
        } 
        else {
            // Just update overall_status for other cases
            $refund->update(['overall_status' => $request->overall_status]);
        }
    
        // Update product_status in order_details (excluding refunded items)
        \DB::table('order_details')
            ->where('order_id', $order_id)
            ->where('product_status', '!=', 'refunded')
            ->update(['product_status' => 'Completed']);
    
        // Insert log entry
        RefundLog::create([
            'user_id' => auth()->id(),
            'activity' => "Updated refund status to {$request->overall_status} for order ID: $order_id",
            'role' => auth()->user()->role,
            'refunded_at' => now(),
        ]);
    
        return redirect()->back()->with('success', 'Refund status updated successfully.');
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
        $orders = \App\Models\Order::select('order_id', 'user_id', 'total_items', 'total_price', 'created_at', 'status', 'payment_method', 'overall_status', 'reference_id')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        foreach ($orders as $order) {
            // Fetch the reference_id from OrderReference table based on order_id
            $reference = \App\Models\OrderReference::where('order_id', $order->order_id)->first();

            // If found, attach it to the order object
            if ($reference) {
                $order->custom_reference_id = $reference->reference_id;
            } else {
                $order->custom_reference_id = null; // optional: set null if no reference found
            }
        }

        session(['pendingCount' => \App\Models\Order::where('status', 'Pending')->count()]);
        session(['pendingRefundCount' => \App\Models\RefundOrder::where('status', 'Pending')->count()]);

        return view('staff.content.staffOrderOverview', compact('orders'));
    }


    


    public function stockOrderOverview()
    {
        $orders = \App\Models\Order::select('order_id', 'user_id', 'total_items', 'total_price', 'created_at', 'status', 'payment_method', 'overall_status')
            ->orderBy('created_at', 'desc')
            ->paginate(10); // Add pagination

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
            ->paginate(14);
    
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
        // Fetch the order by ID
        $order = Order::find($order_id);
        if (!$order) {
            abort(404, 'Order not found');
        }

        // Fetch the reference_id from the reference_order table based on the order_id
        $reference_order = OrderReference::where('order_id', $order_id)->first();
        $reference_id = $reference_order ? $reference_order->reference_id : null;

        // Fetch order details
        $orderDetails = OrderDetail::where('order_id', $order_id)->get();

        // Fetch the model image for each order detail
        foreach ($orderDetails as $detail) {
            $detail->model_image = Models::where('model_id', $detail->model_id)->pluck('model_img')->first();
        }

        // Pass the data to the view
        return view('staff.content.staffOverviewDetails', compact('order', 'orderDetails', 'reference_id'));
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
        $request->validate([
            'status' => 'required|in:pending,Ready to Pickup,In Process,Completed,Cancelled',
            'reference_id' => 'nullable|string|max:255'
        ]);

        $order = Order::find($order_id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        $newStatus = $request->input('status');
        $oldStatus = $order->status;

        DB::transaction(function () use (&$order, $order_id, $newStatus, $oldStatus, $request) {
            $order->status = $newStatus;

            // Get order details
            $orderDetails = OrderDetail::where('order_id', $order_id)->get();

            // Reverse stock if changing from Completed to another status
            if ($oldStatus === 'Completed' && $newStatus !== 'Completed') {
                foreach ($orderDetails as $detail) {
                    $quantity = $detail->quantity;

                    if (!is_null($detail->variant_id) && $detail->variant_id != 0) {
                        DB::table('variants')
                            ->where('variant_id', $detail->variant_id)
                            ->increment('stocks_quantity', $quantity);
                    } else {
                        DB::table('products')
                            ->where('model_id', $detail->model_id)
                            ->increment('stocks_quantity', $quantity);
                    }
                }

                // Also revert product_status
                OrderDetail::where('order_id', $order_id)->update(['product_status' => 'Pending']);
                $order->scan_status = 'Pending';
            }

            // Update product_status to Completed if applicable, skip refunded items
            if ($newStatus === 'Completed' && $oldStatus !== 'Completed') {
                OrderDetail::where('order_id', $order_id)
                    ->whereNotIn('product_status', ['refunded', 'Refunded', 'to be refunded'])
                    ->update(['product_status' => 'Completed']);

                $order->scan_status = 'Completed';
            }

            // Handle reference if status is In Process
            if ($newStatus === 'In Process') {
                $referenceId = $request->input('reference_id');
                if ($referenceId) {
                    OrderReference::create([
                        'order_id' => $order_id,
                        'reference_id' => $referenceId
                    ]);
                }
            }

            $order->updated_at = now();

            $order->save();
        });

        $user = Auth::user();
        $role = $user->role;

        ActivityLog::create([
            'user_id' => Auth::id(),
            'role' => $role,
            'activity' => "Updated order #$order_id status from {$oldStatus} to {$newStatus}",
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully.',
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
        $validStatuses = ['pending', 'Ready to Pickup', 'In Process', 'Completed', 'Cancelled', 'refunded'];
        if (!in_array($request->status, $validStatuses)) {
            return response()->json(['message' => 'Invalid status.'], 400);
        }

        // Get the order associated with this order detail
        $order = Order::find($orderDetail->order_id);

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        // If status is "Cancelled" or "refunded", subtract the total_price of the product from the order total
        if (in_array($request->status, ['Cancelled', 'refunded'])) {
            $order->total_price -= $orderDetail->total_price;
            $order->save();
        }

        // If status is "Completed", add the total_price of the product to the order total
        if ($request->status === 'Completed') {
            $order->total_price += $orderDetail->total_price;
            $order->save();
        }

        // If refunded, restore stock
        if ($request->status === 'refunded') {
            $quantity = $orderDetail->quantity;

            if (!is_null($orderDetail->variant_id) && $orderDetail->variant_id != 0) {
                // Restore stock to variant
                DB::table('variants')
                    ->where('variant_id', $orderDetail->variant_id)
                    ->increment('stocks_quantity', $quantity);
            } else {
                // Restore stock to product
                DB::table('products')
                    ->where('model_id', $orderDetail->model_id)
                    ->increment('stocks_quantity', $quantity);
            }
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

    public function ManagergetOrdersSummary()
    {
        $today = Carbon::today();

        // Count orders where status is 'Pending' or 'pending'
        $pendingOrders = Order::whereIn('status', ['Pending', 'pending'])->count();

        // Count orders where scan_status is 'yes' (On Queue)
        $onQueueOrders = Order::where('scan_status', 'yes')->count();

        // Count orders where status is 'In Process'
        $inProcessOrders = Order::where('status', 'In Process')->count();

        // Calculate total sales for today
        $totalSalesToday = Order::whereDate('created_at', $today)->sum('total_price');

        // Fetch recent pending orders (only today's)
        $recentPendingOrders = Order::whereIn('status', ['Pending', 'pending'])
            ->whereDate('created_at', $today)
            ->with('customer') // Assuming 'customer' is related via user_id
            ->latest()
            ->get();

        return response()->json([
            'pending_orders' => $pendingOrders,
            'on_queue_orders' => $onQueueOrders,
            'in_process_orders' => $inProcessOrders,
            'total_sales_today' => $totalSalesToday,
            'recent_pending_orders' => $recentPendingOrders,
        ]);
    }

    public function StaffgetPaymentImage($order_id, $payment_method)
    {
        if ($payment_method == 'gcash') {
            $payments = \App\Models\GcashPayment::where('order_id', $order_id)->get();
        } elseif ($payment_method == 'pnb') {
            $payments = \App\Models\PnbPayment::where('order_id', $order_id)->get();
        } else {
            return response()->json(['success' => false]);
        }

        if ($payments->isNotEmpty()) {
            $images = $payments->pluck('image'); // Collect only image names
            return response()->json(['success' => true, 'images' => $images]);
        }

        return response()->json(['success' => false]);
    }

    public function StaffupdateRefundMethod(Request $request)
    {
        // Log the initial request data
        Log::info('StaffupdateRefundMethod called', ['request_data' => $request->all()]);

        $request->validate([
            'order_id' => 'required|exists:refund_order,order_id',
            'refund_method' => 'required|in:Cash,GCash,PNB',
            'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $orderId = $request->order_id;
        $newMethod = $request->refund_method;

        // Log the validated orderId and newMethod
        Log::info('Order ID and Refund Method:', [
            'order_id' => $orderId,
            'refund_method' => $newMethod
        ]);

        // If switching to GCash and receipt is uploaded
        if ($newMethod === 'GCash' && $request->hasFile('receipt_image')) {
            Log::info('GCash method selected and receipt image found. Processing GCash receipt upload.');

            $image = $request->file('receipt_image');
            $imageName = $image->getClientOriginalName();  // Get original filename
            $imageExtension = $image->getClientOriginalExtension(); // Get image extension

            // Log the image details
            Log::info('Received GCash receipt:', [
                'image_name' => $imageName,
                'image_extension' => $imageExtension
            ]);

            // Create a unique filename to avoid conflict in case of duplicate names
            $uniqueImageName = time() . '-' . $imageName;

            $imagePath = public_path('onlinereceipts/' . $uniqueImageName);

            // Log the image path
            Log::info('Generated unique image path:', ['image_path' => $imagePath]);

            // Check if the image already exists in the 'onlinereceipts' folder
            if (file_exists($imagePath)) {
                Log::warning('GCash receipt already exists.', ['image_path' => $imagePath]);
                return redirect()->back()->with('error', 'GCash receipt already exists.');
            }

            // Save the new receipt image to the 'onlinereceipts' folder
            $image->move(public_path('onlinereceipts'), $uniqueImageName);

            // Log that the image has been successfully uploaded
            Log::info('GCash receipt uploaded successfully', ['unique_image_name' => $uniqueImageName]);

            // Insert new record into the gcash_payment table
            $gcashInsert = \DB::table('gcash_payment')->insert([
                'order_id' => $orderId,
                'image' => $uniqueImageName,
                'status' => 'Completed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Log the result of the insertion attempt
            if ($gcashInsert) {
                Log::info('GCash payment inserted successfully.');
            } else {
                Log::error('GCash payment insertion failed.');
            }
        }

        // If switching to PNB and receipt is uploaded
        if ($newMethod === 'PNB' && $request->hasFile('receipt_image')) {
            Log::info('PNB method selected and receipt image found. Processing PNB receipt upload.');

            $image = $request->file('receipt_image');
            $imageName = $image->getClientOriginalName();  // Get original filename
            $imageExtension = $image->getClientOriginalExtension(); // Get image extension

            // Log the image details
            Log::info('Received PNB receipt:', [
                'image_name' => $imageName,
                'image_extension' => $imageExtension
            ]);

            // Create a unique filename to avoid conflict in case of duplicate names
            $uniqueImageName = time() . '-' . $imageName;

            $imagePath = public_path('onlinereceipts/' . $uniqueImageName);

            // Log the image path
            Log::info('Generated unique image path:', ['image_path' => $imagePath]);

            // Check if the image already exists in the 'onlinereceipts' folder
            if (file_exists($imagePath)) {
                Log::warning('PNB receipt already exists.', ['image_path' => $imagePath]);
                return redirect()->back()->with('error', 'PNB receipt already exists.');
            }

            // Save the new receipt image to the 'onlinereceipts' folder
            $image->move(public_path('onlinereceipts'), $uniqueImageName);

            // Log that the image has been successfully uploaded
            Log::info('PNB receipt uploaded successfully', ['unique_image_name' => $uniqueImageName]);

            // Insert new record into the pnb_payment table
            \DB::table('pnb_payment')->insert([
                'order_id' => $orderId,
                'image' => $uniqueImageName,
                'status' => 'Completed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Update the refund_order refund_method to the new method (either Cash, GCash, or PNB)
        Log::info('Updating refund method in refund_order table', [
            'order_id' => $orderId,
            'new_refund_method' => $newMethod
        ]);

        \DB::table('refund_order')
            ->where('order_id', $orderId)
            ->update([
                'refund_method' => $newMethod,
                'updated_at' => now(),
            ]);

        Log::info('Refund method updated successfully for order ID:', ['order_id' => $orderId]);

        return redirect()->back()->with('success', 'Refund method updated successfully!');
    }
    



}
