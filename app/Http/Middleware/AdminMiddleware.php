<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated and is a manager
        if (Auth::check() && Auth::user()->role === 'admin') {
            return $next($request);
        }

        // If not authorized, redirect to login with error message
        return redirect()->route('admin.login.view')->with('error', 'Unauthorized access!');
    }
}
