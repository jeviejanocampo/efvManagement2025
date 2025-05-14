<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\RefundOrderController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Variant;
use App\Http\Controllers\StaffPOSController;


Route::get('/csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
});

Route::get('/fetch/payment/{orderId}', [AdminController::class, 'fetchPayment']);




Route::get('/', function () {
    return view('welcome');
});

Route::get('/csrf-token', function (Request $request) {
    return response()->json(['csrf_token' => csrf_token()]);
});

Route::get('/staff/login-view', function () {
    return view('staff.staffLogin');
})->name('staff.login');


Route::get('/admin/login', function () {
    return view('admin.adminLogin');
})->name('admin.login.view');


//Staff
Route::middleware(['staff'])->group(function () {

    Route::get('/staff/queue', function () {
        return view('staff.content.staffOrders');
    })->name('staffQueue');

    Route::get('/staff/pos-view', [StaffPOSController::class, 'index'])->name('staffPOS.view');
    
    Route::get('/staff/get-models-by-brand/{brand_id}', [StaffPOSController::class, 'getModelsByBrand']);

    Route::get('/staff/get-brand-models/{brand_id}', [StaffPOSController::class, 'getBrandModels'])->name('staffPOS.getModels');

    Route::get('/staff/refund-requests', [OrderController::class, 'refundRequests'])->name('staff.refundRequests');

    Route::get('/staff/refund-request/{order_id}', [OrderController::class, 'StaffshowRefundRequestForm'])
    ->name('staff.refundRequestForm');

    Route::get('/staff/order-details/{order_id}', [OrderController::class, 'show'])->name('orders.show');

    Route::get('/staff/overview', [OrderController::class, 'staffOrderOverview'])->name('overView');

    Route::get('/staff/overview/details/{order_id}', [OrderController::class, 'details'])->name('overViewDetails');

    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('logs');
    
    Route::get('/qrcode', [QRCodeController::class, 'showForm'])->name('qr.form');

    Route::get('/orders/fetch', [OrderController::class, 'fetchOrders'])->name('orders.fetch');
    
    Route::get('/request-replacement-form', [RefundOrderController::class, 'createForm'])->name('request.replacement.form');

    Route::get('/order/{order_id}/edit-details', [RefundOrderController::class, 'editDetails'])->name('edit.product');

    Route::get('/order-queue/{order_id}/edit-details', [RefundOrderController::class, 'editDetailsQueue'])->name('edit.product.queue');

    Route::get('/staff/refund-log', [RefundOrderController::class, 'viewRefundLog'])->name('staff.refund.log');

    Route::get('/refund-report-view', [RefundOrderController::class, 'RefundViewList'])->name('refund.report.view');

    Route::get('/refund-report-details-view/{order_id}/{reference_id?}', [RefundOrderController::class, 'RefundDetailsView'])->name('refund.view.details');

    Route::get('/staff/payment-image-uploading/{order_id}/{payment_method}', [OrderController::class, 'StaffgetPaymentImage']);

    Route::get('/staff/payment-image/{order_id}/{payment_method}', [RefundOrderController::class, 'StaffgetPaymentImage']);
    

});

Route::post('/staff-customers/store', [StaffPOSController::class, 'StaffCustomerStore'])->name('staff.customers.store.new');

Route::post('/staff/update-refund-method', [OrderController::class, 'StaffupdateRefundMethod'])->name('staff.updateRefundMethod');

Route::post('/save-order-pos', [StaffPOSController::class, 'saveOrderPOS'])->name('staff.saveOrderPOS');

Route::post('/save-gcash-image', [StaffPOSController::class, 'saveGCashImage']);

Route::post('/save-pnb-image', [StaffPOSController::class, 'savePNBImage']);

Route::post('/customers/store', [StaffPOSController::class, 'CustomerStore'])->name('customers.store.new');

Route::post('/update-order-details', [RefundOrderController::class, 'updateOrderDetails'])->name('update.order.details.preorder');

Route::post('/update-order-details-queue', [RefundOrderController::class, 'updateOrderDetailsQueue'])->name('update.order.details.queue');

