<?php

namespace App\Http\Controllers;

use App\Models\Seminar;
use App\Models\SeminarNilai;
use App\Support\PaginationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exports\MahasiswaGraduationExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\ProgresSkripsiTrait;

class DosenController extends Controller
{
    use ProgresSkripsiTrait;

    public function dashboard(Request $request)
    {
        if (! Auth::guard('dosen')->check()) {
            return redirect()->route('login')->with('error', 'Please log in to access the dashboard.');
        }

        $dosen = Auth::guard('dosen')->user();

        $search = trim((string) $request->input('search', ''));
        $perPage = PaginationHelper::resolvePerPage($request, 5);

        $sortFields = [
            'mahasiswa' => 'mahasiswa.nama',
            'jenis' => 'seminar_jenis.nama',
            'tanggal' => 'seminars.tanggal',
            'status' => 'seminars.status',
            'created_at' => 'seminars.created_at',
        ];

        $sort = $request->input('sort', 'tanggal');
        if (! array_key_exists($sort, $sortFields)) {
            $sort = 'tanggal';
        }

        $direction = strtolower($request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $seminarDitinjauCount = Seminar::where(function ($query) use ($dosen) {
            $query->where('p1_dosen_id', $dosen->id)
                ->orWhere('p2_dosen_id', $dosen->id)
                ->orWhere('pembahas_dosen_id', $dosen->id);
        })->count();
        $nilaidiberikanCount = SeminarNilai::where('dosen_id', $dosen->id)->count();
        $mahasiswaBimbinganAkademikCount = \App\Models\Mahasiswa::where('pembimbing_akademik_id', $dosen->id)->count();

        // Query for active seminars (not finished) - for dashboard display
        $evalSeminarsQuery = Seminar::with(['mahasiswa', 'seminarJenis', 'nilai' => function ($query) use ($dosen) {
            $query->where('dosen_id', $dosen->id);
        }, 'signatures' => function ($query) use ($dosen) {
            $query->where('dosen_id', $dosen->id);
        }])
            ->select('seminars.*')
            ->leftJoin('mahasiswa', 'mahasiswa.id', '=', 'seminars.mahasiswa_id')
            ->leftJoin('seminar_jenis', 'seminar_jenis.id', '=', 'seminars.seminar_jenis_id')
            ->where(function ($query) {
                // Include approved seminars (disetujui) - ready for evaluation
                $query->where('status', 'disetujui')
                // OR incomplete seminars (belum_lengkap) - need completion
                    ->orWhere('status', 'belum_lengkap');
            })
            ->whereNotIn('status', ['diajukan', 'selesai']) // Exclude 'diajukan' (not ready) and 'selesai' (already complete)
            ->where(function ($query) use ($dosen) {
                $query
                    ->where(function ($q) use ($dosen) {
                        $q->where('p1_dosen_id', $dosen->id)
                            ->where('seminar_jenis.p1_required', 1);
                    })
                    ->orWhere(function ($q) use ($dosen) {
                        $q->where('p2_dosen_id', $dosen->id)
                            ->where('seminar_jenis.p2_required', 1);
                    })
                    ->orWhere(function ($q) use ($dosen) {
                        $q->where('pembahas_dosen_id', $dosen->id)
                            ->where('seminar_jenis.pembahas_required', 1);
                    });
            });

        if ($search !== '') {
            $like = "%{$search}%";
            $evalSeminarsQuery->where(function ($query) use ($like) {
                $query->where('seminars.judul', 'like', $like)
                    ->orWhere('mahasiswa.nama', 'like', $like)
                    ->orWhere('mahasiswa.npm', 'like', $like)
                    ->orWhere('seminar_jenis.nama', 'like', $like)
                    ->orWhere('seminars.status', 'like', $like);
            });
        }

        $evalSeminars = $evalSeminarsQuery
            ->orderBy($sortFields[$sort], $direction)
            ->paginate($perPage)
            ->withQueryString();

        $filterBimbingan = request('filter_bimbingan') == '1';

        // Lulus Tepat Waktu stats calculations
        $allPAQuery = \App\Models\Mahasiswa::with('seminars.seminarJenis');
        if ($filterBimbingan) {
            $allPAQuery->where('pembimbing_akademik_id', $dosen->id);
        }
        $allPAStudents = $allPAQuery->get();
        $tepatWaktuCount = 0;
        $tidakTepatWaktuCount = 0;
        $ongoingTepatWaktuCount = 0;
        $ongoingOverdueCount = 0;

        foreach ($allPAStudents as $student) {
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

        $chartData = $this->generateProgresChartData($allPAStudents);

        // Paginated student list
        $searchMhs = trim((string) request()->input('search_mhs', ''));
        $studentsQuery = \App\Models\Mahasiswa::query();
        if ($filterBimbingan) {
            $studentsQuery->where('pembimbing_akademik_id', $dosen->id);
        }
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

        return view('dosen.dashboard', compact(
            'dosen',
            'seminarDitinjauCount',
            'nilaidiberikanCount',
            'mahasiswaBimbinganAkademikCount',
            'evalSeminars',
            'perPage',
            'search',
            'tepatWaktuCount',
            'tidakTepatWaktuCount',
            'ongoingTepatWaktuCount',
            'ongoingOverdueCount',
            'studentsPaginated',
            'searchMhs',
            'chartData'
        ));
    }


    public function updateStudentTanggalLulus(Request $request, \App\Models\Mahasiswa $mahasiswa)
    {
        if (!Auth::guard('dosen')->check()) {
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
        if (!Auth::guard('dosen')->check()) {
            return redirect()->route('login')->with('error', 'Please log in to access this action.');
        }

        $dosen = Auth::guard('dosen')->user();
        return Excel::download(new MahasiswaGraduationExport($dosen->id), 'Data_Kelulusan_Bimbingan_Mahasiswa_' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Show evaluation tasks page
     */
    public function evaluasiIndex(Request $request)
    {
        if (! Auth::guard('dosen')->check()) {
            return redirect()->route('login')->with('error', 'Please log in to access dashboard.');
        }

        $dosen = Auth::guard('dosen')->user();

        $search = trim((string) $request->input('search', ''));
        $perPage = PaginationHelper::resolvePerPage($request, 10);

        $sortFields = [
            'mahasiswa' => 'mahasiswa.nama',
            'jenis' => 'seminar_jenis.nama',
            'tanggal' => 'seminars.tanggal',
            'status' => 'seminars.status',
            'created_at' => 'seminars.created_at',
        ];

        $sort = $request->input('sort', 'tanggal');
        if (! array_key_exists($sort, $sortFields)) {
            $sort = 'tanggal';
        }

        $direction = strtolower($request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        // Query for seminars where dosen needs to complete evaluation
        $evalSeminarsQuery = Seminar::with(['mahasiswa', 'seminarJenis', 'nilai' => function ($query) use ($dosen) {
            $query->where('dosen_id', $dosen->id);
        }, 'signatures' => function ($query) use ($dosen) {
            $query->where('dosen_id', $dosen->id);
        }])
            ->select('seminars.*')
            ->leftJoin('mahasiswa', 'mahasiswa.id', '=', 'seminars.mahasiswa_id')
            ->leftJoin('seminar_jenis', 'seminar_jenis.id', '=', 'seminars.seminar_jenis_id')
            ->where(function ($query) use ($dosen) {
                $query
                    ->where(function ($q) use ($dosen) {
                        $q->where('p1_dosen_id', $dosen->id)
                            ->where('seminar_jenis.p1_required', 1);
                    })
                    ->orWhere(function ($q) use ($dosen) {
                        $q->where('p2_dosen_id', $dosen->id)
                            ->where('seminar_jenis.p2_required', 1);
                    })
                    ->orWhere(function ($q) use ($dosen) {
                        $q->where('pembahas_dosen_id', $dosen->id)
                            ->where('seminar_jenis.pembahas_required', 1);
                    });
            })
            ->where(function ($query) use ($dosen) {
                // Include approved seminars (disetujui) - ready for evaluation
                $query->where('status', 'disetujui')
                // OR incomplete seminars (belum_lengkap) - need completion
                    ->orWhere('status', 'belum_lengkap')
                // OR finished seminars (selesai) where dosen hasn't completed both nilai AND signature
                    ->orWhere(function ($subQuery) use ($dosen) {
                        $subQuery->where('status', 'selesai')
                            ->whereDoesntHave('nilai', function ($nilaiQuery) use ($dosen) {
                                $nilaiQuery->where('dosen_id', $dosen->id);
                            })
                            ->orWhereDoesntHave('signatures', function ($signatureQuery) use ($dosen) {
                                $signatureQuery->where('dosen_id', $dosen->id);
                            });
                    });
            })
            ->whereNotIn('status', ['diajukan', 'selesai']); // Exclude 'diajukan' (not ready) and 'selesai' (already complete)

        if ($search !== '') {
            $like = "%{$search}%";
            $evalSeminarsQuery->where(function ($query) use ($like) {
                $query->where('seminars.judul', 'like', $like)
                    ->orWhere('mahasiswa.nama', 'like', $like)
                    ->orWhere('mahasiswa.npm', 'like', $like)
                    ->orWhere('seminar_jenis.nama', 'like', $like)
                    ->orWhere('seminars.status', 'like', $like);
            });
        }

        $evalSeminars = $evalSeminarsQuery
            ->orderBy($sortFields[$sort], $direction)
            ->paginate($perPage)
            ->withQueryString();

        return view('dosen.evaluasi.index', compact(
            'dosen',
            'evalSeminars',
            'perPage',
            'search'
        ));
    }

    /**
     * Show manage seminars page
     */
    public function manageSeminarIndex(Request $request)
    {
        if (! Auth::guard('dosen')->check()) {
            return redirect()->route('login')->with('error', 'Please log in to access dashboard.');
        }

        $dosen = Auth::guard('dosen')->user();

        $search = trim((string) $request->input('search', ''));
        $perPage = PaginationHelper::resolvePerPage($request, 10);

        $sortFields = [
            'mahasiswa' => 'mahasiswa.nama',
            'jenis' => 'seminar_jenis.nama',
            'tanggal' => 'seminars.tanggal',
            'status' => 'seminars.status',
            'created_at' => 'seminars.created_at',
        ];

        $sort = $request->input('sort', 'tanggal');
        if (! array_key_exists($sort, $sortFields)) {
            $sort = 'tanggal';
        }

        $direction = strtolower($request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        // Query for finished seminars only
        $finishedSeminarsQuery = Seminar::with(['mahasiswa', 'seminarJenis', 'nilai' => function ($query) use ($dosen) {
            $query->where('dosen_id', $dosen->id);
        }, 'signatures' => function ($query) use ($dosen) {
            $query->where('dosen_id', $dosen->id);
        }])
            ->select('seminars.*')
            ->leftJoin('mahasiswa', 'mahasiswa.id', '=', 'seminars.mahasiswa_id')
            ->leftJoin('seminar_jenis', 'seminar_jenis.id', '=', 'seminars.seminar_jenis_id')
            ->where('status', 'selesai') // Only show finished seminars
            ->where(function ($query) use ($dosen) {
                $query->where('p1_dosen_id', $dosen->id)
                    ->orWhere('p2_dosen_id', $dosen->id)
                    ->orWhere('pembahas_dosen_id', $dosen->id);
            });

        if ($search !== '') {
            $like = "%{$search}%";
            $finishedSeminarsQuery->where(function ($query) use ($like) {
                $query->where('seminars.judul', 'like', $like)
                    ->orWhere('mahasiswa.nama', 'like', $like)
                    ->orWhere('mahasiswa.npm', 'like', $like)
                    ->orWhere('seminar_jenis.nama', 'like', $like)
                    ->orWhere('seminars.status', 'like', $like);
            });
        }

        $finishedSeminars = $finishedSeminarsQuery
            ->orderBy($sortFields[$sort], $direction)
            ->paginate($perPage)
            ->withQueryString();

        return view('dosen.manage-seminar.index', compact(
            'dosen',
            'finishedSeminars',
            'perPage',
            'search'
        ));
    }

    /**
     * Show mahasiswa list for dosen (read-only view)
     */
    public function mahasiswaIndex(Request $request)
    {
        if (! Auth::guard('dosen')->check()) {
            return redirect()->route('login')->with('error', 'Please log in to access dashboard.');
        }

        $dosen = Auth::guard('dosen')->user();

        // Get all mahasiswa with their seminars (read-only)
        $mahasiswaQuery = \App\Models\Mahasiswa::with(['seminars' => function ($query) use ($dosen) {
            $query->where(function ($q) use ($dosen) {
                $q->where('p1_dosen_id', $dosen->id)
                    ->orWhere('p2_dosen_id', $dosen->id)
                    ->orWhere('pembahas_dosen_id', $dosen->id);
            });
        }, 'seminars.seminarJenis', 'pembimbingAkademik']);

        $filter = $request->input('filter');
        if ($filter === 'pa') {
            $mahasiswaQuery->where('pembimbing_akademik_id', $dosen->id);
        }
        // No else block needed as we want to show all students by default

        $search = trim((string) $request->input('search', ''));
        $perPage = PaginationHelper::resolvePerPage($request, 15);
        $sort = $request->input('sort', 'nama');
        $direction = $request->input('direction', 'asc');

        if ($search !== '') {
            $mahasiswaQuery->where(function ($query) use ($search) {
                $like = "%{$search}%";
                $query->where('nama', 'like', $like)
                    ->orWhere('npm', 'like', $like)
                    ->orWhere('email', 'like', $like);
            });
        }

        // Apply sorting
        if ($sort === 'nama') {
            $mahasiswaQuery->orderBy('nama', $direction);
        } elseif ($sort === 'npm') {
            $mahasiswaQuery->orderBy('npm', $direction);
        } elseif ($sort === 'email') {
            $mahasiswaQuery->orderBy('email', $direction);
        } else {
            $mahasiswaQuery->orderBy('nama', 'asc');
        }

        $mahasiswaData = $mahasiswaQuery->paginate($perPage)->withQueryString();

        return view('dosen.mahasiswa.index', compact('dosen', 'mahasiswaData', 'perPage', 'search', 'sort', 'direction', 'filter'));
    }
}
