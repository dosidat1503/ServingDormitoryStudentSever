<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route; 
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TestController;
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
//Home

Route::get('/', function () {
    return view('welcome');
}); 
 
Route::get('/email/verify/{id}/{hash}', [HomeController::class, 'verify']) 
->middleware(['signed'])->name('verification.verify');

Route::post('signup', [HomeController::class, 'signup']);
Route::post('signin', [HomeController::class, 'signin']);
Route::get('testmail', [HomeController::class, 'testmail']);

// Route::get('/email/verify', function () {
//     return view('auth.verify-email');
// })->middleware('auth')->name('verification.notice');