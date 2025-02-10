<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Models; 
use App\Models\Products; 
use App\Models\Brand;
use App\Models\Variant; 
use Illuminate\Support\Facades\Log;


class ProductController extends Controller
{
    public function index()
    {
        // Fetch models with related brand and category
        $products = Models::with(['brand.category'])->get();

        return view('stockclerk.content.ProductsView', compact('products'));
    }

    public function create()
    {
        $brands = Brand::all(); // Fetch all brands from the database
        return view('stockclerk.content.addProduct', compact('brands'));
    }

    public function addDetails($model_id)
    {
        // Fetch the model name based on model_id
        $model = \App\Models\Models::where('model_id', $model_id)->first();
    
        // Fetch available brands
        $brands = \App\Models\Brand::all();
    
        return view('stockclerk.content.addDetails', [
            'model_id' => $model_id, 
            'price' => $model ? $model->price : '',
            'model_name' => $model ? $model->model_name : '', 
            'brands' => $brands
        ]);
    }
    

    public function store(Request $request)
    {
        try {
            $request->validate([
                'model_name' => 'required|string|max:255',
                'model_img' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048', 
                'price' => 'required|numeric',
                'brand_id' => 'required|integer|exists:brands,brand_id',
                'w_variant' => 'required|string',
                'status' => 'required|string',
            ]);
    
            // Default image if no file is uploaded
            $imageName = 'default.png';
    
            if ($request->hasFile('model_img')) {
                $image = $request->file('model_img');
    
                // Generate a unique filename to prevent overwriting existing files
                $imageName = time() . '_' . $image->getClientOriginalName();
    
                // Move the image to the public folder
                $image->move(public_path('product-images/'), $imageName);
            }
    
            // Store the product in the database
            $product = Models::create([
                'model_name' => $request->model_name,
                'model_img' => $imageName,
                'price' => $request->price,
                'brand_id' => $request->brand_id,
                'w_variant' => $request->w_variant,
                'status' => $request->status,
            ]);
    
            return "<script>alert('Product successfully inserted!'); window.location.href='" . route('productsView') . "';</script>";
    
        } catch (\Exception $e) {
            return "<script>alert('Error: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
        }
    }
    

    public function brandscreate()
    {
        $brands = Brand::all();
        return view('add_product', compact('brands'));
    }


    public function destroyModel($id)
    {
        $product = Models::find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found!'], 404);
        }

        $product->delete();

