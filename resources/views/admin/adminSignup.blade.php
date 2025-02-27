<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Signup</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="text-white flex items-center justify-center min-h-screen">

    <!-- Alert Section -->
    @if(session('success'))
        <script>
            alert('{{ session('success') }}');
        </script>
    @elseif(session('error'))
        <script>
            alert('{{ session('error') }}');
        </script>
    @endif

    <div class="w-full max-w-md bg-gray-800 p-8 rounded-lg shadow-lg">
        <h1 class="text-2xl font-bold mb-6 text-center">Admin Signup</h1>
        <form action="{{ route('admin.signup.submit') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium">Name</label>
                <input type="text" id="name" name="name" class="w-full px-4 py-2 bg-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500" placeholder="Enter your name" required>
            </div>
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium">Email</label>
                <input type="email" id="email" name="email" class="w-full px-4 py-2 bg-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500" placeholder="Enter your email" required>
            </div>
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium">Password</label>
                <input type="password" id="password" name="password" class="w-full px-4 py-2 bg-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500" placeholder="Enter your password" required>
            </div>
            <div class="mb-4">
                <label for="role" class="block text-sm font-medium">Role</label>
                <select id="role" name="role" class="w-full px-4 py-2 bg-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500" required>
                    <option value="" disabled selected>Select a role</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" class="w-full bg-gray-600 hover:bg-gray-700 py-2 px-4 rounded-lg font-medium">Sign Up</button>
            <div class="mt-4 text-center">
                <a href="/manager/login" class="text-sm text-gray-400 hover:text-white underline">
                    Back to Login
                </a>
            </div>
        </form>
    </div>

</body>
</html>
