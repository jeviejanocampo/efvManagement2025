<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\OrderDetail;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Exports\SalesReportExport;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;

class ActivityLogController extends Controller
{   
    public function index()
    {
        // Fetch activity logs with pagination (20 per page)
        $activityLogs = ActivityLog::orderBy('created_at', 'desc')->paginate(30);

        return view('staff.content.activityLogs', compact('activityLogs'));
    }


    public function confirmUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        // Update status to 'active'
        $user->status = 'active';
        if ($user->save()) {
            return response()->json(['success' => true, 'message' => 'User confirmed successfully.']);
        } else {
            return response()->json(['success' => false, 'message' => 'Failed to update user status.'], 500);
        }
    }

    public function updateUserStatus($id, Request $request)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        // Update user status based on request
        $newStatus = $request->status;
        if (!in_array($newStatus, ['active', 'inactive'])) {
            return response()->json(['success' => false, 'message' => 'Invalid status.'], 400);
        }

        $user->status = $newStatus;
        if ($user->save()) {
            return response()->json(['success' => true, 'message' => "User status updated to $newStatus."]);
        } else {
            return response()->json(['success' => false, 'message' => 'Failed to update user status.'], 500);
        }
    }


    public function Stockindex()
    {
        // Fetch activity logs with pagination (10 per page)
        $activityLogs = ActivityLog::orderBy('created_at', 'desc')->paginate(30); 

        return view('stockclerk.content.StockClerkActivityLogs', compact('activityLogs'));
    }


    public function ManagerStockindex()
    {
        // Fetch all activity logs in descending order by created_at
        $activityLogs = ActivityLog::orderBy('created_at', 'desc')->get(); 

        return view('manager.content.ManagerStockClerkActivityLogs', compact('activityLogs'));
    }

    public function AdminStockindex()
    {
        // Fetch activity logs with pagination (16 per page)
        $activityLogs = ActivityLog::orderBy('created_at', 'desc')->paginate(16); 
    
        return view('admin.content.AdminStockLogs', compact('activityLogs'));
    }
    

    public function UserManagement()
    {
        // Fetch all users in descending order by created_at
        $users = User::orderBy('created_at', 'desc')->get();

        return view('admin.content.AdminUserManagement', compact('users'));
    }


    public function SalesReportIndex(Request $request)
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
        return view('manager.content.SalesReport', compact(
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
        $perPage = 5; // Items per page
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

    public function GenerateIndex(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $month = $request->input('month');
        $year = $request->input('year');

        $query = OrderDetail::where('product_status', 'Completed');

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        if ($month) {
            $query->whereMonth('created_at', $month);
        }

        if ($year) {
            $query->whereYear('created_at', $year);
        }

        // Fetch order details with product relationship
        $orderDetails = $query->with('product')->get();

        // Modify each order detail to include formatted reference_id
        foreach ($orderDetails as $detail) {
            // Extract first 3 letters of brand_name (if available)
            $brand = isset($detail->brand_name) ? strtoupper(substr($detail->brand_name, 0, 3)) : 'N/A';

            // Get part_id
            $partId = $detail->part_id ?? 'N/A';

            // Get order_detail_id
            $orderDetailId = $detail->order_detail_id;

            // Construct formatted reference_id
            $detail->reference_id = "{$brand}-{$partId}{$orderDetailId}";
        }

        // Calculate totals
        $salesAmount = $orderDetails->sum('total_price');
        $salesTotal = $orderDetails->count();

        return view('manager.content.ManagerGenerateReport', compact('orderDetails', 'salesAmount', 'salesTotal'));
    }

    public function AdminGenerateIndex(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $month = $request->input('month');
        $year = $request->input('year');

        $query = OrderDetail::where('product_status', 'Completed');

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        if ($month) {
            $query->whereMonth('created_at', $month);
        }

        if ($year) {
            $query->whereYear('created_at', $year);
        }

        // Fetch order details with product relationship
        $orderDetails = $query->with('product')->get();

        // Modify each order detail to include formatted reference_id
        foreach ($orderDetails as $detail) {
            // Extract first 3 letters of brand_name (if available)
            $brand = isset($detail->brand_name) ? strtoupper(substr($detail->brand_name, 0, 3)) : 'N/A';

            // Get part_id
            $partId = $detail->part_id ?? 'N/A';

            // Get order_detail_id
            $orderDetailId = $detail->order_detail_id;

            // Construct formatted reference_id
            $detail->reference_id = "{$brand}-{$partId}{$orderDetailId}";
        }

        // Calculate totals
        $salesAmount = $orderDetails->sum('total_price');
        $salesTotal = $orderDetails->count();

        return view('admin.content.AdminGenerateReport', compact('orderDetails', 'salesAmount', 'salesTotal'));
    }
    
    public function exportSalesReport(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        return Excel::download(
            new SalesReportExport($startDate, $endDate), 
            'sales_report.xlsx'
        );
    }

    public function AdminexportSalesReport(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        return Excel::download(
            new SalesReportExport($startDate, $endDate), 
            'sales_report.xlsx'
        );
    }

    

}
