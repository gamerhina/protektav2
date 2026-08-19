<?php

namespace App\Http\Controllers;

use App\Models\Seminar;
use App\Models\LandingPageSetting;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use Illuminate\Support\Carbon;
use App\Traits\ProgresSkripsiTrait;

class LandingPageController extends Controller
{
    use ProgresSkripsiTrait;

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

        // Lulus Tepat Waktu stats calculations
        $allStudents = Mahasiswa::with('seminars.seminarJenis')->get();
        $tepatWaktuCount = 0;
        $tidakTepatWaktuCount = 0;
        $ongoingTepatWaktuCount = 0;
        $ongoingOverdueCount = 0;

        foreach ($allStudents as $student) {
            $startDate = $student->getTanggalMulaiKuliah();
            $endDate = $student->getTanggalLulus();
            $isTepatWaktu = $student->isLulusTepatWaktu();
            $limitDate = $startDate ? $startDate->copy()->addYears(4) : null;

            if ($startDate) {
                if ($endDate) {
                    if ($isTepatWaktu) {
                        $tepatWaktuCount++;
                    } else {
                        $tidakTepatWaktuCount++;
                    }
                } else {
                    $now = now()->startOfDay();
                    if ($limitDate && $now->lessThanOrEqualTo($limitDate)) {
                        $ongoingTepatWaktuCount++;
                    } else {
                        $ongoingOverdueCount++;
                    }
                }
            }
        }

        $chartData = $this->generateProgresChartData($allStudents);

        // Paginated student list
        $searchMhs = trim((string) request()->input('search_mhs', ''));
        $studentsQuery = Mahasiswa::query();
        if ($searchMhs !== '') {
            $like = "%{$searchMhs}%";
            $studentsQuery->where(function($q) use ($like) {
                $q->where('nama', 'like', $like)
                  ->orWhere('npm', 'like', $like);
            });
        }
        
        $sort = request('sort', 'npm');
        $direction = request('direction', 'desc');
        $statusFilter = request('status_filter');
        
        $needsMemoryProcessing = in_array($sort, ['tgl_lulus', 'status']) || !empty($statusFilter);
        
        if (!$needsMemoryProcessing && in_array($sort, ['nama', 'npm'])) {
            $studentsQuery->orderBy($sort, $direction);
            $studentsPaginated = $studentsQuery->paginate(5, ['*'], 'page_mhs')->withQueryString();
        } else {
            // For computed properties or filters, fetch and process collection
            $allFiltered = $studentsQuery->with('seminars.seminarJenis')->get();
            
            if ($statusFilter) {
                $allFiltered = $allFiltered->filter(function($student) use ($statusFilter) {
                    $startDate = $student->getTanggalMulaiKuliah();
                    $endDate = $student->getTanggalLulus();
                    $status = null;
                    if ($startDate && $endDate) {
                        $status = $student->isLulusTepatWaktu() ? 'tepat_waktu' : 'terlambat';
                    } elseif ($startDate) {
                        $limitDate = $startDate->copy()->addYears(4);
                        $status = now()->startOfDay()->lessThanOrEqualTo($limitDate) ? 'aktif_aman' : 'aktif_kritis';
                    }
                    return $status === $statusFilter;
                });
            }
            
            if ($sort === 'tgl_lulus') {
                $allFiltered = $allFiltered->sortBy(function($m) use ($direction) {
                    $tgl = $m->getTanggalLulus();
                    return $tgl ? $tgl->timestamp : ($direction === 'asc' ? 9999999999 : -1);
                }, SORT_REGULAR, $direction === 'desc');
            } elseif ($sort === 'status') {
                $getStatusRank = function($student) {
                    $startDate = $student->getTanggalMulaiKuliah();
                    $endDate = $student->getTanggalLulus();
                    if ($startDate && $endDate) {
                        return $student->isLulusTepatWaktu() ? 1 : 2;
                    } elseif ($startDate) {
                        $limitDate = $startDate->copy()->addYears(4);
                        return now()->startOfDay()->lessThanOrEqualTo($limitDate) ? 3 : 4;
                    }
                    return 5;
                };
                $allFiltered = $allFiltered->sortBy($getStatusRank, SORT_REGULAR, $direction === 'desc');
            } elseif (in_array($sort, ['nama', 'npm'])) {
                $allFiltered = $allFiltered->sortBy($sort, SORT_REGULAR, $direction === 'desc');
            }
            
            $allFiltered = $allFiltered->values();
            $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage('page_mhs');
            $perPage = 5;
            $currentItems = $allFiltered->slice(($currentPage - 1) * $perPage, $perPage)->all();
            
            $studentsPaginated = new \Illuminate\Pagination\LengthAwarePaginator(
                $currentItems, 
                $allFiltered->count(), 
                $perPage, 
                $currentPage, 
                [
                    'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                    'pageName' => 'page_mhs',
                ]
            );
            $studentsPaginated->withQueryString();
        }

        return view('landing', [
            'settings' => $settings,
            'stats' => $stats,
            'schedule' => $schedule,
            'tepatWaktuCount' => $tepatWaktuCount,
            'tidakTepatWaktuCount' => $tidakTepatWaktuCount,
            'ongoingTepatWaktuCount' => $ongoingTepatWaktuCount,
            'ongoingOverdueCount' => $ongoingOverdueCount,
            'studentsPaginated' => $studentsPaginated,
            'searchMhs' => $searchMhs,
            'chartData' => $chartData,
        ]);
    }
}
