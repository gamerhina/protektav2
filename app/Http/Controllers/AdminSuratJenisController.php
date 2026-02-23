<?php

namespace App\Http\Controllers;

use App\Models\SuratJenis;
use App\Models\SuratRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminSuratJenisController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        
        $query = SuratJenis::with('templates');
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%$search%")
                  ->orWhere('kode', 'like', "%$search%");
            });
        }
        
        $items = $query->orderBy('nama')->paginate(20)->withQueryString();
        
        return view('admin.suratjenis.index', compact('items'));
    }

    public function create()
    {
        $allDosens = \App\Models\Dosen::orderBy('nama')->get();
        $suratRoles = SuratRole::active()->ordered()->with('delegatedDosen')->get();
        return view('admin.suratjenis.create', compact('allDosens', 'suratRoles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:50|alpha_dash|unique:surat_jenis,kode',
            'keterangan' => 'nullable|string',
            'informasi' => 'nullable|string',
            'form_fields' => 'nullable|array',
            'aktif' => 'nullable|boolean',
            'allow_download' => 'nullable|boolean',
            'is_uploaded' => 'nullable|boolean',
            'target_pemohon' => 'nullable|array',
            'target_pemohon.*' => 'in:mahasiswa,dosen',
            'upload_max_kb' => 'nullable|integer|min:1',
        ]);

        $validated['aktif'] = $request->boolean('aktif');
        $validated['allow_download'] = $request->boolean('allow_download');
        $validated['is_uploaded'] = $request->boolean('is_uploaded');
        // Ensure default array if null, or keep null? Keep null or empty array.
        // If not sent (checkboxes unchecked), it might be missing or empty?
        // If checkboxes not checked, key is missing.
        $validated['target_pemohon'] = $request->input('target_pemohon', []);
        $validated['upload_max_kb'] = $request->input('upload_max_kb') ?: 10240;

        if (Schema::hasColumn('surat_jenis', 'form_fields')) {
            $validated['form_fields'] = $this->normalizeFormFields($request->input('form_fields', []));
        } else {
            unset($validated['form_fields']);
        }

        \DB::transaction(function() use ($validated, $request) {
            $suratJenis = SuratJenis::create($validated);

            // Handle Workflow Steps
            if ($request->has('workflow_enabled')) {
                foreach ($request->input('workflow', []) as $index => $step) {
                    $roleSelect = $step['role_select'] ?? '';
                    $roleNama = $step['role_nama'] ?? null;
                    $dosenId = $step['dosen_id'] ?? null;

                    // Resolve role_nama from role_select
                    $dynamicRoles = ['pembimbing_akademik', 'pembimbing_1', 'pembimbing_2', 'pembahas'];

                    if (in_array($roleSelect, $dynamicRoles)) {
                        $dosenId = null;
                        $roleNama = $roleNama ?: ucwords(str_replace('_', ' ', $roleSelect));
                    } elseif (str_starts_with($roleSelect, 'role_')) {
                        $roleId = (int) str_replace('role_', '', $roleSelect);
                        $masterRole = SuratRole::find($roleId);
                        if ($masterRole) {
                            $roleNama = $masterRole->nama;
                            if (empty($dosenId) && $masterRole->dosen_id) {
                                $dosenId = $masterRole->dosen_id;
                            }
                        }
                    }

                    if (in_array($dosenId, $dynamicRoles)) {
                        $dosenId = null;
                    }

                    if (empty($dosenId) && !in_array($roleSelect, $dynamicRoles)) continue;

                    $suratJenis->workflowSteps()->create([
                        'role_nama' => $roleNama,
                        'dosen_id' => $dosenId,
                        'urutan' => $step['urutan'] ?? ($index + 1),
                        'is_required' => true,
                    ]);
                }
            }
        });

        return redirect()->route('admin.suratjenis.index')->with('success', 'Jenis surat berhasil dibuat.');
    }

    public function edit(SuratJenis $suratJenis)
    {
        $suratJenis->load(['template', 'workflowSteps.dosen']);
        $allDosens = \App\Models\Dosen::orderBy('nama')->get();
        $suratRoles = SuratRole::active()->ordered()->with('delegatedDosen')->get();
        return view('admin.suratjenis.edit', compact('suratJenis', 'allDosens', 'suratRoles'));
    }

    public function update(Request $request, SuratJenis $suratJenis)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:50|alpha_dash|unique:surat_jenis,kode,' . $suratJenis->id,
            'keterangan' => 'nullable|string',
            'informasi' => 'nullable|string',
            'form_fields' => 'nullable|array',
            'aktif' => 'nullable|boolean',
            'allow_download' => 'nullable|boolean',
            'is_uploaded' => 'nullable|boolean',
            'target_pemohon' => 'nullable|array',
            'target_pemohon.*' => 'in:mahasiswa,dosen',
            'upload_max_kb' => 'nullable|integer|min:1',
        ]);

        $validated['aktif'] = $request->boolean('aktif');
        $validated['allow_download'] = $request->boolean('allow_download');
        $validated['is_uploaded'] = $request->boolean('is_uploaded');
        $validated['target_pemohon'] = $request->input('target_pemohon', []);
        $validated['upload_max_kb'] = $request->input('upload_max_kb') ?: 10240;

        if (Schema::hasColumn('surat_jenis', 'form_fields')) {
            $validated['form_fields'] = $this->normalizeFormFields($request->input('form_fields', []));
        } else {
            unset($validated['form_fields']);
        }

        \DB::transaction(function() use ($suratJenis, $validated, $request) {
            $suratJenis->update($validated);

            // Handle Workflow Steps
            if ($request->has('workflow_enabled')) {
                $suratJenis->workflowSteps()->delete();
                foreach ($request->input('workflow', []) as $index => $step) {
                    $roleSelect = $step['role_select'] ?? '';
                    $roleNama = $step['role_nama'] ?? null;
                    $dosenId = $step['dosen_id'] ?? null;

                    // Resolve role_nama from role_select
                    $dynamicRoles = ['pembimbing_akademik', 'pembimbing_1', 'pembimbing_2', 'pembahas'];

                    if (in_array($roleSelect, $dynamicRoles)) {
                        $dosenId = null;
                        $roleNama = $roleNama ?: ucwords(str_replace('_', ' ', $roleSelect));
                    } elseif (str_starts_with($roleSelect, 'role_')) {
                        $roleId = (int) str_replace('role_', '', $roleSelect);
                        $masterRole = SuratRole::find($roleId);
                        if ($masterRole) {
                            $roleNama = $masterRole->nama;
                            if (empty($dosenId) && $masterRole->dosen_id) {
                                $dosenId = $masterRole->dosen_id;
                            }
                        }
                    }

                    if (in_array($dosenId, $dynamicRoles)) {
                        $dosenId = null;
                    }

                    if (empty($dosenId) && !in_array($roleSelect, $dynamicRoles)) continue;

                    $suratJenis->workflowSteps()->create([
                        'role_nama' => $roleNama,
                        'dosen_id' => $dosenId,
                        'urutan' => $step['urutan'] ?? ($index + 1),
                        'is_required' => true,
                    ]);
                }
            }
        });

        return redirect()->route('admin.suratjenis.edit', $suratJenis)->with('success', 'Jenis surat dan alur persetujuan berhasil diperbarui.');
    }

    public function destroy(SuratJenis $suratJenis)
    {
        $suratJenis->delete();
        return redirect()->route('admin.suratjenis.index')->with('success', 'Jenis surat berhasil dihapus.');
    }

    private function normalizeFormFields($raw): array
    {
        if (!is_array($raw)) {
            return [];
        }

        $out = [];
        foreach ($raw as $item) {
            if (!is_array($item)) {
                continue;
            }

            $label = trim((string) ($item['label'] ?? ''));
            $key = trim((string) ($item['key'] ?? ''));
            $type = trim((string) ($item['type'] ?? 'text'));

            if ($label === '' || $key === '') {
                continue;
            }

            $field = [
                'label' => $label,
                'key' => $key,
                'type' => $type,
                'placeholder' => (string) ($item['placeholder'] ?? ''),
                'required' => (bool) ($item['required'] ?? false),
            ];

            if ($type === 'pemohon') {
                $sources = $item['pemohon_sources'] ?? $item['sources'] ?? [];
                if (is_string($sources)) {
                    $sources = preg_split('/\s*,\s*/', trim($sources)) ?: [];
                }
                $sources = array_values(array_unique(array_filter(array_map('trim', (array) $sources))));
                $sources = array_values(array_intersect($sources, ['mahasiswa', 'dosen']));
                if (empty($sources)) {
                    $sources = ['mahasiswa', 'dosen'];
                }
                $field['pemohon_sources'] = $sources;
            }

            if (in_array($type, ['select', 'radio', 'checkbox'], true)) {
                $optionsRaw = $item['options'] ?? [];
                if (is_string($optionsRaw)) {
                    $optionsRaw = preg_split('/\r\n|\r|\n/', $optionsRaw) ?: [];
                }
                $options = [];
                foreach ((array) $optionsRaw as $line) {
                    $line = trim((string) $line);
                    if ($line === '') {
                        continue;
                    }
                    if (str_contains($line, '|')) {
                        [$val, $lbl] = array_map('trim', explode('|', $line, 2));
                        $options[] = ['value' => $val, 'label' => $lbl !== '' ? $lbl : $val];
                    } else {
                        $options[] = ['value' => $line, 'label' => $line];
                    }
                }
                $field['options'] = $options;
            }

            if ($type === 'file') {
                $extRaw = $item['extensions'] ?? '';
                $extensions = [];
                if (is_string($extRaw)) {
                    $extensions = array_filter(array_map('trim', explode(',', $extRaw)));
                } elseif (is_array($extRaw)) {
                    $extensions = array_filter(array_map('trim', $extRaw));
                }
                $field['extensions'] = array_values($extensions);
                $field['max_kb'] = (int) ($item['max_kb'] ?? 0);
            }

            if ($type === 'table') {
                $columnsRaw = $item['columns'] ?? [];
                if (is_string($columnsRaw)) {
                    $columnsRaw = preg_split('/\r\n|\r|\n/', $columnsRaw) ?: [];
                }
                $columns = [];
                foreach ((array) $columnsRaw as $line) {
                    $line = trim((string) $line);
                    if ($line === '') {
                        continue;
                    }
                    if (str_contains($line, '|')) {
                        $parts = array_map('trim', explode('|', $line, 3));
                        $colKey = $parts[0] ?? '';
                        $colLabel = $parts[1] ?? '';
                        $colType = $parts[2] ?? 'text';

                        if ($colKey !== '') {
                            $columns[] = [
                                'key' => $colKey,
                                'label' => $colLabel !== '' ? $colLabel : $colKey,
                                'type' => $colType !== '' ? $colType : 'text'
                            ];
                        }
                    } else {
                        $columns[] = ['key' => $line, 'label' => $line, 'type' => 'text'];
                    }
                }
                $field['columns'] = $columns;
            }

            $out[] = $field;
        }

        return $out;
    }
}
