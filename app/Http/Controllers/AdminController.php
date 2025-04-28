<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; 
use Illuminate\Support\Facades\Log; 
use App\Models\Order;
use App\Models\Models;
use App\Models\PnbPayment;
use App\Models\Products;
use App\Models\DefectiveProduct;
use App\Models\Variant;
use App\Models\Category;
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
use Illuminate\Support\Facades\Validator;


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

    public function saveGcashPayment(Request $request)
    {
        // Validate payment method selection
        $request->validate([
            'order_id' => 'required|exists:orders,order_id',
            'paymentMethod' => 'required|in:gcash',
        ]);
    
        // Validate GCash image only
        $request->validate([
            'gcash_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
    
        $paymentMethod = $request->paymentMethod;
    
        if ($paymentMethod == 'gcash' && $request->hasFile('gcash_image')) {
            $gcashImage = $request->file('gcash_image');
            $gcashImageName = time() . '-' . $gcashImage->getClientOriginalName();
    
            if (file_exists(public_path('onlinereceipts/' . $gcashImageName))) {
                return back()->withErrors(['gcash_image' => 'This file already exists. Please upload a different file.']);
            }
    
            $gcashImage->move(public_path('onlinereceipts'), $gcashImageName);
    
            // Save to gcash_payment table
            GcashPayment::create([
                'order_id' => $request->order_id,
                'image' => $gcashImageName,
                'status' => 'Pending',
            ]);
    
            // Update orders table's payment_method
            \DB::table('orders')
                ->where('order_id', $request->order_id)
                ->update(['payment_method' => 'GCASH']);
        }
    
        return back()->with('success', 'GCash payment saved successfully.');
    }
    
    
    
    public function savePnbPayment(Request $request)
    {
        // Validate payment method selection
        $request->validate([
            'order_id' => 'required|exists:orders,order_id',
            'paymentMethod' => 'required|in:pnb',
        ]);

        // Validate uploaded image
        $request->validate([
            'pnb_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('pnb_image')) {
            $pnbImage = $request->file('pnb_image');
            $pnbImageName = time() . '-' . $pnbImage->getClientOriginalName();

            // Check if file already exists
            if (file_exists(public_path('onlinereceipts/' . $pnbImageName))) {
                return back()->withErrors(['pnb_image' => 'This file already exists. Please upload a different file.']);
            }

            // Move file to public/onlinereceipts
            $pnbImage->move(public_path('onlinereceipts'), $pnbImageName);

            // Insert into pnb_payment table
            \App\Models\PnbPayment::create([
                'order_id' => $request->order_id,
                'image' => $pnbImageName,
                'status' => 'Completed',
            ]);

            // Update orders table's payment_method to PNB
            \DB::table('orders')
                ->where('order_id', $request->order_id)
                ->update(['payment_method' => 'PNB']);
        }

        return back()->with('success', 'PNB payment saved successfully.');
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
        $orders = Order::with('orderReference') // Eager load order_reference
            ->select('order_id', 'user_id', 'total_items', 'total_price', 'created_at', 'status', 'payment_method', 'overall_status', 'reference_id')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        foreach ($orders as $order) {
            if ($order->orderReference) {
                $order->custom_reference_id = $order->orderReference->reference_id;
            } else {
                $order->custom_reference_id = null; // Optional, you can set it null
            }
        }

        session(['pendingCount' => Order::where('status', 'Pending')->count()]);
        session(['pendingRefundCount' => RefundOrder::where('status', 'Pending')->count()]);

        return view('admin.content.adminOrderOverview', compact('orders'));
    }


    public function Admindetails($order_id, Request $request)
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
    

        return view('admin.content.AdminOverviewDetails', compact('order', 'orderDetails', 'reference_id'));
    }
    
    public function getPaymentImage($order_id, $payment_method)
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

    public function AdminDefectiveindexdDashboard()
    {
        // Fetch defective products with pagination, ordered by the created_at field of the associated Order in descending order
        $defectiveProducts = DefectiveProduct::with('orderReference', 'order')  // Load order relation too
            ->paginate(10);
        
        return view('admin.content.adminDefectiveProductsView', compact('defectiveProducts'));
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
    
    public function AdminviewDetailsofProduct($model_id)
    {
        $product = Products::where('model_id', $model_id)->firstOrFail();

        if (!$product) {
            return redirect()->back()->with('error', 'Product not found!');
        }

        return view('admin.content.AdminViewDetails', compact('product', 'model_id'));
    }

    public function AdminupdateProduct(Request $request, $model_id)
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
            return "<script>alert('Product updated successfully!'); window.location.href='" . route('admin.viewDetails', ['model_id' => $model_id]) . "';</script>";
        } catch (\Exception $e) {
            return "<script>alert('Error: " . $e->getMessage() . "'); window.history.back();</script>";
        }
    }

    public function AdminaddDetails($model_id)
    {
        // Fetch the model name based on model_id
        $model = Models::where('model_id', $model_id)->first();

        // Fetch available brands
        $brands = Brand::all();

        // Get the role of the authenticated user
        $user = Auth::user();
        $role = $user->role; // Get the role of the user

        // Log the activity
        ActivityLog::create([
            'user_id' => Auth::id(),
            'role' => $role, // Insert the user's role
            'activity' => "Accessed Add Details page for Model #$model_id ({$model->model_name})",
        ]);

        return view('admin.content.AdminaddDetails', [
            'model_id' => $model_id, 
            'price' => $model ? $model->price : '',
            'model_name' => $model ? $model->model_name : '', 
            'brands' => $brands
        ]);
    }

    public function AdminaddProductDetails(Request $request)
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
        Products::create([
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

        return "<script>alert('Product details added successfully!'); window.location.href='" . route('adminproductsView') . "';</script>";
    }

    public function AdminviewModelDetails($model_id)
    {
        try {
            // Find the model by model_id
            $model = Models::where('model_id', $model_id)->firstOrFail();

            // Return the view with the model details
            return view('admin.content.AdminviewModelDetails', compact('model'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Model not found!');
        }
    }

    public function AdminupdateModel(Request $request, $model_id)
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
    
            return redirect()->route('admin.viewModelDetails', ['model_id' => $model_id])
                ->with('success', 'Model updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function Admincreate()
    {
        $brands = Brand::all(); // Fetch all brands from the database
        return view('admin.content.AdminaddProduct', compact('brands'));
    }

    public function Adminstore(Request $request)
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

    
            return "<script>alert('Product successfully inserted!'); window.location.href='" . route('adminproductsView') . "';</script>";
    
        } catch (\Exception $e) {
            return "<script>alert('Error: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
        }
    }

    public function AdminViewBrandsList()
    {
        $brands = Brand::all();
        $categories = Category::all(); // Fetch all categories
        return view('admin.content.AdminViewBrands', compact('brands', 'categories'));
    }

    public function AdminStockcreate()
    {
        $brands = Brand::all();
        $categories = Category::all(); // Fetch all categories
        return view('admin.content.AdminInsertNewBrand', compact('brands', 'categories'));
    }
    

    public function AdminstoreBrand(Request $request)
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
    
        return redirect()->route('admin.add.brand')->with('success', 'Brand added successfully!');
    } 

    public function AdmineditBrand($brand_id)
    {
        // Fetch the brand details by ID
        $brand = Brand::findOrFail($brand_id);

        // Pass brand data to the edit view
        return view('admin.content.AdminEditBrand', compact('brand'));
    }

    public function AdminupdateBrand(Request $request, $brand_id)
    {
        try {
            // Validate the input data
            $request->validate([
                'brand_name' => 'required|string|max:255',
                'category_name' => 'required|string|max:255',
                'status' => 'required|in:active,inactive',
            ]);

            // Find the brand by ID
            $brand = Brand::findOrFail($brand_id);

            // Update the brand details
            $brand->brand_name = $request->brand_name;
            $brand->category->category_name = $request->category_name; // Assuming category exists
            $brand->status = $request->status;
            $brand->save();

            return redirect()->back()->with('success', 'Brand updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update brand. Please try again.');
        }
    }

    public function AdminStockViewCategory()
    {
        $brands = Brand::all();
        $categories = Category::all(); // Fetch all categories
        return view('admin.content.AdminStockClerkViewCategory', compact('brands', 'categories'));
    }

    public function AdminAddCategory (){
        $brands = Brand::all();
        $categories= Category::all();
        return view('admin.content.AdminAddCategory', compact('brands', 'categories'));
    }

    public function AdminstoreCategory(Request $request)
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

    public function AdmineditCategory($category_id)
    {
        // Fetch the category by ID
        $category = Category::findOrFail($category_id);

        // Pass the category data to the edit view
        return view('admin.content.AdminEditCategory', compact('category'));
    }

    public function AdminupdateCategory(Request $request, $category_id)
    {
        try {
            $category = Category::findOrFail($category_id);

            // Validate request
            $request->validate([
                'category_name' => 'required|string|max:255',
                'cat_image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
                'status' => 'required|in:active,inactive',
            ]);

            // Update category name and status
            $category->category_name = $request->category_name;
            $category->status = $request->status;

            // Handle image upload if a new file is provided
            if ($request->hasFile('cat_image')) {
                $image = $request->file('cat_image');
                $imageName = time() . '.' . $image->extension();
                $image->move(public_path('product-images'), $imageName);
                $category->cat_image = $imageName;
            }

            $category->save();

            return redirect()->back()->with('success', 'Category updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update category. Please try again.');
        }
    }

    public function AdminrefundRequests()
    {
        // Fetch all refund requests    
        $refunds = RefundOrder::select('refund_id', 'order_id', 'user_id', 'status')->get();

        $refunds = RefundOrder::with(['customer', 'orderReference', 'order']) // Eager load 'order' relationship
        ->orderBy('created_at', 'desc') // Sort by newest first
        ->paginate(9); // Show 9 per page

        return view('admin.content.adminRequestRefundList', compact('refunds'));
    }

    public function AdmincreateForm()
    {
        // Get all orders (or limit it as needed)
        $orders = Order::with('customer')->get();

        return view('admin.content.AdminRequestForm', compact('orders'));
    }

    public function AdminstoreForm(Request $request)
    {
        // Validate the input data
        $validated = $request->validate([
            'order_id' => 'required|integer|exists:orders,order_id',
            'user_id' => 'required|integer|exists:customers,id',
            'refund_reason' => 'required|string',
            'processed_by' => 'required|integer|exists:users,id',
            'refund_method' => 'required|string',
            'status' => 'required|string',
        ]);

        // Create the refund order
        RefundOrder::create($validated);

        // Fetch updated orders and return the view
        $orders = Order::with('customer')->get();
        return redirect()->back()->with('success', 'Refund request submitted successfully!');
    }

    public function AdminshowRefundRequestForm($order_id)
    {
        $reference_id = request('reference_id');
    
        $refund = RefundOrder::where('order_id', $order_id)
            ->with('customer')
            ->first();
    
        $orderDetails = OrderDetail::where('order_id', $order_id)->get();
    
        if (!$refund) {
            return redirect()->route('admin.refundRequests')->with('error', 'Refund request not found.');
        }
    
        $models = Models::where('status', 'active')
            ->where('w_variant', '!=', 'YES')
            ->with(['products' => function ($query) {
                $query->select('model_id', 'stocks_quantity');
            }])
            ->get()
            ->map(function ($model) {
                $model->total_stock_quantity = $model->products->sum('stocks_quantity');
                return $model;
            });
    
        $variants = Variant::whereHas('model', function ($query) {
            $query->where('w_variant', 'YES')->where('status', 'active');
        })->get();
    
        $gcashPaymentStatus = GcashPayment::where('order_id', $order_id)->value('status') ?? 'Pending';
        $pnbPaymentStatus = PnbPayment::where('order_id', $order_id)->value('status') ?? 'Pending';
    
        // Fetch defective products for this order (both variant and model)
        $defectiveProducts = DefectiveProduct::where('order_id', $order_id)
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->variant_id ?: $item->model_id => true]; // key by variant_id or model_id
            })
            ->keys()
            ->toArray();
    
        // 👆 now $defectiveProducts is an array of model_ids that are defective
    
        return view('admin.content.adminRequestRefundForm', compact(
            'refund',
            'orderDetails',
            'models',
            'variants',
            'reference_id',
            'gcashPaymentStatus',
            'pnbPaymentStatus',
            'defectiveProducts' // Pass it to view
        ));
    }    



    public function AdminupdateRefundStatusOverall(Request $request, $order_id)
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
        // $newReferenceId = "{$brandCode}-{$partCode1}{$partCode2}";

        $newReferenceId = "{$brandCode}-{$partCode1}{$partCode2}-OR00" . str_pad($order_id, 5, '0', STR_PAD_LEFT);
    
        // Handle "Completed - with changes"
        if ($request->overall_status === 'Completed - with changes') {
            // Update refund order status
            $refund->update([
                'overall_status' => 'Completed - with changes',
                'status' => 'Completed',
            ]);

            // Using Eloquent to update or create the reference_id in order_reference table
            $orderReference = OrderReference::where('order_id', $order_id)->first();

            if ($orderReference) {
                // Update the existing order reference
                $orderReference->update(['reference_id' => $newReferenceId]);
            } else {
                // Create a new order reference if it doesn't exist
                OrderReference::create([
                    'order_id' => $order_id,
                    'reference_id' => $newReferenceId,
                ]);
            }

            // Log the update
            \Log::info("Updated order_reference: Order ID: $order_id, New Reference ID: $newReferenceId");
        }
        // Handle "Completed - no changes"
        elseif ($request->overall_status === 'Completed - no changes') {
            $refund->update([
                'overall_status' => 'Completed - no changes',
                'status' => 'Completed',
            ]);
        }
        // Handle "Complete Refund"
        elseif ($request->overall_status === 'Complete Refund') {
            $refund->update([
                'overall_status' => 'Complete Refund',
                'status' => 'Refunded',
            ]);
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

    public function AdminupdateProductStatusRefunded(Request $request)
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
            
                if ($orderDetail && $orderDetail->product_status !== $status) {
                    if ($status === 'defective-product') {
                        try {
                            DefectiveProduct::create([
                                'order_id' => $order_id,
                                'model_id' => $variant_id != 0 ? null : $product_id, // if variant exists, set model_id to NULL
                                'variant_id' => $variant_id != 0 ? $variant_id : null, // if variant exists, use it
                                'product_name' => $orderDetail->product_name,
                                'brand_name' => $orderDetail->brand_name,
                                'quantity' => $orderDetail->quantity,
                                'price' => $orderDetail->price,
                                'total_price' => $price,
                                'product_status' => 'defective-product',
                                'part_id' => $orderDetail->part_id,
                                'm_part_id' => $orderDetail->m_part_id,
                            ]);                            
                            Log::info("Defective product added: Order ID: $order_id, Product ID: $product_id, Variant ID: $variant_id");
                            continue;
                        } catch (\Exception $e) {
                            Log::error("Error inserting defective product for Order ID: $order_id, Product ID: $product_id: " . $e->getMessage());
                        }
                    }
                    
                    // Handle 'refunded' and 'pending' statuses
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
            
                    if ($status !== 'defective-product') {
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

    public function AdminRefundViewList()
    {
        $refunds = RefundOrder::with('orderReference') // eager load orderReference
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);
    
        return view('admin.content.AdminRefundView', compact('refunds'));
    }
    

    public function AdminRefundDetailsView($order_id, $reference_id = null)
    {

        $orderDetails = OrderDetail::where('order_id', $order_id)->get();

        $refund = RefundOrder::where('order_id', $order_id)->first();
        $orderReference = OrderReference::where('order_id', $order_id)->first();
        
        // Fetch order details from the 'orders' table
        $order = Order::where('order_id', $order_id)->first();

        // Pass refund, orderReference, reference_id, and order details to the view
        return view('admin.content.AdminRefundDetails', compact('refund', 'orderReference', 'reference_id', 'order', 'orderDetails'));
    }

    public function AdminviewRefundLog()
    {
        $logs = RefundLog::with('user')->orderBy('refunded_at', 'desc')->paginate(16); // 10 items per page
        return view('admin.content.AdminRefundLog', compact('logs'));
    }

    public function updateRefundMethod(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:refund_order,order_id',
            'refund_method' => 'required|in:Cash,GCash,PNB', // Added PNB to the allowed refund methods
            'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
    
        $orderId = $request->order_id;
        $newMethod = $request->refund_method;
    
        // If switching to GCash and receipt is uploaded
        if ($newMethod === 'GCash' && $request->hasFile('receipt_image')) {
            $image = $request->file('receipt_image');
            $imageName = $image->getClientOriginalName();  // Get original filename
            $imageHash = md5(file_get_contents($image));  // Hash the file content to ensure uniqueness
            $hashedImageName = $imageHash . '.' . $image->getClientOriginalExtension(); // Use hash in filename
    
            $imagePath = public_path('onlinereceipts/' . $hashedImageName);
    
            // Check if the same image (hashed) already exists in the gcash_payment table for this order_id
            $existingReceipt = \DB::table('gcash_payment')
                ->where('order_id', $orderId)
                ->where('image', $hashedImageName)
                ->first();
    
            if ($existingReceipt) {
                // If receipt already exists, return a message to prevent duplication
                return redirect()->back()->with('error', 'GCash receipt already saved.');
            }
    
            // Save the new receipt image to the 'onlinereceipts' folder
            $image->move(public_path('onlinereceipts'), $hashedImageName);
    
            // Insert new record into the gcash_payment table
            \DB::table('gcash_payment')->insert([
                'order_id' => $orderId,
                'image' => $hashedImageName,
                'status' => 'Completed', // Assuming status is set to Completed initially
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    
        // If switching to PNB and receipt is uploaded
        if ($newMethod === 'PNB' && $request->hasFile('receipt_image')) {
            $image = $request->file('receipt_image');
            $imageName = $image->getClientOriginalName();  // Get original filename
            $imageHash = md5(file_get_contents($image));  // Hash the file content to ensure uniqueness
            $hashedImageName = $imageHash . '.' . $image->getClientOriginalExtension(); // Use hash in filename
    
            $imagePath = public_path('onlinereceipts/' . $hashedImageName);
    
            // Check if the same image (hashed) already exists in the pnb_payment table for this order_id
            $existingReceipt = \DB::table('pnb_payment')
                ->where('order_id', $orderId)
                ->where('image', $hashedImageName)
                ->first();
    
            if ($existingReceipt) {
                // If receipt already exists, return a message to prevent duplication
                return redirect()->back()->with('error', 'PNB receipt already saved.');
            }
    
            // Save the new receipt image to the 'onlinereceipts' folder
            $image->move(public_path('onlinereceipts'), $hashedImageName);
    
            // Insert new record into the pnb_payment table
            \DB::table('pnb_payment')->insert([
                'order_id' => $orderId,
                'image' => $hashedImageName,
                'status' => 'Completed', // Assuming status is set to Completed initially
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    
        // Update the refund_order refund_method to the new method (either Cash, GCash, or PNB)
        \DB::table('refund_order')
            ->where('order_id', $orderId)
            ->update([
                'refund_method' => $newMethod,
                'updated_at' => now(),
            ]);
    
        return redirect()->back()->with('success', 'Refund method updated successfully!');
    }
    

    public function fetchPayment($orderId)
    {
        // Fetch the GcashPayment record where order_id matches the passed orderId
        $payment = GcashPayment::where('order_id', $orderId)->first();

        // Return the payment details as a JSON response
        if ($payment) {
            return response()->json([
                'status' => $payment->status,
                'image' => $payment->image,
            ]);
        } else {
            return response()->json([
                'message' => 'No payment record found',
            ], 404);
        }
    }

    public function saveGcashPaymentNOW(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
            'image' => 'required|file|mimes:jpeg,png,jpg,webp|max:5120', // Max 5MB
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time().'_'.$file->getClientOriginalName();
            $path = public_path('onlinereceipts/');

            // Move file
            $file->move($path, $filename);

            // Save to database
            GcashPayment::create([
                'order_id' => $request->order_id,
                'image' => $filename,
                // status will automatically be "Cancelled" by default
            ]);

            return response()->json(['message' => 'Payment saved successfully'], 200);
        }

        return response()->json(['message' => 'No image uploaded'], 400);
    }

    public function savePnbPaymentNOW(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
            'image' => 'required|file|mimes:jpeg,png,jpg,webp|max:5120', // Max 5MB
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time().'_'.$file->getClientOriginalName();
            $path = public_path('onlinereceipts');

            // Move file
            $file->move($path, $filename);

            // Save to database for PNB
            PnbPayment::create([
                'order_id' => $request->order_id,
                'image' => $filename,
                // status will automatically be "Cancelled" by default
            ]);

            return response()->json(['message' => 'Payment saved successfully'], 200);
        }

        return response()->json(['message' => 'No image uploaded'], 400);
    }


}
