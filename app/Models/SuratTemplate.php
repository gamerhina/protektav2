<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'surat_jenis_id',
        'nama',
        'template_html',
        'header_image_path',
        'paper_size',
        'signature_method',
        'qr_code_enabled',
        'qr_code_position',
        'qr_code_size',
        'available_tags',
        'tag_mappings',
        'tag_types',
        'tag_properties',
        'email_subject_template',
        'email_body_template',
        'header_repeat',
        'header_visibility',
        'header_custom_pages',
        'aktif',
    ];

    protected $casts = [
        'available_tags' => 'array',
        'tag_mappings' => 'array',
        'tag_types' => 'array',
        'tag_properties' => 'array',
        'qr_code_enabled' => 'boolean',
        'qr_code_size' => 'integer',
        'header_repeat' => 'boolean',
        'aktif' => 'boolean',
    ];


    public function jenis()
    {
        return $this->belongsTo(SuratJenis::class, 'surat_jenis_id');
    }

    public static function getAvailableFields(?SuratJenis $jenis = null): array
    {
        $fields = [
            'Surat' => [
                'surat_no' => 'Nomor Surat',
                'surat_tanggal' => 'Tanggal Surat (DD MMMM YYYY)',
                'surat_hari' => 'Hari (Indonesia)',
                'surat_tahun' => 'Tahun',
                'surat_tujuan' => 'Tujuan/Instansi',
                'surat_perihal' => 'Perihal',
                'surat_isi' => 'Isi/Keterangan',
                'surat_email' => 'Email Penerima',
                'surat_jenis_nama' => 'Jenis Surat',
                'surat_qr_code' => 'QR Code Verifikasi',
            ],
        ];

        $allowMahasiswa = true;
        $allowDosen = true;
        $customFields = [];

        if ($jenis && is_array($jenis->form_fields)) {
            $allowMahasiswa = false;
            $allowDosen = false;

            foreach ($jenis->form_fields as $f) {
                if (!is_array($f)) continue;

                $type = (string) ($f['type'] ?? '');
                if ($type === 'pemohon') {
                    $sources = $f['pemohon_sources'] ?? $f['sources'] ?? ['mahasiswa', 'dosen'];
                    if (!is_array($sources)) {
                        $sources = ['mahasiswa', 'dosen'];
                    }
                    $allowMahasiswa = $allowMahasiswa || in_array('mahasiswa', $sources, true);
                    $allowDosen = $allowDosen || in_array('dosen', $sources, true);
                }

                $key = trim((string) ($f['key'] ?? ''));
                $label = trim((string) ($f['label'] ?? ''));
                if ($key === '' || $label === '') continue;

                // auto_no_surat maps to surat_no, pemohon uses built-in dosen/mahasiswa blocks
                if (in_array($type, ['auto_no_surat', 'pemohon'], true)) {
                    continue;
                }

                // For table fields, add each column as a separate field
                if ($type === 'table' && isset($f['columns']) && is_array($f['columns'])) {
                    // Automatically add the 'no' field for row numbering
                    $customFields[$key . '.no'] = $label . ' - Nomor Urut';

                    foreach ($f['columns'] as $col) {
                        if (!is_array($col)) continue;
                        $colKey = trim((string) ($col['key'] ?? ''));
                        $colLabel = trim((string) ($col['label'] ?? ''));
                        if ($colKey === '') continue;
                        
                        $compositeKey = $key . '.' . $colKey;
                        $compositeLabel = $label . ' - ' . ($colLabel !== '' ? $colLabel : $colKey);
                        $customFields[$compositeKey] = $compositeLabel;
                    }
                    continue;
                }

                $customFields[$key] = $label;
            }
        }

        if ($allowDosen) {
            $fields['Pemohon (Dosen)'] = [
                'dosen_nama' => 'Nama Dosen',
                'dosen_nip' => 'NIP Dosen',
                'dosen_email' => 'Email Dosen',
                'dosen_qr_signature' => 'QR Signature Dosen',
                'dosen_signature' => 'Image Signature Dosen',
            ];
        }

        if ($allowMahasiswa) {
            $fields['Mahasiswa'] = [
                'mahasiswa_nama' => 'Nama Mahasiswa',
                'mahasiswa_npm' => 'NPM Mahasiswa',
                'mahasiswa_prodi' => 'Program Studi',
                'mahasiswa_email' => 'Email Mahasiswa',
            ];
        }

        if (!empty($customFields)) {
            $fields['Custom Fields'] = $customFields;
        }

        // Tambahkan Role Persetujuan secara dinamis
        $approvalFields = [];

        // 1. Ambil dari Master Roles
        try {
            $roles = \App\Models\SuratRole::where('is_active', true)->get();
            foreach ($roles as $role) {
                $prefix = strtolower($role->kode);
                $approvalFields[$prefix . '_nama'] = 'Nama ' . $role->nama;
                $approvalFields[$prefix . '_nip'] = 'NIP ' . $role->nama;
                $approvalFields[$prefix . '_jabatan'] = 'Jabatan ' . $role->nama;
                $approvalFields[$prefix . '_qr_signature'] = 'QR Signature ' . $role->nama;
                $approvalFields[$prefix . '_signature'] = 'Image Signature ' . $role->nama;
            }
        } catch (\Exception $e) {}

        // 2. Ambil dari Workflow Steps (Dinamis)
        if ($jenis) {
            $workflowRoles = $jenis->workflowSteps()->get();
            $dynamicMap = [
                'Pembimbing Akademik' => 'pa',
                'Pembimbing 1' => 'p1',
                'Pembimbing 2' => 'p2',
                'Pembahas' => 'pmb'
            ];

            foreach ($workflowRoles as $step) {
                $roleName = $step->role_nama;
                $prefix = null;

                // Cek apakah ini role dinamis
                foreach ($dynamicMap as $key => $p) {
                    if (str_contains(strtolower($roleName), strtolower($key))) {
                        $prefix = $p;
                        break;
                    }
                }

                if ($prefix && !isset($approvalFields[$prefix . '_nama'])) {
                    $approvalFields[$prefix . '_nama'] = 'Nama ' . $roleName;
                    $approvalFields[$prefix . '_nip'] = 'NIP ' . $roleName;
                    $approvalFields[$prefix . '_qr_signature'] = 'QR Signature ' . $roleName;
                    $approvalFields[$prefix . '_signature'] = 'Image Signature ' . $roleName;
                }
            }
        }

        if (!empty($approvalFields)) {
            $fields['Role Persetujuan (Approval)'] = $approvalFields;
        }

        // Pembimbing & Evaluator (General Reference)
        $fields['Pembimbing & Evaluator'] = [
            'pa_nama' => 'Nama Pembimbing Akademik (PA)',
            'pa_nip' => 'NIP Pembimbing Akademik (PA)',
            'pa_qr_signature' => 'QR Signature Pembimbing Akademik (PA)',
            'pa_signature' => 'Image Signature Pembimbing Akademik (PA)',

            'p1_nama' => 'Nama Pembimbing 1',
            'p1_nip' => 'NIP Pembimbing 1',
            'p1_qr_signature' => 'QR Signature Pembimbing 1',
            'p1_signature' => 'Image Signature Pembimbing 1',

            'p2_nama' => 'Nama Pembimbing 2',
            'p2_nip' => 'NIP Pembimbing 2',
            'p2_qr_signature' => 'QR Signature Pembimbing 2',
            'p2_signature' => 'Image Signature Pembimbing 2',

            'pmb_nama' => 'Nama Pembahas / Evaluator',
            'pmb_nip' => 'NIP Pembahas / Evaluator',
            'pmb_qr_signature' => 'QR Signature Pembahas / Evaluator',
            'pmb_signature' => 'Image Signature Pembahas / Evaluator',
        ];

        return $fields;
    }
}
