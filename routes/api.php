<?php

use App\Http\Controllers\Api\Client\AboutUsController;
use App\Http\Controllers\Api\Client\AdController as PublicAdController;
use App\Http\Controllers\Api\Client\AdPackageController as ClientAdPackageController;
use App\Http\Controllers\Api\Client\BlogController;
use App\Http\Controllers\Api\Client\ContactController;
use App\Http\Controllers\Api\Client\ContactUsController;
use App\Http\Controllers\Api\Client\GeneralSettingController;
use App\Http\Controllers\Api\Client\PrivacyController;
use App\Http\Controllers\Api\Client\TermsAndCondiotionsController;
use App\Http\Controllers\Api\User\AdController;
use App\Http\Controllers\Api\User\AuthController as UserAuthController;
use App\Http\Controllers\Api\User\NafathController;
use App\Http\Controllers\Api\User\ProfileController as UserProfileController;
use App\Http\Controllers\Api\User\SubscriptionController;
use App\Http\Controllers\Api\User\TransactionController;
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

// Ad Packages (Public – with optional auth for is_subscribed flag)
Route::get('packages', [ClientAdPackageController::class, 'index'])->name('packages.index');
Route::get('packages/{id}', [ClientAdPackageController::class, 'show'])->name('packages.show');

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
    Route::delete('/', [UserProfileController::class, 'destroy'])->name('destroy');
    Route::post('change-phone', [UserAuthController::class, 'changePhone'])->name('change-phone');
});

// Notifications (User)
Route::middleware('auth:api')->group(function () {
    require __DIR__.'/notifications.php';
});

// Subscriptions & Transactions (Authenticated Users)
Route::middleware('auth:api')->group(function () {
    Route::get('subscriptions/active', [SubscriptionController::class, 'active'])->name('subscriptions.active');
    Route::post('subscriptions', [SubscriptionController::class, 'subscribe'])->name('subscriptions.subscribe');
    Route::post('transactions/{id}/process', [TransactionController::class, 'process'])->name('transactions.process');

    Route::apiResource('ads', AdController::class);
});

// Nafath Identity Verification
Route::middleware('auth:api')->prefix('nafath')->name('nafath.')->group(function () {
    Route::post('verify', [NafathController::class, 'initiate'])->name('initiate');
});

// Nafath Webhook Callback (unauthenticated – called by Nafath servers)
Route::post('nafath/callback', [NafathController::class, 'callback'])->name('nafath.callback');

Route::prefix('public/ads')->name('public.ads.')->group(function () {
    Route::get('/', [PublicAdController::class, 'index'])->name('index');
    Route::get('/{id}', [PublicAdController::class, 'show'])->name('show');

    Route::middleware('auth:api')->group(function () {
        Route::post('/{id}/reviews', [PublicAdController::class, 'storeReview'])->name('reviews.store');
        Route::post('/{id}/reports', [PublicAdController::class, 'storeReport'])->name('reports.store');
    });
});
