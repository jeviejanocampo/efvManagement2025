<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;


class StaffController extends Controller
{
    
    public function showSignupForm()
    {
        $roles = ['administrator', 'staff', 'manager', 'stock clerk']; // Define valid roles
        return view('staff.staffSignup', compact('roles')); // Pass roles to the view
    }

    public function AdminshowSignupForm()
    {
        $roles = ['administrator', 'staff', 'manager', 'stock clerk']; // Define valid roles
        return view('admin.adminSignup', compact('roles')); // Pass roles to the view
    }

    public function showStockSignupForm()
    {
        $roles = ['administrator', 'staff', 'manager', 'stock clerk']; // Define valid roles
        return view('stockclerk.stockClerkSignup', compact('roles')); // Pass roles to the view
    }

    public function signup(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:administrator,staff,manager,stock clerk', // Validate selected role
        ]);

        // Create a new user
        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()->route('staff.signup.form')->with('success', 'Account created successfully!');
    }

    public function AdminSignup(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:admin,staff,manager,stock clerk', 
        ]);

        // Create a new user
        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()->route('admin.signup.form')->with('success', 'Account created successfully!');
    }

    public function Clerksignup(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:stock clerk',
        ]);

        // Create a new user
        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()->route('stockclerk.signup.form')->with('success', 'Account created successfully!');
    }

    public function StaffLogin(Request $request)
    {
        $credentials = $request->validate([
            'name' => 'required|string',
            'password' => 'required|string|min:8',
        ]);
        $user = User::where('name', $credentials['name'])->first();
        if ($user && Hash::check($credentials['password'], $user->password)) {
            if ($user->role === 'staff') {
                Auth::login($user);
                session(['user_id' => $user->id]);
                return redirect()->route('overView')->with('success', 'Successfully logged in!');
            } else {
                // Log failed login due to role mismatch
                ActivityLog::create([
                    'user_id' => $user->id,
                    'activity' => 'Failed login: Unauthorized role access attempt.',
                    'role' => $user->role,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
    
                return back()->with('error', 'Access denied! Only staff can log in.');
            }
        } else {
            // If email is found but password is incorrect or email not found
            $failedUser = User::where('email', $credentials['email'])->first();
    
            ActivityLog::create([
                'user_id' => $failedUser?->id,
                'activity' => 'Failed login attempt via email/password.',
                'role' => $failedUser?->role ?? 'guest',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return back()->with('error', 'Incorrect email or password!');
        }
    }

    public function StockClerkLogin(Request $request)
    {
        // Validate the login form input
        $credentials = $request->validate([
            'name' => 'required|string',
            'password' => 'required|string|min:8',
        ]);

        // Attempt to log the user in
        $user = User::where('name', $credentials['name'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            if ($user->role === 'stock clerk') {
                // Store user in session
                Auth::login($user);

                // Optionally, you can pass the user ID to the session
                session(['user_id' => $user->id]);

                // Redirect to dashboard with success message
                return redirect()->route('productsView')->with('success', 'Successfully logged in!');
            } else {
                // Role mismatch error
                return back()->with('error', 'Access denied! Only Stock clerks can log in.');
            }
        } else {
            // Redirect back with an error message
            return back()->with('error', 'Incorrect email or password!');
        }
    }

    public function ManagerLogin(Request $request)
    {
        // Validate the login form input
        $credentials = $request->validate([
            'name' => 'required|string',
            'password' => 'required|string|min:8',
        ]);

        // Attempt to log the user in
        $user = User::where('name', $credentials['name'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            if ($user->role === 'manager') {
                // Store user in session
                Auth::login($user);

                // Optionally, you can pass the user ID to the session
                session(['user_id' => $user->id]);

                // Redirect to dashboard with success message
                return redirect()->route('managerproductsView')->with('success', 'Successfully logged in!');
            } else {
                // Role mismatch error
                return back()->with('error', 'Access denied! Only Stock clerks can log in.');
            }
        } else {
            // Redirect back with an error message
            return back()->with('error', 'Incorrect email or password!');
        }
    }

    public function AdminLogin(Request $request)
    {
        // Validate the login form input
        $credentials = $request->validate([
            'name' => 'required|string',
            'password' => 'required|string|min:8',
        ]);

        // Attempt to log the user in
        $user = User::where('name', $credentials['name'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            if ($user->role === 'admin') {
                // Store user in session
                Auth::login($user);

                // Optionally, you can pass the user ID to the session
                session(['user_id' => $user->id]);

                // Redirect to dashboard with success message
                return redirect()->route('admin.salesreport')->with('success', 'Successfully logged in!');
            } else {
                // Role mismatch error
                return back()->with('error', 'Access denied! Only Stock clerks can log in.');
            }
        } else {
            // Redirect back with an error message
            return back()->with('error', 'Incorrect email or password!');
        }
    }

    public function Scannerlogin(Request $request)
    {
        // Validate the incoming data
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Attempt to find the user by email
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // If authentication is successful, you can return user data or a token if using API authentication
        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
        ]);
    }


    public function updateScanStatus(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,order_id',
        ]);
    
        // Update scan_status to "yes"
        Order::whereIn('order_id', $request->order_ids)
            ->update(['scan_status' => 'yes', 'updated_at' => now()]);
    
        return response()->json(['message' => 'Scan statuses updated successfully!'], 200);
    }
    
    public function getOrdersSummary()
    {
        $today = Carbon::today();

        // Count orders where status is 'Pending' or 'pending'
        $pendingOrders = Order::whereIn('status', ['Pending', 'pending'])->count();

        // Count orders where scan_status is 'yes' (On Queue)
        $onQueueOrders = Order::where('scan_status', 'yes')->count();

        // Count orders where status is 'In Process'
        $inProcessOrders = Order::where('status', 'In Process')->count();

        // Calculate total sales for today
        $totalSalesToday = Order::whereDate('created_at', $today)->sum('total_price');

        // Fetch recent pending orders (only today's)
        $recentPendingOrders = Order::whereIn('status', ['Pending', 'pending'])
            ->whereDate('created_at', $today)
            ->with('customer') // Assuming 'customer' is related via user_id
            ->latest()
            ->get();

        return response()->json([
            'pending_orders' => $pendingOrders,
            'on_queue_orders' => $onQueueOrders,
            'in_process_orders' => $inProcessOrders,
            'total_sales_today' => $totalSalesToday,
            'recent_pending_orders' => $recentPendingOrders,
        ]);
    }


}
