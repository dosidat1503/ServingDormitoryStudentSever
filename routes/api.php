<?php

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

Route::post('signup', [AuthenticationController::class, 'signup']);
Route::post('signin', [AuthenticationController::class, 'signin']);
 
Route::post('addPost', [HomeController::class, 'addPost']);
Route::get('getInfoPost', [HomeController::class, 'getInfoPost']);
Route::get('searchPost', [HomeController::class, 'searchPost']);
Route::get('setData', [HomeController::class, 'setData']);
 
Route::get('getOrderInfoOfUser', [OrderManagementOfUserController::class, 'getOrderInfoOfUser']);

Route::get('getFADShop', [OrderFADHomeController::class, 'getFADShop']);
Route::get('getFADShopDetailInfo', [OrderFADHomeController::class, 'getFADShopDetailInfo']);
// Route::get('/email/verify', function () {
//     return view('auth.verify-email');
// })->middleware('auth')->name('verification.notice');