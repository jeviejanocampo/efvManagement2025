<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Brand;
use App\Models\Models;
use App\Models\Variant;
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
            $order = Order::create([
                'user_id' => $request->customerId,
                'reference_id' => $request->referenceId,
                'total_items' => $request->totalItems,
                'total_price' => $request->totalPrice,
                'original_total_amount' => $request->totalPrice,
                'payment_method' => ucfirst(strtolower($request->paymentMethod ?? 'Cash')),
                'status' => 'Completed',
                'overall_status' => 'Completed',
                'customers_change' => (string) $request->changeAmount,
                'cash_received' => $request->cashReceived,
            ]);
    
            foreach ($request->orderItems as $item) {
                $partId = $item['part_id'] ?? null;
                $mPartId = $item['m_part_id'] ?? $partId;
                $variantId = $item['variant_id'] ?? null;
                $productId = $item['model_id'];
                $quantity = $item['quantity'];
                $brandName = 'Unknown';  // Default brand name if no match is found.
            
                // If variant_id is not null or 0
                if (!empty($variantId) && $variantId != 0) {
                    $variant = Variant::find($variantId);
            
                    if (!$variant) {
                        throw new \Exception("Variant with variant_id $variantId not found.");
                    }
            
                    $variant->stocks_quantity = max(0, $variant->stocks_quantity - $quantity);
                    $variant->save();
            
                    // Use model_id from the variant to fetch brand_name
                    $model = Models::where('model_id', $productId)->first();
                    if ($model) {
                        $brand = Brand::where('brand_id', $model->brand_id)->first();
                        if ($brand) {
                            $brandName = $brand->brand_name;  // Use the brand name from the brands table.
                        }
                    }
            
                } else {  // If variant_id is 0 or null
                    // Use model_id from the products table to get the brand_name
                    $product = Products::where('model_id', $productId)->first();
            
                    if (!$product) {
                        throw new \Exception("Product with model_id $productId not found.");
                    }
            
                    $product->stocks_quantity = max(0, $product->stocks_quantity - $quantity);
                    $product->save();
            
                    // Fetch brand_name from the products table
                    $brandName = $product->brand_name;
                }
            
                // Create order detail with the correct brand_name
                OrderDetail::create([
                    'order_id' => $order->order_id,
                    'model_id' => $productId,
                    'variant_id' => $variantId,
                    'product_name' => $item['product_name'],
                    'brand_name' => $brandName,  // Insert the fetched brand_name here
                    'quantity' => $quantity,
                    'price' => $item['price'],
                    'total_price' => $item['total_price'],
                    'product_status' => 'Completed',
                    'part_id' => $partId ?? '0000',
                    'm_part_id' => $mPartId ?? '000',
                ]);
            }            
    
            if (!empty($request->image)) {
                GcashPayment::create([
                    'order_id' => $order->order_id,
                    'image' => $request->image,
                    'status' => 'Completed',
                ]);
            }
    
            DB::commit();
    
            $latestOrder = Order::with('orderDetails')->find($order->order_id);
    
            return response()->json([
                'success' => true,
                'message' => 'Order saved successfully!',
                'order' => $latestOrder
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to save order: ' . $e->getMessage()
            ]);
        }
    }
    

    
    
    

    
    

}
