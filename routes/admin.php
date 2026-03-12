<?php

use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\PermissionController;
use App\Http\Controllers\Api\Admin\ProfileController;
use App\Http\Controllers\Api\Admin\RoleController;
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

    Route::apiResource('roles', RoleController::class);
    Route::put('roles/{role}/toggle-status', [RoleController::class, 'toggleStatus'])->name('roles.toggle-status');

    Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
});
