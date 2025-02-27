<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Models; 
use App\Models\Category; 
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog; 
use App\Models\Products; 
use App\Models\Brand;
use App\Models\Variant; 
use Illuminate\Support\Facades\Log;


class ProductController extends Controller
{

    public function index()
    {
        $products = Models::with(['brand.category'])->paginate(8); 
        $brands = Brand::pluck('brand_name'); 
        $statuses = Products::distinct()->pluck('status');
    
        return view('stockclerk.content.ProductsView', compact('products', 'brands', 'statuses'));
    }

    public function lowUnitsProducts() {
        // Count the products with stocks_quantity between 0 and 5 from the 'products' table
        $lowStockProductsCount = Products::whereBetween('stocks_quantity', [0, 5])->count();
    
        // Count the variants with stocks_quantity between 0 and 5 from the 'variants' table
        $lowStockVariantsCount = Variant::whereBetween('stocks_quantity', [0, 5])->count();
    
        // Combine the counts from both tables
        $lowStockCount = $lowStockProductsCount + $lowStockVariantsCount;
    
        // Fetch brands and their categories (assuming relationship exists)
        $brands = Brand::pluck('brand_name'); 
    
        // Fetch products with their related brand and category
        $products = Models::with(['brand.category'])->paginate(15);
    
        // Fetch unique product statuses from the 'products' table
        $statuses = Products::distinct()->pluck('status');
        
        // Pass the counts and other data to the view
        return view('manager.content.managerLowProductsView', compact('lowStockCount', 'brands', 'products', 'statuses'));
    }

    public function StockClerklowUnitsProducts() {
        // Count the products with stocks_quantity between 0 and 5 from the 'products' table
        $lowStockProductsCount = Products::whereBetween('stocks_quantity', [0, 5])->count();
    
        // Count the variants with stocks_quantity between 0 and 5 from the 'variants' table
        $lowStockVariantsCount = Variant::whereBetween('stocks_quantity', [0, 5])->count();
    
        // Combine the counts from both tables
        $lowStockCount = $lowStockProductsCount + $lowStockVariantsCount;
    
        // Fetch brands and their categories (assuming relationship exists)
        $brands = Brand::pluck('brand_name'); 
    
        // Fetch products with their related brand and category
        $products = Models::with(['brand.category'])->paginate(15);
    
        // Fetch unique product statuses from the 'products' table
        $statuses = Products::distinct()->pluck('status');
        
        // Pass the counts and other data to the view
        return view('stockclerk.content.stockClerkLowProductsView', compact('lowStockCount', 'brands', 'products', 'statuses'));
    }
    
    

    public function Managerindex()
    {
        $products = Models::with(['brand.category'])->paginate(8); 
        $brands = Brand::pluck('brand_name');
        $statuses = Products::distinct()->pluck('status');
        
        return view('manager.content.managerProductsView', compact('products', 'brands', 'statuses'));
    }

    public function Adminindex()
    {
        $products = Models::with(['brand.category'])->paginate(8); 
        $brands = Brand::pluck('brand_name');
        $statuses = Products::distinct()->pluck('status');
        
        return view('admin.dashboard.adminDashboard', compact('products', 'brands', 'statuses'));
    }

    public function create()
    {
        $brands = Brand::all(); // Fetch all brands from the database
        return view('stockclerk.content.addProduct', compact('brands'));
    }

    public function Managercreate()
    {
        $brands = Brand::all(); // Fetch all brands from the database
        return view('manager.content.ManageraddProduct', compact('brands'));
    }

    public function Stockcreate()
    {
        $brands = Brand::all();
        $categories = Category::all(); // Fetch all categories
        return view('stockclerk.content.StockClerkAddBrand', compact('brands', 'categories'));
    }

    public function ManagerStockcreate()
    {
        $brands = Brand::all();
        $categories = Category::all(); // Fetch all categories
        return view('manager.content.ManagerAddBrand', compact('brands', 'categories'));
    }


