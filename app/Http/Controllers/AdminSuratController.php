<?php

namespace App\Http\Controllers;

use App\Models\Surat;
use App\Models\SuratJenis;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Admin;
use App\Notifications\SuratSubmittedNotification;
use App\Notifications\SuratStatusUpdatedNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\SuratTemplate;
use Illuminate\Http\Request;
use App\Exports\SuratExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\PdfGeneratorService;
use Illuminate\Support\Facades\Mail;

class AdminSuratController extends Controller
{
    public function export()
    {
        return Excel::download(new SuratExport, 'rekap_surat_' . date('Y-m-d_H-i') . '.xlsx');
    }

    public function index(Request $request)
    {
        $sortFields = [
            'no_surat' => 'surats.no_surat',
            'pemohon' => 'pemohon_nama', // We will use raw select for this
            'surat_jenis_id' => 'surat_jenis.nama',
            'tanggal_surat' => 'surats.tanggal_surat',
            'status' => 'surats.status',
            'created_at' => 'surats.created_at',
        ];

        $sort = $request->input('sort', 'created_at');
        if (!array_key_exists($sort, $sortFields)) {
            $sort = 'created_at';
        }

        $direction = strtolower($request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $query = Surat::with(['jenis', 'pemohonDosen', 'pemohonMahasiswa', 'mahasiswa'])
            ->select('surats.*')
            ->selectRaw('COALESCE(m.nama, d.nama) as pemohon_nama')
            ->leftJoin('mahasiswa as m', 'm.id', '=', 'surats.pemohon_mahasiswa_id')
            ->leftJoin('dosen as d', 'd.id', '=', 'surats.pemohon_dosen_id')
            ->leftJoin('surat_jenis', 'surat_jenis.id', '=', 'surats.surat_jenis_id');

        if ($request->filled('status_filter')) {
            $query->where('surats.status', $request->input('status_filter'));
        }

        if ($request->filled('search')) {
            $s = '%' . trim((string) $request->input('search')) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('surats.no_surat', 'like', $s)
                    ->orWhere('surats.tujuan', 'like', $s)
                    ->orWhere('surats.perihal', 'like', $s)
                    ->orWhere('surats.status', 'like', $s)
                    ->orWhere('m.nama', 'like', $s)
                    ->orWhere('d.nama', 'like', $s)
                    ->orWhere('surat_jenis.nama', 'like', $s);
            });
        }

        $query->orderBy($sortFields[$sort], $direction);

        $items = $query->paginate(20)->withQueryString();
        $jenisList = SuratJenis::orderBy('nama')->get();

