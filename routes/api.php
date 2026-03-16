<?php

use App\Http\Controllers\Api\Client\ContactController;
use App\Http\Controllers\Api\User\AuthController as UserAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Client API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register Client API routes for your application.
| These routes are loaded by the RouteServiceProvider with the 'api'
| middleware group and versioned with the API prefix.
|
*/

// Contact Details
Route::get('contact', [ContactController::class, 'index'])->name('contact.index');

// User Authentication (OTP-based)
Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('register', [UserAuthController::class, 'register'])->name('register');
    Route::post('login', [UserAuthController::class, 'login'])->name('login');
    Route::post('verify-otp', [UserAuthController::class, 'verifyOtp'])->name('verify-otp');
    Route::post('resend-otp', [UserAuthController::class, 'resendOtp'])->name('resend-otp');
});
