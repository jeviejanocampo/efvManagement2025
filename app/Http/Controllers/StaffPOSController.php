<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Brand;
use App\Models\Models;
use App\Models\Products;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;



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
    
    
    

}
