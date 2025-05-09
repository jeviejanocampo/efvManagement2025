<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login</title>
    <!-- Import Lato Font from Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Lato', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white  rounded-lg w-full max-w-3xl flex rounded-xl">
        
        <!-- Left Side -->
        <div class="w-1/2 bg-black text-white flex flex-col justify-center items-center rounded-xl overflow-hidden">
            <img src="{{ asset('product-images/loginstockclerk.png') }}" alt="EFV Logo" class="w-full h-64 object-cover rounded-t-xl">
            <h1 class="text-1xl font-semibold mt-4">EFV Auto Parts Management System</h1>
            <h1 class="text-2xl font-semibold">Welcome Back!</h1>
            <p class="mb-4 text-gray-300">Enter your credentials to access your account.</p>
            <a href="#" class="text-sm text-gray-300 hover:text-white">Forgot Password?</a>
        </div>

        <!-- Right Side -->
        <div class="w-1/2 p-8">
            <h2 class="text-3xl font-bold mb-6">Staff Login</h2>
            <form action="{{ route('staff.login.submit') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 mb-2">Email</label>
                    <input type="email" id="email" name="email" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-300" placeholder="Enter your email" required>
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-gray-700 mb-2">Password</label>
                    <input type="password" id="password" name="password" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-300" placeholder="Enter your password" required>
                </div>
                <div class="flex justify-between items-center mb-4">
                    <a href="#" class="text-sm text-gray-500 hover:text-gray-700">Forgot Password?</a>
                </div>
                <button type="submit" class="w-full bg-gray-900 text-white py-2 px-4 rounded-lg hover:bg-gray-700">Login</button>
            </form>

            <!-- Browser Alerts -->
            @if(session('success'))
                <script>
                    alert('{{ session('success') }}');
                </script>
            @elseif(session('error'))
                <script>
                    alert('{{ session('error') }}');
                </script>
            @endif
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">Don't have an account? <a href="/staff/signup" class="text-gray-900 font-semibold hover:underline">Create Account</a></p>
            </div>
        </div>
    </div>
</body>
</html>
