<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\OrderDetail;
use Carbon\Carbon;
use App\Models\Order;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
        public function index()
    {
        // Fetch all activity logs in descending order by created_at
        $activityLogs = ActivityLog::orderBy('created_at', 'desc')->get(); 

        return view('staff.content.activityLogs', compact('activityLogs'));
    }

    public function Stockindex()
    {
        // Fetch all activity logs in descending order by created_at
        $activityLogs = ActivityLog::orderBy('created_at', 'desc')->get(); 

        return view('stockclerk.content.StockClerkActivityLogs', compact('activityLogs'));
    }

    public function ManagerStockindex()
    {
        // Fetch all activity logs in descending order by created_at
        $activityLogs = ActivityLog::orderBy('created_at', 'desc')->get(); 

        return view('manager.content.ManagerStockClerkActivityLogs', compact('activityLogs'));
    }

    public function SalesReportIndex()
    {
        // Fetch all activity logs in descending order by created_at
        $activityLogs = ActivityLog::orderBy('created_at', 'desc')->get();
    
        // Fetch data from the order_details table where product_status is 'Completed'
        $salesData = OrderDetail::where('product_status', 'Completed')
            ->select('product_name', 'quantity', 'price')
            ->get()
            ->groupBy('product_name')
            ->map(function ($groupedDetails) {
                // Compute total quantity and sales for each product
                return [
                    'product_name' => $groupedDetails->first()->product_name,
                    'quantity' => $groupedDetails->sum('quantity'),
                    'sales' => $groupedDetails->sum(function ($detail) {
                        return $detail->quantity * $detail->price;
                    }),
                ];
            });
    
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

        // Pass the data to the view
        return view('manager.content.SalesReport', compact(
            'activityLogs',
            'salesData',
            'orderStatuses',
            'totalSales',
            'percentagePerWeek',
            'months'
        ));
        }
}
