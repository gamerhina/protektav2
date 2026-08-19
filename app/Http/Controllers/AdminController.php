<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Seminar;
use App\Models\DocumentTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exports\MahasiswaGraduationExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\ProgresSkripsiTrait;

class AdminController extends Controller
{
    use ProgresSkripsiTrait;

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
        
        $totalMonth = Seminar::whereMonth('created_at', now()->month)->count();
        $scheduledMonth = Seminar::whereMonth('created_at', now()->month)->whereNotNull('tanggal')->count();
        $progressPercent = $totalMonth > 0 ? round(($scheduledMonth / $totalMonth) * 100) : 100;

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

        return view('admin.dashboard', compact(
            'mahasiswaCount', 
            'dosenCount', 
            'seminarCount',
            'weeklyActivity',
            'labels',
            'trendPercent',
            'recentActivities',
            'scheduledSeminarsCount',
            'progressPercent',
            'tepatWaktuCount',
            'tidakTepatWaktuCount',
            'ongoingTepatWaktuCount',
            'ongoingOverdueCount',
            'studentsPaginated',
            'searchMhs',
            'chartData'
        ));
    }


    public function updateStudentTanggalLulus(Request $request, Mahasiswa $mahasiswa)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('login')->with('error', 'Please log in to access this action.');
        }

        $request->validate([
            'tanggal_lulus_manual' => 'nullable|date',
            'tanggal_mulai_kuliah_manual' => 'nullable|date',
        ]);

        $mahasiswa->tanggal_lulus_manual = $request->input('tanggal_lulus_manual');
        $mahasiswa->tanggal_mulai_kuliah_manual = $request->input('tanggal_mulai_kuliah_manual');
        $mahasiswa->save();

        return back()->with('success', 'Tanggal mulai kuliah dan kelulusan mahasiswa ' . $mahasiswa->nama . ' berhasil diperbarui.');
    }

    public function exportGraduation()
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('login')->with('error', 'Please log in to access this action.');
        }

        return Excel::download(new MahasiswaGraduationExport(), 'Data_Kelulusan_Mahasiswa_' . date('Y-m-d') . '.xlsx');
    }
}
