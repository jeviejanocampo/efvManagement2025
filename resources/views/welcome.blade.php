<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="text-center">
        <h1 class="text-4xl font-bold mb-10 text-gray-800">EFV Auto Parts Management System</h1>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 max-w-5xl mx-auto">
            <div class="bg-white p-6  shadow-lg">
                <h2 class="text-2xl font-semibold mb-4">Admin Portal</h2>
                <a href="{{ url('/admin/login') }}" class="inline-block mt-4 px-6 py-2 bg-blue-800 text-white rounded-lg hover:bg-blue-700 transition">Click Here</a>
            </div>
            <div class="bg-white p-6  shadow-lg">
                <h2 class="text-2xl font-semibold mb-4">Manager Portal</h2>
                <a href="{{ url('/manager/login') }}" class="inline-block mt-4 px-6 py-2 bg-red-800 text-white rounded-lg hover:bg-blue-700 transition">Click Here</a>
            </div>
            <div class="bg-white p-6  shadow-lg">
                <h2 class="text-2xl font-semibold mb-4">Staff Portal</h2>
                <a href="{{ url('/staff/login-view') }}" class="inline-block mt-4 px-6 py-2 bg-green-800 text-white rounded-lg hover:bg-green-700 transition">Click Here</a>
            </div>
            <div class="bg-white p-6  shadow-lg">
                <h2 class="text-2xl font-semibold mb-4">Stock Clerk Portal</h2>
                <a href="{{ url('/stock-clerk/login') }}" class="inline-block mt-4 px-6 py-2 bg-purple-800 text-white rounded-lg hover:bg-purple-700 transition">Click Here</a>
            </div>
        </div>
    </div>
</body>
</html>
