<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Seminar;
use App\Models\SeminarNilai;
use App\Models\SeminarSignature;
use App\Models\Surat;
use Carbon\Carbon;

class LayoutComposer
{
    public function compose(View $view)
    {
        $user = null;
        $guard = null;

        $isImpersonating = session()->has('impersonated_by');
        $guardPriority = $isImpersonating ? ['dosen', 'mahasiswa', 'admin'] : ['admin', 'dosen', 'mahasiswa'];

        foreach ($guardPriority as $g) {
            if (Auth::guard($g)->check()) {
                $user = Auth::guard($g)->user();
                $guard = $g;
                break;
            }
        }
        
        $notifications = $this->buildNavbarNotifications($guard, $user);

        $view
            ->with('user', $user)
            ->with('guard', $guard)
            ->with('navbarNotifications', $notifications);
    }

    protected function buildNavbarNotifications(?string $guard, $user): array
    {
        if (!$user || !$guard) {
            return ['items' => [], 'count' => 0];
        }

        $realNotificationsQuery = $user->unreadNotifications()
            ->orderBy('created_at', 'desc')
            ->limit(10);

        // Filter out comment notifications for lecturers
        if ($guard === 'dosen') {
            $realNotificationsQuery->whereNotIn('type', [
                'App\Notifications\NewSeminarCommentNotification'
            ]);
        }

        $realNotifications = $realNotificationsQuery->get()
            ->map(function ($n) {
                return [
                    'key' => $n->id,
                    'title' => $this->formatNotificationTitle($n),
                    'message' => $n->data['message'] ?? '',
                    'url' => $n->data['action_url'] ?? '#',
                    'level' => 'info',
                    'is_real' => true,
                    'created_at' => $n->created_at->diffForHumans(),
                ];
            })->toArray();

        $virtualNotifications = match ($guard) {
            'admin' => $this->buildAdminNotifications(),
            'dosen' => $this->buildDosenNotifications($user),
            'mahasiswa' => $this->buildMahasiswaNotifications($user),
            default => ['items' => [], 'count' => 0],
        };

        // Merge real first, then virtual
        $mergedItems = array_merge($realNotifications, $virtualNotifications['items']);
        
        // Count should reflect both or just real? 
        // Let's use real count for the badge if available, otherwise virtual count
        if (count($realNotifications) > 0) {
            $countQuery = $user->unreadNotifications();
            if ($guard === 'dosen') {
                $countQuery->whereNotIn('type', [
                    'App\Notifications\NewSeminarCommentNotification'
                ]);
            }
            $count = $countQuery->count();
        } else {
            $count = $virtualNotifications['count'];
        }

        return [
            'items' => array_slice($mergedItems, 0, 15),
            'count' => $count
        ];
    }

    protected function formatNotificationTitle($n): string
    {
        $type = $n->type;
        if (str_contains($type, 'NewSeminarRegistration')) return 'Pendaftaran Seminar Baru';
        if (str_contains($type, 'SuratSubmitted')) return 'Pengajuan Surat Baru';
        if (str_contains($type, 'SuratStatusUpdated')) return 'Status Surat';
        if (str_contains($type, 'SeminarStatusUpdated')) return 'Status Seminar';
        return 'Notifikasi';
    }

