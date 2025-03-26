<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockClerkMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated and is a manager
        if (Auth::check() && Auth::user()->role === 'stock clerk') {
            return $next($request);
        }

        // If not authorized, redirect to login with error message
        return redirect()->route('stockclerk.login')->with('error', 'Unauthorized access!');
    }
}
