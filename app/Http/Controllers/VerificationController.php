<?php

namespace App\Http\Controllers;

use App\Models\Seminar;
use App\Models\Surat;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    /**
     * Verify a seminar document via QR code.
     */
    public function verifySeminar($seminarId)
    {
        $seminar = Seminar::find($seminarId);
        
        if (!$seminar) {
            return response()->view('verification.not-found', [
                'message' => 'Seminar tidak ditemukan',
                'type' => 'seminar'
            ], 404);
        }
        
        $seminar->load(['mahasiswa', 'seminarJenis', 'p1Dosen', 'p2Dosen', 'pembahasDosen', 'signatures']);
        
        // Generate the rendered HTML preview
        $previewHtml = null;
        $templateId = request('template');
        if ($templateId) {
            $template = \App\Models\DocumentTemplate::find($templateId);
        } else {
            $template = $seminar->seminarJenis?->documentTemplates()?->where('aktif', true)->first();
        }
        
        if ($template && $template->template_html) {
            $pdfService = app(\App\Services\PdfGeneratorService::class);
            $renderedHtml = $pdfService->replaceSeminarTags($template->template_html, $seminar, $template);
            $previewHtml = $pdfService->renderHtml($renderedHtml, $template->header_image_path, [
                'header_repeat' => $template->header_repeat ?? false,
                'header_visibility' => $template->header_visibility ?? 'all',
            ]);
        }
        
        return view('verification.seminar', compact('seminar', 'previewHtml'));
    }

    /**
     * Verify a seminar signature via QR code.
     */
    public function verifySeminarSignature($seminarId, string $type)
    {
        $seminar = Seminar::find($seminarId);
        
        if (!$seminar) {
            return response()->view('verification.not-found', [
                'message' => 'Seminar tidak ditemukan',
                'type' => 'seminar'
            ], 404);
        }
        
        $seminar->load(['mahasiswa', 'seminarJenis', 'p1Dosen', 'p2Dosen', 'pembahasDosen', 'signatures']);
        
        // Get the specific signature
        // Get the specific signature using token if available, otherwise fallback to the first signature for this role
        $token = request('token');
        $signature = $seminar->signatures()
            ->where('jenis_penilai', $type)
            ->when($token, function($q) use ($token) {
                return $q->where('verification_token', $token);
            })
            ->when(!$token, function($q) use ($seminar, $type) {
                // Fallback: match the current assigned evaluator for this seminar
                return $q->where(function($sub) use ($seminar, $type) {
                    if ($type === 'p1') $sub->where('dosen_id', $seminar->p1_dosen_id);
                    elseif ($type === 'p2') $sub->where('dosen_id', $seminar->p2_dosen_id);
                    elseif ($type === 'pembahas') $sub->where('dosen_id', $seminar->pembahas_dosen_id);
                });
            })
            ->first();
        
        // Get the evaluator name based on type
        $evaluatorName = '';
        $evaluatorNip = '';
        
        switch ($type) {
            case 'p1':
                $evaluatorName = $signature->dosen->nama ?? $seminar->p1Dosen->nama ?? $seminar->p1_nama ?? '-';
                $evaluatorNip = $signature->dosen->nip ?? $seminar->p1Dosen->nip ?? $seminar->p1_nip ?? '-';
                break;
            case 'p2':
                $evaluatorName = $signature->dosen->nama ?? $seminar->p2Dosen->nama ?? $seminar->p2_nama ?? '-';
                $evaluatorNip = $signature->dosen->nip ?? $seminar->p2Dosen->nip ?? $seminar->p2_nip ?? '-';
                break;
            case 'pembahas':
                $evaluatorName = $signature->dosen->nama ?? $seminar->pembahasDosen->nama ?? $seminar->pembahas_nama ?? '-';
                $evaluatorNip = $signature->dosen->nip ?? $seminar->pembahasDosen->nip ?? $seminar->pembahas_nip ?? '-';
                break;
            default:
                // For dynamic roles like 'koor', 'dekan', etc.
                if ($signature && $signature->dosen) {
                    $evaluatorName = $signature->dosen->nama;
                    $evaluatorNip = $signature->dosen->nip;
                } else {
                    // Fallback to current SuratRole holder if no specific signature record exists yet
                    $role = \App\Models\SuratRole::where('kode', strtoupper($type))->with('delegatedDosen')->first();
                    $evaluatorName = $role ? ($role->delegatedDosen->nama ?? '-') : '-';
                    $evaluatorNip = $role ? ($role->delegatedDosen->nip ?? '-') : '-';
                }
                break;
        }
        
        // Determine approval time
        $approvalTime = $signature ? $signature->created_at : null;
        
        // Fallback for role-based signatures (KAJUR, KOOR, etc.) if no specific signature record
        if (!$approvalTime && in_array(strtolower($type), ['kajur', 'sekjur', 'koor', 'dekan'])) {
            $approvalTime = $seminar->tanggal_nilai ?? $seminar->updated_at;
        }

        // Generate the rendered HTML preview for document content verification
        $previewHtml = null;
        $templateId = request('template');
        if ($templateId) {
            $template = \App\Models\DocumentTemplate::find($templateId);
        } else {
            $template = $seminar->seminarJenis?->documentTemplates()?->where('aktif', true)->first();
        }
        
        if ($template && $template->template_html) {
            $pdfService = app(\App\Services\PdfGeneratorService::class);
            $renderedHtml = $pdfService->replaceSeminarTags($template->template_html, $seminar, $template);
            $previewHtml = $pdfService->renderHtml($renderedHtml, $template->header_image_path, [
                'header_repeat' => $template->header_repeat ?? false,
                'header_visibility' => $template->header_visibility ?? 'all',
            ]);
        }
        
        return view('verification.seminar-signature', compact('seminar', 'signature', 'type', 'evaluatorName', 'evaluatorNip', 'previewHtml', 'approvalTime'));
    }

    /**
     * Verify a surat document via QR code.
     */
    public function verifySurat($suratId)
    {
        $surat = Surat::find($suratId);
        
        if (!$surat) {
            return response()->view('verification.not-found', [
                'message' => 'Surat tidak ditemukan',
                'type' => 'surat'
            ], 404);
        }
        
        $surat->load(['jenis', 'pemohonDosen', 'pemohonMahasiswa', 'approvals.role', 'approvals.dosen']);
        
        // If it's an uploaded PDF, we show a special verification page for stamped documents
        if ($surat->jenis->is_uploaded && ($surat->uploaded_pdf_path || $surat->generated_file_path)) {
            return view('verification.surat-upload', compact('surat'));
        }

        // Generate the rendered HTML preview
        $previewHtml = null;
        $template = $surat->jenis?->template;
        
        if ($template && $template->template_html) {
            $pdfService = app(\App\Services\PdfGeneratorService::class);
            $renderedHtml = $pdfService->replaceSuratTags($template->template_html, $surat);
            $previewHtml = $pdfService->renderHtml($renderedHtml, $template->header_image_path, [
                'header_repeat' => $template->header_repeat ?? false,
                'header_visibility' => $template->header_visibility ?? 'all',
            ]);
        }
        
        return view('verification.surat', compact('surat', 'previewHtml'));
    }

    /**
     * Verify a surat signature via QR code.
     */
    public function verifySuratSignature($suratId, string $type)
    {
        $surat = Surat::find($suratId);
        
        if (!$surat) {
            return response()->view('verification.not-found', [
                'message' => 'Surat tidak ditemukan',
                'type' => 'surat'
            ], 404);
        }
        
        $surat->load(['jenis', 'approvals.role', 'approvals.dosen']);
        
        $approval = null;
        if ($type === 'pemohon') {
            // Pemohon is special
            $approverName = $surat->pemohonDosen->nama ?? $surat->pemohonMahasiswa->nama ?? '-';
            $approverNip = $surat->pemohonDosen->nip ?? $surat->pemohonMahasiswa->npm ?? '-';
            $approvalTime = $surat->created_at;
            $roleName = 'Pemohon';
        } else {
            // Check if type is specific approval ID (e.g. approval-5)
            // Also handle common typo 'approvel-'
            if (str_starts_with($type, 'approval-') || str_starts_with($type, 'approvel-')) {
                $parts = explode('-', $type);
                $approvalId = end($parts);
                $approval = $surat->approvals->where('id', $approvalId)->first();
            }

            // If not found by ID, try role code or name match
            if (!$approval) {
                // Find approval by role code or role_nama slug
                $typeNormalized = preg_replace('/[^a-z0-9]/', '', strtolower($type));
                
                $approval = $surat->approvals->filter(function($a) use ($typeNormalized) {
                    // Normalize all searchable parts
                    $codes = [];
                    if ($a->role) {
                        if ($a->role->kode) $codes[] = preg_replace('/[^a-z0-9]/', '', strtolower($a->role->kode));
                        if ($a->role->nama) $codes[] = preg_replace('/[^a-z0-9]/', '', strtolower($a->role->nama));
                    }
                    if ($a->role_nama) $codes[] = preg_replace('/[^a-z0-9]/', '', strtolower($a->role_nama));
                    
                    foreach (array_unique($codes) as $check) {
                        if ($check === $typeNormalized || str_contains($check, $typeNormalized)) {
                            return true;
                        }
                    }

                    return false;
                })->first();
            }

            if (!$approval || $approval->status !== \App\Models\SuratApproval::STATUS_APPROVED) {
                return response()->view('verification.invalid-signature', [
                    'message' => 'Tanda tangan untuk verifikasi ' . strtoupper($type) . ' belum tersedia atau tidak valid.',
                    'surat' => $surat
                ], 403);
            }

            // Allow override via query param for custom additional signers
            // If signer_name is present, we try to use signer_nip and signer_role from query as well
            $approverName = request('signer_name') ?: ($approval->dosen->nama ?? '-');
            $approverNip = request('signer_nip') ?: (request('signer_name') ? '-' : ($approval->dosen->nip ?? '-'));
            $approvalTime = $approval->approved_at;
            
            // Prioritize signer_role from query if present (for extra signers)
            $roleName = request('signer_role') ?: ($approval->role_nama ?: ($approval->role?->nama ?? 'Penyetuju'));
        }
        
        // Generate the rendered HTML preview for HTML-based letters
        $previewHtml = null;
        if (!$surat->jenis->is_uploaded) {
            $template = $surat->jenis?->template;
            if ($template && $template->template_html) {
                $pdfService = app(\App\Services\PdfGeneratorService::class);
                $renderedHtml = $pdfService->replaceSuratTags($template->template_html, $surat);
                $previewHtml = $pdfService->renderHtml($renderedHtml, $template->header_image_path, [
                    'header_repeat' => $template->header_repeat ?? false,
                    'header_visibility' => $template->header_visibility ?? 'all',
                ]);
            }
        }
        
        return view('verification.surat-signature', compact('surat', 'type', 'approverName', 'approverNip', 'approvalTime', 'roleName', 'approval', 'previewHtml'));
    }
}
