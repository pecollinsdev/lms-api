<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;
use App\Models\User;

class NotificationController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        return response()->json([
            'status' => 'success',
            'data' => $user->getNotifications()
        ]);
    }

    public function unread()
    {
        /** @var User $user */
        $user = Auth::user();
        return response()->json([
            'status' => 'success',
            'data' => $user->getUnreadNotifications()
        ]);
    }

    public function markAsRead($id)
    {
        /** @var User $user */
        $user = Auth::user();
        $success = $user->markNotificationAsRead($id);
        
        if (!$success) {
            return response()->json([
                'status' => 'error',
                'message' => 'Notification not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Notification marked as read'
        ]);
    }

    public function markAllAsRead()
    {
        /** @var User $user */
        $user = Auth::user();
        $count = $user->markAllNotificationsAsRead();

        return response()->json([
            'status' => 'success',
            'message' => "{$count} notifications marked as read"
        ]);
    }

    public function destroy($id)
    {
        /** @var User $user */
        $user = Auth::user();
        $notification = $user
            ->notifications()
            ->findOrFail($id);
            
        $notification->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Notification deleted successfully'
        ]);
    }
}
