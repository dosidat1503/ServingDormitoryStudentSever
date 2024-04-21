<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;
use App\Http\Controllers\ManageProductController; 
use App\Http\Controllers\PayOnlineController;
use App\Http\Controllers\AddProductController;
use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\AdminManageOrderController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InfoProductController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\InfoAccountController;
 
use App\Http\Controllers\SearchProductController;
use App\Http\Controllers\UpdateProductController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ManageAccountCustomerController;
use App\Http\Controllers\ManageAccountStaff;
use App\Http\Controllers\MyOrderController;
use App\Http\Controllers\ReviewProduct;
use App\Http\Controllers\SetDataController;
use App\Http\Controllers\StatisticController;
use App\Http\Controllers\VoucherController;
use Illuminate\Support\Facades\Auth;

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
Route::get('/email/verify', function () {
    // Your code here
})->name('verification.notice');