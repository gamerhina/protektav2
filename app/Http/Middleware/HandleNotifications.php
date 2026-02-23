<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandleNotifications
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = null;
        $guard = null;

        $isImpersonating = session()->has('impersonated_by');
        $guardPriority = $isImpersonating ? ['dosen', 'mahasiswa', 'admin'] : ['admin', 'dosen', 'mahasiswa'];

        foreach ($guardPriority as $g) {
            if (auth($g)->check()) {
                $user = auth($g)->user();
                $guard = $g;
                break;
            }
        }

        // When impersonating, let the LayoutComposer handle notifications
        // to avoid conflicts between view()->share() and $view->with()
        if ($isImpersonating) {
            return $next($request);
        }

        $notifications = [];
        $count = 0;

        if ($user) {
            $unreadNotifications = $user->unreadNotifications()->orderBy('created_at', 'desc')->limit(10)->get();
            $count = $user->unreadNotifications()->count();
            
            foreach ($unreadNotifications as $notification) {
                $item = [
                    'key' => $notification->id,
                    'title' => $this->getNotificationTitle($notification),
                    'message' => $notification->data['message'] ?? 'Notifikasi baru',
                    'url' => $notification->data['action_url'] ?? $notification->data['url'] ?? '#',
                    'created_at' => $notification->created_at,
                    'read_at' => $notification->read_at,
                ];
                $notifications[] = $item;
            }
        }

        view()->share('navbarNotifications', [
            'items' => $notifications,
            'count' => $count,
            'guard' => $guard,
        ]);

        return $next($request);
    }

    private function getNotificationTitle($notification)
    {
        switch ($notification->type) {
            case 'App\Notifications\SuratSubmittedNotification':
                return 'Pengajuan Surat Baru';
            case 'App\Notifications\SuratStatusUpdatedNotification':
                return 'Status Surat Diperbarui';
            case 'App\Notifications\NewSeminarRegistrationNotification':
                return 'Pendaftaran Seminar Baru';
            default:
                return 'Notifikasi';
        }
    }
}
