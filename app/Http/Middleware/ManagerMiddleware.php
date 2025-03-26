<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManagerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated and is a manager
        if (Auth::check() && Auth::user()->role === 'manager') {
            return $next($request);
        }

        // If not authorized, redirect to login with error message
        return redirect()->route('manager.login')->with('error', 'Unauthorized access!');
    }
}