Route::post('/update-refund', [OrderController::class, 'updateRefund']);

Route::post('/order/update-status-refunded', [OrderController::class, 'updateProductStatusRefunded'])->name('order.updateStatus.refunded');

Route::post('/staff/refund-request/update-status/{order_id}', [OrderController::class, 'updateRefundStatusOverall'])
    ->name('staff.updateRefundStatus');

Route::post('/refund/store', [RefundOrderController::class, 'store'])->name('refund.store');


Route::get('/staff/main/dashboard', function () {
    return view('staff.content.staffDashboard');
})->name('staff.dashboard.page');

Route::get('/staff/dashboard/orders-summary', [StaffController::class, 'getOrdersSummary']);



Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate(); 
    $request->session()->regenerateToken(); 

    return redirect()->route('staff.login')->with('success', 'Logged out successfully!');
})->name('staff.logout');

Route::post('/admin-logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate(); 
    $request->session()->regenerateToken(); 

    return redirect()->route('admin.login.view')->with('success', 'Logged out successfully!');
})->name('admin.logout');


Route::post('/manager-logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate(); 
    $request->session()->regenerateToken(); 

    return redirect()->route('manager.login')->with('success', 'Logged out successfully!');
})->name('manager.logout');

Route::post('/stock-clerk-logout', function (Request $request) {
    
    Auth::logout();
    $request->session()->invalidate(); 
    $request->session()->regenerateToken(); 

    return redirect()->route('stockclerk.login')->with('success', 'Logged out successfully!');
})->name('stockclerk.logout');


Route::get('/staff/signup', function () {
    return view('staff.staffSignup');
});

Route::get('/dashboard', function () {
    return view('staff.content.staffDashboard');
})->name('dashboardView');

Route::get('/staff/signup', [StaffController::class, 'showSignupForm'])->name('staff.signup.form');

Route::post('/staff/signup', [StaffController::class, 'signup'])->name('staff.signup.submit');

Route::post('/staff/login', [StaffController::class, 'StaffLogin'])->name('staff.login.submit');

Route::get('/staff/dashboard', function () {
    return view('staff.dashboard.StaffMain');
})->name('staff.dashboard');


Route::post('/generate-qr', [QRCodeController::class, 'generate'])->name('generate.qr');

















