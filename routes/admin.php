<?php

use App\Http\Controllers\Api\Admin\AdPackageController;
use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\Admin\AboutUsSettingController;
use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\BlogController;
use App\Http\Controllers\Api\Admin\ContactMessageController;
use App\Http\Controllers\Api\Admin\ContactSettingController;
use App\Http\Controllers\Api\Admin\GeneralSettingController;
use App\Http\Controllers\Api\Admin\PermissionController;
use App\Http\Controllers\Api\Admin\ProfileController;
use App\Http\Controllers\Api\Admin\PrivacySettingController;
use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\Admin\TermsAndCondiotionsSettingController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\NotificationGroupController;
use Illuminate\Support\Facades\Route;

// Authentication
Route::post('auth/login', [AuthController::class, 'login'])->name('admin.auth.login');
Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword'])->name('admin.auth.forgot-password');
Route::post('auth/reset-password', [AuthController::class, 'resetPassword'])
    ->middleware('auth:admin')
    ->name('admin.auth.reset-password');
Route::post('auth/verify-otp', [AuthController::class, 'verifyOtp'])->name('admin.auth.verify-otp');



Route::middleware('auth:admin')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout'])->name('admin.auth.logout');
    Route::get('auth/profile', [ProfileController::class, 'show'])->name('admin.auth.profile.show');
    Route::put('auth/profile', [ProfileController::class, 'update'])->name('admin.auth.profile.update');

    Route::apiResource('admins', AdminController::class);
    Route::put('admins/{admin}/toggle-status', [AdminController::class, 'toggleStatus'])->name('admins.toggle-status');

    // Users
    Route::apiResource('users', UserController::class)->only(['index', 'show']);
    Route::put('users/{user}/toggle-status', [UserController::class, 'toggleUserStatus'])->name('users.toggle-status');
    Route::get('users/export/excel', [UserController::class, 'exportExcel'])->name('users.export.excel');
    Route::get('users/export/pdf', [UserController::class, 'exportPdf'])->name('users.export.pdf');

    Route::apiResource('roles', RoleController::class);
    Route::put('roles/{role}/toggle-status', [RoleController::class, 'toggleStatus'])->name('roles.toggle-status');

    Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');

    // Contact Settings
    Route::get('contact-settings', [ContactSettingController::class, 'show'])->name('contact-settings.show');
    Route::put('contact-settings', [ContactSettingController::class, 'update'])->name('contact-settings.update');

    // About Us Settings
    Route::get('aboutus-settings', [AboutUsSettingController::class, 'show'])->name('about-us-settings.show');
    Route::put('aboutus-settings', [AboutUsSettingController::class, 'update'])->name('about-us-settings.update');

    // Terms And Conditions Settings
    Route::put('terms-settings', [TermsAndCondiotionsSettingController::class, 'update'])->name('terms-and-conditions-settings.update');
    Route::get('terms-settings', [TermsAndCondiotionsSettingController::class, 'show'])->name('terms-and-conditions-settings.show');

    // Privacy Settings
    Route::get('privacy-settings', [PrivacySettingController::class, 'show'])->name('privacy-settings.show');
    Route::put('privacy-settings', [PrivacySettingController::class, 'update'])->name('privacy-settings.update');

    // General Settings
    Route::get('general-settings', [GeneralSettingController::class, 'show'])->name('general-settings.show');
    Route::put('general-settings', [GeneralSettingController::class, 'update'])->name('general-settings.update');

    // Contact Us Messages
    Route::get('contact-messages', [ContactMessageController::class, 'index'])->name('contact-messages.index');
    Route::get('contact-messages/{id}', [ContactMessageController::class, 'show'])->name('contact-messages.show');
    Route::post('contact-messages/{id}/reply', [ContactMessageController::class, 'reply'])->name('contact-messages.reply');

    // Advertising Packages
    Route::apiResource('ad-packages', AdPackageController::class);
    Route::put('ad-packages/{id}/toggle-status', [AdPackageController::class, 'toggleStatus'])->name('ad-packages.toggle-status');

    // Grouped Notifications
    Route::apiResource('notification-groups', NotificationGroupController::class)
        ->only(['index', 'store', 'show', 'destroy']);

    // Blog Management
    Route::apiResource('blogs', BlogController::class);
    Route::put('blogs/{id}/toggle-status', [BlogController::class, 'toggleStatus'])->name('blogs.toggle-status');
    Route::put('comments/{commentId}/toggle-visibility', [BlogController::class, 'toggleCommentVisibility'])->name('comments.toggle-visibility');
    Route::delete('comments/{commentId}', [BlogController::class, 'destroyComment'])->name('comments.destroy');

    // Notifications (Admin)
    require __DIR__ . '/notifications.php';
});
