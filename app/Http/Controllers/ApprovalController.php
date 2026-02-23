<?php

namespace App\Http\Controllers;

use App\Models\SuratApproval;
use App\Services\ApprovalWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    use Concerns\DetectsImpersonation;

    protected $workflowService;

    public function __construct(ApprovalWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    public function dashboard(Request $request)
    {
        $isImpersonating = session()->has('impersonated_by');
        
        // When impersonating, treat as the target guard, not admin
        if ($isImpersonating) {
            if (Auth::guard('dosen')->check()) {
                $user = Auth::guard('dosen')->user();
                $isAdmin = false;
            } elseif (Auth::guard('mahasiswa')->check()) {
                $user = Auth::guard('mahasiswa')->user();
                $isAdmin = false;
            } else {
                $user = Auth::user();
                $isAdmin = Auth::guard('admin')->check();
            }
        } else {
            $user = Auth::user();
            $isAdmin = Auth::guard('admin')->check();
        }
        
        $search = $request->query('search');
        $statusFilter = $request->query('status_filter');
        
        $allowedSorts = ['no_surat', 'tanggal_surat', 'status', 'created_at', 'surat_jenis_id'];
        $sort = $request->query('sort', 'created_at');
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }
        $direction = $request->query('direction', 'desc') === 'asc' ? 'asc' : 'desc';
        $perPage = $request->query('per_page', 20);

        if ($isAdmin) {
            // Admin sees:
            // 1. 'submitted' (diajukan) - Waiting for Admin Review
            // 2. 'disetujui_admin' - Waiting for Pimpinan/Approvers
            // 3. 'disetujui_pimpinan' - Ready for Stamping (or partial finished)
            $query = \App\Models\Surat::with(['jenis', 'pemohonDosen', 'pemohonMahasiswa'])
                ->where(function($q) {
                    $q->whereIn('approval_status', ['pending', 'in_progress', 'approved']) // Using valid enum statuses
                      ->orWhere('approval_status', 'pending'); // Legacy check
                });
                
        } else {
            // Dosen Approver
            $dosenId = $user->id;
            
            $query = \App\Models\Surat::whereHas('approvals', function($q) use ($dosenId) {
                    $q->where('dosen_id', $dosenId);
                })
                ->where('status', '!=', 'diajukan')
                ->with(['jenis', 'pemohonDosen', 'pemohonMahasiswa', 'approvals' => function($q) use ($dosenId) {
                    $q->where('dosen_id', $dosenId);
                }]);
        }

        // Apply filters
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('no_surat', 'like', "%$search%")
                  ->orWhere('perihal', 'like', "%$search%")
                  ->orWhere('tujuan', 'like', "%$search%")
                  ->orWhereHas('jenis', function($q) use ($search) {
                      $q->where('nama', 'like', "%$search%");
                  });
            });
        }

        if ($statusFilter) {
            if ($statusFilter === 'diajukan') {
                $query->whereIn('status', ['diajukan', 'submitted']);
            } elseif ($statusFilter === 'diproses') {
                $query->whereIn('status', ['diproses', 'approved_by_admin', 'approved_by_pimpinan']);
            } elseif ($statusFilter === 'selesai') {
                $query->whereIn('status', ['selesai', 'completed', 'approved']);
            } elseif ($statusFilter === 'ditolak') {
                $query->whereIn('status', ['ditolak', 'rejected']);
            } else {
                $query->where('status', $statusFilter);
            }
        }

        if ($sort === 'surat_jenis_id') {
            $query->join('surat_jenis', 'surats.surat_jenis_id', '=', 'surat_jenis.id')
                  ->orderBy('surat_jenis.nama', $direction)
                  ->select('surats.*');
        } else {
            $query->orderBy($sort, $direction);
        }

        $surats = $query->paginate($perPage)->withQueryString();

        return view('admin.approval.dashboard', compact('surats', 'isAdmin'));
    }

    /**
     * Show approval detail
     */
    public function show(SuratApproval $approval)
    {
        $this->authorize('view', $approval);

        // Jika surat memerlukan stamping PDF (uploaded), langsung arahkan ke editor stamping
        if ($approval->surat->jenis?->is_uploaded) {
            return redirect()->route('admin.approval.stamping.show', $approval);
        }

        $approval->load(['surat.jenis', 'surat.pemohonDosen', 'surat.pemohonMahasiswa', 'role', 'surat.comments.user', 'surat.approvals.role', 'surat.approvals.dosen']);
        $isAdmin = $this->isEffectivelyAdmin();

        return view('admin.approval.show', compact('approval', 'isAdmin'));
    }

    /**
     * Approve surat
     */
    public function approve(Request $request, SuratApproval $approval)
    {
        $this->authorize('approve', $approval);

        if (!$approval->isReady()) {
            return redirect()->back()->with('error', 'Anda belum dapat menyetujui karena masih ada tahap persetujuan sebelumnya yang belum selesai. Silakan tunggu giliran Anda.');
        }

        $validated = $request->validate([
            'signature' => 'nullable|string',
            'signature_type' => 'nullable|in:canvas,upload,qr',
            'catatan' => 'nullable|string|max:1000',
        ]);

        $success = $this->workflowService->approve($approval, $validated);

        if ($success) {
            return redirect()
                ->route('admin.approval.dashboard')
                ->with('success', 'Surat berhasil disetujui!');
        }

        return redirect()
            ->back()
            ->with('error', 'Gagal menyetujui surat.');
    }

    /**
     * Reject surat
     */
    public function reject(Request $request, SuratApproval $approval)
    {
        $this->authorize('reject', $approval);

        if (!$approval->isReady()) {
            return redirect()->back()->with('error', 'Anda belum dapat menolak karena masih ada tahap persetujuan sebelumnya yang belum selesai. Silakan tunggu giliran Anda.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $success = $this->workflowService->reject($approval, $validated['reason']);

        if ($success) {
            return redirect()
                ->route('admin.approval.dashboard')
                ->with('success', 'Surat berhasil ditolak.');
        }

        return redirect()
            ->back()
            ->with('error', 'Gagal menolak surat.');
    }

    /**
     * Show approval history for a surat
     */
    public function history($suratId)
    {
        $approvals = SuratApproval::where('surat_id', $suratId)
            ->with(['role', 'dosen'])
            ->ordered()
            ->get();

        return view('admin.approval.history', compact('approvals'));
    }
}
