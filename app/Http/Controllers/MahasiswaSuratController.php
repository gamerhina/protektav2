<?php

namespace App\Http\Controllers;

use App\Models\Surat;
use App\Models\SuratJenis;
use App\Models\Admin;
use App\Notifications\SuratSubmittedNotification;
use App\Models\SuratTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
class MahasiswaSuratController extends Controller
{
    public function index(Request $request)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        $search = $request->query('search');

        $items = Surat::with(['jenis'])
            ->where(function ($q) use ($mahasiswa) {
                $q->where('pemohon_mahasiswa_id', $mahasiswa->id)
                  ->orWhere('mahasiswa_id', $mahasiswa->id);
            })
            ->when($search, function($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('no_surat', 'like', "%$search%")
                      ->orWhere('perihal', 'like', "%$search%")
                      ->orWhere('tujuan', 'like', "%$search%")
                      ->orWhereHas('jenis', function($q) use ($search) {
                          $q->where('nama', 'like', "%$search%");
                      });
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('mahasiswa.surat.index', compact('items'));
    }

    public function create()
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        
        // Get active letter types that allow mahasiswa as pemohon
        $jenisList = SuratJenis::where('aktif', true)
            ->get()
            ->filter(function ($jenis) {
                // Check target_pemohon setting
                $targets = $jenis->target_pemohon;
                if (!is_null($targets) && !in_array('mahasiswa', $targets)) {
                    return false;
                }

                $fields = is_array($jenis->form_fields) ? $jenis->form_fields : [];
                $pemohonField = null;
                foreach ($fields as $f) {
                    if (($f['type'] ?? '') === 'pemohon') {
                        $pemohonField = $f;
                        break;
                    }
                }

                if ($pemohonField) {
                    $sources = $pemohonField['pemohon_sources'] ?? $pemohonField['sources'] ?? ['mahasiswa', 'dosen'];
                    return in_array('mahasiswa', (array)$sources);
                }

                return true; 
            })
            ->sortBy('nama')
            ->values();
        
        $jenisListPayload = $jenisList
            ->map(fn ($j) => [
                'id' => $j->id, 
                'nama' => $j->nama, 
                'form_fields' => $j->form_fields,
                'informasi' => $j->informasi
            ])
            ->values();

        $currentMahasiswaPayload = [
            'id' => $mahasiswa->id,
            'nama' => $mahasiswa->nama,
            'npm' => $mahasiswa->npm,
            'email' => $mahasiswa->email,
        ];

        return view('mahasiswa.surat.create', compact('jenisList', 'jenisListPayload', 'currentMahasiswaPayload'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'surat_jenis_id' => 'required|exists:surat_jenis,id',
        ]);

        $jenis = SuratJenis::findOrFail((int) $request->input('surat_jenis_id'));
        $formFields = is_array($jenis->form_fields) ? $jenis->form_fields : [];

        $rules = [
            'surat_jenis_id' => 'required|exists:surat_jenis,id',
            'form_data' => 'nullable|array',
            'form_files' => 'nullable|array',
        ];

        $dateFieldKey = null;

        foreach ($formFields as $f) {
            if (!is_array($f)) continue;
            $key = trim((string) ($f['key'] ?? ''));
            $type = trim((string) ($f['type'] ?? 'text'));
            $required = (bool) ($f['required'] ?? false);

            if ($key === '' || $type === 'auto_no_surat') continue;

            if ($type === 'pemohon') {
                $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'array';
                $rules["form_data.$key.type"] = ($required ? 'required|' : 'nullable|') . 'in:mahasiswa';
                $rules["form_data.$key.id"] = ($required ? 'required|' : 'nullable|') . 'integer|min:1';
                continue;
            }

            if ($type === 'date') {
                $dateFieldKey = $dateFieldKey ?? $key;
                $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'date';
            } elseif ($type === 'email') {
                $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'email';
            } elseif ($type === 'number') {
                $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'numeric';
            } elseif (in_array($type, ['select', 'radio'], true)) {
                $options = collect($f['options'] ?? [])->map(fn($o) => (string)($o['value'] ?? ''))->toArray();
                $inRule = !empty($options) ? '|in:' . implode(',', array_map(fn($v) => str_replace(',', '\\,', $v), $options)) : '';
                $rules["form_data.$key"] = ($required ? 'required' : 'nullable') . $inRule;
            } elseif ($type === 'checkbox') {
                $options = collect($f['options'] ?? [])->map(fn($o) => (string)($o['value'] ?? ''))->toArray();
                if (!empty($options)) {
                    $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'array';
                    $rules["form_data.$key.*"] = 'in:' . implode(',', array_map(fn($v) => str_replace(',', '\\,', $v), $options));
                } else {
                    $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'boolean';
                }
            } elseif ($type === 'table') {
                $columns = is_array($f['columns'] ?? null) ? $f['columns'] : [];
                $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'array';
                $rules["form_data.$key.*"] = 'array';
                foreach ($columns as $col) {
                    if (is_array($col) && isset($col['key'])) {
                        $colKey = $col['key'];
                        $colType = $col['type'] ?? 'text';
                        if ($colType === 'pemohon') {
                            $rules["form_data.$key.*.$colKey"] = 'nullable|array';
                            $rules["form_data.$key.*.$colKey.type"] = 'nullable|in:mahasiswa,dosen';
                            $rules["form_data.$key.*.$colKey.id"] = 'nullable|integer|min:1';
                        } else {
                            $rules["form_data.$key.*.$colKey"] = 'nullable|string|max:500';
                        }
                    }
                }
            } elseif ($type === 'file') {
                $exts = array_filter(array_map('trim', (array) ($f['extensions'] ?? [])));
                $maxKb = (int) ($f['max_kb'] ?? 0);
                $rule = ($required ? 'required|' : 'nullable|') . 'file';
                if (!empty($exts)) $rule .= '|mimes:' . implode(',', $exts);
                if ($maxKb > 0) $rule .= '|max:' . $maxKb;
                $rules["form_files.$key"] = $rule;
            } else {
                $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'string';
            }
        }

        if ($jenis->is_uploaded) {
            $rules['uploaded_pdf'] = 'required|file|mimes:pdf|max:10240';
        }

        $validated = $request->validate($rules);
        $data = $validated['form_data'] ?? [];
        $files = $validated['form_files'] ?? [];
        $mahasiswa = Auth::guard('mahasiswa')->user();

        $payload = [
            'surat_jenis_id' => $jenis->id,
            'pemohon_type' => 'mahasiswa',
            'pemohon_mahasiswa_id' => $mahasiswa->id,
            'mahasiswa_id' => $mahasiswa->id, // Often also as the main student reference
            'untuk_type' => 'umum',
            'no_surat' => null,
            'tanggal_surat' => now()->timezone('Asia/Jakarta')->toDateString(),
            'tujuan' => null,
            'perihal' => $jenis->nama,
            'isi' => null,
            'penerima_email' => null,
            'data' => [],
            'status' => 'diajukan',
        ];

        // Handle PDF upload if required
        if ($jenis->is_uploaded && $request->hasFile('uploaded_pdf')) {
            $path = $request->file('uploaded_pdf')->store('documents/surat/uploaded', 'uploads');
            $payload['uploaded_pdf_path'] = $path;
            $payload['approval_status'] = 'pending'; // Start approval for uploaded PDF
        }

        if ($dateFieldKey && !empty($data[$dateFieldKey])) {
            try { $payload['tanggal_surat'] = Carbon::parse($data[$dateFieldKey])->toDateString(); } catch (\Throwable $e) {}
        }

        foreach (['tujuan', 'perihal', 'isi', 'penerima_email', 'untuk_type'] as $k) {
            if (array_key_exists($k, $data)) {
                $payload[$k] = $data[$k];
                unset($data[$k]);
            }
        }

        $stored = [];
        foreach ($files as $k => $file) {
            if ($file) $stored[$k] = $file->store('documents/surat/attachments', 'uploads');
        }

        $payload['data'] = array_merge($data, $stored);
        $surat = Surat::create($payload);

        // Initiate approval workflow for uploaded PDF
        if ($jenis->is_uploaded) {
            app(\App\Services\ApprovalWorkflowService::class)->initiate($surat);
        }

        // Notify admins
        $admins = Admin::all();
        foreach ($admins as $admin) {
            $admin->notify(new SuratSubmittedNotification($surat));
        }

        return redirect()->route('mahasiswa.surat.index')->with('success', 'Permohonan surat berhasil diajukan.');
    }

