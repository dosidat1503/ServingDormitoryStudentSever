<?php

use App\Http\Controllers\AdminFADController;
use App\Http\Controllers\AuthenticationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderManagementOfUserController;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\OrderFADHomeController;

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
//Home

Route::get('/', function () {
    return view('welcome');
});

Route::get('/email/verify/{id}/{hash}', [AuthenticationController::class, 'verify'])
    ->middleware(['signed'])->name('verification.verify');

// authentication api
Route::post('signup', [AuthenticationController::class, 'signup']);
Route::post('signin', [AuthenticationController::class, 'signin']);


// Posts api
Route::post('addPost', [HomeController::class, 'addPost']);
Route::get('getInfoPost', [HomeController::class, 'getInfoPost']);
Route::get('searchPost', [HomeController::class, 'searchPost']);
Route::get('setData', [HomeController::class, 'setData']);


// Orders api
Route::get('getOrderInfoOfUser', [OrderManagementOfUserController::class, 'getOrderInfoOfUser']);

// Shop api
Route::get('getFADShop', [OrderFADHomeController::class, 'getFADShop']);
Route::get('getFADShopDetailInfo', [OrderFADHomeController::class, 'getFADShopDetailInfo']);


// admin api
// admin fad
Route::post('addFAD', [AdminFADController::class, 'addFAD']);
Route::get('getAllFAD', [AdminFADController::class, 'getFoodsAndDrinksAdmin']);
Route::get('searchFAD', [AdminFADController::class, 'searchFAD']);
Route::get('getFAD/{id}', [AdminFADController::class, 'getFAD']);
Route::put('updateFAD/{id}', [AdminFADController::class, 'updateFAD']);
Route::delete('deleteFAD/{id}', [AdminFADController::class, 'deleteFAD']);


// Route::get('/email/verify', function () {
//     return view('auth.verify-email');
// })->middleware('auth')->name('verification.notice');


<?php

use App\Http\Controllers\AccountManagementController;
use App\Http\Controllers\AdminFADController;
use App\Http\Controllers\AuthenticationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderAndPaymentController;
use App\Http\Controllers\OrderManagementOfUserController;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\OrderFADHomeController;

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
//Home

Route::get('/', function () {
    return view('welcome');
});

Route::get('/email/verify/{id}/{hash}', [AuthenticationController::class, 'verify'])
    ->middleware(['signed'])->name('verification.verify');

// authentication api
Route::post('signup', [AuthenticationController::class, 'signup']);
Route::post('signin', [AuthenticationController::class, 'signin']);


// Posts api
Route::post('addPost', [HomeController::class, 'addPost']);
Route::get('getInfoPost', [HomeController::class, 'getInfoPost']);
Route::get('searchPost', [HomeController::class, 'searchPost']);
Route::get('setData', [HomeController::class, 'setData']);
Route::post('interactPost', [HomeController::class, 'interactPost']);

// Orders api
Route::get('getOrderInfoOfUser', [OrderManagementOfUserController::class, 'getOrderInfoOfUser']);
Route::get('getOrderDetailInfo', [OrderManagementOfUserController::class, 'getOrderDetailInfo']);
Route::get('getInfoProductToRate', [OrderManagementOfUserController::class, 'getInfoProductToRate']);
Route::post('saveRate', [OrderManagementOfUserController::class, 'saveRate']);
Route::post('changeOrderStatusToCancel', [OrderManagementOfUserController::class, 'changeOrderStatusToCancel']);


// Shop api
Route::get('getFADShop', [OrderFADHomeController::class, 'getFADShop']);
Route::get('getFADShopDetailInfo', [OrderFADHomeController::class, 'getFADShopDetailInfo']);
Route::get('getVoucherInfo', [OrderFADHomeController::class, 'getVoucherInfo']);
Route::get('getFADInfo', [OrderFADHomeController::class, 'getFADInfo']);
Route::get('getFADDetailInfo', [OrderFADHomeController::class, 'getFADDetailInfo']);
Route::get('userSearchFAD', [OrderFADHomeController::class, 'userSearchFAD']);

Route::post('sendMailRecoverPassword', [AuthenticationController::class, 'sendMailRecoverPassword']);

Route::get('getDefaultDeliveryInfo', [OrderAndPaymentController::class, 'getDefaultDeliveryInfo']);
Route::get('getDeliveryInfo', [OrderAndPaymentController::class, 'getDeliveryInfo']);

Route::post('saveOrder', [OrderAndPaymentController::class, 'saveOrder']);
Route::get('paymentOnline', [OrderAndPaymentController::class, 'paymentOnline']);
Route::post('updateDeliveryInfo', [OrderAndPaymentController::class, 'updateDeliveryInfo']);
Route::post('applyVoucher', [OrderAndPaymentController::class, 'applyVoucher']);

//acount
Route::get('getInfoAccount', [AccountManagementController::class, 'getInfoAccount']);
Route::post('updateAccountInfo', [AccountManagementController::class, 'updateAccountInfo']);
Route::post('verifyChangeMail', [AccountManagementController::class, 'verifyChangeMail']);
Route::post('changePassword', [AccountManagementController::class, 'changePassword']);
Route::post('updateDeliveryInfo', [OrderAndPaymentController::class, 'updateDeliveryInfo']);
Route::post('editPost', [HomeController::class, 'editPost']);
Route::post('deletePost', [HomeController::class, 'deletePost']);
  
 
// admin api
// admin fad
Route::post('addFAD', [AdminFADController::class, 'addFAD']);
Route::get('getAllFAD', [AdminFADController::class, 'getFoodsAndDrinksAdmin']);
Route::get('searchFAD', [AdminFADController::class, 'searchFAD']);
Route::get('getFAD/{id}', [AdminFADController::class, 'getFAD']);
Route::put('updateFAD/{id}', [AdminFADController::class, 'updateFAD']);
Route::delete('deleteFAD/{id}', [AdminFADController::class, 'deleteFAD']);


// Route::get('/email/verify', function () {
//     return view('auth.verify-email');
// })->middleware('auth')->name('verification.notice');