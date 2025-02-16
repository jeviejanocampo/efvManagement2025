<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\SalesController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/staff/login', function () {
    return view('staff.staffLogin');
});

Route::get('/stock-clerk/login', function () {
    return view('stockclerk.stockClerkLogin');
});

Route::get('/manager/login', function () {
    return view('manager.managerLogin');
});


Route::get('/staff/signup', function () {
    return view('staff.staffSignup');
});

Route::get('/stock-clerk/signup', function () {
    return view('stockclerk.stockClerkSignup');
});

Route::get('/manager/signup', function () {
    return view('manager.managerSignup');
});

Route::get('/stockclerk/signup', [StaffController::class, 'showStockSignupForm'])->name('staff.signup.form');

Route::get('/staff/queue', function () {
    return view('staff.content.staffOrders');
})->name('staffQueue');

Route::get('/dashboard', function () {
    return view('staff.content.staffDashboard');
})->name('dashboardView');

Route::get('/stock/products-view', function () {
    return view('stockclerk.content.ProductsView');
})->name('dashboardView');


Route::get('/staff/overview', [OrderController::class, 'staffOrderOverview'])->name('overView');


Route::get('/staff/signup', [StaffController::class, 'showSignupForm'])->name('staff.signup.form');

Route::post('/staff/signup', [StaffController::class, 'signup'])->name('staff.signup.submit');

Route::post('/stock-clerk/signup', [StaffController::class, 'Clerksignup'])->name('stock-clerk.signup.submit');

Route::get('/stock-/signup', [StaffController::class, 'showStockSignupForm'])->name('stockclerk.signup.form');

Route::post('/staff/login', [StaffController::class, 'StaffLogin'])->name('staff.login.submit');

Route::post('/stock-clerk/login', [StaffController::class, 'StockClerkLogin'])->name('stockclerk.login.submit');

Route::post('/manager/login', [StaffController::class, 'ManagerLogin'])->name('manager.login.submit');


Route::get('/stock-clerk/dashboard', function () {
    return view('stockclerk.dashboard.stockClerkDashboard');
})->name('stockclerk.dashboard');

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

Route::get('/stockclerk/overview/details/{order_id}', [OrderController::class, 'stockDetails'])->name('stockclerkoverViewDetails');

Route::get('/manager/overview/details/{order_id}', [OrderController::class, 'ManagerstockDetails'])->name('manageroverViewDetails');


Route::get('/stockclerk/overview', [OrderController::class, 'stockOrderOverview'])->name('stockoverView');

Route::get('/manager/overview', [OrderController::class, 'ManagerstockOrderOverview'])->name('ManagerstockoverView');


Route::get('/manager-low-units', [ProductController::class, 'lowUnitsProducts'])->name('managerLow');

Route::get('/stockclerk-low-units', [ProductController::class, 'StockClerklowUnitsProducts'])->name('stockclerkLow');

Route::post('/orders/update-status/{order_id}', [OrderController::class, 'updateStatus']);


Route::post('/orders/update-product-status/{orderDetailId}', [OrderController::class, 'updateProductStatus']);


Route::get('/users/{user}', function ($userId) {
    $user = \App\Models\Customer::find($userId);
    return response()->json($user);
});

Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('logs');

Route::get('/stock-activity-logs', [ActivityLogController::class, 'Stockindex'])->name('Stocklogs');

Route::get('/manager-salesreport', [ActivityLogController::class, 'SalesReportIndex'])->name('manager.salesreport');

Route::get('/manager-stock-activity-logs', [ActivityLogController::class, 'ManagerStockindex'])->name('manager.Stocklogs');

Route::post('/scanner-login', [StaffController::class, 'Scannerlogin']);

Route::get('/csrf-token', function (Request $request) {
    return response()->json(['csrf_token' => csrf_token()]);
});

Route::post('/scan-qr', [StaffController::class, 'updateScanStatus']);


Route::get('/products', [ProductController::class, 'index'])->name('productsView');


Route::get('/manager-products', [ProductController::class, 'Managerindex'])->name('ManagerproductsView');

