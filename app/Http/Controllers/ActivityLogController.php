<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index()
    {
        // Fetch all activity logs
        $activityLogs = ActivityLog::all(); // You can apply pagination if needed

        return view('staff.content.activityLogs', compact('activityLogs'));
    }
}
