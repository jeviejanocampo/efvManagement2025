<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\OrderController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/staff/login', function () {
    return view('staff.staffLogin');
});

Route::get('/staff/signup', function () {
    return view('staff.staffSignup');
});


Route::get('/dashboard', function () {
    return view('staff.content.staffDashboard');
})->name('dashboardView');

Route::get('/staff/orders', function () {
    return view('staff.content.staffOrders');
})->name('ordersView');

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