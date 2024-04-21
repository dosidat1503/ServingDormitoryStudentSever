<?php

use App\Http\Controllers\Controller;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
}); 

Route::get('test', [HomeController::class, 'test']);
Route::post('signup', [HomeController::class, 'signup']);
