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
        $notifications = $user
            ->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $notifications
        ]);
    }

    public function unread()
    {
        /** @var User $user */
        $user = Auth::user();
        $unreadNotifications = $user
            ->unreadNotifications()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $unreadNotifications
        ]);
    }

    public function markAsRead($id)
    {
        /** @var User $user */
        $user = Auth::user();
        $notification = $user
            ->notifications()
            ->findOrFail($id);
            
        $notification->markAsRead();

        return response()->json([
            'status' => 'success',
            'message' => 'Notification marked as read'
        ]);
    }

    public function markAllAsRead()
    {
        /** @var User $user */
        $user = Auth::user();
        $user
            ->unreadNotifications()
            ->update(['read_at' => now()]);

        return response()->json([
            'status' => 'success',
            'message' => 'All notifications marked as read'
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
