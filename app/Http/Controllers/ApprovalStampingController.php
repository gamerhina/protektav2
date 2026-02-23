<?php

namespace App\Http\Controllers;

use App\Models\SuratApproval;
use App\Models\Surat;
use App\Models\Dosen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ApprovalStampingController extends Controller
{
    use Concerns\DetectsImpersonation;

    public function index()
    {
        // Prioritize Dosen guard (likely Impersonation or actual Login)
        $dosen = Auth::guard('dosen')->user();
        
        if ($dosen) {
            $isAdmin = false; // Force view as Dosen if Dosen guard is active
        } else {
            $isAdmin = Auth::guard('admin')->check();
        }

        $query = SuratApproval::with(['surat.jenis', 'role', 'dosen', 'surat.pemohonDosen', 'surat.pemohonMahasiswa'])
            ->whereHas('surat.jenis', function($q) {
                $q->where('is_uploaded', true);
            });

        if (!$isAdmin) {
            if (!$dosen) {
                $user = Auth::user();
                $dosen = Dosen::where('email', $user->email)->first();
                if (!$dosen) abort(403, 'Profil Dosen tidak ditemukan.');
            }
            $query->where('dosen_id', $dosen->id)
                  ->whereHas('surat', function($q) {
                      $q->where('status', '!=', 'diajukan');
                  });
        }

        $pendingStampings = (clone $query)->pending()->paginate(10, ['*'], 'pending_page');
        $completedStampings = (clone $query)->where('status', SuratApproval::STATUS_APPROVED)->latest('approved_at')->paginate(10, ['*'], 'completed_page');

        return view('admin.approval.stamping.index', compact('pendingStampings', 'completedStampings', 'isAdmin'));
    }

    /**
     * Show the stamping interface for a specific approval.
     */
    public function show(SuratApproval $approval)
    {
        $this->authorize('view', $approval);
        
        $approval->load([
            'surat.jenis', 
            'role', 
            'dosen', 
            'surat.pemohonDosen', 
            'surat.pemohonMahasiswa', 
            'surat.pemohonAdmin',
            'surat.comments.user'
        ]);
        
        if (!$approval->surat->uploaded_pdf_path) {
            return redirect()->back()->with('error', 'Dokumen PDF tidak ditemukan untuk surat ini.');
        }

        $approvals = $approval->surat->approvals()->with(['role', 'dosen'])->orderBy('urutan')->get();
        $isAdmin = $this->isEffectivelyAdmin();

        return view('admin.approval.stamping.show', compact('approval', 'approvals', 'isAdmin'));
    }

    /**
     * Process the stamping.
     */
    public function stamp(Request $request, SuratApproval $approval)
    {
        $this->authorize('approve', $approval);

        // Block stamping if surat is rejected or completed
        $suratStatus = $approval->surat->status;
        if (in_array($suratStatus, ['ditolak', 'selesai'])) {
            $label = $suratStatus === 'ditolak' ? 'ditolak' : 'selesai';
            return redirect()->back()->with('error', "Surat sudah berstatus \"$label\", tidak dapat melakukan stamping.");
        }

        if (!$approval->isReady()) {
            return redirect()->back()->with('error', 'Anda belum dapat menyetujui atau menempelkan tanda tangan karena masih ada tahap persetujuan sebelumnya yang belum selesai. Silakan tunggu giliran Anda.');
        }

        // Check if we have bulk data from the fallback hidden input
        if ($request->filled('full_stamps_data')) {
            $stampsData = json_decode($request->input('full_stamps_data'), true);
            
            if (is_array($stampsData)) {
                 
                 // Group stamps by approval_id
                 // Group stamps by approval_id
                 $grouped = collect($stampsData)->groupBy('approval_id');
                 $surat = $approval->surat;
                 
                 // Get ALL approvals for this surat to handle deletions
                 $allSuratApprovals = SuratApproval::where('surat_id', $surat->id)->get();
         
                 $isAdmin = $this->isEffectivelyAdmin();
                 $dosen = Auth::guard('dosen')->user();

                 foreach ($allSuratApprovals as $currApproval) {
                     // Security: Only allow modifying own approval (unless Admin)
                     if (!$isAdmin) {
                         $currentUser = $dosen ?: (\App\Models\Dosen::where('email', Auth::user()->email)->first());
                         if (!$currentUser || $currApproval->dosen_id != $currentUser->id) {
                            continue;
                         }
                     }

                     $approvalId = $currApproval->id;
                     
                     // Check if this approval has stamps in the submitted data
                     if ($grouped->has($approvalId)) {
                        $items = $grouped->get($approvalId);
                        
                        // Get all QRs
                        $qrs = $items->where('type', 'qr')->values();
                        $mainQr = $qrs->shift(); // First QR is main
                        $extraQrs = $qrs; // Remaining are extras
                        
                        // Sanitize mainQr data
                        if ($mainQr) {
                            $sanitize = fn($val) => is_array($val) ? null : $val;
                            $mainQr = [
                                'x' => (float)($sanitize($mainQr['x']) ?? 50),
                                'y' => (float)($sanitize($mainQr['y']) ?? 50),
                                'width' => (int)($sanitize($mainQr['width']) ?? 120),
                                'height' => (int)($sanitize($mainQr['height']) ?? 120),
                                'page' => (int)($sanitize($mainQr['page']) ?? 1),
                            ];
                        }

                        // Merge extra QRs with non-QR items
                        $sanitize = fn($val) => is_array($val) ? null : $val;
                        $extras = $items->where('type', '!=', 'qr')->concat($extraQrs)->map(function($item) use ($sanitize) {
                            // Skip stamp entirely if it has any array values
                            foreach ($item as $value) {
                                if (is_array($value)) {
                                    return null; // Skip this stamp
                                }
                            }
                            
                            return [
                                'type' => $sanitize($item['type'] ?? null),
                                'text' => $sanitize($item['text'] ?? null),
                                'role_nama' => $sanitize($item['role_nama'] ?? ($item['text'] ?? null)),
                                'nip' => $sanitize($item['nip'] ?? null),
                                'custom_role' => $sanitize($item['custom_role'] ?? null),
                                'key' => $sanitize($item['key'] ?? null),
                                'x' => (float)($sanitize($item['x'] ?? 0) ?? 0),
                                'y' => (float)($sanitize($item['y'] ?? 0) ?? 0),
                                'width' => max(0, (int)($sanitize($item['width'] ?? 0) ?? 0)),
                                'height' => max(0, (int)($sanitize($item['height'] ?? 0) ?? 0)),
                                'font' => $sanitize($item['font'] ?? 'Arial'),
                                'fontSize' => (int)($sanitize($item['fontSize'] ?? 10) ?? 10),
                                'page' => (int)($sanitize($item['page'] ?? 1) ?? 1),
                                'isBold' => !empty($sanitize($item['isBold'] ?? false)),
                                'isItalic' => !empty($sanitize($item['isItalic'] ?? false)),
                                'isUnderline' => !empty($sanitize($item['isUnderline'] ?? false)),
                            ];
                        })->filter()->values()->all();
            
                        if ($mainQr) {
                            // Validate mainQr dimensions before saving
                            if (!isset($mainQr['width']) || $mainQr['width'] <= 0) $mainQr['width'] = 120;
                            if (!isset($mainQr['height']) || $mainQr['height'] <= 0) $mainQr['height'] = 120;
                            
                            $currApproval->update([
                                'stamp_x' => $mainQr['x'],
                                'stamp_y' => $mainQr['y'],
                                'stamp_width' => $mainQr['width'],
                                'stamp_height' => $mainQr['height'],
                                'stamp_page' => $mainQr['page'] ?? 1,
                                'status' => SuratApproval::STATUS_APPROVED,
                                'approved_at' => $currApproval->approved_at ?: now(),
                                'is_stamped' => true,
                                'additional_stamps' => $extras,
                            ]);
                        } else {
                            // Group exists, BUT QR is missing.
                            // If there are ANY extras, we want to stamp them but HIDE QR.
                            if (count($extras) > 0) {
                                $currApproval->update([
                                    'is_stamped' => true, // Still marked as stamped
                                    'stamp_width' => 0,   // Signal to hide QR
                                    'stamp_height' => 0,
                                    'additional_stamps' => $extras,
                                ]); 
                            } else {
                                // No QR, No Extras -> Truly deleted
                                $currApproval->update([
                                    'status' => SuratApproval::STATUS_PENDING,
                                    'approved_at' => null,
                                    'is_stamped' => false,
                                    'stamp_x' => 0,
                                    'stamp_y' => 0,
                                    'stamp_width' => 0,
                                    'stamp_height' => 0,
                                    'stamp_page' => 1,
                                    'additional_stamps' => null, 
                                ]);
                            }
                        }
                     } else {
                         // Approval is missing from submitted stamps -> It was DELETED
                         // Reset stamp status so it doesn't appear on PDF
                         $currApproval->update([
                             'status' => SuratApproval::STATUS_PENDING,
                             'approved_at' => null,
                             'is_stamped' => false,
                             'stamp_x' => 0,
                             'stamp_y' => 0,
                             'stamp_width' => 0,
                             'stamp_height' => 0,
                             'stamp_page' => 1,
                             'additional_stamps' => null,
                         ]);
                     }
                 }
                 
                // Process PDF - Always regenerate even if no stamps remain (to clear them)
                 try {
                    // We can pass any approval to process() as it just needs the surat reference
                    // Use the current approval object
                    app(\App\Services\PdfStampingService::class)->process($approval);
                    app(\App\Services\ApprovalWorkflowService::class)->checkAndSyncStatus($surat);
                } catch (\Exception $e) {
                    return redirect()
                        ->route('admin.surat.show', $surat)
                        ->with('error', 'Gagal memproses PDF: ' . $e->getMessage());
                }

                return redirect()
                    ->route('admin.surat.preview', $surat)
                    ->with('success', 'Semua tanda tangan berhasil dibubuhkan!');
            }
        }

        // --- Legacy Single Stamp Logic (Fallback) ---

        $validated = $request->validate([
            'x' => 'required|numeric',
            'y' => 'required|numeric',
            'width' => 'nullable|numeric|min:20',
            'height' => 'nullable|numeric|min:20',
            'page' => 'required|integer|min:1',
            'signature_type' => 'required|in:canvas,qr',
            'additional_stamps' => 'nullable|json',
        ]);

        // Logic for actually stamping the PDF will be implemented in a Service
        // For now, we save the coordinates and mark as pending for the backend service
        $additionalStamps = $request->input('additional_stamps') ? json_decode($request->input('additional_stamps'), true) : [];
        
        // Sanitize additional stamps - remove arrays and ensure scalar values
        $sanitize = fn($val) => is_array($val) ? null : $val;
        $additionalStamps = collect($additionalStamps)->map(function($stamp) use ($sanitize) {
            // Skip stamp entirely if it has any array values
            foreach ($stamp as $value) {
                if (is_array($value)) {
                    return null; // Skip this stamp
                }
            }
            
            return [
                'type' => $sanitize($stamp['type'] ?? null),
                'text' => $sanitize($stamp['text'] ?? null),
                'role_nama' => $sanitize($stamp['role_nama'] ?? null),
                'nip' => $sanitize($stamp['nip'] ?? null),
                'custom_role' => $sanitize($stamp['custom_role'] ?? null),
                'key' => $sanitize($stamp['key'] ?? null),
                'x' => (float)($sanitize($stamp['x'] ?? 0) ?? 0),
                'y' => (float)($sanitize($stamp['y'] ?? 0) ?? 0),
                'width' => max(0, (int)($sanitize($stamp['width'] ?? 0) ?? 0)),
                'height' => max(0, (int)($sanitize($stamp['height'] ?? 0) ?? 0)),
                'font' => $sanitize($stamp['font'] ?? 'Arial'),
                'fontSize' => (int)($sanitize($stamp['fontSize'] ?? 10) ?? 10),
                'page' => (int)($sanitize($stamp['page'] ?? 1) ?? 1),
                'isBold' => !empty($sanitize($stamp['isBold'] ?? false)),
                'isItalic' => !empty($sanitize($stamp['isItalic'] ?? false)),
                'isUnderline' => !empty($sanitize($stamp['isUnderline'] ?? false)),
            ];
        })->filter()->values()->all();

        $approval->update([
            'stamp_x' => $validated['x'],
            'stamp_y' => $validated['y'],
            // Ensure width and height are valid
            'stamp_width' => isset($validated['width']) && $validated['width'] > 0 ? $validated['width'] : 120,
            'stamp_height' => isset($validated['height']) && $validated['height'] > 0 ? $validated['height'] : 120,
            'stamp_page' => $validated['page'],
            'status' => SuratApproval::STATUS_APPROVED,
            'approved_at' => now(),
            'is_stamped' => true,
            'additional_stamps' => $additionalStamps,
        ]);

        // Process the actual PDF stamping
        try {
            app(\App\Services\PdfStampingService::class)->process($approval);
            
            // Sync Surat status if it was the last approval
            app(\App\Services\ApprovalWorkflowService::class)->checkAndSyncStatus($approval->surat);
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.approval.stamping.index')
                ->with('error', 'Tanda tangan berhasil disimpan di database, namun gagal membubuhkan ke PDF: ' . $e->getMessage());
        }

        return redirect()
            ->route('admin.surat.preview', $approval->surat)
            ->with('success', 'Tanda tangan berhasil dibubuhkan!');

    }

    public function bulkStamp(Request $request, Surat $surat)
    {
        // Only Admin or designated roles should probably do this
        if (!$this->isEffectivelyAdmin()) {
            abort(403, 'Hanya Admin yang dapat melakukan pembubuhan masal.');
        }

        $validated = $request->validate([
            'stamps' => 'required|json',
        ]);

        $stampsData = json_decode($validated['stamps'], true);
        
        // Group stamps by approval_id
        // Group stamps by approval_id
        $grouped = collect($stampsData)->groupBy('approval_id');
        
        $allSuratApprovals = SuratApproval::where('surat_id', $surat->id)->get();

        foreach ($allSuratApprovals as $approval) {
             $approvalId = $approval->id;

             if ($grouped->has($approvalId)) {
                $items = $grouped->get($approvalId);
                
                // Get all QRs
                $qrs = $items->where('type', 'qr')->values();
                $mainQr = $qrs->shift(); // First QR is main
                $extraQrs = $qrs; // Remaining are extras
                
                // Sanitize mainQr data
                if ($mainQr) {
                    $sanitize = fn($val) => is_array($val) ? null : $val;
                    $mainQr = [
                        'x' => (float)($sanitize($mainQr['x']) ?? 50),
                        'y' => (float)($sanitize($mainQr['y']) ?? 50),
                        'width' => (int)($sanitize($mainQr['width']) ?? 120),
                        'height' => (int)($sanitize($mainQr['height']) ?? 120),
                        'page' => (int)($sanitize($mainQr['page']) ?? 1),
                    ];
                }

                // Merge extra QRs with non-QR items
                $sanitize = fn($val) => is_array($val) ? null : $val;
                $extras = $items->where('type', '!=', 'qr')->concat($extraQrs)->map(function($item) use ($sanitize) {
                    // Skip stamp entirely if it has any array values
                    foreach ($item as $value) {
                        if (is_array($value)) {
                            return null; // Skip this stamp
                        }
                    }
                    
                    return [
                        'type' => $sanitize($item['type'] ?? null),
                        'text' => $sanitize($item['text'] ?? null),
                        'role_nama' => $sanitize($item['role_nama'] ?? ($item['text'] ?? null)),
                        'nip' => $sanitize($item['nip'] ?? null),
                        'custom_role' => $sanitize($item['custom_role'] ?? null),
                        'key' => $sanitize($item['key'] ?? null),
                        'x' => (float)($sanitize($item['x'] ?? 0) ?? 0),
                        'y' => (float)($sanitize($item['y'] ?? 0) ?? 0),
                        'width' => max(0, (int)($sanitize($item['width'] ?? 0) ?? 0)),
                        'height' => max(0, (int)($sanitize($item['height'] ?? 0) ?? 0)),
                        'font' => $sanitize($item['font'] ?? 'Arial'),
                        'fontSize' => (int)($sanitize($item['fontSize'] ?? 10) ?? 10),
                        'page' => (int)($sanitize($item['page'] ?? 1) ?? 1),
                        'isBold' => !empty($sanitize($item['isBold'] ?? false)),
                        'isItalic' => !empty($sanitize($item['isItalic'] ?? false)),
                        'isUnderline' => !empty($sanitize($item['isUnderline'] ?? false)),
                    ];
                })->filter()->values()->all();
    
                if ($mainQr) {
                    // Validate mainQr dimensions before saving
                    if (!isset($mainQr['width']) || $mainQr['width'] <= 0) $mainQr['width'] = 120;
                    if (!isset($mainQr['height']) || $mainQr['height'] <= 0) $mainQr['height'] = 120;
                    
                    $approval->update([
                        'stamp_x' => $mainQr['x'],
                        'stamp_y' => $mainQr['y'],
                        'stamp_width' => $mainQr['width'],
                        'stamp_height' => $mainQr['height'],
                        'stamp_page' => $mainQr['page'] ?? 1,
                        'status' => SuratApproval::STATUS_APPROVED,
                        'approved_at' => $approval->approved_at ?: now(),
                        'is_stamped' => true,
                        'additional_stamps' => $extras,
                    ]);
                } else {
                     // Group exists but QR is missing (deleted)
                     if (count($extras) > 0) {
                         $approval->update([
                             'is_stamped' => true,
                             'stamp_width' => 0,
                             'stamp_height' => 0,
                             'additional_stamps' => $extras,
                         ]);
                     } else {
                         $approval->update([
                             'is_stamped' => false,
                             'additional_stamps' => null,
                         ]);
                     }
                }
             } else {
                 // Deleted
                 $approval->update([
                     'is_stamped' => false,
                     'additional_stamps' => null,
                 ]);
             }
        }

        // Process the actual PDF stamping - Always regenerate
        try {
            $anyApproval = $surat->approvals()->first();
            if ($anyApproval) {
                app(\App\Services\PdfStampingService::class)->process($anyApproval);
                app(\App\Services\ApprovalWorkflowService::class)->checkAndSyncStatus($surat);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'success' => true, 
            'message' => 'Semua tanda tangan berhasil dibubuhkan!',
            'redirect' => route('admin.surat.preview', $surat)
        ]);
    }
}
