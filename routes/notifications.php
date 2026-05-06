<?php

use App\Http\Controllers\Api\Notification\NotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Notification Routes
|--------------------------------------------------------------------------
|
| These routes are available for all authenticated users (Admin, User, etc.)
| They handle notification listing, counting, and marking as read.
|
*/

// Get all notifications (paginated)
Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');

// Get unread notifications count
Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');

// Mark all notifications as read
Route::post('notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-all-read');

// Mark single notification as read
Route::post('notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