        return view('admin.surat.index', compact('items', 'jenisList', 'sort', 'direction'));
    }

    /**
     * Provide next nomor surat for selected surat jenis.
     */
    public function getNextNoSurat(Request $request)
    {
        $validated = $request->validate([
            'surat_jenis_id' => 'required|exists:surat_jenis,id',
        ]);

        $nextNoSurat = $this->generateDefaultNoSurat((int) $validated['surat_jenis_id']);

        return response()->json([
            'next_no_surat' => $nextNoSurat,
        ]);
    }

    public function create()
    {
        $jenisList = SuratJenis::where('aktif', true)->orderBy('nama')->get(['id', 'nama', 'kode', 'form_fields', 'is_uploaded', 'upload_max_kb', 'informasi']);
        
        // Only pass essential fields to prevent JavaScript truncation
        // Email is removed to reduce data size significantly
        // LIMIT records to prevent script truncation (users can search if needed)
        $dosens = Dosen::orderBy('nama')->limit(50)->get(['id', 'nama', 'nip'])->map(function($d) {
            return [
                'id' => $d->id,
                'nama' => $d->nama,
                'nip' => $d->nip
            ];
        });
        
        $mahasiswas = Mahasiswa::orderBy('nama')->limit(100)->get(['id', 'nama', 'npm'])->map(function($m) {
            return [
                'id' => $m->id,
                'nama' => $m->nama,
                'npm' => $m->npm
            ];
        });

        $admins = Admin::orderBy('nama')->get(['id', 'nama', 'email'])->map(function($a) {
            return [
                'id' => $a->id,
                'nama' => $a->nama,
                'email' => $a->email
            ];
        });

        $jenisListPayload = $jenisList
            ->map(fn ($j) => [
                'id' => $j->id, 
                'nama' => $j->nama, 
                'kode' => $j->kode,
                'form_fields' => $j->form_fields,
                'is_uploaded' => (bool)$j->is_uploaded,
                'upload_max_kb' => $j->upload_max_kb,
                'informasi' => $j->informasi
            ])
            ->values();

        return view('admin.surat.create', compact('jenisList', 'jenisListPayload', 'dosens', 'mahasiswas', 'admins'));
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
            'no_surat' => [
                'nullable', 
                'string', 
                'max:100', 
                \Illuminate\Validation\Rule::unique('surats')->where(function ($query) use ($request) {
                    return $query->where('surat_jenis_id', $request->surat_jenis_id)
                                 ->whereYear('created_at', date('Y'));
                })
            ],
            'form_data' => 'nullable|array',
            'form_files' => 'nullable|array',
            'uploaded_pdf' => 'nullable|file|mimes:pdf|max:' . ($jenis->upload_max_kb ?: 10240),
        ];

        $pemohonFieldKey = null;
        $dateFieldKey = null;

        foreach ($formFields as $f) {
            if (!is_array($f)) {
                continue;
            }
            $key = trim((string) ($f['key'] ?? ''));
            $type = trim((string) ($f['type'] ?? 'text'));
            $required = (bool) ($f['required'] ?? false);

            if ($key === '') {
                continue;
            }

            if ($type === 'pemohon') {
                $pemohonFieldKey = $pemohonFieldKey ?? $key;

                $sources = $f['pemohon_sources'] ?? ['mahasiswa', 'dosen'];
                if (!is_array($sources) || empty($sources)) {
                    $sources = ['mahasiswa', 'dosen'];
                }
                
                // Add 'custom' to allowed types
                $allowedTypes = array_merge($sources, ['custom']);

                $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'array';
                $rules["form_data.$key.type"] = ($required ? 'required|' : 'nullable|') . 'in:' . implode(',', $allowedTypes);
                $rules["form_data.$key.id"] = ($required ? 'required|' : 'nullable|') . 'integer|min:0';
                
                // Add validation for custom inputs (conditional)
                $rules["form_data.$key.custom_nama"] = 'nullable|string|max:255';
                $rules["form_data.$key.custom_nip"] = 'nullable|string|max:100';
                continue;
            }

            if ($type === 'auto_no_surat') {
                continue;
            }

            if ($type === 'date') {
                $dateFieldKey = $dateFieldKey ?? $key;
                $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'date';
                continue;
            }

            if ($type === 'email') {
                $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'email';
                continue;
            }

            if ($type === 'number') {
                $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'numeric';
                continue;
            }

            if (in_array($type, ['select', 'radio'], true)) {
                $options = [];
                foreach ((array) ($f['options'] ?? []) as $opt) {
                    if (is_array($opt) && isset($opt['value'])) {
                        $options[] = (string) $opt['value'];
                    }
                }
                $inRule = !empty($options) ? '|in:' . implode(',', array_map(fn ($v) => str_replace(',', '\\,', $v), $options)) : '';
                $rules["form_data.$key"] = ($required ? 'required' : 'nullable') . $inRule;
                continue;
            }

            if ($type === 'checkbox') {
                $options = [];
                foreach ((array) ($f['options'] ?? []) as $opt) {
                    if (is_array($opt) && isset($opt['value'])) {
                        $options[] = (string) $opt['value'];
                    }
                }

                if (!empty($options)) {
                    $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'array';
                    $rules["form_data.$key.*"] = 'in:' . implode(',', array_map(fn ($v) => str_replace(',', '\\,', $v), $options));
                } else {
                    $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'boolean';
                }
                continue;
            }

            if ($type === 'table') {
                $columns = is_array($f['columns'] ?? null) ? $f['columns'] : [];
                $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'array';
                
                // Validate each row
                $rules["form_data.$key.*"] = 'array';
                
                // Validate each column in each row
                foreach ($columns as $col) {
                    if (is_array($col) && isset($col['key'])) {
                        $colKey = $col['key'];
                        $colType = $col['type'] ?? 'text';
                        
                        // Handle column with pemohon type
                        if ($colType === 'pemohon') {
                            $sources = $col['pemohon_sources'] ?? ['mahasiswa', 'dosen'];
                            if (!is_array($sources) || empty($sources)) {
                                $sources = ['mahasiswa', 'dosen'];
                            }
                            
                            // Add 'custom' to allowed types for table columns too
                            $allowedTypes = array_merge($sources, ['custom']);
                            
                            $rules["form_data.$key.*.$colKey"] = 'nullable|array';
                            $rules["form_data.$key.*.$colKey.type"] = 'nullable|in:' . implode(',', $allowedTypes);
                            $rules["form_data.$key.*.$colKey.id"] = 'nullable|integer|min:0';
                        } else {
                            $rules["form_data.$key.*.$colKey"] = 'nullable|string|max:500';
                        }
                    }
                }
                continue;
            }

            if ($type === 'file') {
                $exts = array_filter(array_map('trim', (array) ($f['extensions'] ?? [])));
                $maxKb = (int) ($f['max_kb'] ?? 0);

                $rule = ($required ? 'required|' : 'nullable|') . 'file';
                if (!empty($exts)) {
                    $rule .= '|mimes:' . implode(',', $exts);
                }
                if ($maxKb > 0) {
                    $rule .= '|max:' . $maxKb;
                }

                $rules["form_files.$key"] = $rule;
                continue;
            }

            // default
            $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'string';
        }

        $validated = $request->validate($rules);

        $data = is_array($validated['form_data'] ?? null) ? $validated['form_data'] : [];
        $files = is_array($validated['form_files'] ?? null) ? $validated['form_files'] : [];

        $payload = [
            'surat_jenis_id' => $jenis->id,
            'pemohon_type' => 'dosen',
            'pemohon_dosen_id' => null,
            'pemohon_mahasiswa_id' => null,
            'mahasiswa_id' => null,
            'untuk_type' => 'umum',
            'no_surat' => $this->generateDefaultNoSurat($jenis->id),
            'tanggal_surat' => now()->timezone('Asia/Jakarta')->toDateString(),
            'tujuan' => null,
            'perihal' => null,
            'isi' => null,
            'penerima_email' => null,
            'data' => [],
            'status' => 'diajukan',
        ];

        if (!empty($validated['no_surat'])) {
            $payload['no_surat'] = $this->normalizeNoSurat((string) $validated['no_surat']);
        }

        if ($pemohonFieldKey && isset($data[$pemohonFieldKey]) && is_array($data[$pemohonFieldKey])) {
            $pemType = $data[$pemohonFieldKey]['type'] ?? null;
            $pemId = (int) ($data[$pemohonFieldKey]['id'] ?? 0);
            
            if ($pemType === 'custom') {
                $payload['pemohon_type'] = 'custom';
                $payload['pemohon_mahasiswa_id'] = null;
                $payload['pemohon_dosen_id'] = null;
                $payload['pemohon_admin_id'] = null;
            } elseif ($pemType === 'admin' && $pemId > 0) {
                $payload['pemohon_type'] = 'admin';
                $payload['pemohon_admin_id'] = $pemId;
                $payload['pemohon_mahasiswa_id'] = null;
                $payload['pemohon_dosen_id'] = null;
            } elseif ($pemType === 'mahasiswa' && $pemId > 0) {
                $payload['pemohon_type'] = 'mahasiswa';
                $payload['pemohon_mahasiswa_id'] = $pemId;
                $payload['pemohon_dosen_id'] = null;
                $payload['pemohon_admin_id'] = null;
            } elseif ($pemType === 'dosen' && $pemId > 0) {
                $payload['pemohon_type'] = 'dosen';
                $payload['pemohon_dosen_id'] = $pemId;
                $payload['pemohon_mahasiswa_id'] = null;
                $payload['pemohon_admin_id'] = null;
            }
        }

        // tanggal surat from configured date field if present
        if ($dateFieldKey && !empty($data[$dateFieldKey])) {
            try {
                $payload['tanggal_surat'] = Carbon::parse($data[$dateFieldKey])->toDateString();
            } catch (\Throwable $e) {
                // keep default
            }
        }

        // Common mapping from well-known keys
        foreach (['tujuan', 'perihal', 'isi', 'penerima_email', 'untuk_type', 'mahasiswa_id'] as $k) {
            if (array_key_exists($k, $data)) {
                $payload[$k] = $data[$k];
                unset($data[$k]);
            }
        }

        if ($request->hasFile('uploaded_pdf')) {
            $payload['uploaded_pdf_path'] = $request->file('uploaded_pdf')->store('documents/surat/uploaded', 'uploads');
        }

        // Handle uploads
        $stored = [];
        foreach ($files as $k => $file) {
            if (!$file) continue;
            $stored[$k] = $file->store('documents/surat/attachments', 'uploads');
        }

        // Store remaining data + uploaded file paths
        $payload['data'] = array_merge($data, $stored);

        $surat = Surat::create($payload);

        // Send notification to all admins
        $admins = Admin::all();
        foreach ($admins as $admin) {
            $admin->notify(new SuratSubmittedNotification($surat));
        }

        return redirect()->route('admin.surat.show', $surat)->with('success', 'Surat berhasil dibuat.');
    }

    /**
     * Generate default nomor surat that resets every year starting from 001
     */
    private function generateDefaultNoSurat(?int $suratJenisId = null): string
    {
        $currentYear = Carbon::now()->year;

        $query = Surat::whereYear('created_at', $currentYear);

        if ($suratJenisId) {
            $query->where('surat_jenis_id', $suratJenisId);
        }

        $maxNumber = $query->max(DB::raw('CAST(no_surat AS UNSIGNED)'));
        $nextNumber = $maxNumber ? ((int) $maxNumber + 1) : 1;

        return $this->normalizeNoSurat((string) $nextNumber);
    }

    private function normalizeNoSurat(string $value): string
    {
        $numeric = ltrim($value, '0');
        if ($numeric === '') {
            $numeric = '0';
        }

        return str_pad($numeric, 3, '0', STR_PAD_LEFT);
    }

    public function show(Surat $surat)
    {
        $surat->load(['jenis.template', 'pemohonDosen', 'pemohonMahasiswa', 'mahasiswa', 'approvals.dosen', 'approvals.role', 'comments.user']);
        $dosens = Dosen::orderBy('nama')->get(['id', 'nama', 'nip', 'email']);
        $mahasiswas = Mahasiswa::orderBy('nama')->get(['id', 'nama', 'npm', 'email']);
        $admins = \App\Models\Admin::orderBy('nama')->get(['id', 'nama', 'email', 'nip']);

        // Sync approvals to resolve missing data (like PA)
        app(\App\Services\ApprovalWorkflowService::class)->syncApprovals($surat);

        $approvals = $surat->approvals()->orderBy('urutan')->get();
        
        // Default selection: first pending OR first stamped
        $approval = $approvals->firstWhere('status', 'pending') 
                  ?? $approvals->firstWhere('is_stamped', true)
                  ?? $approvals->first();
        
        return view('admin.surat.show', compact('surat', 'dosens', 'mahasiswas', 'admins', 'approval', 'approvals'));
    }

    public function update(Request $request, Surat $surat)
    {
        $year = $surat->created_at ? $surat->created_at->year : date('Y');
        
        $validated = $request->validate([
            'no_surat' => [
                'nullable', 
                'string', 
                'max:100',
                \Illuminate\Validation\Rule::unique('surats')->ignore($surat->id)->where(function ($query) use ($year, $surat) {
                    return $query->where('surat_jenis_id', $surat->surat_jenis_id)
                                 ->whereYear('created_at', $year);
                })
            ],
            'tanggal_surat' => 'required|date',
            'tujuan' => 'nullable|string|max:255',
            'perihal' => 'nullable|string|max:255',
            'isi' => 'nullable|string',
            'penerima_email' => 'nullable|email',
            'status' => 'required|in:diajukan,diproses,selesai,ditolak',
            'form_data' => 'nullable|array',
            'uploaded_pdf' => 'nullable|file|mimes:pdf|max:' . ($surat->jenis->upload_max_kb ?: 10240),
        ]);

        if (!empty($validated['no_surat'])) {
            $validated['no_surat'] = $this->normalizeNoSurat((string) $validated['no_surat']);
        }

        // Auto-generate no_surat if status is 'diproses' or 'selesai' and no_surat is empty
        if (isset($validated['status']) && in_array($validated['status'], ['diproses', 'selesai']) && empty($validated['no_surat']) && empty($surat->no_surat)) {
            $validated['no_surat'] = $this->generateDefaultNoSurat($surat->surat_jenis_id);
        }

        $data = $request->input('form_data', []);
        if (is_array($data)) {
            // Mapping common keys just like in store()
            foreach (['tujuan', 'perihal', 'isi', 'penerima_email', 'untuk_type', 'mahasiswa_id'] as $k) {
                if (array_key_exists($k, $data)) {
                    $validated[$k] = $data[$k];
                    unset($data[$k]);
                }
            }

            // Handle pemohon field mapping if present
            $jenis = $surat->jenis;
            $formFields = is_array($jenis?->form_fields) ? $jenis->form_fields : [];
            $pemohonFieldKey = null;
            foreach ($formFields as $f) {
                if (is_array($f) && ($f['type'] ?? '') === 'pemohon') {
                    $pemohonFieldKey = $f['key'] ?? null;
                    break;
                }
            }

            if ($pemohonFieldKey && isset($data[$pemohonFieldKey]) && is_array($data[$pemohonFieldKey])) {
                $pemType = $data[$pemohonFieldKey]['type'] ?? null;
                $pemId = (int) ($data[$pemohonFieldKey]['id'] ?? 0);
                
                if ($pemType === 'custom') {
                    $validated['pemohon_type'] = 'custom';
                    $validated['pemohon_mahasiswa_id'] = null;
                    $validated['pemohon_dosen_id'] = null;
                } elseif ($pemType === 'mahasiswa' && $pemId > 0) {
                    $validated['pemohon_type'] = 'mahasiswa';
                    $validated['pemohon_mahasiswa_id'] = $pemId;
                    $validated['pemohon_dosen_id'] = null;
                } elseif ($pemType === 'dosen' && $pemId > 0) {
                    $validated['pemohon_type'] = 'dosen';
                    $validated['pemohon_dosen_id'] = $pemId;
                    $validated['pemohon_mahasiswa_id'] = null;
                }
            }

            $validated['data'] = array_merge($surat->data ?? [], $data);
        }

        $previousStatus = $surat->status;
        
        $surat->fill($validated);

        // Generate QR/Verification Token if finalized and not present
        if ($surat->status === 'selesai' && !$surat->verification_token) {
            $template = $surat->jenis->template;
            // Default to qr_code if not set, or if explicitly set to qr_code
            $shouldGenerate = !$template || $template->signature_method === 'qr_code';
            
            if ($shouldGenerate) {
                $surat->verification_token = \Illuminate\Support\Str::uuid()->toString();
                $surat->signature_type = 'qr_code';
            }
        }

        // Sync approval_status based on main status change
        if ($surat->isDirty('status')) {
            if ($surat->status === 'diproses') {
                $surat->approval_status = $surat->approval_status === 'pending' ? 'in_progress' : $surat->approval_status;
            } elseif ($surat->status === 'selesai') {
                $surat->approval_status = 'approved';
            } elseif ($surat->status === 'ditolak') {
                $surat->approval_status = 'rejected';
            }
        }

        if ($request->hasFile('uploaded_pdf')) {
            $surat->uploaded_pdf_path = $request->file('uploaded_pdf')->store('documents/surat/uploaded', 'uploads');
        }

        $surat->save();

        // Send notification if status changed
        if ($previousStatus !== $surat->status) {
            // 1. Notify signer if status changed to 'diproses'
            if ($surat->status === 'diproses') {
                app(\App\Services\ApprovalWorkflowService::class)->notifyNextApprover($surat);
            }

            // 2. Notify pemohon (dosen or mahasiswa)
            if ($surat->pemohon_type === 'dosen' && $surat->pemohonDosen) {
                $surat->pemohonDosen->notify(new SuratStatusUpdatedNotification($surat, $previousStatus));
            } elseif ($surat->pemohon_type === 'mahasiswa' && $surat->pemohonMahasiswa) {
                $surat->pemohonMahasiswa->notify(new SuratStatusUpdatedNotification($surat, $previousStatus));
            }
        }

        return redirect()->route('admin.surat.show', $surat)->with('success', 'Surat berhasil diperbarui.');
    }

    public function previewPdf(Request $request, Surat $surat)
    {
        if ($surat->status === 'ditolak') {
            return response('Dokumen tidak dapat diakses karena pengajuan ditolak.', 403);
        }

        // Default to wrapper view unless raw stream is requested
        if ($request->input('mode') !== 'stream') {
            return view('admin.surat.preview-wrapper', compact('surat'));
        }

        // Raw Stream Logic
        if ($surat->jenis?->is_uploaded) {
            $path = $surat->generated_file_path ?: $surat->uploaded_pdf_path;
            if (!$path || !\Storage::disk('uploads')->exists($path)) {
                return back()->with('error', 'Berkas PDF tidak ditemukan.');
            }
            
            // Add headers to prevent caching
            return response()->file(\Storage::disk('uploads')->path($path), [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="preview_surat_' . ($surat->no_surat ?? $surat->id) . '.pdf"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);
        }
        
        $pdfService = app(\App\Services\PdfGeneratorService::class);
        try {
            $pdf = $pdfService->generateSuratPdf($surat);
            // Stream generated PDF
            return $pdf->stream('preview_surat_' . ($surat->no_surat ?? $surat->id) . '.pdf');
        } catch (\Exception $e) {
            // If viewing raw stream, returning a redirect/view might break iframe, 
            // but for now a simple error text is safer than a full page that loads inside iframe
            return response('Gagal memproses pratinjau: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Preview HTML version of the letter (Mirroring Seminar template preview features)
     */
public function previewHtml(Request $request, Surat $surat, ?SuratTemplate $template = null)
{
    if ($surat->status === 'ditolak') {
        return response('Dokumen tidak dapat diakses karena pengajuan ditolak.', 403);
    }

    // If template ID is passed but not the model
    if (!$template && $request->route('template')) {
        $template = SuratTemplate::find($request->route('template'));
    }

        // Load necessary relationships
        $surat->load(['jenis.templates', 'pemohonDosen', 'pemohonMahasiswa', 'mahasiswa', 'approvals.role', 'approvals.dosen']);

        $pdfService = app(\App\Services\PdfGeneratorService::class);
        $previewHtml = $pdfService->generateSuratHtml($surat, $template);

        $backUrl = route('admin.surat.show', $surat);
        $downloadUrl = route('admin.surat.download', $surat);

        return view('admin.surat.preview-html', compact('surat', 'previewHtml', 'backUrl', 'downloadUrl', 'template'));
    }

    /**
     * Save custom HTML content for the letter.
     */
    public function saveHtml(Request $request, Surat $surat)
    {
        $request->validate([
            'html_content' => 'required|string',
        ]);

        $surat->update([
            'html_content' => $request->input('html_content'),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Reset custom HTML content for the letter.
     */
    public function resetHtml(Surat $surat)
    {
        $surat->update([
            'html_content' => null,
        ]);

        return response()->json(['success' => true]);
    }

    public function downloadPdf(Surat $surat)
    {
        if ($surat->status === 'ditolak') {
            return back()->with('error', 'Dokumen tidak dapat diunduh karena pengajuan ditolak.');
        }


        if ($surat->jenis?->is_uploaded) {
            $path = $surat->generated_file_path ?: $surat->uploaded_pdf_path;
            if (!$path || !\Storage::disk('uploads')->exists($path)) {
                return back()->with('error', 'Berkas PDF tidak ditemukan.');
            }
            return response()->download(\Storage::disk('uploads')->path($path), 'surat_' . ($surat->no_surat ?? $surat->id) . '.pdf');
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
     * Approve surat (move from diajukan to diproses)
     */
    public function approve(Surat $surat)
    {
        if ($surat->status !== 'diajukan') {
            return back()->with('error', 'Hanya surat dengan status "Diajukan" yang dapat diproses.');
        }

        $previousStatus = $surat->status;
        
        $updateData = [
            'status' => 'diproses',
            'approval_status' => 'in_progress',
        ];

        if (empty($surat->no_surat)) {
            $updateData['no_surat'] = $this->generateDefaultNoSurat($surat->surat_jenis_id);
        }

        $surat->update($updateData);

        // Triggger notification to first signer
        app(\App\Services\ApprovalWorkflowService::class)->notifyNextApprover($surat);

        // Notify pemohon
        if ($surat->pemohon_type === 'dosen' && $surat->pemohonDosen) {
            $surat->pemohonDosen->notify(new \App\Notifications\SuratStatusUpdatedNotification($surat, $previousStatus));
        } elseif ($surat->pemohon_type === 'mahasiswa' && $surat->pemohonMahasiswa) {
            $surat->pemohonMahasiswa->notify(new \App\Notifications\SuratStatusUpdatedNotification($surat, $previousStatus));
        }

        return back()->with('success', 'Surat berhasil diproses dan dikirim ke penandatangan.');
    }

    /**
     * Reject surat
     */
    public function reject(Surat $surat)
    {
        if ($surat->status === 'selesai' || $surat->status === 'ditolak') {
            return back()->with('error', 'Surat yang sudah selesai atau ditolak tidak dapat diubah statusnya.');
        }

        $previousStatus = $surat->status;
        $surat->update([
            'status' => 'ditolak',
            'approval_status' => 'rejected',
        ]);

        // Notify pemohon
        if ($surat->pemohon_type === 'dosen' && $surat->pemohonDosen) {
            $surat->pemohonDosen->notify(new \App\Notifications\SuratStatusUpdatedNotification($surat, $previousStatus));
        } elseif ($surat->pemohon_type === 'mahasiswa' && $surat->pemohonMahasiswa) {
            $surat->pemohonMahasiswa->notify(new \App\Notifications\SuratStatusUpdatedNotification($surat, $previousStatus));
        }

        return back()->with('success', 'Surat telah ditolak.');
    }

    public function destroy(Surat $surat)
    {
        // Clean up generated file and any uploaded attachments stored in public disk.
        $paths = [];

        if (is_string($surat->generated_file_path) && $surat->generated_file_path !== '') {
            $paths[] = $surat->generated_file_path;
        }

        $data = $surat->data;
        $stack = is_array($data) ? [$data] : [];
        while (!empty($stack)) {
            $current = array_pop($stack);
            foreach ($current as $v) {
                if (is_array($v)) {
                    $stack[] = $v;
                    continue;
                }
                if (is_string($v) && $v !== '') {
                    $paths[] = $v;
                }
            }
        }

        $paths = array_values(array_unique(array_filter($paths)));
        foreach ($paths as $p) {
            $normalized = str_replace('\\', '/', ltrim((string) $p, '/'));

            // Only allow deleting known directories
            if (Str::startsWith($normalized, ['surat-attachments/', 'documents/surat/generated/', 'documents/surat/attachments/'])) {
                try {
                    Storage::disk('uploads')->delete($normalized);
                } catch (\Throwable $e) {
                    // ignore cleanup errors
                }
            }
        }

        $surat->delete();

        return redirect()->route('admin.surat.index')->with('success', 'Permohonan surat berhasil dihapus.');
    }

    public function previewEmail(Request $request, Surat $surat)
    {
        try {
            // 1. Temporarily override surat fields with current form state if provided
            if ($request->has('no_surat')) $surat->no_surat = $request->input('no_surat');
            
            if ($request->filled('tanggal_surat')) {
                try {
                    $surat->tanggal_surat = Carbon::parse($request->input('tanggal_surat'));
                } catch (\Exception $e) {
                    \Log::warning("Failed to parse date in previewEmail: " . $request->input('tanggal_surat'));
                }
            }
            
            if ($request->has('status')) $surat->status = $request->input('status');
            
            if ($request->has('form_data')) {
                $formData = $request->input('form_data');
                // Ensure data is array
                $currentData = is_array($surat->data) ? $surat->data : [];
                $surat->data = array_merge($currentData, $formData);
            }

            $template = $surat->jenis->template;
            $pdfService = app(PdfGeneratorService::class);

            // 2. Determine default content
            $subject = $template ? $template->email_subject_template : 'Pemberitahuan Terkait Permohonan Surat';
            $body = $template ? $template->email_body_template : "Halo,\n\nPermohonan surat Anda dengan nomor <<surat_no>> saat ini berstatus: <<status>>.\n\nAnda dapat mengunduh dokumen melalui tautan berikut:\n<<link_dokumen>>\n\nTerima kasih.";

            // 3. Replace tags
            if ($template) {
                $subject = $pdfService->replaceSuratTags($subject ?? '', $surat);
                $body = $pdfService->replaceSuratTags($body ?? '', $surat);
            } else {
                $subject = str_replace(['<<surat_no>>', '<<no_surat>>'], $surat->no_surat ?? '-', $subject);
                $body = str_replace(['<<surat_no>>', '<<no_surat>>'], $surat->no_surat ?? '-', $body);
                $body = str_replace('<<status>>', ucfirst($surat->status), $body);
            }

            // 4. Force inject link_dokumen if present in body but not replaced, or as a fallback
            $downloadUrl = route('admin.surat.preview', $surat);
            $body = str_ireplace('<<link_dokumen>>', $downloadUrl, $body);

            return response()->json([
                'subject' => $subject,
                'body' => $body
            ]);
        } catch (\Exception $e) {
            \Log::error("Error in previewEmail: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Gagal memproses pratinjau: ' . $e->getMessage()], 500);
        }
    }

    public function sendEmail(Request $request, Surat $surat)
    {
        $request->validate([
            'recipient' => 'required|email',
            'subject' => 'required|string',
            'body' => 'required|string',
        ]);

        try {
            \Mail::raw($request->body, function ($message) use ($request) {
                $message->to($request->recipient)
                        ->subject($request->subject);
            });

            // Ensure status reaches selesai if it wasn't already
            if ($surat->status !== 'selesai') {
                $surat->update(['status' => 'selesai', 'sent_at' => now()]);
            } else {
                $surat->update(['sent_at' => now()]);
            }

            return back()->with('success', 'Email berhasil dikirim ke ' . $request->recipient);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengirim email: ' . $e->getMessage());
        }
    }
}