//Admin
Route::middleware(['admin'])->group(function () {

    
    Route::get('/admin-salesreport', [AdminController::class, 'AdminSalesReportIndex'])->name('admin.salesreport');

    Route::get('/admin/export-sales-report', [ActivityLogController::class, 'AdminexportSalesReport'])->name('admin.exportSalesReport');

    Route::get('/admin/users', [AdminController::class, 'adminUsers'])->name('admin.users');

    Route::get('/admin/customers-view', [AdminController::class, 'adminCustomersView'])->name('admin.customers.view');

    Route::post('/admin/customers/update-status', [AdminController::class, 'updateCustomerStatus'])->name('admin.customer.updateStatus');

    Route::get('/admin/main/dashboard', function () {
        return view('admin.content.adminDashboardPage');
    })->name('admin.dashboard.page');

    Route::get('/admin/users/edit/{id}', [AdminController::class, 'adminEditUser'])->name('admin.users.edit');

    Route::get('/admin/users/create', [AdminController::class, 'createUser'])->name('admin.users.create');

    Route::get('/admin-view-side', [AdminController::class, 'AdminindexdDashboard'])->name('adminproductsView');

    Route::get('/admin-view-defective-products', [AdminController::class, 'AdminDefectiveindexdDashboard'])->name('admindefectiveproductsView');

    Route::get('/admin/generate-report', [ActivityLogController::class, 'AdminGenerateIndex'])->name('admin.generateReport');

    Route::get('/admin/overview', [AdminController::class, 'AdminOrderOverview'])->name('AdminoverView');

    Route::get('/admin/overview/details/{order_id}', [AdminController::class, 'Admindetails'])->name('AdminoverViewDetails');

    Route::get('/admin-order/{order_id}/edit-details', [AdminController::class, 'AdmineditDetails'])->name('admin.edit.product');

    Route::get('/admin/pos-view', [AdminController::class, 'AdminindexView'])->name('adminPOS.view');

    Route::get('/admin/get-brand-models/{brand_id}', [AdminController::class, 'AdmingetBrandModels'])->name('AdminPOS.getModels');

    Route::get('/admin-view', [AdminController::class, 'AdminProductsView'])->name('adminproductsView');

    Route::get('/admin-view-variants/{model_id}', [AdminController::class, 'AdminindexVariant'])->name('admin.variantsView');

    Route::get('/admin-add-variant/{model_id}', [AdminController::class, 'AdminIndexAddVariant'])->name('admin.add.variant');

    Route::get('/admin-edit-variant/{model_id}/{variant_id}', [AdminController::class, 'AdmineditVariant'])->name('admin.edit.variant');

    Route::get('/admin-view-details/{model_id}', [AdminController::class, 'AdminviewDetailsofProduct'])->name('admin.viewDetails');

    Route::get('/admin-add-details-product/{model_id}', [AdminController::class, 'AdminaddDetails'])->name('admin.addDetails');
    
    Route::get('/admin-product/{model_id}/details', [AdminController::class, 'AdminviewModelDetails'])->name('admin.viewModelDetails');

    Route::get('/admin-add-product', [AdminController::class, 'Admincreate'])->name('admin.add.product');

    Route::get('/admin-stock-view-brand', [AdminController::class, 'AdminViewBrandsList'])->name('admin.view.brands');

    Route::get('/admin-add-brand', [AdminController::class, 'AdminStockcreate'])->name('admin.add.brand');

    Route::get('/admin/edit-brand/{brand_id}', [AdminController::class, 'AdmineditBrand'])
    ->name('admin.edit.brand');

    Route::get('/admin-stock-view-category', [AdminController::class, 'AdminStockViewCategory'])->name('admin.view.category');

    Route::get('/admin-add-category', [AdminController::class, 'AdminAddCategory'])->name('admin.add.category');

    Route::get('/admin/edit-category/{category_id}', [AdminController::class, 'AdmineditCategory'])
    ->name('admin.edit.category');
    
    Route::get('/admin/refund-requests', [AdminController::class, 'AdminrefundRequests'])->name('admin.refundRequests');

    Route::get('/admin-request-replacement-form', [AdminController::class, 'AdmincreateForm'])->name('admin.request.replacement.form');

    Route::get('/admin/refund-request/{order_id}', [AdminController::class, 'AdminshowRefundRequestForm'])
    ->name('admin.refundRequestForm');

    Route::get('/admin-refund-report-view', [AdminController::class, 'AdminRefundViewList'])->name('admin.refund.report.view');

    Route::get('/admin-refund-report-details-view/{order_id}/{reference_id?}', 
    [AdminController::class, 'AdminRefundDetailsView'])->name('admin.refund.view.details');

    Route::get('/admin/refund-log', [AdminController::class, 'AdminviewRefundLog'])->name('admin.refund.log');
    
    Route::get('/admin/payment-image/{order_id}/{payment_method}', [AdminController::class, 'getPaymentImage']);
    


});

Route::post('/admin/update-payment-status', [AdminController::class, 'AdminupdatePaymentStatus'])->name('admin.updatePaymentStatus');

Route::post('/admin/save-gcash-payment', [AdminController::class, 'saveGcashPayment'])->name('admin.saveGcashPayment');

Route::post('/admin/save-pnb-payment', [AdminController::class, 'savePnbPayment'])->name('admin.savePnbPayment');

Route::post('/admin-refund/store', [AdminController::class, 'AdminstoreForm'])->name('admin.refund.store');

Route::post('/admin/update-refund-method', [AdminController::class, 'updateRefundMethod'])->name('admin.updateRefundMethod');

Route::post('/admin/refund-request/update-status/{order_id}', [AdminController::class, 'AdminupdateRefundStatusOverall'])
    ->name('admin.updateRefundStatus');

