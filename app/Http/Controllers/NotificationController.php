<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Authenticated endpoints for the current user's in-app notifications.
 *
 * All routes are scoped to Auth::user() so a user can only read or modify
 * notifications addressed to that user. Super Admins see only notifications
 * addressed to their own user account; cross-tenant data lives inside the
 * notification payload, not in the recipient resolution.
 */
class NotificationController extends Controller
{
    /**
     * Return the current user's recent notifications, newest first.
     */
    public function index(Request $request): JsonResponse
    {
        $limit = (int) $request->input('limit', 10);

        $notifications = Auth::user()
            ->notifications()
            ->latest()
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $notifications->map(fn ($notification) => [
                'id' => $notification->id,
                'type' => $notification->type,
                'data' => $notification->data,
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at,
            ]),
        ]);
    }

    /**
     * Return the current user's unread notification count.
     */
    public function unreadCount(): JsonResponse
    {
        return response()->json([
            'count' => Auth::user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark a single notification as read.
     *
     * The notification must belong to the authenticated user.
     */
    public function markAsRead(string $notification): JsonResponse
    {
        $notification = Auth::user()
            ->notifications()
            ->findOrFail($notification);

        $notification->markAsRead();

        return response()->json([
            'id' => $notification->id,
            'read_at' => $notification->fresh()->read_at,
        ]);
    }

    /**
     * Mark all of the current user's unread notifications as read.
     */
    public function markAllRead(): JsonResponse
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json([
            'count' => Auth::user()->unreadNotifications()->count(),
        ]);
    }
}
