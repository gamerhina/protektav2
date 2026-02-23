<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\Surat;
use App\Models\SuratJenis;
use App\Models\Admin;
use App\Notifications\SuratSubmittedNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Models\SuratTemplate;
use Illuminate\Http\Request;

class DosenSuratController extends Controller
{
    public function index(Request $request)
    {
        $dosen = auth('dosen')->user();
        $search = $request->query('search');
        $statusFilter = $request->query('status_filter');
        $sort = $request->query('sort', 'created_at');
        $direction = $request->query('direction', 'desc');
        $perPage = $request->query('per_page', 20);

        // Validasi sort column
        $allowedSorts = ['no_surat', 'tanggal_surat', 'status', 'created_at', 'surat_jenis_id'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }

        $items = Surat::with(['jenis', 'mahasiswa'])
            ->where('pemohon_dosen_id', $dosen->id)
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
            ->when($statusFilter, function($query, $status) {
                if ($status === 'diajukan') {
                    $query->whereIn('status', ['diajukan', 'submitted']);
                } elseif ($status === 'diproses') {
                    $query->whereIn('status', ['diproses', 'approved_by_admin', 'approved_by_pimpinan']);
                } elseif ($status === 'selesai') {
                    $query->whereIn('status', ['selesai', 'completed', 'approved']);
                } elseif ($status === 'ditolak') {
                    $query->whereIn('status', ['ditolak', 'rejected']);
                } else {
                    $query->where('status', $status);
                }
            })
            ->when($sort === 'surat_jenis_id', function ($query) use ($direction) {
                $query->join('surat_jenis', 'surats.surat_jenis_id', '=', 'surat_jenis.id')
                    ->orderBy('surat_jenis.nama', $direction)
                    ->select('surats.*');
            }, function ($query) use ($sort, $direction) {
                $query->orderBy($sort, $direction);
            })
            ->paginate($perPage)
            ->withQueryString();

        return view('dosen.surat.index', compact('items'));
    }

    public function show(Surat $surat)
    {
        $dosen = auth('dosen')->user();

        if ($surat->pemohon_type !== 'dosen' || (int) $surat->pemohon_dosen_id !== (int) $dosen->id) {
            abort(403);
        }

        $surat->load(['jenis.templates', 'approvals.role', 'approvals.dosen', 'mahasiswa']);
        $mahasiswas = Mahasiswa::orderBy('nama')->get(['id', 'nama', 'npm', 'email']);

        return view('dosen.surat.show', compact('surat', 'mahasiswas'));
    }

    public function create()
    {
        $dosen = auth('dosen')->user();
        
        // Get active letter types that allow dosen as pemohon
        $jenisList = SuratJenis::where('aktif', true)
            ->get()
            ->filter(function ($jenis) {
                // Check target_pemohon setting
                $targets = $jenis->target_pemohon;
                if (!is_null($targets) && !in_array('dosen', $targets)) {
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
                    return in_array('dosen', (array)$sources);
                }

                return true; // Default to allow if no pemohon field defined
            })
            ->sortBy('nama')
            ->values();

        $mahasiswas = Mahasiswa::orderBy('nama')->get();

        $jenisListPayload = $jenisList
            ->map(fn ($j) => [
                'id' => $j->id, 
                'nama' => $j->nama, 
                'form_fields' => $j->form_fields,
                'informasi' => $j->informasi
            ])
            ->values();

        $mahasiswasPayload = $mahasiswas
            ->map(fn ($m) => ['id' => $m->id, 'nama' => $m->nama, 'npm' => $m->npm, 'email' => $m->email])
            ->values();

        $currentDosenPayload = [
            'id' => $dosen?->id,
            'nama' => $dosen?->nama ?? $dosen?->name ?? 'Dosen',
            'nip' => $dosen?->nip,
            'email' => $dosen?->email,
        ];

        return view('dosen.surat.create', compact('jenisList', 'mahasiswas', 'jenisListPayload', 'mahasiswasPayload', 'currentDosenPayload'));
    }

