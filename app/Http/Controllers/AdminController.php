<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; 
use Illuminate\Support\Facades\Log; 
use App\Models\Order;
use App\Models\Models;
use App\Models\Products;
use App\Models\Variant;
use App\Models\GcashPayment;
use App\Models\RefundOrder;
use App\Models\OrderReference;
use App\Models\RefundLog;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog;  
use App\Models\Brand;  
use App\Models\Customer;  
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;


class AdminController extends Controller
{
    public function AdminSalesReportIndex(Request $request)
    {
        // Fetch all activity logs in descending order by created_at
        $activityLogs = ActivityLog::orderBy('created_at', 'desc')->get();
    
        $salesDataQuery = OrderDetail::where('product_status', 'Completed')
        ->with('model') // Eager load the related Models
        ->get()
        ->groupBy('product_name')
        ->map(function ($groupedDetails) {
            return [
                'product_name' => $groupedDetails->first()->product_name,
                'quantity' => $groupedDetails->sum('quantity'),
                'sales' => $groupedDetails->sum(function ($detail) {
                    return $detail->quantity * $detail->price;
                }),
                'model_img' => $groupedDetails->first()->model?->model_img,
            ];
        });

        // Paginate the collection
        $perPage = 10; // Items per page
        $currentPage = LengthAwarePaginator::resolveCurrentPage(); // Get current page
        $items = $salesDataQuery->forPage($currentPage, $perPage); // Slice the collection for the current page

        $salesData = new LengthAwarePaginator(
            $items,
            $salesDataQuery->count(), // Total items
            $perPage,
            $currentPage,
            ['path' => $request->url()] // Ensure pagination links use the current URL
        );
        
        // Fetch order counts based on status from App\Models\Order
        $orderStatuses = [
            'In Process' => Order::where('status', 'In Process')->count(),
            'Completed' => Order::where('status', 'Completed')->count(),
            'Pending' => Order::where('status', 'Pending')->count(),
            'Cancelled' => Order::where('status', 'Cancelled')->count(),
        ];
    
        // Logic for total sales and percentage average per week for 'Completed' orders
        $completedOrders = Order::where('status', 'Completed')->get();
    
        // Calculate total sales
        $totalSales = $completedOrders->sum('total_price');
    
        // Calculate sales grouped by week
        $weeklySales = $completedOrders->groupBy(function ($order) {
            // Group by the week of the year (using Carbon)
            return \Carbon\Carbon::parse($order->created_at)->startOfWeek()->format('Y-m-d');
        })->map(function ($weeklyOrders) {
            // Sum total_price for each week
            return $weeklyOrders->sum('total_price');
        });
    
        // Calculate percentage contribution per week
        $percentagePerWeek = $weeklySales->map(function ($weeklyTotal) use ($totalSales) {
            return $totalSales > 0 ? ($weeklyTotal / $totalSales) * 100 : 0;
        });
    
        // Line chart sales data for the last 6 months
        $salesLineData = Order::where('status', 'Completed')
            ->whereDate('created_at', '>=', now()->subMonths(6)) // Filter orders from the last 6 months
            ->get()
            ->groupBy(function ($order) {
                // Group by month and year (e.g., 'January 2025')
                return \Carbon\Carbon::parse($order->created_at)->format('F Y');
            })->map(function ($monthlyOrders) {
                return $monthlyOrders->sum('total_price'); // Sum the total_price for each month
            });
    
        // Fill missing months with 0 sales
        $months = collect([]);
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('F Y');
            $months->put($month, $salesLineData->get($month, 0));
        }
    
        // Get date range from request, with defaults to last 30 days
        $startDate = $request->input('start_date', now()->subDays(29)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        // Daily sales for the selected date range
        $dailySales = Order::where('status', 'Completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->groupBy(function ($order) {
                return \Carbon\Carbon::parse($order->created_at)->format('Y-m-d');
            })->map(function ($dailyOrders) {
                return $dailyOrders->sum('total_price');
            });

        // Fill missing dates with 0 sales
        $days = collect([]);
        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
        foreach ($period as $date) {
            $days->put($date->format('Y-m-d'), $dailySales->get($date->format('Y-m-d'), 0));
        }
    