        return response()->json(['success' => true, 'message' => 'Product deleted successfully!']);
    }

    public function addProductDetails(Request $request)
    {
        $request->validate([
            'model_id' => 'required|integer|exists:models,model_id',
            'model_img' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048', 
            'price' => 'required|numeric',
            'brand_id' => 'required|integer|exists:brands,brand_id',
            'description' => 'required|string',
            'm_part_id' => 'required|string',
            'stocks_quantity' => 'required|integer',
            'status' => 'required|string|in:active,inactive,on_order',
        ]);

        // Handle image upload
        $imageName = 'default.png';
            if ($request->hasFile('model_img')) {
                $image = $request->file('model_img');
                $imageName = $image->getClientOriginalName(); // Keep original filename
                $image->move(public_path('assets/product-images'), $imageName);
            }

        // Insert product details into the products table
        \App\Models\Products::create([
            'model_id' => $request->model_id,
            'brand_id' => $request->brand_id,
            'model_name' => $request->model_name,
            'brand_name' => \App\Models\Brand::where('brand_id', $request->brand_id)->value('brand_name'),
            'price' => $request->price,
            'description' => $request->description,
            'm_part_id' => $request->m_part_id,
            'model_img' => $imageName,
            'stocks_quantity' => $request->stocks_quantity,
            'status' => $request->status,
        ]);

        return "<script>alert('Product details added successfully!'); window.location.href='" . route('productsView') . "';</script>";
    }


    public function viewDetailsofProduct($model_id)
    {
        $product = Products::where('model_id', $model_id)->firstOrFail();

        if (!$product) {
            return redirect()->back()->with('error', 'Product not found!');
        }

        return view('stockclerk.content.ViewDetails', compact('product', 'model_id'));
    }

    public function updateProduct(Request $request, $model_id)
    {
        try {
            // Validate the input
            $request->validate([
                'model_name' => 'required|string|max:255',
                'brand_name' => 'required|string|max:255',
                'price' => 'required|numeric',
                'description' => 'nullable|string',
                'm_part_id' => 'nullable|string',
                'stocks_quantity' => 'required|integer',
                'status' => 'required|string',
                'model_img' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048' // Image validation
            ]);
    
            // Find the product by model_id
            $product = Products::where('model_id', $model_id)->firstOrFail();
    
            // Handle image upload
            if ($request->hasFile('model_img')) {
                $image = $request->file('model_img');
                $imageName = $image->getClientOriginalName(); // Keep original filename
                $image->move(public_path('assets/product-images'), $imageName);
                $product->model_img = $imageName; // Update model_img field in database
            }
    
            // Update the product details
            $product->update([
                'model_name' => $request->model_name,
                'brand_name' => $request->brand_name,
                'price' => $request->price,
                'description' => $request->description,
                'm_part_id' => $request->m_part_id,
                'stocks_quantity' => $request->stocks_quantity,
                'status' => $request->status,
            ]);
    
            // Save updated product
            $product->save();
    
            // Return success alert and reload the page
            return "<script>alert('Product updated successfully!'); window.location.href='" . route('viewDetails', ['model_id' => $model_id]) . "';</script>";
    
        } catch (\Exception $e) {
            return "<script>alert('Error: " . $e->getMessage() . "'); window.history.back();</script>";
        }
    }

    public function viewModelDetails($model_id)
    {
        try {
            // Find the model by model_id
            $model = Models::where('model_id', $model_id)->firstOrFail();

            // Return the view with the model details
            return view('stockclerk.content.viewModelDetails', compact('model'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Model not found!');
        }
    }

    public function updateModel(Request $request, $model_id)
    {
        try {
            // Validate request
            $request->validate([
                'model_name' => 'required|string|max:255',
                'price' => 'required|numeric',
                'status' => 'required|string',
                'model_img' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // Validate image
            ]);

            // Find model by ID
            $model = Models::findOrFail($model_id);

            // Update fields
            $model->model_name = $request->model_name;
            $model->price = $request->price;
            $model->status = $request->status;

            // Handle image upload if provided
            if ($request->hasFile('model_img')) {
                $image = $request->file('model_img');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('product-images'), $imageName);
                $model->model_img = $imageName;
            }

            $model->save();

            return redirect()->route('viewModelDetails', ['model_id' => $model_id])
                ->with('success', 'Model updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    
    public function updateStatus(Request $request, $model_id)
    {
        try {
            Log::info("Attempting to update status for model_id: " . $model_id);
    
            // Retrieve the correct model from the `models` table
            $model = Models::where('model_id', $model_id)->first(); // Use `Models`, not `Products`
    
            if (!$model) {
                Log::error("Model with model_id $model_id not found.");
                return response()->json([
                    'success' => false,
                    'message' => 'Model not found!'
                ]);
            }
    
            Log::info("Current status: " . $model->status);
            Log::info("New status: " . $request->status);
    
            // Update status
            $model->status = $request->status;
            $model->save();
    
            Log::info("Status updated successfully!");
    
            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully!',
                'updated_status' => $model->status
            ]);
        } catch (\Exception $e) {
            Log::error("Error updating status: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status!'
            ]);
        }
    }
    
    public function indexVariant($model_id)
    {
        // Fetch the variants related to the model_id
        $variants = Variant::where('model_id', $model_id)->get();

        $model = Models::where('model_id', $model_id)->first();

        if (!$model) {
            return redirect()->back()->with('error', 'Model not found.');
        }

        return view('stockclerk.content.viewVariants', compact('variants', 'model_id', 'model'));
    }

    public function IndexAddVariant($model_id)
    {
        $model = Models::where('model_id', $model_id)->first();

        if (!$model) {
            return redirect()->back()->with('error', 'Model not found.');
        }

        return view('stockclerk.content.addVariant', compact('model', 'model_id'));
    }

    public function StoreVariant(Request $request, $model_id)
    {
        // Validate form inputs
        $request->validate([
            'product_name' => 'required|string|max:255',
            'variant_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'part_id' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'specification' => 'nullable|string',
            'description' => 'nullable|string',
            'stocks_quantity' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        // Handle Image Upload
        if ($request->hasFile('variant_image')) {
            $originalName = $request->file('variant_image')->getClientOriginalName(); // Get original filename
            $request->file('variant_image')->move(public_path('product-images'), $originalName);
            $variantImagePath = $originalName; // Store only the filename
        } else {
            return redirect()->back()->with('error', 'Image upload failed.');
        }


        // Create the variant record
        Variant::create([
            'model_id' => $model_id,
            'product_name' => $request->product_name,
            'variant_image' => $variantImagePath,
            'part_id' => $request->part_id,
            'price' => $request->price,
            'specification' => $request->specification,
            'description' => $request->description,
            'stocks_quantity' => $request->stocks_quantity,
            'status' => $request->status,
        ]);

        return redirect()->route('add.variant', ['model_id' => $model_id])->with('success', 'Variant added successfully.');
    }

    
    public function editVariant($model_id, $variant_id, Request $request)
    {
        $variant = Variant::where('model_id', $model_id)->where('variant_id', $variant_id)->first();
    
        if (!$variant) {
            return redirect()->back()->with('error', 'Variant not found.');
        }
    
        // Store the previous URL in session
        session(['previous_url' => url()->previous()]);
    
        return view('stockclerk.content.editVariant', compact('variant', 'model_id', 'variant_id'));
    }
    
    

    public function deleteVariant($id)
    {
        $variant = Variant::find($id);
        if (!$variant) {
            return redirect()->back()->with('error', 'Variant not found.');
        }

        $variant->delete();
        return redirect()->back()->with('success', 'Variant deleted successfully.');
    }

    public function updateVariant(Request $request, $model_id, $variant_id)
    {
        $request->validate([
            'product_name' => 'required|string|max:255',
            'part_id' => 'required|string|max:255',
            'price' => 'required|numeric',
            'specification' => 'required|string|max:500',
            'description' => 'required|string',
            'stocks_quantity' => 'required|integer',
            'status' => 'required|in:active,inactive',
        ]);
    
        $variant = Variant::where('model_id', $model_id)
                          ->where('variant_id', $variant_id)
                          ->first();
    
        if (!$variant) {
            return redirect()->back()->with('error', 'Variant not found.');
        }
    
        // Handle Image Upload
        if ($request->hasFile('variant_image')) {
            $imageName = $request->file('variant_image')->getClientOriginalName(); // Get original file name only
            $request->file('variant_image')->move(public_path('product-images'), $imageName);
            $variant->variant_image = $imageName;
        }
    
        // Update Variant Details
        $variant->product_name = $request->product_name;
        $variant->part_id = $request->part_id;
        $variant->price = $request->price;
        $variant->specification = $request->specification;
        $variant->description = $request->description;
        $variant->stocks_quantity = $request->stocks_quantity;
        $variant->status = $request->status;
    
        if ($variant->save()) {
            return redirect()->route('variantsView', ['model_id' => $model_id])->with('success', 'Variant updated successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to update variant.');
        }
    }
    

    public function updateVariantStatus(Request $request, $variant_id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        $variant = Variant::find($variant_id);
        if (!$variant) {
            return response()->json(['success' => false, 'message' => 'Variant not found.']);
        }

        $variant->status = $request->status;

        if ($variant->save()) {
            return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
        } else {
            return response()->json(['success' => false, 'message' => 'Failed to update status.']);
        }
    }

    
    


}