    protected function buildMahasiswaNotifications($mahasiswa): array
    {
        $items = [];

        // Surat notifications (if mahasiswa is a pemohon)
        try {
            $surats = Surat::with('jenis')
                ->where('pemohon_type', 'mahasiswa')
                ->where('pemohon_mahasiswa_id', $mahasiswa->id)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

            foreach ($surats as $surat) {
                $jenisNama = $surat->jenis->nama ?? 'Surat';
                $noSurat = $surat->no_surat ? (' No: ' . $surat->no_surat) : '';

                if ($surat->status === 'diajukan') {
                    $items[] = [
                        'key' => 'surat_' . $surat->id . '_diajukan',
                        'title' => 'Permohonan Dikirim',
                        'message' => $jenisNama . ' sedang menunggu verifikasi Admin.',
                        'url' => route('mahasiswa.dashboard'),
                        'level' => 'info',
                    ];
                } elseif ($surat->status === 'diproses') {
                    $items[] = [
                        'key' => 'surat_' . $surat->id . '_diproses',
                        'title' => 'Sedang Diproses',
                        'message' => $jenisNama . ' sedang dikerjakan oleh Tim Admin.',
                        'url' => route('mahasiswa.dashboard'),
                        'level' => 'warning',
                    ];
                } elseif ($surat->status === 'dikirim') {
                    $items[] = [
                        'key' => 'surat_' . $surat->id . '_dikirim',
                        'title' => 'Surat Selesai',
                        'message' => $jenisNama . ' telah diterbitkan dan dikirim.',
                        'url' => route('mahasiswa.dashboard'),
                        'level' => 'success',
                    ];
                } elseif ($surat->status === 'ditolak') {
                    $items[] = [
                        'key' => 'surat_' . $surat->id . '_ditolak',
                        'title' => 'Permohonan Ditolak',
                        'message' => 'Mohon maaf, ' . $jenisNama . ' Anda tidak dapat disetujui.',
                        'url' => route('mahasiswa.dashboard'),
                        'level' => 'error',
                    ];
                }
            }
        } catch (\Throwable $e) { }

        $seminars = Seminar::with(['seminarJenis', 'nilai', 'signatures'])
            ->where('mahasiswa_id', $mahasiswa->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        foreach ($seminars as $seminar) {
            if ($seminar->status === 'diajukan') {
                $items[] = [
                    'key' => 'seminar_diajukan_' . $seminar->id,
                    'title' => 'Pendaftaran Terkirim',
                    'message' => 'Berkas seminar Anda sedang dalam proses peninjauan Admin.',
                    'url' => route('mahasiswa.dashboard') . '#seminar-saya',
                    'level' => 'info',
                ];
            }

            if ($seminar->status === 'disetujui' && !empty($seminar->no_surat)) {
                $items[] = [
                    'key' => 'seminar_disetujui_' . $seminar->id,
                    'title' => 'Pendaftaran Disetujui',
                    'message' => 'Lolos verifikasi Admin (No: ' . $seminar->no_surat . ')',
                    'url' => route('mahasiswa.dashboard') . '#seminar-saya',
                    'level' => 'success',
                ];
            }

            if (in_array($seminar->status, ['disetujui', 'selesai'], true) && $seminar->tanggal && $seminar->lokasi) {
                $items[] = [
                    'key' => 'seminar_jadwal_' . $seminar->id,
                    'title' => 'Jadwal Seminar',
                    'message' => 'Dilaksanakan pada ' . $seminar->tanggal->translatedFormat('d F Y') . ' di ' . $seminar->lokasi,
                    'url' => route('mahasiswa.dashboard') . '#seminar-saya',
                    'level' => 'info',
                ];
            }

            $expectedEvaluators = collect([$seminar->p1_dosen_id, $seminar->p2_dosen_id, $seminar->pembahas_dosen_id])->filter()->count();
            $filledScores = $seminar->nilai->unique('dosen_id')->count();

            if ($expectedEvaluators > 0 && $filledScores > 0 && $filledScores < $expectedEvaluators) {
                $items[] = [
                    'key' => 'seminar_penilaian_' . $seminar->id . '_' . $filledScores,
                    'title' => 'Proses Penilaian',
                    'message' => $filledScores . ' dari ' . $expectedEvaluators . ' Dosen telah memberikan nilai.',
                    'url' => route('mahasiswa.dashboard') . '#seminar-saya',
                    'level' => 'warning',
                ];
            }

            if ($seminar->status === 'selesai' || $seminar->nilai_sent_at) {
                $items[] = [
                    'key' => 'seminar_selesai_' . $seminar->id,
                    'title' => 'Nilai Akhir Terbit',
                    'message' => 'Selamat, nilai akhir seminar Anda sudah dapat dilihat.',
                    'url' => route('mahasiswa.dashboard') . '#seminar-saya',
                    'level' => 'success',
                ];
            }
        }

        $items = collect($items)
            ->unique(fn ($n) => trim(($n['title'] ?? '') . '|' . ($n['message'] ?? '')))
            ->take(8)
            ->values()
            ->all();

        return ['items' => $items, 'count' => count($items)];
    }

    protected function buildDosenNotifications($dosen): array
    {
        $items = [];

        try {
            $surats = Surat::with('jenis')
                ->where('pemohon_type', 'dosen')
                ->where('pemohon_dosen_id', $dosen->id)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

            foreach ($surats as $surat) {
                if ($surat->status === 'diajukan') {
                    $items[] = [
                        'key' => 'surat_' . $surat->id . '_diajukan',
                        'title' => 'Pengajuan Surat',
                        'message' => ($surat->jenis->nama ?? 'Surat') . ' Anda sedang dalam antrean verifikasi.',
                        'url' => route('dosen.surat.index'),
                        'level' => 'info',
                    ];
                } elseif ($surat->status === 'dikirim') {
                    $items[] = [
                        'key' => 'surat_' . $surat->id . '_dikirim',
                        'title' => 'Surat Terbit',
                        'message' => ($surat->jenis->nama ?? 'Surat') . ' sudah disetujui dan dikirim.',
                        'url' => route('dosen.surat.index'),
                        'level' => 'success',
                    ];
                }
            }
        } catch (\Throwable $e) { }

        $seminars = Seminar::with(['mahasiswa', 'nilai', 'signatures'])
            ->whereIn('status', ['disetujui', 'belum_lengkap', 'selesai'])
            ->where(function ($q) use ($dosen) {
                $q->where('p1_dosen_id', $dosen->id)
                    ->orWhere('p2_dosen_id', $dosen->id)
                    ->orWhere('pembahas_dosen_id', $dosen->id);
            })
            ->orderBy('tanggal')
            ->limit(10)
            ->get();

        foreach ($seminars as $seminar) {
            $roleLabel = ($seminar->p1_dosen_id === $dosen->id) ? 'Pembimbing 1' : (($seminar->p2_dosen_id === $dosen->id) ? 'Pembimbing 2' : 'Pembahas');
            $evaluatorType = ($seminar->p1_dosen_id === $dosen->id) ? 'p1' : (($seminar->p2_dosen_id === $dosen->id) ? 'p2' : 'pembahas');

            $canEvaluate = in_array($seminar->status, ['disetujui', 'selesai'], true);
            $isPast = $seminar->tanggal && Carbon::parse($seminar->tanggal)->isPast();
            $nilai = $seminar->nilai->firstWhere('dosen_id', $dosen->id);
            $signature = $seminar->signatures->where('dosen_id', $dosen->id)->where('jenis_penilai', $evaluatorType)->first();

            // 1. Tugas Baru
            if (!$isPast && !$nilai) {
                $items[] = [
                    'key' => 'dosen_tugas_' . $seminar->id,
                    'title' => 'Agenda Penguji',
                    'message' => 'Tugas ' . $roleLabel . ' untuk ' . ($seminar->mahasiswa->nama ?? 'Mahasiswa'),
                    'url' => route('dosen.evaluasi.index'),
                    'level' => 'info',
                ];
            }

            // 2. Perlu Nilai
            if ($canEvaluate && !$nilai) {
                $items[] = [
                    'key' => 'dosen_perlu_nilai_' . $seminar->id,
                    'title' => 'Input Nilai',
                    'message' => 'Mohon isi nilai ' . $roleLabel . ' untuk ' . ($seminar->mahasiswa->nama ?? '-'),
                    'url' => route('dosen.nilai.input', $seminar),
                    'level' => 'warning',
                ];
            }

            // 3. Perlu TTD
            if ($canEvaluate && $nilai && !$signature) {
                $items[] = [
                    'key' => 'dosen_perlu_ttd_' . $seminar->id,
                    'title' => 'Tanda Tangan Digital',
                    'message' => 'Nilai tersimpan, mohon lengkapi TTD Digital untuk ' . ($seminar->mahasiswa->nama ?? '-'),
                    'url' => route('dosen.signature.form', ['seminarId' => $seminar->id, 'evaluatorType' => $evaluatorType]),
                    'level' => 'info',
                ];
            }
        }

        $items = collect($items)
            ->unique(fn ($n) => trim(($n['title'] ?? '') . '|' . ($n['message'] ?? '')))
            ->take(10)
            ->values()
            ->all();

        return ['items' => $items, 'count' => count($items)];
    }

    protected function buildAdminNotifications(): array
    {
        $items = [];

        // 1. Pendaftaran Baru
        $pendingCount = Seminar::where('status', 'diajukan')->count();
        if ($pendingCount > 0) {
            $latest = Seminar::with('mahasiswa')->where('status', 'diajukan')->orderByDesc('created_at')->limit(3)->get();
            $names = $latest->pluck('mahasiswa.nama')->implode(', ');
            $items[] = [
                'key' => 'admin_pending_reg_' . $pendingCount,
                'title' => 'Pendaftaran Masuk',
                'message' => $pendingCount . ' mahasiswa baru menunggu verifikasi: ' . $names,
                'url' => '/admin/seminars?filter=diajukan',
                'level' => 'warning',
            ];
        }

        // 2. Tanda Tangan Dosen (Baru & Relevan)
        try {
            $recentSigs = \App\Models\SeminarSignature::with(['seminar.mahasiswa', 'dosen'])
                ->where('created_at', '>=', now()->subDays(3))
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

            foreach ($recentSigs as $sig) {
                $items[] = [
                    'key' => 'admin_sig_' . $sig->id,
                    'title' => 'TTD Dosen Masuk',
                    'message' => 'Dosen ' . ($sig->dosen->nama ?? 'Penguji') . ' telah menandatangani nilai ' . ($sig->seminar->mahasiswa->nama ?? 'Mahasiswa'),
                    'url' => '/admin/seminars?search=' . urlencode($sig->seminar->mahasiswa->nama ?? ''),
                    'level' => 'success',
                ];
            }
        } catch (\Throwable $e) { }

        // 3. Permohonan Surat
        $pendingSuratCount = Surat::where('status', 'diajukan')->count();
        if ($pendingSuratCount > 0) {
            $items[] = [
                'key' => 'admin_pending_surat_' . $pendingSuratCount,
                'title' => 'Draft Surat Baru',
                'message' => $pendingSuratCount . ' pengajuan surat baru perlu diproses.',
                'url' => route('admin.surat.index', ['status' => 'diajukan']),
                'level' => 'warning',
            ];
        }

        // 4. Perlu Jadwal
        $approvedWithoutSchedule = Seminar::where('status', 'disetujui')->where(fn($q) => $q->whereNull('tanggal')->orWhereNull('lokasi'))->count();
        if ($approvedWithoutSchedule > 0) {
            $items[] = [
                'key' => 'admin_perlu_jadwal_' . $approvedWithoutSchedule,
                'title' => 'Plotting Jadwal',
                'message' => $approvedWithoutSchedule . ' seminar telah disetujui & menunggu jadwal.',
                'url' => '/admin/seminars?filter=disetujui',
                'level' => 'info',
            ];
        }

        // 5. Siap Kirim Nilai Final
        $readyForFinalGrade = Seminar::where('status', 'disetujui')->whereNotNull('tanggal')->whereNull('nilai_sent_at')->whereHas('nilai')->count();
        if ($readyForFinalGrade > 0) {
            $items[] = [
                'key' => 'admin_siap_nilai_' . $readyForFinalGrade,
                'title' => 'Siap Kirim Nilai',
                'message' => $readyForFinalGrade . ' seminar telah selesai dinilai & siap diterbitkan.',
                'url' => '/admin/seminars?filter=ready',
                'level' => 'success',
            ];
        }

        return [
            'items' => collect($items)->unique('key')->take(12)->values()->all(),
            'count' => count($items)
        ];
    }
}