    public function store(Request $request)
    {
        $dosen = auth('dosen')->user();

        $request->validate([
            'surat_jenis_id' => 'required|exists:surat_jenis,id',
        ]);

        $jenis = SuratJenis::findOrFail((int) $request->input('surat_jenis_id'));
        $formFields = is_array($jenis->form_fields) ? $jenis->form_fields : [];

        // Ensure pemohon field (if configured) is always set to the authenticated dosen
        $data = is_array($request->input('form_data')) ? $request->input('form_data') : [];
        foreach ($formFields as $f) {
            if (!is_array($f)) continue;
            if (($f['type'] ?? null) !== 'pemohon') continue;
            $key = trim((string) ($f['key'] ?? ''));
            if ($key === '') continue;
            if (!isset($data[$key]) || !is_array($data[$key])) {
                $data[$key] = [];
            }
            $data[$key]['type'] = 'dosen';
            $data[$key]['id'] = (int) $dosen->id;
            break;
        }
        $request->merge(['form_data' => $data]);

        $rules = [
            'surat_jenis_id' => 'required|exists:surat_jenis,id',
            'form_data' => 'nullable|array',
            'form_files' => 'nullable|array',
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
                $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'array';
                $rules["form_data.$key.type"] = ($required ? 'required|' : 'nullable|') . 'in:dosen';
                $rules["form_data.$key.id"] = ($required ? 'required|' : 'nullable|') . 'integer|min:1';
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

            $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'string';
        }

        if ($jenis->is_uploaded) {
            $rules['uploaded_pdf'] = 'required|file|mimes:pdf|max:10240';
        }

        $validated = $request->validate($rules);

        $data = is_array($validated['form_data'] ?? null) ? $validated['form_data'] : [];
        $files = is_array($validated['form_files'] ?? null) ? $validated['form_files'] : [];

        $payload = [
            'surat_jenis_id' => $jenis->id,
            'pemohon_type' => 'dosen',
            'pemohon_dosen_id' => $dosen->id,
            'pemohon_mahasiswa_id' => null,
            'mahasiswa_id' => null,
            'untuk_type' => 'umum',
            'no_surat' => null,
            'tanggal_surat' => now()->timezone('Asia/Jakarta')->toDateString(),
            'tujuan' => null,
            'perihal' => null,
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

        // Dosen rules: jika untuk mahasiswa, mahasiswa_id wajib ada
        if (($payload['untuk_type'] ?? null) === 'mahasiswa' && empty($payload['mahasiswa_id'])) {
            return redirect()->back()->withInput()->withErrors(['form_data.mahasiswa_id' => 'Mahasiswa wajib dipilih untuk jenis permohonan ini.']);
        }

        if (($payload['untuk_type'] ?? null) !== 'mahasiswa') {
            $payload['mahasiswa_id'] = null;
        }

        // Handle uploads
        $stored = [];
        foreach ($files as $k => $file) {
            if (!$file) continue;
            $stored[$k] = $file->store('documents/surat/attachments', 'uploads');
        }

        $payload['data'] = array_merge($data, $stored);

        $surat = Surat::create($payload);

        // Initiate approval workflow for uploaded PDF
        if ($jenis->is_uploaded) {
            app(\App\Services\ApprovalWorkflowService::class)->initiate($surat);
        }

        // Send notification to all admins
        $admins = Admin::all();
        foreach ($admins as $admin) {
            $admin->notify(new SuratSubmittedNotification($surat));
        }

        return redirect()->route('dosen.surat.index')->with('success', 'Permohonan surat berhasil dikirim.');
    }

    public function update(Request $request, Surat $surat)
    {
        $dosen = auth('dosen')->user();

        if ($surat->pemohon_type !== 'dosen' || (int) $surat->pemohon_dosen_id !== (int) $dosen->id) {
            abort(403);
        }

        if ($surat->status !== 'diajukan') {
            return redirect()->route('dosen.surat.show', $surat)->with('error', 'Permohonan yang sudah diproses tidak dapat diubah.');
        }

        $jenis = $surat->jenis;
        $formFields = is_array($jenis->form_fields) ? $jenis->form_fields : [];

        $rules = [
            'form_data' => 'nullable|array',
            'form_files' => 'nullable|array',
        ];

        $dateFieldKey = null;
        foreach ($formFields as $f) {
            if (!is_array($f)) continue;
            $key = trim((string) ($f['key'] ?? ''));
            $type = trim((string) ($f['type'] ?? 'text'));
            $required = (bool) ($f['required'] ?? false);
            if ($key === '' || $type === 'pemohon' || $type === 'auto_no_surat') continue;

            if ($type === 'date') {
                $dateFieldKey = $dateFieldKey ?? $key;
                $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'date';
            } elseif ($type === 'email') {
                $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'email';
            } elseif ($type === 'number') {
                $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'numeric';
            } elseif (in_array($type, ['select', 'radio'], true)) {
                $options = [];
                foreach ((array) ($f['options'] ?? []) as $opt) {
                    if (is_array($opt) && isset($opt['value'])) $options[] = (string) $opt['value'];
                }
                $inRule = !empty($options) ? '|in:' . implode(',', array_map(fn ($v) => str_replace(',', '\\,', $v), $options)) : '';
                $rules["form_data.$key"] = ($required ? 'required' : 'nullable') . $inRule;
            } elseif ($type === 'checkbox') {
                $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . (isset($f['options']) && !empty($f['options']) ? 'array' : 'boolean');
            } elseif ($type === 'file') {
                $exts = array_filter(array_map('trim', (array) ($f['extensions'] ?? [])));
                $maxKb = (int) ($f['max_kb'] ?? 0);
                $rule = 'nullable|file'; // Files are always optional on update
                if (!empty($exts)) $rule .= '|mimes:' . implode(',', $exts);
                if ($maxKb > 0) $rule .= '|max:' . $maxKb;
                $rules["form_files.$key"] = $rule;
            } else {
                $rules["form_data.$key"] = ($required ? 'required|' : 'nullable|') . 'string';
            }
        }

        $validated = $request->validate($rules);
        $data = is_array($validated['form_data'] ?? null) ? $validated['form_data'] : [];
        $files = is_array($validated['form_files'] ?? null) ? $validated['form_files'] : [];

        $payload = [
            'tanggal_surat' => $surat->tanggal_surat,
            'tujuan' => $surat->tujuan,
            'perihal' => $surat->perihal,
            'isi' => $surat->isi,
            'penerima_email' => $surat->penerima_email,
            'untuk_type' => $surat->untuk_type,
            'mahasiswa_id' => $surat->mahasiswa_id,
        ];

        if ($dateFieldKey && !empty($data[$dateFieldKey])) {
            try { $payload['tanggal_surat'] = Carbon::parse($data[$dateFieldKey])->toDateString(); } catch (\Throwable $e) {}
        }

        foreach (['tujuan', 'perihal', 'isi', 'penerima_email', 'untuk_type', 'mahasiswa_id'] as $k) {
            if (array_key_exists($k, $data)) {
                $payload[$k] = $data[$k];
                unset($data[$k]);
            }
        }

        if (($payload['untuk_type'] ?? null) === 'mahasiswa' && empty($payload['mahasiswa_id'])) {
            return redirect()->back()->withInput()->withErrors(['form_data.mahasiswa_id' => 'Mahasiswa wajib dipilih untuk jenis permohonan ini.']);
        }

        if (($payload['untuk_type'] ?? null) !== 'mahasiswa') $payload['mahasiswa_id'] = null;

        $stored = [];
        foreach ($files as $k => $file) {
            if ($file) $stored[$k] = $file->store('documents/surat/attachments', 'uploads');
        }

        $payload['data'] = array_merge($surat->data ?? [], $data, $stored);
        $surat->update($payload);

        return redirect()->route('dosen.surat.index')->with('success', 'Permohonan surat berhasil diperbarui.');
    }

    public function destroy(Surat $surat)
    {
        $dosen = auth('dosen')->user();

        if ($surat->pemohon_type !== 'dosen' || (int) $surat->pemohon_dosen_id !== (int) $dosen->id) {
            abort(403);
        }

        $cancellableStatuses = ['diajukan', 'submitted'];
        if (!in_array($surat->status, $cancellableStatuses)) {
            return redirect()->route('dosen.surat.show', $surat)->with('error', 'Permohonan yang sudah diproses tidak dapat dibatalkan.');
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

        return redirect()->route('dosen.surat.index')->with('success', 'Permohonan surat berhasil dibatalkan.');
    }

    public function downloadPdf(Surat $surat)
    {
        $dosen = auth('dosen')->user();

        if ($surat->pemohon_type !== 'dosen' || (int) $surat->pemohon_dosen_id !== (int) $dosen->id) {
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
        $dosen = auth('dosen')->user();

        if ($surat->pemohon_type !== 'dosen' || (int) $surat->pemohon_dosen_id !== (int) $dosen->id) {
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
        $dosen = auth('dosen')->user();

        if ($surat->pemohon_type !== 'dosen' || (int) $surat->pemohon_dosen_id !== (int) $dosen->id) {
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
}