    public function ManagerAddBrand (){
        $brands = Brand::all();
        $categories= Category::all();
        return view('manager.content.ManagerAddCategory', compact('brands', 'categories'));
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|max:255',
            'cat_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'required|in:active,inactive',
        ]);
    
        try {
            // Handle image upload
            $imageName = time() . '.' . $request->cat_image->extension();
            $request->cat_image->move(public_path('product-images'), $imageName); // Store in public/product-images
    
            // Insert into DB
            Category::create([
                'category_name' => $request->category_name,
                'cat_image' => $imageName, // Only store the filename
                'status' => $request->status,
            ]);
    
            return redirect()->back()->with('success', 'Category added successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to add category.');
        }
    }
    
    public function StockViewBrands()
    {
        $brands = Brand::all();
        $categories = Category::all(); // Fetch all categories
        return view('stockclerk.content.StockClerkViewBrands', compact('brands', 'categories'));
    }

    public function ManagerStockViewBrands()
    {
        $brands = Brand::all();
        $categories = Category::all(); // Fetch all categories
        return view('stockclerk.content.StockClerkViewCategory', compact('brands', 'categories'));
    }

    public function StockClerkStockViewCategory()
    {
        $brands = Brand::all();
        $categories = Category::all(); // Fetch all categories
        return view('stockclerk.content.StockClerkViewCategory', compact('brands', 'categories'));
    }

    public function ManagerStockViewCategory()
    {
        $brands = Brand::all();
        $categories = Category::all(); // Fetch all categories
        return view('manager.content.ManagerStockClerkViewCategory', compact('brands', 'categories'));
    }

    public function storeBrand(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,category_id',
            'brand_name' => 'required|string|max:255',
            'brand_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:Active,Inactive',
        ]);
    
        $brand = new Brand();
        $brand->cat_id = $request->category_id;
        $brand->brand_name = $request->brand_name;
    
        if ($request->hasFile('brand_image')) {
            $file = $request->file('brand_image');
            $filename = time() . '.' . $file->getClientOriginalExtension(); // Generate unique filename
            $file->move(public_path('product-images'), $filename); // Move to assets folder
            $brand->brand_image = $filename; // Store only the filename
        }
    
        $brand->status = $request->status;
        $brand->save();
    
        return redirect()->route('stockclerk.add.brand')->with('success', 'Brand added successfully!');
    }    


    public function ManagerAddQuantity()
    {
        $brands = Brand::all(); // Fetch all brands from the database
        return view('stockclerk.content.ManageraddQuantity', compact('brands'));
    }

    public function addDetails($model_id)
    {
        // Fetch the model name based on model_id
        $model = \App\Models\Models::where('model_id', $model_id)->first();

        // Fetch available brands
        $brands = \App\Models\Brand::all();

        // Get the role of the authenticated user
        $user = Auth::user();
        $role = $user->role; // Get the role of the user

        // Log the activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'role' => $role, // Insert the user's role
            'activity' => "Accessed Add Details page for Model #$model_id ({$model->model_name})",
        ]);

        return view('stockclerk.content.addDetails', [
            'model_id' => $model_id, 
            'price' => $model ? $model->price : '',
            'model_name' => $model ? $model->model_name : '', 
            'brands' => $brands
        ]);
    }

    public function ManageraddDetails($model_id)
    {
        // Fetch the model name based on model_id
        $model = \App\Models\Models::where('model_id', $model_id)->first();

        // Fetch available brands
        $brands = \App\Models\Brand::all();

        // Get the role of the authenticated user
        $user = Auth::user();
        $role = $user->role; // Get the role of the user

        // Log the activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'role' => $role, // Insert the user's role
            'activity' => "Accessed Add Details page for Model #$model_id ({$model->model_name})",
        ]);

        return view('manager.content.ManageraddDetails', [
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
                $image->move(public_path('product-images//'), $imageName);
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

            ActivityLog::create([
                'user_id' => Auth::id(),
                'role' => Auth::user()->role, // Get user's role
                'activity' => "Added a new product: {$request->model_name} (Brand ID: {$request->brand_id})",
            ]);

    
            return "<script>alert('Product successfully inserted!'); window.location.href='" . route('productsView') . "';</script>";
    
        } catch (\Exception $e) {
            return "<script>alert('Error: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
        }
    }


    public function Managerstore(Request $request)
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
                $image->move(public_path('product-images//'), $imageName);
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

            ActivityLog::create([
                'user_id' => Auth::id(),
                'role' => Auth::user()->role, // Get user's role
                'activity' => "Added a new product: {$request->model_name} (Brand ID: {$request->brand_id})",
            ]);

    
            return "<script>alert('Product successfully inserted!'); window.location.href='" . route('ManagerproductsView') . "';</script>";
    
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

        ActivityLog::create([
            'user_id' => Auth::id(),
            'role' => Auth::user()->role,
            'activity' => "Deleted a product: {$product->model_name} (Model ID: {$product->model_id})",
        ]);


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
                $image->move(public_path('product-images/'), $imageName);
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

        ActivityLog::create([
            'user_id' => Auth::id(),
            'role' => Auth::user()->role, // Get user's role
            'activity' => "Added a new product: {$request->model_name} (Model ID: {$request->model_id})",
        ]);


        return "<script>alert('Product details added successfully!'); window.location.href='" . route('productsView') . "';</script>";
    }

    public function ManageraddProductDetails(Request $request)
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
                $image->move(public_path('product-images/'), $imageName);
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

        ActivityLog::create([
            'user_id' => Auth::id(),
            'role' => Auth::user()->role, // Get user's role
            'activity' => "Added a new product: {$request->model_name} (Model ID: {$request->model_id})",
        ]);

        return "<script>alert('Product details added successfully!'); window.location.href='" . route('ManagerproductsView') . "';</script>";
    }


    public function viewDetailsofProduct($model_id)
    {
        $product = Products::where('model_id', $model_id)->firstOrFail();

        if (!$product) {
            return redirect()->back()->with('error', 'Product not found!');
        }

        return view('stockclerk.content.ViewDetails', compact('product', 'model_id'));
    }
    

    public function ManagerviewDetailsofProduct($model_id)
    {
        $product = Products::where('model_id', $model_id)->firstOrFail();

        if (!$product) {
            return redirect()->back()->with('error', 'Product not found!');
        }

        return view('manager.content.ManagerViewDetails', compact('product', 'model_id'));
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

            // Track changes
            $changes = [];
            foreach (['model_name', 'brand_name', 'price', 'description', 'm_part_id', 'stocks_quantity', 'status'] as $field) {
                if ($product->$field != $request->$field) {
                    $changes[] = ucfirst(str_replace('_', ' ', $field)) . " changed from '{$product->$field}' to '{$request->$field}'";
                }
            }

            // Handle image upload
            if ($request->hasFile('model_img')) {
                $image = $request->file('model_img');
                $imageName = $image->getClientOriginalName(); // Keep original filename
                $image->move(public_path('product-images/'), $imageName);
                $changes[] = "Model image updated";

                // Update model_img field in database
                $product->model_img = $imageName;
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

            // Insert activity log with specific details
            ActivityLog::create([
                'user_id' => Auth::id(),
                'role' => Auth::user()->role, // Get user's role
                'activity' => "Updated product #$model_id details: " . implode(', ', $changes),
            ]);

            // Return success alert and reload the page
            return "<script>alert('Product updated successfully!'); window.location.href='" . route('manager.viewDetails', ['model_id' => $model_id]) . "';</script>";
        } catch (\Exception $e) {
            return "<script>alert('Error: " . $e->getMessage() . "'); window.history.back();</script>";
        }
    }

    public function ManagerupdateProduct(Request $request, $model_id)
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

            // Track changes
            $changes = [];
            foreach (['model_name', 'brand_name', 'price', 'description', 'm_part_id', 'stocks_quantity', 'status'] as $field) {
                if ($product->$field != $request->$field) {
                    $changes[] = ucfirst(str_replace('_', ' ', $field)) . " changed from '{$product->$field}' to '{$request->$field}'";
                }
            }

            // Handle image upload
            if ($request->hasFile('model_img')) {
                $image = $request->file('model_img');
                $imageName = $image->getClientOriginalName(); // Keep original filename
                $image->move(public_path('product-images/'), $imageName);
                $changes[] = "Model image updated";

                // Update model_img field in database
                $product->model_img = $imageName;
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

            // Insert activity log with specific details
            ActivityLog::create([
                'user_id' => Auth::id(),
                'role' => Auth::user()->role, // Get user's role
                'activity' => "Updated product #$model_id details: " . implode(', ', $changes),
            ]);

            // Return success alert and reload the page
            return "<script>alert('Product updated successfully!'); window.location.href='" . route('manager.viewDetails', ['model_id' => $model_id]) . "';</script>";
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

    public function ManagerviewModelDetails($model_id)
    {
        try {
            // Find the model by model_id
            $model = Models::where('model_id', $model_id)->firstOrFail();

            // Return the view with the model details
            return view('manager.content.ManagerviewModelDetails', compact('model'));
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
                $image->move(public_path('product-images/'), $imageName);
                $model->model_img = $imageName;
            }

            $model->save();

            // ✅ Insert activity log after saving the updated model
            ActivityLog::create([
                'user_id' => Auth::id(),
                'role' => Auth::user()->role, // Get user's role
                'activity' => "Updated model #$model_id details",
            ]);


            return redirect()->route('viewModelDetails', ['model_id' => $model_id])
                ->with('success', 'Model updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function ManagerupdateModel(Request $request, $model_id)
    {
        try {
            // Validate request
            $request->validate([
                'model_name' => 'required|string|max:255',
                'price' => 'required|numeric',
                'status' => 'required|string',
                'w_variant' => 'required|in:none,YES', // ✅ Validate new field
                'model_img' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // Validate image
            ]);
    
            // Find model by ID
            $model = Models::findOrFail($model_id);
    
            // Update fields
            $model->model_name = $request->model_name;
            $model->price = $request->price;
            $model->status = $request->status;
            $model->w_variant = $request->w_variant; // ✅ Update `w_variant`
    
            // Handle image upload if provided
            if ($request->hasFile('model_img')) {
                $image = $request->file('model_img');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('product-images/'), $imageName);
                $model->model_img = $imageName;
            }
    
            $model->save();
    
            // ✅ Insert activity log after saving the updated model
            ActivityLog::create([
                'user_id' => Auth::id(),
                'role' => Auth::user()->role,
                'activity' => "Updated model #$model_id details",
            ]);
    
            return redirect()->route('manager.viewModelDetails', ['model_id' => $model_id])
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
    
            ActivityLog::create([
                'user_id' => Auth::id(),
                'role' => Auth::user()->role, // Get user's role
                'activity' => "Updated model #$model_id status to {$model->status}",
            ]);
    
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

    public function ManagerindexVariant($model_id)
    {
        // Fetch the variants related to the model_id
        $variants = Variant::where('model_id', $model_id)->get();

        $model = Models::where('model_id', $model_id)->first();

        if (!$model) {
            return redirect()->back()->with('error', 'Model not found.');
        }

        return view('manager.content.ManagerviewVariants', compact('variants', 'model_id', 'model'));
    }

    public function IndexAddVariant($model_id)
    {
        $model = Models::where('model_id', $model_id)->first();

        if (!$model) {
            return redirect()->back()->with('error', 'Model not found.');
        }

        return view('stockclerk.content.addVariant', compact('model', 'model_id'));
    }

    public function ManagerIndexAddVariant($model_id)
    {
        $model = Models::where('model_id', $model_id)->first();

        if (!$model) {
            return redirect()->back()->with('error', 'Model not found.');
        }

        return view('manager.content.ManageraddVariant', compact('model', 'model_id'));
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
            $request->file('variant_image')->move(public_path('product-images/'), $originalName);
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

        ActivityLog::create([
            'user_id' => Auth::id(),
            'role' => Auth::user()->role, // Get user's role
            'activity' => "Added new variant '{$request->product_name}' for model #$model_id",
        ]);


        return redirect()->route('add.variant', ['model_id' => $model_id])->with('success', 'Variant added successfully.');
    }

    public function ManagerStoreVariant(Request $request, $model_id)
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
            $request->file('variant_image')->move(public_path('product-images/'), $originalName);
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

        ActivityLog::create([
            'user_id' => Auth::id(),
            'role' => Auth::user()->role, // Get user's role
            'activity' => "Added new variant '{$request->product_name}' for model #$model_id",
        ]);


        return redirect()->route('manager.add.variant', ['model_id' => $model_id])->with('success', 'Variant added successfully.');
    }

    
    public function editVariant($model_id, $variant_id, Request $request)
    {
        $variant = Variant::where('model_id', $model_id)->where('variant_id', $variant_id)->first();
    
        if (!$variant) {
            return redirect()->back()->with('error', 'Variant not found.');
        }
    
        // Store the previous URL in session
        session(['previous_url' => url()->previous()]);

        // ✅ Log activity when a user accesses the edit variant page
        ActivityLog::create([
            'user_id' => Auth::id(),
            'role' => Auth::user()->role, // Get user's role
            'activity' => "Accessed edit page for variant #$variant_id of model #$model_id",
        ]);

    
        return view('stockclerk.content.editVariant', compact('variant', 'model_id', 'variant_id'));
    }

    public function ManagereditVariant($model_id, $variant_id, Request $request)
    {
        $variant = Variant::where('model_id', $model_id)->where('variant_id', $variant_id)->first();
    
        if (!$variant) {
            return redirect()->back()->with('error', 'Variant not found.');
        }
    
        // Store the previous URL in session
        session(['previous_url' => url()->previous()]);

        // ✅ Log activity when a user accesses the edit variant page
        ActivityLog::create([
            'user_id' => Auth::id(),
            'role' => Auth::user()->role, // Get user's role
            'activity' => "Accessed edit page for variant #$variant_id of model #$model_id",
        ]);

    
        return view('manager.content.ManagerEditVariant', compact('variant', 'model_id', 'variant_id'));
    }
    
    

    public function deleteVariant($id)
    {
        $variant = Variant::find($id);
        if (!$variant) {
            return redirect()->back()->with('error', 'Variant not found.');
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'role' => Auth::user()->role, // Get user's role
            'activity' => "Deleted variant #$id",
        ]);

        $variant->delete();
        return redirect()->back()->with('success', 'Variant deleted successfully.');
    
    }

    public function ManagerdeleteVariant($id)
    {
        $variant = Variant::find($id);
        if (!$variant) {
            return redirect()->back()->with('error', 'Variant not found.');
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'role' => Auth::user()->role, // Get user's role
            'activity' => "Deleted variant #$id",
        ]);

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
            $request->file('variant_image')->move(public_path('product-images/'), $imageName);
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

        ActivityLog::create([
            'user_id' => Auth::id(),
            'role' => Auth::user()->role, // Get user's role
            'activity' => "Updated variant #$variant_id of model #$model_id",
        ]);

    
        if ($variant->save()) {
            return redirect()->route('manager.variantsView', ['model_id' => $model_id])->with('success', 'Variant updated successfully.');
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

        ActivityLog::create([
            'user_id' => Auth::id(),
            'role' => Auth::user()->role, // Get user's role
            'activity' => "Updated status of variant #$variant_id to {$request->status}",
        ]);


        if ($variant->save()) {
            return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
        } else {
            return response()->json(['success' => false, 'message' => 'Failed to update status.']);
        }
    }

    
    


}
