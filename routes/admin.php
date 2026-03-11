<?php

use App\Http\Controllers\Api\Admin\PermissionController;
use App\Http\Controllers\Api\Admin\RoleController;
use Illuminate\Support\Facades\Route;

// Roles – full CRUD + toggle active status
Route::apiResource('roles', RoleController::class);
Route::put('roles/{role}/toggle-status', [RoleController::class, 'toggleStatus'])->name('roles.toggle-status');

// Permissions – list only (used for dropdowns)
Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
