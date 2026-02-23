<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Admin;

class ImpersonateController extends Controller
{
    /**
     * Start impersonating a user.
     */
    public function loginAs(Request $request, $type, $id)
    {
        // Only admins can start impersonating
        if (!Auth::guard('admin')->check() && !$request->session()->has('impersonated_by')) {
            abort(403, 'Hanya admin yang dapat melakukan aksi ini.');
        }

        $adminId = $request->session()->get('impersonated_by') ?? Auth::guard('admin')->id();

        $user = null;
        $targetGuard = null;

        if ($type === 'dosen') {
            $user = Dosen::findOrFail($id);
            $targetGuard = 'dosen';
            $redirectTo = route('dosen.dashboard');
        } elseif ($type === 'mahasiswa') {
            $user = Mahasiswa::findOrFail($id);
            $targetGuard = 'mahasiswa';
            $redirectTo = route('mahasiswa.dashboard');
        } else {
            abort(404);
        }

        // Login as target user WITHOUT logging out admin
        Auth::guard($targetGuard)->login($user);

        // Mark impersonation in session and remember where admin came from
        $request->session()->put('impersonated_by', $adminId);
        $request->session()->put('impersonate_return_url', url()->previous());
        $request->session()->save();

        return redirect($redirectTo)->with('success', "Sekarang Anda login sebagai {$user->nama}");
    }

    /**
     * Stop impersonating and return to admin.
     */
    public function leave(Request $request)
    {
        // Check if Admin is logged in (Dual Login State)
        if (!Auth::guard('admin')->check()) {
            // Fallback: Check session key if auth check failed for some reason
            if (!$request->session()->has('impersonated_by')) {
                return redirect('/');
            }
        }

        // Logout from possible target guards
        Auth::guard('dosen')->logout();
        Auth::guard('mahasiswa')->logout();

        // Clear the impersonate flag and get return URL
        $returnUrl = $request->session()->get('impersonate_return_url');
        $request->session()->forget('impersonated_by');
        $request->session()->forget('impersonate_return_url');
        $request->session()->save();

        return redirect($returnUrl ?: route('admin.dashboard'))->with('success', 'Kembali ke akun Admin.');
    }
}
