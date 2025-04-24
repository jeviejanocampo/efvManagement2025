<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Brand;
use App\Models\Models;
use App\Models\Products;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\GcashPayment;
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
        $models = \App\Models\Models::where('brand_id', $brand_id)
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
                    ->select('product_name', 'variant_image', 'price', 'variant_id', 'stocks_quantity', 'model_id', 'part_id') // <- added
                    ->get();
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
    

    public function saveOrderPOS(Request $request)
    {
        DB::beginTransaction();
    
        try {
            // Insert into the 'orders' table
            $order = Order::create([
                'user_id' => $request->customerId,
                'reference_id' => $request->referenceId,
                'total_items' => $request->totalItems,
                'total_price' => $request->totalPrice,
                'original_total_amount' => $request->totalPrice,  // Assuming original total is same
                'payment_method' => ucfirst(strtolower($request->paymentMethod ?? 'Cash')),
                'status' => 'Completed',  // Default status
                'overall_status' => 'Completed',  // Default status
                'customers_change' => (string) $request->changeAmount, // Store as string
                'cash_received' => $request->cashReceived, // ✅ Store this
            ]);
    
            // Insert into the 'order_details' table
            foreach ($request->orderItems as $item) {
                $partId = $item['part_id'] ?? null;
                $mPartId = $item['m_part_id'] ?? null;
    
                // If m_part_id is null, use part_id
                if ($mPartId === null) {
                    $mPartId = $partId;
                }
    
                // Fetch brand_id from the 'models' table based on the model_id
                $model = Models::find($item['model_id']);
                $brandName = null;
                
                // If the model exists, fetch the brand_name from the 'brands' table
                if ($model) {
                    $brand = $model->brand; // The brand relation in the Models model
                    if ($brand) {
                        $brandName = $brand->brand_name; // Get the brand_name from the related Brand
                    }
                }
    
                // Default to 'Unknown' if brand_name is not found
                $brandName = $brandName ?? 'Unknown';
    
                // Insert into the order_details table
                OrderDetail::create([
                    'order_id' => $order->order_id,  // Link to the order just created
                    'model_id' => $item['model_id'],
                    'variant_id' => $item['variant_id'] ?? null,  // Allow null for variants
                    'product_name' => $item['product_name'],
                    'brand_name' => $brandName,  // Set the brand_name
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total_price' => $item['total_price'],
                    'product_status' => 'Completed',
                    'part_id' => $partId ?? '0000', // If part_id is not provided, use default
                    'm_part_id' => $mPartId ?? '000',  // If m_part_id is null, use default
                ]);
            }

            // ✅ 3. Insert into 'gcash_payment' if image is provided
            if (!empty($request->image)) {
                GcashPayment::create([
                    'order_id' => $order->order_id,
                    'image' => $request->image,
                    'status' => 'Completed',
                ]);
            }
    
            DB::commit();
    
            // Success Message
            return response()->json([
                'success' => true,
                'message' => 'Order saved successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Error Message
            return response()->json([
                'success' => false,
                'message' => 'Failed to save order: ' . $e->getMessage()
            ]);
        }
    }
    
    

    
    

}
