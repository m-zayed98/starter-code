<?php

namespace App\Http\Controllers\Api\Notification;

use App\Facades\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Services\ApiResponse\StatusCode;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get all notifications for authenticated user
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return ApiResponse::respondWithCollection(
            NotificationResource::collection($notifications)
        )
            ->withPagination()
            ->send();
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount(Request $request)
    {
        $user = $request->user();

        $count = $user->unreadNotifications()->count();

        return ApiResponse::respondWithArray([
            'unread_count' => $count,
        ])->send();
    }

    /**
     * Mark notification(s) as read
     * If id is provided, mark single notification as read
     * If no id, mark all notifications as read
     */
    public function markAsRead(Request $request, ?string $id = null)
    {
        $user = $request->user();

        if ($id) {
            // Mark single notification as read
            $notification = $user->notifications()->find($id);

            if (!$notification) {
                return ApiResponse::respondWithError(
                    __('Notification not found'),
                    StatusCode::NOT_FOUND,
                    404
                )->send();
            }

            $notification->markAsRead();

            return ApiResponse::respondWithSuccess(
                __('Notification marked as read')
            )->send();
        }

        // Mark all notifications as read
        $user->unreadNotifications->markAsRead();

        return ApiResponse::respondWithSuccess(
            __('All notifications marked as read')
        )->send();
    }
}
