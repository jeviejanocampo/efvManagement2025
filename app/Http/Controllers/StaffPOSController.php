<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Brand;
use App\Models\Models;
use App\Models\Variant;
use App\Models\Products;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderReference;
use App\Models\GcashPayment;
use App\Models\PnbPayment;
use DB;


class StaffPOSController extends Controller
{

    public function index(Request $request)
    {
        $brands = Brand::where('status', 'active')->get();
        $selectedBrandId = $request->query('brand_id');
        $models = [];
        $customers = Customer::where('status', 'active')->get(); // Fetch active customers
    
        if ($selectedBrandId) {
            $models = \App\Models\Models::where('brand_id', $selectedBrandId)
                ->where('status', 'active')
                ->select('model_name', 'model_img', 'price', 'model_id', 'w_variant')
                ->with(['products' => function ($query) {
                    $query->select('model_id', 'stocks_quantity', 'm_part_id');
                }])
                ->get();
    
            foreach ($models as $model) {
                if ($model->w_variant === 'YES') {
                    $model->variants = \App\Models\Variant::where('model_id', $model->model_id)
                        ->where('status', 'active')
                        ->select('product_name', 'variant_image', 'price', 'variant_id', 'stocks_quantity', 'model_id', 'part_id')
                        ->get();
                }
            }
        }
    
        return view('staff.content.POSView', compact('brands', 'models', 'selectedBrandId', 'customers'));
    }

    public function getBrandModels($brand_id)
    {
        $models = Models::where('brand_id', $brand_id)
            ->where('status', 'active')
            ->select('model_name', 'model_img', 'price', 'model_id', 'w_variant', 'brand_id') // ✅ include brand_id
            ->with(['products' => function ($query) {
                $query->select('model_id', 'stocks_quantity', 'm_part_id', 'brand_name'); // ✅ include brand_name
            }])
            ->get();

        foreach ($models as $model) {
            if ($model->w_variant === 'YES') {
                $brand = Brand::select('brand_name')->find($model->brand_id);
                $brandName = $brand ? $brand->brand_name : 'Unknown Brand';

                $model->variants = Variant::where('model_id', $model->model_id)
                    ->where('status', 'active')
                    ->select('product_name', 'variant_image', 'price', 'variant_id', 'stocks_quantity', 'model_id', 'part_id')
                    ->get()
                    ->map(function ($variant) use ($brandName) {
                        $variant->brand_name = $brandName;
                        return $variant;
                    });
            }
        }

        return response()->json($models);
    }
    


    
    
    // public function getModelsByBrand($brand_id)
    // {
    //     $models = \App\Models\Models::where('brand_id', $brand_id)
    //     ->where('status', 'active')
    //     ->select('model_name', 'model_img', 'price', 'model_id', 'w_variant')
    //     ->with(['products' => function ($query) {
    //         // Join with the products table to fetch the stocks_quantity
    //         $query->select('model_id', 'stocks_quantity');
    //     }])
    //     ->get();

    //     foreach ($models as $model) {
    //         if ($model->w_variant === 'YES') {
    //             // Fetch the variants for this model
    //             $model->variants = \App\Models\Variant::where('model_id', $model->model_id)
    //                 ->where('status', 'active')
    //                 ->select('product_name', 'variant_image', 'price', 'variant_id', 'stocks_quantity')
    //                 ->get();
    //         }
    //     }

    //     return response()->json($models);
    // }