        // Pass the data to the view
        return view('admin.content.adminSalesReport', compact(
            'activityLogs',
            'salesData',
            'orderStatuses',
            'totalSales',
            'weeklySales', // Include weeklySales for frontend use
            'percentagePerWeek',
            'months',
            'days',
            'salesData'
        ));
    }  

    public function adminUsers()
    {
        $users = \App\Models\User::paginate(16);
        return view('admin.content.adminUsers', compact('users'));
    }

    public function adminEditUser($id)
    {
        $user = \App\Models\User::findOrFail($id);
        return view('admin.content.adminEditUser', compact('user'));
    }

    public function updateUser(Request $request, $id)
    {
        try {
            $user = \App\Models\User::findOrFail($id);

            $user->name = $request->name;
            $user->email = $request->email;
            $user->status = $request->status;
            $user->role = $request->role;

            // Update password only if a new one is provided
            if ($request->filled('password')) {
                $user->password = bcrypt($request->password);
            }

            $user->save();

            return redirect()->route('admin.users.edit', $id)->with('success', 'User updated successfully!');
        } catch (\Exception $e) {
            return redirect()->route('admin.users.edit', $id)->with('error', 'Failed to update user.');
        }
    }

    // In AdminController.php
    public function createUser()
    {
        return view('admin.content.addUser');  // This assumes the view is located at resources/views/admin/users/addUser.blade.php
    }

    // In AdminController.php
    public function storeUser(Request $request)
    {
        \Log::info('User creation request data:', $request->all()); // Log the incoming request data

        // Validate the input data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,staff,manager,stock-clerk',
            'status' => 'required|in:active,inactive',
            'password' => 'required|string|min:6',
        ]);

        // Create the new user
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'status' => $request->status,
            'password' => bcrypt($request->password),
        ]);

        // Redirect with success message
        return redirect()->route('admin.users.create')->with('success', 'User created successfully!');
    }

    public function AdminOrderOverview()
    {
        $orders = \App\Models\Order::select('order_id', 'user_id', 'total_items', 'total_price', 'created_at', 'status', 'payment_method', 'overall_status', 'reference_id')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    
        foreach ($orders as $order) {
            // Check if reference exists in order_reference table
            $existingReference = \App\Models\OrderReference::where('order_id', $order->order_id)->value('reference_id');
    
            if ($existingReference) {
                $order->custom_reference_id = $existingReference; // Keep original reference_id, add custom_reference_id
                continue; // Skip the rest of the loop and use the existing reference
            }
    
            $orderDetails = OrderDetail::where('order_id', $order->order_id)
                ->latest('order_detail_id')
                ->take(2)
                ->get(['part_id', 'variant_id', 'brand_name']);
    
            $cleanParts = collect();
            $brandNames = collect();
    
            foreach ($orderDetails as $detail) {
                if (!empty($detail->variant_id) && $detail->variant_id != 0) {
                    $variantPartId = \App\Models\Variant::where('variant_id', $detail->variant_id)->value('part_id');
                    $cleanPart = $variantPartId ? substr(preg_replace('/[^A-Za-z0-9]/', '', $variantPartId), 0, 3) : '';
                    $brandName = $detail->brand_name ? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $detail->brand_name), 0, 3)) : '';
                    $brandNames->push($brandName);
                } else {
                    $cleanPart = substr(preg_replace('/[^A-Za-z0-9]/', '', $detail->part_id), 0, 4);
                }
    
                $cleanParts->push($cleanPart);
            }
    
            $productBrandName = \App\Models\Products::whereIn('m_part_id', $orderDetails->pluck('part_id'))->value('brand_name');
            $shortProductBrand = $productBrandName ? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '-', $productBrandName), 0, 3)) : '';
            $finalBrand = $brandNames->isNotEmpty() ? $brandNames->first() : $shortProductBrand;
    
            if ($cleanParts->count() === 2) {
                $order->custom_reference_id = $finalBrand;
            } elseif ($cleanParts->count() === 1) {
                $order->reference_id = $finalBrand . $cleanParts[0];
            } else {
                $order->reference_id = $finalBrand;
            }
        }
    
        session(['pendingCount' => \App\Models\Order::where('status', 'Pending')->count()]);
        session(['pendingRefundCount' => \App\Models\RefundOrder::where('status', 'Pending')->count()]);
    
        return view('admin.content.adminOrderOverview', compact('orders'));
    }
        

    public function Admindetails($order_id, Request $request)
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
    
        return view('admin.content.AdminOverviewDetails', compact('order', 'orderDetails'));
    }

    public function AdmineditDetails($order_id)
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
    
        return view('admin.content.adminEditDetailsRefund', compact('orderDetails'));
    }

    public function AdminupdateOrderDetails(Request $request)
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
        return redirect()->route('admin.edit.product', ['order_id' => $orderId])->with('success', 'Order details updated successfully.');
        } catch (\Exception $e) {
            // Rollback in case of error
            \DB::rollBack();
    
            // Redirect back with error message
            return redirect()->route('admin.edit.product', ['order_id' => $request->order_id])->with('error', 'Error updating order details: ' . $e->getMessage());
        }
    }

    public function AdminupdateStatus($order_id, Request $request)
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

    public function AdminindexdDashboard()
    {
        $products = Models::with(['brand.category'])->paginate(8); 
        $brands = Brand::pluck('brand_name');
        $statuses = Products::distinct()->pluck('status');
        
        return view('admin.dashboard.adminDashboard', compact('products', 'brands', 'statuses'));
    }


    public function AdminindexView(Request $request)
    {
        $brands = Brand::where('status', 'active')->get();
        $selectedBrandId = $request->query('brand_id');
        $models = [];
        $customers = Customer::where('status', 'active')->get(); // Fetch active customers
    
        if ($selectedBrandId) {
            $models = Models::where('brand_id', $selectedBrandId)
                ->where('status', 'active')
                ->select('model_name', 'model_img', 'price', 'model_id', 'w_variant')
                ->with(['products' => function ($query) {
                    $query->select('model_id', 'stocks_quantity', 'm_part_id');
                }])
                ->get();
    
            foreach ($models as $model) {
                if ($model->w_variant === 'YES') {
                    $model->variants = Variant::where('model_id', $model->model_id)
                        ->where('status', 'active')
                        ->select('product_name', 'variant_image', 'price', 'variant_id', 'stocks_quantity', 'model_id', 'part_id')
                        ->get();
                }
            }
        }
    
        return view('admin.content.AdminPOSView', compact('brands', 'models', 'selectedBrandId', 'customers'));
    }

    public function AdminCustomerStore(Request $request)
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

    public function AdmingetBrandModels($brand_id)
    {
        $models = Models::where('brand_id', $brand_id)
            ->where('status', 'active')
            ->select('model_name', 'model_img', 'price', 'model_id', 'w_variant')
            ->with(['products' => function ($query) {
                $query->select('model_id', 'stocks_quantity', 'm_part_id');
            }])
            ->get();
    
        foreach ($models as $model) {
            if ($model->w_variant === 'YES') {
                $model->variants = Variant::where('model_id', $model->model_id)
                    ->where('status', 'active')
                    ->select('product_name', 'variant_image', 'price', 'variant_id', 'stocks_quantity', 'model_id', 'part_id') // <- added
                    ->get();
            }
        }
    
        return response()->json($models);
    }

    public function AdminsaveGCashImage(Request $request)
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

    public function AdminsavePNBImage(Request $request)
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

    public function AdminsaveOrderPOS(Request $request)
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

    public function AdminProductsView()
    {
        $products = Models::with(['brand.category'])
                          ->orderBy('created_at', 'desc') // or 'id' if you prefer
                          ->paginate(10); 
                          
        $brands = Brand::pluck('brand_name');
        $statuses = Products::distinct()->pluck('status');
        
        return view('admin.content.adminProductsView', compact('products', 'brands', 'statuses'));
    }  

    public function AdminindexVariant($model_id)
    {
        // Fetch the variants related to the model_id
        $variants = Variant::where('model_id', $model_id)->get();

        $model = Models::where('model_id', $model_id)->first();

        if (!$model) {
            return redirect()->back()->with('error', 'Model not found.');
        }

        return view('admin.content.AdminviewVariants', compact('variants', 'model_id', 'model'));
    }

    
    public function AdminIndexAddVariant($model_id)
    {
        $model = Models::where('model_id', $model_id)->first();

        if (!$model) {
            return redirect()->back()->with('error', 'Model not found.');
        }

        return view('admin.content.AdminaddVariant', compact('model', 'model_id'));
    }

    public function AdminStoreVariant(Request $request, $model_id)
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


        return redirect()->route('admin.add.variant', ['model_id' => $model_id])->with('success', 'Variant added successfully.');
    }

    public function AdmineditVariant($model_id, $variant_id, Request $request)
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

    
        return view('admin.content.AdminEditVariant', compact('variant', 'model_id', 'variant_id'));
    }

    public function AdminupdateVariant(Request $request, $model_id, $variant_id)
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
            return redirect()->route('admin.variantsView', ['model_id' => $model_id])->with('success', 'Variant updated successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to update variant.');
        }
    }

    public function AdmindeleteVariant($id)
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


}