    private function generateDefaultNoSurat(?int $suratJenisId = null): string
    {
        $currentYear = Carbon::now()->year;
        $query = Surat::whereYear('created_at', $currentYear);
        if ($suratJenisId) $query->where('surat_jenis_id', $suratJenisId);
        $maxNumber = $query->max(DB::raw('CAST(no_surat AS UNSIGNED)'));
        $nextNumber = $maxNumber ? ((int) $maxNumber + 1) : 1;
        return str_pad((string)$nextNumber, 3, '0', STR_PAD_LEFT);
    }

    public function show(Surat $surat)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();

        $isOwner = (int) $surat->pemohon_mahasiswa_id === (int) $mahasiswa->id;
        $isTarget = (int) $surat->mahasiswa_id === (int) $mahasiswa->id;

        if (!$isOwner && !$isTarget) {
            abort(403);
        }

        $surat->load(['jenis.templates', 'approvals.role', 'approvals.dosen']);
        return view('mahasiswa.surat.show', compact('surat'));
    }

    public function downloadPdf(Surat $surat)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();

        $isOwner = (int) $surat->pemohon_mahasiswa_id === (int) $mahasiswa->id;
        $isTarget = (int) $surat->mahasiswa_id === (int) $mahasiswa->id;

        if (!$isOwner && !$isTarget) {
            abort(403);
        }

        if (!$surat->jenis?->allow_download && $surat->status !== 'selesai') {
            return back()->with('error', 'Dokumen belum tersedia untuk diunduh. Tunggu hingga status Selesai.');
        }

        if (!$surat->jenis?->allow_download) {
             return back()->with('error', 'Fitur unduh mandiri dinonaktifkan untuk jenis surat ini.');
        }

        if ($surat->status !== 'selesai' && $surat->status !== 'diproses') {
             return back()->with('error', 'Dokumen belum tersedia untuk diunduh.');
        }



        if ($surat->jenis?->is_uploaded) {
            $path = $surat->generated_file_path ?: $surat->uploaded_pdf_path;
            if (!$path || !Storage::disk('uploads')->exists($path)) {
                return back()->with('error', 'Berkas PDF tidak ditemukan.');
            }
            return response()->download(Storage::disk('uploads')->path($path), 'surat_' . ($surat->no_surat ?? $surat->id) . '.pdf');
        }

        $pdfService = app(\App\Services\PdfGeneratorService::class);
        try {
            $pdf = $pdfService->generateSuratPdf($surat);
            return $pdf->download('surat_' . ($surat->no_surat ?? $surat->id) . '.pdf');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Preview HTML template for uploaded letter types
     */
    public function previewHtml(Request $request, Surat $surat, ?SuratTemplate $template = null)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();

        $isOwner = (int) $surat->pemohon_mahasiswa_id === (int) $mahasiswa->id;
        $isTarget = (int) $surat->mahasiswa_id === (int) $mahasiswa->id;

        if (!$isOwner && !$isTarget) {
            abort(403);
        }

        // If template ID is passed but not the model
        if (!$template && $request->route('template')) {
            $template = SuratTemplate::find($request->route('template'));
        }

        // Load necessary relationships
        $surat->load(['jenis.templates', 'pemohonDosen', 'pemohonMahasiswa', 'mahasiswa', 'approvals.role', 'approvals.dosen']);

        // Generate HTML with data
        $pdfService = app(\App\Services\PdfGeneratorService::class);
        $previewHtml = $pdfService->generateSuratHtml($surat, $template);

        return view('shared.surat.print-preview', compact('surat', 'previewHtml', 'template'));
    }

    /**
     * Download HTML template for uploaded letter types
     */
    public function downloadHtml(Surat $surat)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();

        $isOwner = (int) $surat->pemohon_mahasiswa_id === (int) $mahasiswa->id;
        $isTarget = (int) $surat->mahasiswa_id === (int) $mahasiswa->id;

        if (!$isOwner && !$isTarget) {
            abort(403);
        }

        // Load necessary relationships
        $surat->load(['jenis.templates', 'approvals.role', 'approvals.dosen']);

        // Get template from either relationship
        $templateHtml = $surat->jenis?->template?->template_html ?? $surat->jenis?->templates?->first()?->template_html;
        
        if (!$templateHtml) {
            return back()->with('error', 'Template HTML tidak tersedia.');
        }

        if ($surat->status !== 'selesai') {
            return back()->with('error', 'Download template hanya tersedia setelah surat selesai.');
        }

        // Generate HTML with data
        $pdfService = app(\App\Services\PdfGeneratorService::class);
        $html = $pdfService->generateSuratHtml($surat);

        $filename = 'template_' . ($surat->no_surat ?? $surat->id) . '.html';

        return response()->make($html, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    public function destroy(Surat $surat)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();

        // Only the owner can cancel
        if ((int) $surat->pemohon_mahasiswa_id !== (int) $mahasiswa->id) {
            abort(403);
        }

        // Only allow cancellation when status is still pending
        $cancellableStatuses = ['diajukan', 'submitted'];
        if (!in_array($surat->status, $cancellableStatuses)) {
            return back()->with('error', 'Permohonan surat tidak dapat dibatalkan karena sudah diproses.');
        }

        // Delete related approvals
        $surat->approvals()->delete();

        // Delete uploaded files if any
        if ($surat->uploaded_pdf_path && Storage::disk('uploads')->exists($surat->uploaded_pdf_path)) {
            Storage::disk('uploads')->delete($surat->uploaded_pdf_path);
        }
        if ($surat->generated_file_path && Storage::disk('uploads')->exists($surat->generated_file_path)) {
            Storage::disk('uploads')->delete($surat->generated_file_path);
        }

        // Delete attachment files from data
        $data = $surat->data ?? [];
        foreach ($data as $value) {
            if (is_string($value) && str_starts_with($value, 'documents/') && Storage::disk('uploads')->exists($value)) {
                Storage::disk('uploads')->delete($value);
            }
        }

        $surat->delete();

        return redirect()->route('mahasiswa.surat.index')->with('success', 'Permohonan surat berhasil dibatalkan.');
    }
}
