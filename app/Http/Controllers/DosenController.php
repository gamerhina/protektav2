<?php

namespace App\Http\Controllers;

use App\Models\Seminar;
use App\Models\SeminarNilai;
use App\Support\PaginationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DosenController extends Controller
{
    public function dashboard(Request $request)
    {
        if (! Auth::guard('dosen')->check()) {
            return redirect()->route('login')->with('error', 'Please log in to access the dashboard.');
        }

        $dosen = Auth::guard('dosen')->user();

        $search = trim((string) $request->input('search', ''));

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
            ->orderBy('seminars.tanggal', 'desc')
            ->take(5) // Limit to 5 most recent for dashboard
            ->get();

        return view('dosen.dashboard', compact(
            'dosen',
            'seminarDitinjauCount',
            'nilaidiberikanCount',
            'mahasiswaBimbinganAkademikCount',
            'evalSeminars'
        ));
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
        } else {
            // Default: show PA students OR students evaluated by this dosen
            $mahasiswaQuery->where(function($q) use ($dosen) {
                $q->where('pembimbing_akademik_id', $dosen->id)
                  ->orWhereHas('seminars', function($sq) use ($dosen) {
                      $sq->where('p1_dosen_id', $dosen->id)
                         ->orWhere('p2_dosen_id', $dosen->id)
                         ->orWhere('pembahas_dosen_id', $dosen->id);
                  });
            });
        }

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