Route::post('/admin-order/update-status-refunded', [AdminController::class, 'AdminupdateProductStatusRefunded'])->name('admin.order.updateStatus.refunded');

Route::put('/admin-models/update/{model_id}', [AdminController::class, 'AdminupdateModel'])->name('admin.updateModel');

Route::post('/admin/products/store', [AdminController::class, 'Adminstore'])->name('admin.products.store');

Route::post('/admin-update-product/{model_id}', [AdminController::class, 'AdminupdateProduct'])->name('admin.updateProduct');

Route::post('/admin-add-details-stocks/store', [AdminController::class, 'AdminaddProductDetails'])->name('admin.add.details.store');

Route::post('/admin-add-brand/store', [AdminController::class, 'AdminstoreBrand'])->name('admin.add.brand.store');

Route::post('/admin/update-brand/{brand_id}', [AdminController::class, 'AdminupdateBrand'])
->name('admin.update.brand');

Route::post('/admin-store-category', [AdminController::class, 'AdminstoreCategory'])->name('admin.store.category');

Route::post('/admin/update-category/{category_id}', [AdminController::class, 'AdminupdateCategory'])
->name('admin.update.category');

Route::post('/admin-store-variant/{model_id}', [AdminController::class, 'AdminStoreVariant'])->name('admin.store.variant');

Route::put('/admin-update-variant/{model_id}/{variant_id}', [AdminController::class, 'AdminupdateVariant'])->name('admin.update.variant');

Route::post('/admin-variant/delete/{id}', [AdminController::class, 'AdmindeleteVariant'])->name('admin.delete.variant');

Route::post('/admin-update-order-details', [AdminController::class, 'AdminupdateOrderDetails'])->name('admin.update.order.details.preorder');

Route::post('/admin-orders/update-status/{order_id}', [AdminController::class, 'AdminupdateStatus']);

Route::post('/admin-customers/store', [AdminController::class, 'AdminCustomerStore'])->name('admin.customers.store.new');

Route::post('/admin-save-gcash-image', [AdminController::class, 'AdminsaveGCashImage']);

Route::post('/admin-save-pnb-image', [AdminController::class, 'AdminsavePNBImage']);

Route::post('/admin-save-order-pos', [AdminController::class, 'AdminsaveOrderPOS'])->name('admin.saveOrderPOS');

Route::put('/admin/users/update/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');

Route::post('/admin/add-users', [AdminController::class, 'storeUser'])->name('admin.users.store.user');

Route::get('/admin/signup', [StaffController::class, 'AdminshowSignupForm'])->name('admin.signup.form');

Route::post('/admin/signup', [StaffController::class, 'AdminSignup'])->name('admin.signup.submit');

Route::post('/admin/login', [StaffController::class, 'AdminLogin'])->name('admin.login.submit');


Route::post('/orders/update-status/{order_id}', [OrderController::class, 'updateStatus']);

Route::post('/orders/update-product-status/{orderDetailId}', [OrderController::class, 'updateProductStatus']);

Route::get('/users/{user}', function ($userId) {
    $user = \App\Models\Customer::find($userId);
    return response()->json($user);
});

Route::get('/admin-salesreport', [ActivityLogController::class, 'AdminSalesReportIndex'])->name('admin.salesreport');

Route::get('/admin-stock-activity-logs', [ActivityLogController::class, 'AdminStockindex'])->name('admin.Stocklogs');

Route::get('/admin/dashboard', function () {
    return view('admin.content.adminDashboardPage');
})->name('admin.dashboard');

Route::get('/admin-user-management', [ActivityLogController::class, 'UserManagement'])->name('admin.user.management');

Route::post('/users/confirm/{id}', [ActivityLogController::class, 'confirmUser'])->name('users.confirm');

Route::post('/users/update-status/{id}', [ActivityLogController::class, 'updateUserStatus'])->name('users.updateStatus');

Route::post('/scanner-login', [StaffController::class, 'Scannerlogin']);

