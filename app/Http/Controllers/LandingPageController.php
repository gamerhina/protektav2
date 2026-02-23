<?php

namespace App\Http\Controllers;

use App\Models\Seminar;
use App\Models\LandingPageSetting;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use Illuminate\Support\Carbon;

class LandingPageController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        // Set locale ke bahasa Indonesia
        app()->setLocale('id');
        Carbon::setLocale('id');
        
        $today = Carbon::today();

        $settings = LandingPageSetting::first();
        if (! $settings) {
            $settings = LandingPageSetting::create([
                'hero_title' => 'Pusat Informasi Seminar Protekta',
                'hero_subtitle' => 'Monitoring jadwal, status, serta ekosistem seminar dalam satu dashboard responsif.',
                'app_description' => 'Platform terpadu untuk mengelola seminar akademik.',
                'cta_label' => 'Daftar Sekarang',
                'cta_link' => '/login',
                'schedule_heading' => 'Jadwal Seminar Terbaru',
                'primary_color' => '#1d4ed8',
                'secondary_color' => '#0f172a',
                'accent_color' => '#f97316',
                'button_color' => '#0ea5e9',
            ]);
        }

        $seminarCount = Seminar::count();
        $completedSeminars = Seminar::where('status', 'selesai')->count();
        $schedule = Seminar::with(['seminarJenis', 'mahasiswa', 'p1Dosen', 'p2Dosen'])
            ->whereDate('tanggal', '>=', $today)
            ->orderBy('tanggal')
            ->orderBy('waktu_mulai')
            ->take(12)
            ->get()
            ->map(function (Seminar $seminar) {
                return [
                    'tanggal' => $seminar->tanggal ? $seminar->tanggal->translatedFormat('d F Y') : '-',
                    'tanggal_raw' => $seminar->tanggal ? $seminar->tanggal->format('Y-m-d') : null,
                    'waktu' => $seminar->waktu_mulai ? Carbon::parse($seminar->waktu_mulai)->format('H.i') . ' WIB' : 'TBA',
                    'waktu_raw' => $seminar->waktu_mulai ? Carbon::parse($seminar->waktu_mulai)->format('H:i') : null,
                    'judul' => $seminar->judul ?? '',
                    'jenis' => optional($seminar->seminarJenis)->nama ?? 'Umum',
                    'mahasiswa' => optional($seminar->mahasiswa)->nama ?? '-',
                    'pembimbing' => collect([
                        optional($seminar->p1Dosen)->nama,
                        optional($seminar->p2Dosen)->nama,
                    ])->filter()->implode(' & '),
                    'lokasi' => $seminar->lokasi ?? 'TBA',
                    'status' => ucfirst($seminar->status ?? 'diajukan'),
                ];
            });

        if ($schedule->isEmpty()) {
            $schedule = Seminar::with(['seminarJenis', 'mahasiswa', 'p1Dosen', 'p2Dosen'])
                ->orderByDesc('tanggal')
                ->orderByDesc('waktu_mulai')
                ->take(12)
                ->get()
                ->map(function (Seminar $seminar) {
                    return [
                        'tanggal' => $seminar->tanggal ? $seminar->tanggal->translatedFormat('d F Y') : '-',
                        'tanggal_raw' => $seminar->tanggal ? $seminar->tanggal->format('Y-m-d') : null,
                        'waktu' => $seminar->waktu_mulai ? Carbon::parse($seminar->waktu_mulai)->format('H.i') . ' WIB' : 'TBA',
                        'waktu_raw' => $seminar->waktu_mulai ? Carbon::parse($seminar->waktu_mulai)->format('H:i') : null,
                        'judul' => $seminar->judul ?? '',
                        'jenis' => optional($seminar->seminarJenis)->nama ?? 'Umum',
                        'mahasiswa' => optional($seminar->mahasiswa)->nama ?? '-',
                        'pembimbing' => collect([
                            optional($seminar->p1Dosen)->nama,
                            optional($seminar->p2Dosen)->nama,
                        ])->filter()->implode(' & '),
                        'lokasi' => $seminar->lokasi ?? 'TBA',
                        'status' => ucfirst($seminar->status ?? 'diajukan'),
                    ];
                });
        }

        $stats = [
            'seminar' => [
                'label' => 'Total Seminar',
                'value' => number_format($seminarCount, 0, ',', '.'),
                'helper' => 'Keseluruhan agenda terdata',
            ],
            'lulusan' => [
                'label' => 'Jumlah Lulusan',
                'value' => number_format($completedSeminars, 0, ',', '.'),
                'helper' => 'Seminar berstatus selesai',
            ],
            'dosen' => [
                'label' => 'Dosen Aktif',
                'value' => number_format(Dosen::count(), 0, ',', '.'),
                'helper' => 'Pengajar terdaftar',
            ],
            'mahasiswa' => [
                'label' => 'Mahasiswa',
                'value' => number_format(Mahasiswa::count(), 0, ',', '.'),
                'helper' => 'Peserta akademik',
            ],
        ];

        return view('landing', [
            'settings' => $settings,
            'stats' => $stats,
            'schedule' => $schedule,
        ]);
    }
}
