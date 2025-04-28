<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Session;

Route::post('/update-scan-status', [StaffController::class, 'updateScanStatus']);


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
});

Route::post('/admin/save-order', [AdminController::class, 'CustomerSaveOrder']);

Route::post('/save-gcash-payment', [AdminController::class, 'saveGcashPaymentNOW']);

Route::post('/save-pnb-payment', [AdminController::class, 'savePnbPaymentNOW']);
