<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // Index method removed as the notification page is disabled.

    /**
     * Mark all notifications as read.
     */
    public function markAsRead(Request $request)
    {
        $isImpersonating = session()->has('impersonated_by');
        $guardPriority = $isImpersonating ? ['dosen', 'mahasiswa', 'admin'] : ['admin', 'dosen', 'mahasiswa'];

        foreach ($guardPriority as $g) {
            if (Auth::guard($g)->check()) {
                $user = Auth::guard($g)->user();
                break;
            }
        }

        if ($user) {
            $user->unreadNotifications->markAsRead();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markNotificationAsRead(Request $request, $notification)
    {
        $isImpersonating = session()->has('impersonated_by');
        $guardPriority = $isImpersonating ? ['dosen', 'mahasiswa', 'admin'] : ['admin', 'dosen', 'mahasiswa'];

        foreach ($guardPriority as $g) {
            if (Auth::guard($g)->check()) {
                $user = Auth::guard($g)->user();
                break;
            }
        }

        if ($user) {
            $notification = $user->notifications()->find($notification);
            if ($notification) {
                $notification->markAsRead();
            }
        }

        return response()->json(['success' => true]);
    }
}