Route::post('/scan-qr', [StaffController::class, 'updateScanStatus']);

Route::get('/add-details-product/{model_id}', [ProductController::class, 'addDetails'])->name('addDetails');

Route::post('/products/store', [ProductController::class, 'store'])->name('products.store');

Route::delete('/product/delete/{id}', [ProductController::class, 'destroyModel'])->name('delete.product');

Route::post('/add-details-product/store', [ProductController::class, 'addProductDetails'])->name('add.details.store');

Route::post('/update-product/{model_id}', [ProductController::class, 'updateProduct'])->name('updateProduct');

Route::get('/product/{model_id}/details', [ProductController::class, 'viewModelDetails'])->name('viewModelDetails');

Route::put('/models/update/{model_id}', [ProductController::class, 'updateModel'])->name('updateModel');

Route::post('/update-model-status/{model_id}', [ProductController::class, 'updateStatus'])->name('update.model.status');
















//Stock Clerk
Route::middleware(['stock-clerk'])->group(function () {

    Route::get('/stockclerk/overview/details/{order_id}', [OrderController::class, 'stockDetails'])->name('stockclerkoverViewDetails');

    Route::get('/stockclerk/overview', [OrderController::class, 'stockOrderOverview'])->name('stockoverView');

    Route::get('/stockclerk-low-units', [ProductController::class, 'StockClerklowUnitsProducts'])->name('stockclerkLow');

    Route::post('/stock-add-brand/store', [ProductController::class, 'storeBrand'])->name('stockclerk.add.brand.store');

    Route::get('/stock-view-brand', [ProductController::class, 'StockViewBrands'])->name('stockclerk.view.brands');

    Route::get('/stockclerk-stock-view-category', [ProductController::class, 'StockClerkStockViewCategory'])->name('stockclerk.view.category');

    Route::get('/stockclerk-add-quantity', [ProductController::class, 'ManagerAddQuantity'])->name('stockclerk.add.quantity');

    Route::get('/stockclerk/edit-brand/{brand_id}', [ProductController::class, 'StockClerkeditBrand'])
    ->name('stockclerk.edit.brand');

    Route::get('/products', [ProductController::class, 'index'])->name('productsView');

    Route::get('/stock-activity-logs', [ActivityLogController::class, 'Stockindex'])->name('Stocklogs');

    Route::get('/stockclerk-add-product', [ProductController::class, 'Managercreate'])->name('stockclerk.add.product');

    Route::get('/stock-add-brand', [ProductController::class, 'Stockcreate'])->name('stockclerk.add.brand');

    Route::get('/add-variant/{model_id}', [ProductController::class, 'IndexAddVariant'])->name('add.variant');

    Route::get('/view-details/{model_id}', [ProductController::class, 'viewDetailsofProduct'])->name('viewDetails');

});

Route::get('/view-variants/{model_id}', [ProductController::class, 'indexVariant'])->name('variantsView');

Route::get('/add-product', [ProductController::class, 'create'])->name('add.product');

Route::get('/stockclerk/signup', [StaffController::class, 'showStockSignupForm'])->name('staff.signup.form');

Route::get('/stock-clerk/signup', function () {
    return view('stockclerk.stockClerkSignup');
});

Route::get('/stock-clerk/login', function () {
    return view('stockclerk.stockClerkLogin');
})->name('stockclerk.login');


Route::get('/stock-/signup', [StaffController::class, 'showStockSignupForm'])->name('stockclerk.signup.form');

Route::get('/stock-clerk/dashboard', function () {
    return view('stockclerk.dashboard.stockClerkDashboard');
})->name('stockclerk.dashboard');

Route::get('/stock/products-view', function () {
    return view('stockclerk.content.ProductsView');
})->name('dashboardView');

Route::post('/stock-clerk/signup', [StaffController::class, 'Clerksignup'])->name('stock-clerk.signup.submit');

Route::post('/stock-clerk/login', [StaffController::class, 'StockClerkLogin'])->name('stockclerk.login.submit');

