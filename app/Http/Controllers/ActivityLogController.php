<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
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

}
