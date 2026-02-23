<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SuratCommentController extends Controller
{
    public function store(Request $request, \App\Models\Surat $surat)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $isImpersonating = session()->has('impersonated_by');
        $guardPriority = $isImpersonating ? ['dosen', 'mahasiswa', 'admin'] : ['admin', 'dosen', 'mahasiswa'];
        
        $user = null;
        foreach ($guardPriority as $g) {
            if (auth($g)->check()) {
                $user = auth($g)->user();
                break;
            }
        }

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        $comment = $surat->comments()->create([
            'user_id' => $user->id,
            'user_type' => get_class($user),
            'message' => $validated['message'],
        ]);

        // Notifications - notify all involved parties EXCEPT the sender
        $senderClass = get_class($user);
        $senderId = $user->id;

        // 1. Notify Admins (unless sender is admin)
        if ($senderClass !== \App\Models\Admin::class) {
            $admins = \App\Models\Admin::all();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\NewSuratCommentNotification($surat, $user));
            }
        }

        // 2. Notify Applicant (pemohon) - unless sender is the applicant
        $applicant = $surat->pemohonDosen ?? $surat->pemohonMahasiswa;
        if ($applicant && !($senderClass === get_class($applicant) && $senderId === $applicant->id)) {
            $applicant->notify(new \App\Notifications\NewSuratCommentNotification($surat, $user));
        }

        // 3. Notify Approver Dosens - unless sender is that dosen
        $approverDosens = $surat->approvals()->with('dosen')->get()
            ->pluck('dosen')->filter()->unique('id');
        foreach ($approverDosens as $dosen) {
            // Skip if sender is this dosen, or if this dosen is also the applicant (already notified)
            if ($senderClass === \App\Models\Dosen::class && $senderId === $dosen->id) continue;
            if ($applicant && get_class($applicant) === \App\Models\Dosen::class && $applicant->id === $dosen->id) continue;
            $dosen->notify(new \App\Notifications\NewSuratCommentNotification($surat, $user));
        }

        return back()->with('success', 'Komentar ditambahkan.');
    }
}
