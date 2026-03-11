<?php

use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\PermissionController;
use App\Http\Controllers\Api\Admin\RoleController;
use Illuminate\Support\Facades\Route;

// Authentication
Route::post('auth/login', [AuthController::class, 'login'])->name('admin.auth.login');
Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword'])->name('admin.auth.forgot-password');
Route::post('auth/reset-password', [AuthController::class, 'resetPassword'])->name('admin.auth.reset-password');
Route::post('auth/verify-otp', [AuthController::class, 'verifyOtp'])->name('admin.auth.verify-otp');

// Roles – full CRUD + toggle active status
Route::apiResource('roles', RoleController::class);
Route::put('roles/{role}/toggle-status', [RoleController::class, 'toggleStatus'])->name('roles.toggle-status');

// Permissions – list only (used for dropdowns)
Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
