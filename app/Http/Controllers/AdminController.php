<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Seminar;
use App\Models\DocumentTemplate;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function dashboard()
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('login')->with('error', 'Please log in to access the dashboard.');
        }

        $mahasiswaCount = Mahasiswa::count();
        $dosenCount = Dosen::count();
        $seminarCount = Seminar::count();

        // Calculate Weekly Activity (Last 7 Days)
        $weeklyActivity = [];
        $labels = [];
        $now = now();
        
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $labels[] = $date->translatedFormat('D'); // D for Day name
            $weeklyActivity[] = Seminar::whereDate('created_at', $date->toDateString())->count();
        }

        // Calculate trend (vs previous 7 days)
        $thisWeekCount = array_sum($weeklyActivity);
        $lastWeekCount = Seminar::whereBetween('created_at', [
            $now->copy()->subDays(13)->startOfDay(),
            $now->copy()->subDays(7)->endOfDay()
        ])->count();

        $trendPercent = 0;
        if ($lastWeekCount > 0) {
            $trendPercent = round((($thisWeekCount - $lastWeekCount) / $lastWeekCount) * 100, 1);
        } else {
            $trendPercent = $thisWeekCount > 0 ? 100 : 0;
        }

        // Recent Activities (Latest 5 Seminars)
        $recentActivities = Seminar::with('mahasiswa')
            ->latest()
            ->take(5)
            ->get()
            ->map(function($seminar) {
                return [
                    'title' => 'Pendaftaran ' . ($seminar->jenis->nama ?? 'Seminar'),
                    'time' => $seminar->created_at->diffForHumans(),
                    'icon' => 'fa-calendar-plus',
                    'color' => 'text-purple-500',
                    'desc' => 'Mahasiswa: <b>' . ($seminar->mahasiswa->nama ?? 'N/A') . '</b> mendaftarkan judul: <i>' . \Illuminate\Support\Str::limit($seminar->judul, 60) . '</i>'
                ];
            });

        $scheduledSeminarsCount = Seminar::where('tanggal', '>=', now()->toDateString())->count();
        
        // Progress tasks (e.g., seminars this month that have been scheduled vs total this month)
        $totalMonth = Seminar::whereMonth('created_at', now()->month)->count();
        $scheduledMonth = Seminar::whereMonth('created_at', now()->month)->whereNotNull('tanggal')->count();
        $progressPercent = $totalMonth > 0 ? round(($scheduledMonth / $totalMonth) * 100) : 100;

        return view('admin.dashboard', compact(
            'mahasiswaCount', 
            'dosenCount', 
            'seminarCount',
            'weeklyActivity',
            'labels',
            'trendPercent',
            'recentActivities',
            'scheduledSeminarsCount',
            'progressPercent'
        ));
    }
}
