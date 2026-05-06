<?php

use App\Http\Controllers\Api\Client\AboutUsController;
use App\Http\Controllers\Api\Client\BlogController;
use App\Http\Controllers\Api\Client\ContactController;
use App\Http\Controllers\Api\Client\ContactUsController;
use App\Http\Controllers\Api\Client\GeneralSettingController;
use App\Http\Controllers\Api\Client\PrivacyController;
use App\Http\Controllers\Api\Client\TermsAndCondiotionsController;
use App\Http\Controllers\Api\User\AuthController as UserAuthController;
use App\Http\Controllers\Api\User\ProfileController as UserProfileController;
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
Route::get('about-us', [AboutUsController::class, 'index'])->name('about-us.index');
Route::get('terms', [TermsAndCondiotionsController::class, 'index'])->name('terms-and-conditions.index');
Route::get('privacy', [PrivacyController::class, 'index'])->name('privacy.index');
Route::get('general-settings', [GeneralSettingController::class, 'index'])->name('general-settings.index');
Route::post('contact-us', [ContactUsController::class, 'store'])->name('contact-us.store');

// Blogs (Public)
Route::get('blogs', [BlogController::class, 'index'])->name('blogs.index');
Route::get('blogs/{id}', [BlogController::class, 'show'])->name('blogs.show');

// Blog Comments (Authenticated Users)
Route::middleware('auth:api')->group(function () {
    Route::post('blogs/{id}/comments', [BlogController::class, 'storeComment'])->name('blogs.comments.store');
});

// User Authentication (OTP-based)
Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('register', [UserAuthController::class, 'register'])->name('register');
    Route::post('login', [UserAuthController::class, 'login'])->name('login');
    Route::post('verify-otp', [UserAuthController::class, 'verifyOtp'])->name('verify-otp');
    Route::post('resend-otp', [UserAuthController::class, 'resendOtp'])->name('resend-otp');
    Route::post('logout', [UserAuthController::class, 'logout'])->middleware('auth:api')->name('logout');
});

// User Profile (authenticated)
Route::middleware('auth:api')->prefix('profile')->name('profile.')->group(function () {
    Route::get('/', [UserProfileController::class, 'index'])->name('index');
    Route::put('/', [UserProfileController::class, 'update'])->name('update');
    Route::post('change-phone', [UserAuthController::class, 'changePhone'])->name('change-phone');
});

// Notifications (User)
Route::middleware('auth:api')->group(function () {
    require __DIR__ . '/notifications.php';
});