    public function CustomerStore(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
        ]);
    
        // Always assign the default password
        $validated['password'] = bcrypt('customer123');
        $validated['status'] = 'active';
    
        // Create the new customer
        $customer = Customer::create($validated);
    
        // Log the creation for debugging
        Log::info('New customer created', [
            'id' => $customer->id,
            'full_name' => $customer->full_name,
            'email' => $customer->email
        ]);
    
        // Redirect back with success message
        return Redirect::back()->with('success', 'Customer added successfully!');
    }

    public function saveGCashImage(Request $request)
    {
        try {
            $imageData = $request->input('image'); // Get the base64 encoded image data
            $filename = $request->input('filename'); // Get the file name
    
            // Check if the file already exists
            $filePath = public_path('onlinereceipts/' . $filename);
            if (file_exists($filePath)) {
                return response()->json(['success' => false, 'message' => 'This image has already been saved.']);
            }
    
            // Decode the image and save it to the specified location
            $image = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $imageData));
    
            file_put_contents($filePath, $image);
    
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    

    public function savePNBImage(Request $request)
    {
        try {
            $imageData = $request->input('image'); // Get the base64 encoded image data
            $filename = $request->input('filename'); // Get the file name
    
            // Check if the file already exists
            $filePath = public_path('onlinereceipts/' . $filename);
            if (file_exists($filePath)) {
                return response()->json(['success' => false, 'message' => 'This image has already been saved.']);
            }
    
            // Decode the image and save it to the specified location
            $image = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $imageData));
    
            file_put_contents($filePath, $image);
    
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    

    public function saveOrderPOS(Request $request)
    {
        DB::beginTransaction();

        try {
            // Create the order first (without reference_id generation yet)
            $order = Order::create([
                'user_id' => $request->customerId,
                'reference_id' => $request->referenceId, // reference_id is still passed here but will be replaced later
                'total_items' => $request->totalItems,
                'total_price' => $request->totalPrice,
                'original_total_amount' => $request->totalPrice,
                'payment_method' => ucfirst(strtolower($request->paymentMethod ?? 'Cash')),
                'status' => 'Completed',
                'overall_status' => 'Completed',
                'customers_change' => (string) $request->changeAmount,
                'cash_received' => $request->cashReceived,
            ]);

             $paymentMethod = strtolower($order->payment_method);
            
           // ✅ GCash image save
            if ($paymentMethod === 'gcash' && !empty($request->image)) {
                GcashPayment::create([
                    'order_id' => $order->order_id,
                    'image' => $request->image,
                    'status' => 'Completed'
                ]);
            }

            // ✅ PNB image save
            if ($paymentMethod === 'pnb' && !empty($request->image)) {
                PnbPayment::create([
                    'order_id' => $order->order_id,
                    'image' => $request->image,
                    'status' => 'Completed'
                ]);
            }

            // ========== NEWLY ADDED PART ==========
            $paymentMethod = strtolower($order->payment_method);
            if (in_array($paymentMethod, ['gcash', 'pnb'])) {
                $today = now()->format('ymd'); // Get YYMMDD format
                $onlineReferenceId = 'EFV-' . $today . '-OR00' . str_pad($order->order_id, 4, '0', STR_PAD_LEFT);

                $confirmationMessage = 'We have received the money transferred to our account, Thank you for your payment. Total Amount Paid: ' . number_format($order->total_price, 2) . '.';

                // Update the order with the new online_reference_id and confirmation message
                $order->update([
                    'online_reference_id' => $onlineReferenceId,
                    'payed_online_confirmation_message' => $confirmationMessage,
                ]);
            }
            // ========== END OF NEWLY ADDED PART ==========

            // Loop through the order items and process each item
            foreach ($request->orderItems as $item) {
                $partId = $item['part_id'] ?? '0000'; // Default to '0000' if not provided
                $mPartId = $item['m_part_id'] ?? $partId; // Default to partId if mPartId is not provided
                $variantId = $item['variant_id'] ?? null;
                $productId = $item['model_id'];
                $quantity = $item['quantity'];
                $brandName = 'Unknown'; // Default brand name

                // Process the variant or product
                if (!empty($variantId) && $variantId != 0) {
                    $variant = Variant::find($variantId);
                    if (!$variant) {
                        throw new \Exception("Variant with variant_id $variantId not found.");
                    }
                    $variant->stocks_quantity = max(0, $variant->stocks_quantity - $quantity);
                    $variant->save();
                    $model = Models::where('model_id', $productId)->first();
                    if ($model) {
                        $brand = Brand::where('brand_id', $model->brand_id)->first();
                        if ($brand) {
                            $brandName = $brand->brand_name;
                        }
                    }
                } else {
                    $product = Products::where('model_id', $productId)->first();
                    if (!$product) {
                        throw new \Exception("Product with model_id $productId not found.");
                    }
                    $product->stocks_quantity = max(0, $product->stocks_quantity - $quantity);
                    $product->save();
                    $brandName = $product->brand_name;
                }

                // Save the order details
                OrderDetail::create([
                    'order_id' => $order->order_id,
                    'model_id' => $productId,
                    'variant_id' => $variantId,
                    'product_name' => $item['product_name'],
                    'brand_name' => $brandName,
                    'quantity' => $quantity,
                    'price' => $item['price'],
                    'total_price' => $item['total_price'],
                    'product_status' => 'Completed',
                    'part_id' => $partId,
                    'm_part_id' => $mPartId,
                ]);
            }

            // Generate the reference_id using the received data
            $brandShort = substr($brandName, 0, 3); // Get first 3 characters of brand_name
            $partShort = substr($partId, 0, 3); // Get first 3 characters of part_id
            $mPartShort = substr($mPartId, 0, 3); // Get first 3 characters of m_part_id

            // Combine to form reference_id
            $referenceId = $brandShort . $partShort . $mPartShort . '-OR00' . str_pad($order->order_id, 4, '0', STR_PAD_LEFT);

            // Create OrderReference with the generated reference_id
            OrderReference::create([
                'order_id' => $order->order_id,
                'reference_id' => $referenceId, // Use the generated reference_id
            ]);

            // Commit the transaction
            DB::commit();

            // Fetch the latest order with details and return response
            $latestOrder = Order::with(['orderDetails', 'orderReference'])->find($order->order_id);

            return response()->json([
                'success' => true,
                'message' => 'Order saved successfully!',
                'order' => $latestOrder
            ]);
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to save order: ' . $e->getMessage()
            ]);
        }
    }
    
    
    public function StaffCustomerStore(Request $request)
     {
        // Validate the incoming request
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email',
        ]);

        // Set default if email is left blank
        if (empty($validated['email'])) {
        $validated['email'] = null; // ✅ this is actual SQL NULL
        }

        // Assign default password and active status
        $validated['password'] = bcrypt('customer123');
        $validated['status'] = 'active';

        // Create the new customer
        $customer = Customer::create($validated);

        // Log the creation for debugging
        Log::info('New customer created', [
            'id' => $customer->id,
            'full_name' => $customer->full_name,
            'email' => $customer->email
        ]);

        // Redirect back with success message
        return Redirect::back()->with('success', 'Customer added successfully!');
    }

    
    
    

    
    

}
