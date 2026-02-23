<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MahasiswaController extends Controller
{
    public function dashboard()
    {
        if (!Auth::guard('mahasiswa')->check()) {
            return redirect()->route('login')->with('error', 'Please log in to access the dashboard.');
        }

        $mahasiswa = Auth::guard('mahasiswa')->user();
        $seminars = $mahasiswa->seminars()->with('seminarJenis', 'nilai')->get();
        $seminarCount = $seminars->count();
        $lastStatus = optional($seminars->last())->status ?? 'N/A';
        $highestGrade = $seminars->flatMap->nilai->max('nilai_angka') ?: 'N/A';

        return view('mahasiswa.dashboard', compact(
            'mahasiswa',
            'seminars',
            'seminarCount',
            'lastStatus',
            'highestGrade'
        ));
    }
}
