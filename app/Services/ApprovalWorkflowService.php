<?php

namespace App\Services;

use App\Models\Surat;
use App\Models\SuratApproval;
use App\Models\SuratRoleAssignment;
use App\Models\Dosen;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ApprovalWorkflowService
{
    /**
     * Initiate approval workflow for a surat
     */
    public function initiate(Surat $surat): void
    {
        $assignments = $surat->jenis->workflowSteps;

        if ($assignments->isEmpty()) {
            // No approval flow configured
            $surat->update(['approval_status' => 'none']);
            \Illuminate\Support\Facades\Log::info("Approval workflow for Surat #{$surat->id}: No workflow steps found for Jenis #{$surat->surat_jenis_id}");
            return;
        }

        DB::transaction(function () use ($surat, $assignments) {
            // Create approval records if not exists
            foreach ($assignments as $assignment) {
                $exists = $surat->approvals()->where('urutan', $assignment->urutan)
                                           ->where('role_nama', $assignment->role_nama)
                                           ->exists();
                if ($exists) continue;

                $targetDosenId = $assignment->dosen_id;

                if (is_null($targetDosenId)) {
                    $targetDosenId = $this->resolveDynamicDosen($assignment->role_nama, $surat);
                }

                SuratApproval::create([
                    'surat_id' => $surat->id,
                    'role_nama' => $assignment->role_nama,
                    'dosen_id' => $targetDosenId,
                    'urutan' => $assignment->urutan,
                    'status' => SuratApproval::STATUS_PENDING,
                ]);
            }

            // Sync existing ones just in case
            $this->syncApprovals($surat);

            // Update surat status
            $surat->update([
                'approval_status' => 'pending',
                'status' => 'diajukan',
                'approval_initiated_at' => $surat->approval_initiated_at ?? now(),
            ]);
        });

        // Notify admins
        $admins = \App\Models\Admin::all();
        foreach ($admins as $admin) {
             $admin->notify(new \App\Notifications\NewSuratSubmissionNotification($surat));
        }
    }

    /**
     * Sync and resolve missing data in approvals (e.g. missing PA dosen_id or missing steps from template)
     */
    public function syncApprovals(Surat $surat): void
    {
        $assignments = $surat->jenis->workflowSteps;
        
        // 1. Add missing approvals from template
        foreach ($assignments as $assignment) {
            // Check if this role already exists for this surat
            // We use role_nama as the primary key for uniqueness in sync
            $exists = $surat->approvals()
                ->where('role_nama', $assignment->role_nama)
                ->exists();
                
            if (!$exists) {
                $targetDosenId = $assignment->dosen_id;
                
                // Resolve dynamic dosen if needed
                if (is_null($targetDosenId)) {
                    $targetDosenId = $this->resolveDynamicDosen($assignment->role_nama, $surat);
                }
                
                SuratApproval::create([
                    'surat_id' => $surat->id,
                    'role_nama' => $assignment->role_nama,
                    'dosen_id' => $targetDosenId,
                    'urutan' => $assignment->urutan,
                    'status' => SuratApproval::STATUS_PENDING,
                ]);
            } else {
                // Update urutan if it changed in template
                $surat->approvals()
                    ->where('role_nama', $assignment->role_nama)
                    ->update(['urutan' => $assignment->urutan]);
            }
        }

        // Deduplicate: If there are multiple approvals with exact same role_nama, keep one
        $allApprovals = $surat->approvals()->get()->groupBy('role_nama');
        foreach ($allApprovals as $roleName => $group) {
            if ($group->count() > 1) {
                // Keep the one that is approved, or the first one
                $toKeep = $group->firstWhere('status', 'approved') ?? $group->first();
                foreach ($group as $app) {
                    if ($app->id !== $toKeep->id) {
                        $app->delete();
                    }
                }
            }
        }

        // 2. Resolve missing dosen_id in existing approvals
        $approvals = $surat->approvals()->whereNull('dosen_id')->get();
        
        foreach ($approvals as $approval) {
            $resolvedId = $this->resolveDynamicDosen($approval->role_nama, $surat);
            
            if ($resolvedId) {
                $approval->update(['dosen_id' => $resolvedId]);
                
                // If the surat is already in proper state, notify the dosen
                if (in_array($surat->status, ['diajukan', 'diproses', 'dikirim'])) {
                    $dosen = Dosen::find($resolvedId);
                    if ($dosen && $approval->status === SuratApproval::STATUS_PENDING) {
                        try {
                            $dosen->notify(new \App\Notifications\SuratApprovalRequestNotification($approval));
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error("Failed to notify Dosen for Surat #{$surat->id}: " . $e->getMessage());
                        }
                    }
                }
            }
        }
    }

    /**
     * Approve a surat approval
     */
    public function approve(SuratApproval $approval, array $data): bool
    {
        if (!$approval->isPending()) {
            return false;
        }

        DB::transaction(function () use ($approval, $data) {
            // Save signature if provided
            if (isset($data['signature'])) {
                $signaturePath = $this->saveSignature($data['signature'], $approval->id);
                $data['signature_path'] = $signaturePath;
            }

            // Update approval
            $approval->update([
                'status' => SuratApproval::STATUS_APPROVED,
                'approved_at' => now(),
                'signature_type' => $data['signature_type'] ?? SuratApproval::SIGNATURE_CANVAS,
                'signature_path' => $data['signature_path'] ?? null,
                'qr_code_url' => $data['qr_code_url'] ?? null,
                'catatan' => $data['catatan'] ?? null,
            ]);

            $this->checkAndSyncStatus($approval->surat);
        });

        return true;
    }

    /**
     * Check if all approvals are complete and update surat status
     */
    public function checkAndSyncStatus(Surat $surat): void
    {
        $pendingCount = $surat->approvals()->pending()->count();

        if ($pendingCount === 0) {
            // All approved!
            $surat->update([
                'approval_status' => 'approved',
                'status' => 'selesai', 
                'approved_at' => now(),
            ]);

            $this->notifyStakeholders($surat, 'selesai');
        } else {
            // Revert back to diproses if it was 'selesai'
            if ($surat->status === 'selesai') {
                $surat->update([
                    'approval_status' => 'in_progress',
                    'status' => 'diproses',
                    'approved_at' => null,   
                ]);
            }
            // Notify next approver
            $this->notifyNextApprover($surat);
        }
    }

    /**
     * Reject a surat approval
     */
    public function reject(SuratApproval $approval, string $reason): bool
    {
        if (!$approval->isPending()) {
            return false;
        }

        DB::transaction(function () use ($approval, $reason) {
            $approval->update([
                'status' => SuratApproval::STATUS_REJECTED,
                'rejected_at' => now(),
                'catatan' => $reason,
            ]);

            // Update surat status
            $surat = $approval->surat;
            $previousStatus = $surat->status;
            
            $surat->update([
                'approval_status' => 'rejected',
                'status' => 'ditolak', // Sync with main status
                'rejected_at' => now(),
            ]);

            // Notify requester about rejection
            $this->notifyStakeholders($surat, 'rejected', $previousStatus);
        });

        return true;
    }

    /**
     * Get next approver
     */
    public function getNextApprover(Surat $surat): ?SuratApproval
    {
        return $surat->approvals()
            ->pending()
            ->ordered()
            ->first();
    }

    /**
     * Get pending approvals for a dosen
     */
    public function getPendingApprovalsForDosen(Dosen $dosen)
    {
        return SuratApproval::pending()
            ->forDosen($dosen->id)
            ->whereHas('surat', function($q) {
                $q->where('status', 'diproses');
            })
            // Only show if there are no pending approvals with a smaller urutan for this surat
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('surat_approvals as sa2')
                    ->whereColumn('sa2.surat_id', 'surat_approvals.surat_id')
                    ->where('sa2.status', SuratApproval::STATUS_PENDING)
                    ->whereColumn('sa2.urutan', '<', 'surat_approvals.urutan');
            })
            ->with(['surat.jenis', 'surat.pemohonMahasiswa', 'surat.pemohonDosen'])
            ->ordered()
            ->get();
    }

    /**
     * Save signature file
     */
    private function saveSignature(string $signatureData, int $approvalId): string
    {
        // Handle base64 signature
        if (str_starts_with($signatureData, 'data:image')) {
            $image = str_replace('data:image/png;base64,', '', $signatureData);
            $image = str_replace(' ', '+', $image);
            $imageName = 'approval_signature_' . $approvalId . '_' . time() . '.png';
            
            Storage::disk('uploads')->put('signatures/' . $imageName, base64_decode($image));
            
            return 'signatures/' . $imageName;
        }

        return '';
    }

    /**
     * Notify stakeholders about final status (approved/rejected)
     */
    private function notifyStakeholders(Surat $surat, string $action, string $previousStatus = 'diproses'): void
    {
        $pemohon = $surat->pemohonDosen ?? $surat->pemohonMahasiswa;
        
        if ($pemohon) {
            $pemohon->notify(new \App\Notifications\SuratStatusUpdatedNotification($surat, $previousStatus));
        }

        // Also notify admins if fully approved or rejected
        if ($action === 'selesai' || $action === 'rejected') {
            $admins = \App\Models\Admin::all();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\SuratStatusUpdatedNotification($surat, $previousStatus));
            }
        }
    }

    /**
     * Notify next approver
     */
    public function notifyNextApprover(Surat $surat): void
    {
        // Only notify if status is specifically 'diproses'
        if ($surat->status !== 'diproses') {
            return;
        }

        // Find the lowest urutan that still has pending approvals
        $lowestPendingUrutan = $surat->approvals()
            ->pending()
            ->min('urutan');

        if ($lowestPendingUrutan === null) {
            return;
        }

        // Get all pending approvals at this urutan level
        $nextApprovals = $surat->approvals()
            ->pending()
            ->where('urutan', $lowestPendingUrutan)
            ->get();
            
        foreach ($nextApprovals as $approval) {
            if ($approval->dosen) {
                $approval->dosen->notify(new \App\Notifications\SuratApprovalRequestNotification($approval));
            }
        }
    }

    /**
     * Get approval statistics for dashboard
     */
    public function getApprovalStats(Dosen $dosen): array
    {
        return [
            'pending' => SuratApproval::pending()
                ->forDosen($dosen->id)
                ->whereHas('surat', function($q) {
                    $q->where('status', 'diproses');
                })
                ->count(),
            'approved_today' => SuratApproval::approved()
                ->forDosen($dosen->id)
                ->whereDate('approved_at', today())
                ->count(),
            'total_approved' => SuratApproval::approved()->forDosen($dosen->id)->count(),
            'total_rejected' => SuratApproval::rejected()->forDosen($dosen->id)->count(),
        ];
    }

    /**
     * Resolve dynamic dosen based on role name and surat context
     */
    private function resolveDynamicDosen(?string $roleNama, Surat $surat): ?int
    {
        if (!$roleNama || $surat->pemohon_type !== 'mahasiswa' || !$surat->mahasiswa) {
            return null;
        }

        $lowRole = strtolower($roleNama);
        $student = $surat->mahasiswa;

        // 1. Pembimbing Akademik
        if (str_contains($lowRole, 'pembimbing a')) {
            return $student->pembimbing_akademik_id;
        }

        // 2. Seminar Roles (Pembimbing 1, 2, Pembahas)
        // We look for the latest seminar of this student
        $latestSeminar = $student->seminars()->latest()->first();
        if ($latestSeminar) {
            if (str_contains($lowRole, 'pembimbing 1')) {
                return $latestSeminar->p1_dosen_id;
            }
            if (str_contains($lowRole, 'pembimbing 2')) {
                return $latestSeminar->p2_dosen_id;
            }
            if (str_contains($lowRole, 'pembahas')) {
                return $latestSeminar->pembahas_dosen_id;
            }
        }

        return null;
    }
}
