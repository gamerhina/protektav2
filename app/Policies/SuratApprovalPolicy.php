<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SuratApproval;

class SuratApprovalPolicy
{
    public function view($user, SuratApproval $approval): bool
    {
        // Admins can view everything
        if ($user instanceof \App\Models\Admin) {
            return true;
        }

        // Dosen can only view if assigned to them
        return (int)$user->id === (int)$approval->dosen_id;
    }

    /**
     * Determine if the user can approve
     */
    public function approve($user, SuratApproval $approval): bool
    {
        if ($approval->isRejected()) {
            return false;
        }

        // Admins can approve anything (super admin)
        if ($user instanceof \App\Models\Admin) {
            return true;
        }

        // Dosen can only approve if assigned
        return (int)$user->id === (int)$approval->dosen_id;
    }

    /**
     * Determine if the user can reject
     */
    public function reject($user, SuratApproval $approval): bool
    {
        if ($approval->isRejected()) {
            return false;
        }

        // Admins can reject anything
        if ($user instanceof \App\Models\Admin) {
            return true;
        }

        return (int)$user->id === (int)$approval->dosen_id;
    }
}