Route::delete('/stockclerk/delete-category/{category_id}', [ProductController::class, 'StockClerkdeleteCategory'])
    ->name('stockclerk.delete.category');

Route::get('/stockclerk-add-category', [ProductController::class, 'StockClerkAddBrand'])->name('stockclerk.add.category');

Route::get('/stockclerk/edit-category/{category_id}', [ProductController::class, 'StockClerkeditCategory'])
    ->name('stockclerk.edit.category');

Route::post('/stockclerk/update-category/{category_id}', [ProductController::class, 'StockClerkupdateCategory'])
->name('stockclerk.update.category');

Route::post('/stockclerk/update-brand/{brand_id}', [ProductController::class, 'updateBrand'])
->name('stockclerk.update.brand');

Route::delete('/stockclerk/delete-brand/{brand_id}', [ProductController::class, 'StockClerkdeleteBrand'])
->name('stockclerk.delete.brand');

Route::post('/store-variant/{model_id}', [ProductController::class, 'StoreVariant'])->name('store.variant');

Route::put('/update-variant/{model_id}/{variant_id}', [ProductController::class, 'updateVariant'])->name('update.variant');

Route::put('/update-variant-status/{variant_id}', [ProductController::class, 'updateVariantStatus']);

Route::post('/variant/delete/{id}', [ProductController::class, 'deleteVariant'])->name('delete.variant');


Route::get('/stock-clerk/main/dashboard', function () {
    return view('stockclerk.content.stockClerkDashboard');
})->name('stockclerk.dashboard.page');











//Manager

    Route::get('/manager/login', function () {
        return view('manager.managerLogin');
    })->name('manager.login');


    Route::get('/manager/signup', function () {
        return view('manager.managerSignup');
    });

    Route::post('/manager/login', [StaffController::class, 'ManagerLogin'])->name('manager.login.submit');


    Route::get('/manager/dashboard/orders-summary', [OrderController::class, 'ManagergetOrdersSummary']);


    //
    Route::middleware(['manager'])->group(function () {

    Route::get('/manager/overview/details/{order_id}', [OrderController::class, 'Managerdetails'])->name('ManageroverViewDetails');

    // Route::get('/manager/overview', [OrderController::class, 'ManagerstockOrderOverview'])
    // ->name('ManagerstockoverView');

    Route::get('/manager/main/dashboard', function () {
        return view('manager.content.managerDashboard');
    })->name('manager.dashboard.page');


    Route::get('/manager-add-category', [ProductController::class, 'ManagerViewAddBrand'])->name('manager.add.category');

    Route::get('/stockclerk-add-category', [ProductController::class, 'StockClerkAddBrand'])->name('stockclerk.add.category');

    Route::delete('/manager/delete-category/{category_id}', [ProductController::class, 'ManagerdeleteCategory'])
    ->name('manager.delete.category');

    Route::get('/manager/edit-category/{category_id}', [ProductController::class, 'ManagereditCategory'])
    ->name('manager.edit.category');

    Route::post('/manager/update-category/{category_id}', [ProductController::class, 'ManagerupdateCategory'])
    ->name('manager.update.category');

    Route::post('/manager-add-brand/store', [ProductController::class, 'ManagerstoreBrand'])->name('manager.add.brand.store');

    // Route::get('/manager/overview/details/{order_id}', [OrderController::class, 'ManagerstockDetails'])->name('manageroverViewDetails');

    Route::get('/manager-low-units', [ProductController::class, 'lowUnitsProducts'])->name('managerLow');

    Route::get('/manager-view', [ProductController::class, 'Managerindex'])->name('managerproductsView');

    Route::get('/manager-salesreport', [ActivityLogController::class, 'SalesReportIndex'])->name('manager.salesreport');

    Route::get('/manager-stock-activity-logs', [ActivityLogController::class, 'ManagerStockindex'])->name('manager.Stocklogs');

    Route::get('/manager-products', [ProductController::class, 'Managerindex'])->name('ManagerproductsView');

    Route::get('/manager-low-products', [ProductController::class, 'ManagerLowIndex'])->name('ManagerLowProducts');

    Route::get('/manager-add-brand', [ProductController::class, 'ManagerStockcreate'])->name('manager.add.brand');

    Route::get('/manager-add-category', [ProductController::class, 'ManagerkAddCategory'])->name('manager.add.category');

    Route::get('/manager-add-product', [ProductController::class, 'Managercreate'])->name('manager.add.product');

    Route::get('/manager-stock-view-brand', [ProductController::class, 'ManagerViewBrandsList'])->name('manager.view.brands');

    Route::get('/manager-stock-view-category', [ProductController::class, 'ManagerStockViewCategory'])->name('manager.view.category');

    Route::get('/manager-add-quantity', [ProductController::class, 'ManagerAddQuantity'])->name('manager.add.quantity');

    Route::get('/manager-add-details-product/{model_id}', [ProductController::class, 'ManageraddDetails'])->name('manager.addDetails');

    Route::get('/manager-view-details/{model_id}', [ProductController::class, 'ManagerviewDetailsofProduct'])->name('manager.viewDetails');

    Route::get('/manager-product/{model_id}/details', [ProductController::class, 'ManagerviewModelDetails'])->name('manager.viewModelDetails');

    Route::get('/edit-variant/{model_id}/{variant_id}', [ProductController::class, 'editVariant'])->name('edit.variant');

    Route::get('/manager-view-variants/{model_id}', [ProductController::class, 'ManagerindexVariant'])->name('manager.variantsView');

    Route::get('/manager-edit-variant/{model_id}/{variant_id}', [ProductController::class, 'ManagereditVariant'])->name('manager.edit.variant');

    Route::get('/manageradd-variant/{model_id}', [ProductController::class, 'ManagerIndexAddVariant'])->name('manager.add.variant');

    Route::get('/manager/generate-report', [ActivityLogController::class, 'GenerateIndex'])->name('manager.generateReport');

    Route::get('/manager/export-sales-report', [ActivityLogController::class, 'exportSalesReport'])->name('manager.exportSalesReport');

    Route::get('/manager/refund-log', [RefundOrderController::class, 'ManagerviewRefundLog'])->name('manager.refund.log');
    

});
//