Route::get('/manager-low-products', [ProductController::class, 'ManagerLowIndex'])->name('ManagerLowProducts');


Route::get('/manager-view', [ProductController::class, 'Managerindex'])->name('managerproductsView');


Route::get('/add-product', [ProductController::class, 'create'])->name('add.product');

Route::get('/manager-add-product', [ProductController::class, 'Managercreate'])->name('manager.add.product');


Route::get('/add-details-product/{model_id}', [ProductController::class, 'addDetails'])->name('addDetails');

Route::get('/manager-add-details-product/{model_id}', [ProductController::class, 'ManageraddDetails'])->name('manager.addDetails');



Route::post('/products/store', [ProductController::class, 'store'])->name('products.store');

Route::post('/manager/products/store', [ProductController::class, 'Managerstore'])->name('manager.products.store');


Route::delete('/product/delete/{id}', [ProductController::class, 'destroyModel'])->name('delete.product');

Route::post('/add-details-product/store', [ProductController::class, 'addProductDetails'])->name('add.details.store');

Route::post('/add-details-stocks/store', [ProductController::class, 'ManageraddProductDetails'])->name('manager.add.details.store');


Route::get('/view-details/{model_id}', [ProductController::class, 'viewDetailsofProduct'])->name('viewDetails');

Route::get('/manager-view-details/{model_id}', [ProductController::class, 'ManagerviewDetailsofProduct'])->name('manager.viewDetails');


Route::post('/update-product/{model_id}', [ProductController::class, 'updateProduct'])->name('updateProduct');

Route::post('/manager-update-product/{model_id}', [ProductController::class, 'updateProduct'])->name('manager.updateProduct');


Route::post('/manager-update-product/{model_id}', [ProductController::class, 'ManagerupdateProduct'])->name('manager.updateProduct');


Route::get('/product/{model_id}/details', [ProductController::class, 'viewModelDetails'])->name('viewModelDetails');

Route::get('/manager-product/{model_id}/details', [ProductController::class, 'ManagerviewModelDetails'])->name('manager.viewModelDetails');

Route::put('/models/update/{model_id}', [ProductController::class, 'updateModel'])->name('updateModel');

Route::put('/manager-models/update/{model_id}', [ProductController::class, 'ManagerupdateModel'])->name('manager.updateModel');

Route::post('/update-model-status/{model_id}', [ProductController::class, 'updateStatus'])->name('update.model.status');

Route::get('/view-variants/{model_id}', [ProductController::class, 'indexVariant'])->name('variantsView');

Route::get('/manager-view-variants/{model_id}', [ProductController::class, 'ManagerindexVariant'])->name('manager.variantsView');

Route::get('/add-variant/{model_id}', [ProductController::class, 'IndexAddVariant'])->name('add.variant');

Route::get('/manageradd-variant/{model_id}', [ProductController::class, 'ManagerIndexAddVariant'])->name('manager.add.variant');

Route::post('/store-variant/{model_id}', [ProductController::class, 'StoreVariant'])->name('store.variant');

Route::post('/manager-store-variant/{model_id}', [ProductController::class, 'ManagerStoreVariant'])->name('manager.store.variant');

Route::get('/edit-variant/{model_id}/{variant_id}', [ProductController::class, 'editVariant'])->name('edit.variant');

Route::get('/manager-edit-variant/{model_id}/{variant_id}', [ProductController::class, 'ManagereditVariant'])->name('manager.edit.variant');

Route::post('/variant/delete/{id}', [ProductController::class, 'deleteVariant'])->name('delete.variant');

Route::delete('/variant/delete/{id}', [ProductController::class, 'ManagerdeleteVariant'])->name('manager.delete.variant');

Route::put('/update-variant/{model_id}/{variant_id}', [ProductController::class, 'updateVariant'])->name('update.variant');

Route::put('/update-variant-status/{variant_id}', [ProductController::class, 'updateVariantStatus']);

Route::get('/manager/generate-report', [ActivityLogController::class, 'GenerateIndex'])->name('manager.generateReport');

Route::get('/manager/export-sales-report', [ActivityLogController::class, 'exportSalesReport'])->name('manager.exportSalesReport');
