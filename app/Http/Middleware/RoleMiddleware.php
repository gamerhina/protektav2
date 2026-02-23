<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Check if the authenticated user has the specified role
        $user = null;

        // Check each user type
        if (auth('admin')->check()) {
            $user = auth('admin')->user();
        } elseif (auth('dosen')->check()) {
            $user = auth('dosen')->user();
        } elseif (auth('mahasiswa')->check()) {
            $user = auth('mahasiswa')->user();
        } else {
            $user = auth()->user(); // Standard user guard
        }

        if (!$user || !$user->hasRole($role)) {
            abort(403, 'Unauthorized access');
        }

        return $next($request);
    }
}
