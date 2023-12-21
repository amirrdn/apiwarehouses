<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemsController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\SalespaymentController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\CustomercouponController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\StockadjusmentController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PurchasereturnController;
use App\Http\Controllers\Warehousesalescontroller;
use App\Http\Controllers\AdministrativeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware(['api'])->group(function() {
    Route::post('/auth',[AuthController::class, 'login']);
    Route::get('/unauthenticated',[AuthController::class, 'unauthenticated'])->name('unauthenticated');
    Route::middleware('auth:api')->group(function() {
        Route::group(['prefix'=>'items'], function(){
            Route::get('/list', [ItemsController::class, 'ItemsList']);
            Route::post('/store', [ItemsController::class, 'store']);
            Route::get('/view/{id}', [ItemsController::class, 'view']);
            Route::post('/update', [ItemsController::class, 'update']);
            Route::delete('/delete', [ItemsController::class, 'destroy']);
        });
        Route::group(['prefix' => 'branch'], function(){
            Route::get('/list', [BranchController::class, 'getBranch']);
        });
        Route::group(['prefix' => 'category'], function(){
            Route::get('/list', [CategoriesController::class, 'getCategories']);
            Route::post('/store', [CategoriesController::class, 'store']);
            Route::post('/view', [CategoriesController::class, 'view']);
            Route::post('/update', [CategoriesController::class, 'update']);
            Route::delete('/delete', [CategoriesController::class, 'destroy']);
        });
        Route::group(['prefix' => 'unit'], function(){
            Route::get('/list', [UnitController::class, 'getUnits']);
        });
        Route::group(['prefix' => 'tax'], function() {
            Route::get('/list', [TaxController::class, 'GetTaxs']);
        });
        Route::group(['prefix' => 'sales'], function() {
            Route::get('/total-of-sales', [SalesController::class, 'totalSales']);
            Route::get('/list', [SalesController::class, 'listSales']);
            Route::post('/store', [SalesController::class, 'store']);
            Route::get('/view/{sales_id}', [SalesController::class, 'view']);
            Route::post('/update', [SalesController::class, 'update']);
            Route::delete('/delete', [SalesController::class, 'destroy']);
            Route::group(['prefix' => 'payment'], function(){
                Route::get('/detail/{sales_id}', [SalespaymentController::class, 'paymentDetail']);
                Route::post('/store', [SalespaymentController::class, 'store']);
                Route::delete('/delete', [SalespaymentController::class, 'destroy']);
            });
        });
        Route::group(['prefix' => 'customer'], function(){
            Route::get('/list', [CustomerController::class, 'customerList']);
            Route::post('/store', [CustomerController::class, 'store']);
            Route::get('/view/{customer_id}', [CustomerController::class, 'view']);
            Route::post('/update', [CustomerController::class, 'updatecustomer']);
            Route::delete('/delete', [CustomerController::class, 'destroy']);
            Route::group(['prefix' => 'coupon'], function() {
                Route::get('/list', [CustomercouponController::class, 'Couponlist']);
                Route::post('/store', [CustomercouponController::class, 'store']);
                Route::post('/view', [CustomercouponController::class, 'view']);
                Route::post('/update', [CustomercouponController::class, 'update']);
                Route::delete('/delete', [CustomercouponController::class, 'destroy']);
            });
        });
        Route::group(['prefix' => 'warehouse'], function(){
            Route::get('/list', [WarehouseController::class, 'warehouseList']);
        });
        Route::group(['prefix' => 'account'], function(){
            Route::get('/list', [AccountController::class, 'accountList']);
        });
        Route::group(['prefix' => 'supplier'], function(){
            Route::get('/list', [SupplierController::class, 'supplierList']);
            Route::post('/store', [SupplierController::class, 'store']);
            Route::get('/view/{supplier_id}', [SupplierController::class, 'view']);
            Route::post('/update', [SupplierController::class, 'update']);
            Route::delete('/delete', [SupplierController::class, 'destory']);
        });
        Route::group(['prefix' => 'users'], function(){
            Route::get('/view', [UsersController::class, 'view']);
            Route::post('/update', [UsersController::class, 'update']);
        });
        Route::group(['prefix' => 'quotations'], function(){
            Route::get('/list', [QuotationController::class, 'quotationList']);
            Route::post('/store', [QuotationController::class, 'store']);
            Route::get('/view/{quotation_id}', [QuotationController::class, 'view']);
            Route::post('/update', [QuotationController::class, 'update']);
            Route::delete('/delete', [QuotationController::class, 'destroy']);
        });
        Route::group(['prefix' => 'stock'], function(){
            Route::get('/adjustment/list', [StockadjusmentController::class, 'stockadjustList']);
            Route::post('/adjustment/store', [StockadjusmentController::class, 'store']);
            Route::get('/adjustment/view/{adjustment_id}', [StockadjusmentController::class, 'view']);
            Route::post('/adjustment/update', [StockadjusmentController::class, 'update']);
            Route::delete('/adjustment/delete', [StockadjusmentController::class, 'destroy']);
        });
        Route::group(['prefix' => 'purchase'], function(){
            Route::get('/total-invoice-purchase', [PurchaseController::class, 'totalInvoicePurchase']);
            Route::get('/list', [PurchaseController::class, 'listPurchase']);
            Route::put('/view', [PurchaseController::class, 'view']);
            Route::post('/store', [PurchaseController::class, 'store']);
            Route::post('/update', [PurchaseController::class, 'update']);
            Route::delete('/delete', [PurchaseController::class, 'destroy']);
            Route::group(['prefix' => 'return'], function(){
                Route::get('/list', [PurchasereturnController::class, 'returnList']);
                Route::post('/store', [PurchasereturnController::class, 'store']);
                Route::put('/view', [PurchasereturnController::class, 'view']);
                Route::post('/update', [PurchasereturnController::class, 'update']);
                Route::group(['prefix' => 'payment'], function(){
                    Route::post('/view', [PurchasereturnController::class, 'viewPaymentReturn']);
                });
            });
        });
        Route::group(['prefix' => 'message'], function(){
            Route::post('/send-sms', [MessageController::class, 'sendsmsMessage']);
        });
        Route::get('/total-warehouse-sales', [Warehousesalescontroller::class, 'getTotalawrehouse']);
        Route::get('/store-wise-detail', [Warehousesalescontroller::class, 'storeWise']);
        Route::get('/recent-items', [Warehousesalescontroller::class, 'recentItems']);
        Route::get('/recent-sales-invoice', [Warehousesalescontroller::class, 'recentSalesInvoice']);
        Route::get('/dashboard-detail', [Warehousesalescontroller::class, 'detaildashobardWarehouse']);
        Route::get('/stock-alert', [Warehousesalescontroller::class, 'stockAlert']);
        Route::get('/trending-items', [Warehousesalescontroller::class, 'trendingItems']);

        Route::get('administrative', [AdministrativeController::class, 'getcountryState']);
    });
});
