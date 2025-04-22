<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;  // Make sure the User model is imported
use Illuminate\Support\Facades\Log;  // Import for logging

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

        





}
