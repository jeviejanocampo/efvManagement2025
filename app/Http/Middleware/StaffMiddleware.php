<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated and is a staff member
        if (Auth::check() && Auth::user()->role === 'staff') {
            return $next($request);
        }

        // If not authorized, redirect to login with error message
        return redirect()->route('staff.login')->with('error', 'Unauthorized access!');
    }
}
