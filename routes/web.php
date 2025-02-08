<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ActivityLogController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/staff/login', function () {
    return view('staff.staffLogin');
});

Route::get('/staff/signup', function () {
    return view('staff.staffSignup');
});

Route::get('/staff/queue', function () {
    return view('staff.content.staffOrders');
})->name('staffQueue');

Route::get('/dashboard', function () {
    return view('staff.content.staffDashboard');
})->name('dashboardView');



Route::get('/staff/overview', [OrderController::class, 'staffOrderOverview'])->name('overView');


Route::get('/staff/signup', [StaffController::class, 'showSignupForm'])->name('staff.signup.form');

Route::post('/staff/signup', [StaffController::class, 'signup'])->name('staff.signup.submit');

Route::post('/staff/login', [StaffController::class, 'StaffLogin'])->name('staff.login.submit');

Route::get('/staff/dashboard', function () {
    return view('staff.dashboard.staffMain');
})->name('staff.dashboard');

Route::get('/orders/fetch', [OrderController::class, 'fetchOrders'])->name('orders.fetch');

// Make sure this route exists
Route::get('/staff/order-details/{order_id}', [OrderController::class, 'show'])->name('orders.show');



Route::get('/qrcode', [QRCodeController::class, 'showForm'])->name('qr.form');

Route::post('/generate-qr', [QRCodeController::class, 'generate'])->name('generate.qr');


// Route for staff order details (passing order_id)
Route::get('/staff/overview/details/{order_id}', [OrderController::class, 'details'])->name('overViewDetails');

Route::post('/orders/update-status/{order_id}', [OrderController::class, 'updateStatus']);


Route::post('/orders/update-product-status/{orderDetailId}', [OrderController::class, 'updateProductStatus']);


Route::get('/users/{user}', function ($userId) {
    $user = \App\Models\Customer::find($userId);
    return response()->json($user);
});

Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('logs');


Route::post('/scanner-login', [StaffController::class, 'Scannerlogin']);


Route::get('/csrf-token', function (Request $request) {
    return response()->json(['csrf_token' => csrf_token()]);
});


Route::post('/scan-qr', [StaffController::class, 'updateScanStatus']);

