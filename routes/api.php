<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Providers\RouteServiceProvider;

use App\Http\Controllers\AuthController;
Route::middleware('throttle:signup-limiter')->group(function () {
    Route::post('signup', [AuthController::class, 'signup']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