Route::post('/manager/update-payment-status', [AdminController::class, 'ManagerupdatePaymentStatus'])->name('manager.updatePaymentStatus');

Route::post('/manager/save-gcash-payment', [AdminController::class, 'ManagersaveGcashPaymentNOW'])->name('manager.saveGcashPayment');

Route::get('/manager-order/{order_id}/edit-details', [RefundOrderController::class, 'ManagereditDetails'])->name('manager.edit.product');

Route::post('/manager-update-order-details', [RefundOrderController::class, 'ManagerupdateOrderDetails'])->name('manager.update.order.details.preorder');

Route::get('/manager/payment-image/{order_id}/{payment_method}', [OrderController::class, 'ManagergetPaymentImage']);

Route::get('/manager/overview', [OrderController::class, 'ManagerOrderOverview'])->name('manageroverView');

Route::post('/manager-store-category', [ProductController::class, 'storeCategory'])->name('manager.store.category');

Route::post('/manager/products/store', [ProductController::class, 'Managerstore'])->name('manager.products.store');

Route::post('/add-details-stocks/store', [ProductController::class, 'ManageraddProductDetails'])->name('manager.add.details.store');

Route::post('/manager-update-product/{model_id}', [ProductController::class, 'updateProduct'])->name('manager.updateProduct');

Route::post('/manager-update-product/{model_id}', [ProductController::class, 'ManagerupdateProduct'])->name('manager.updateProduct');

Route::put('/manager-models/update/{model_id}', [ProductController::class, 'ManagerupdateModel'])->name('manager.updateModel');

Route::delete('/variant/delete/{id}', [ProductController::class, 'ManagerdeleteVariant'])->name('manager.delete.variant');

Route::post('/manager-store-variant/{model_id}', [ProductController::class, 'ManagerStoreVariant'])->name('manager.store.variant');

