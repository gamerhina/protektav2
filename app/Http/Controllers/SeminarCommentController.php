<?php

namespace App\Http\Controllers;

use App\Models\Seminar;
use App\Models\Admin;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Notifications\NewSeminarCommentNotification;
use Illuminate\Http\Request;

class SeminarCommentController extends Controller
{
    public function store(Request $request, Seminar $seminar)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'is_internal' => 'nullable|boolean',
        ]);

        $user = null;

        // Impersonation-aware guard priority
        $isImpersonating = session()->has('impersonated_by');
        $guardPriority = $isImpersonating ? ['dosen', 'mahasiswa', 'admin'] : ['admin', 'dosen', 'mahasiswa'];
        
        foreach ($guardPriority as $g) {
            if (auth($g)->check()) {
                $user = auth($g)->user();
                break;
            }
        }

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        $comment = $seminar->comments()->create([
            'user_id' => $user->id,
            'user_type' => get_class($user),
            'message' => $validated['message'],
            'is_internal' => $request->boolean('is_internal', false),
        ]);

        // Notifications Logic
        $recipients = collect();

        if ($user instanceof Mahasiswa) {
            // If student comments, notify all involved admins (but NOT lecturers anymore)
            $recipients = $recipients->concat(Admin::all());
        } else {
            // If anyone else (Admin or Dosen) comments, only notify the student
            if ($seminar->mahasiswa) {
                $recipients->push($seminar->mahasiswa);
            }
            
            // Optional: If a lecturer comments, we might want to notify Admins for record-keeping
            // but the user said "tidak perlu ke akun lain", so we'll stick to just the student.
        }

        $recipients = $recipients->unique(function ($item) {
            return get_class($item) . $item->id;
        });

        foreach ($recipients as $recipient) {
            $recipient->notify(new NewSeminarCommentNotification($seminar, $user));
        }

        return back()->with('success', 'Komentar ditambahkan.');
    }
}
