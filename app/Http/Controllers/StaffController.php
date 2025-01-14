<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class StaffController extends Controller
{
    
    public function showSignupForm()
    {
        $roles = ['administrator', 'staff', 'manager', 'stock clerk']; // Define valid roles
        return view('staff.staffSignup', compact('roles')); // Pass roles to the view
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

    public function StaffLogin(Request $request)
    {
        // Validate the login form input
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        // Attempt to log the user in
        $user = User::where('email', $credentials['email'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            if ($user->role === 'staff') {
                // Store user in session
                Auth::login($user);

                // Redirect to dashboard with success message
                return redirect()->route('staff.dashboard')->with('success', 'Successfully logged in!');
            } else {
                // Role mismatch error
                return back()->with('error', 'Access denied! Only staff can log in.');
            }
        } else {
            // Redirect back with an error message
            return back()->with('error', 'Incorrect email or password!');
        }
    }

}
