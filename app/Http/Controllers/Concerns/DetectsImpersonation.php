<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Auth;

trait DetectsImpersonation
{
    /**
     * Get the effective guard considering impersonation.
     * When impersonating, prioritizes the target guard (dosen/mahasiswa).
     */
    protected function getEffectiveGuard(): ?string
    {
        $isImpersonating = session()->has('impersonated_by');
        $guardPriority = $isImpersonating
            ? ['dosen', 'mahasiswa', 'admin']
            : ['admin', 'dosen', 'mahasiswa'];

        foreach ($guardPriority as $guard) {
            if (Auth::guard($guard)->check()) {
                return $guard;
            }
        }

        return null;
    }

    /**
     * Get the effective user considering impersonation.
     */
    protected function getEffectiveUser()
    {
        $guard = $this->getEffectiveGuard();
        return $guard ? Auth::guard($guard)->user() : null;
    }

    /**
     * Check if the effective role is admin (false when impersonating).
     */
    protected function isEffectivelyAdmin(): bool
    {
        return $this->getEffectiveGuard() === 'admin';
    }
}
