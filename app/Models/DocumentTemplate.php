<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'kode',
        'seminar_jenis_id',
        'template_html',
        'header_image_path',
        'header_repeat',
        'header_visibility',
        'header_custom_pages',
        'paper_size',
        'signature_method',
        'qr_code_enabled',
        'qr_code_position',
        'qr_code_size',
        'keterangan',
        'mapping_fields',
        'available_tags',
        'tag_mappings',
        'tag_types',
        'tag_properties',
        'email_subject_template',
        'email_body_template',
        'file_path',
        'aktif',
        'download_rules',
    ];

    protected $casts = [
        'mapping_fields' => 'array',
        'available_tags' => 'array',
        'tag_mappings' => 'array',
        'tag_types' => 'array',
        'tag_properties' => 'array',
        'qr_code_enabled' => 'boolean',
        'qr_code_size' => 'integer',
        'header_repeat' => 'boolean',
        'aktif' => 'boolean',
        'download_rules' => 'array',
    ];

    public function seminarJenis()
    {
        return $this->belongsTo(SeminarJenis::class, 'seminar_jenis_id');
    }

    public static function getAvailableFields($seminarJenis = null)
    {
        $fields = [
            'Mahasiswa' => [
                'mahasiswa_nama' => 'Nama Lengkap Mahasiswa',
                'mahasiswa_npm' => 'NPM Mahasiswa',
                'mahasiswa_prodi' => 'Program Studi',
                'mahasiswa_email' => 'Email Mahasiswa',
                'mahasiswa_no_hp' => 'Nomor WhatsApp Mahasiswa',
            ],
            'Seminar' => [
                'seminar_no_surat' => 'Nomor Surat Seminar',
                'seminar_judul' => 'Judul Seminar/Skripsi',
                'seminar_tanggal' => 'Tanggal Pelaksanaan (1 Januari 2024)',
                'seminar_hari' => 'Hari Pelaksanaan (Senin)',
                'seminar_tahun' => 'Tahun Pelaksanaan (2024)',
                'seminar_waktu_mulai' => 'Waktu Mulai',
                'seminar_lokasi' => 'Lokasi Seminar',
                'seminar_status' => 'Status Seminar',
                'seminar_jenis_nama' => 'Nama Jenis Seminar',
                'seminar_qr_code' => 'QR Code Verifikasi',
            ],
            'Dosen & Evaluator' => [
                'p1_nama' => 'Nama Pembimbing 1',
                'p1_nip' => 'NIP Pembimbing 1',
                'p1_email' => 'Email Pembimbing 1',
                'p1_qr_signature' => 'QR Signature Pembimbing 1',
                'p1_signature' => 'Image Signature Pembimbing 1',
                'p2_nama' => 'Nama Pembimbing 2',
                'p2_nip' => 'NIP Pembimbing 2',
                'p2_email' => 'Email Pembimbing 2',
                'p2_qr_signature' => 'QR Signature Pembimbing 2',
                'p2_signature' => 'Image Signature Pembimbing 2',
                'pmb_nama' => 'Nama Pembahas / Evaluator',
                'pmb_nip' => 'NIP Pembahas / Evaluator',
                'pmb_email' => 'Email Pembahas / Evaluator',
                'pmb_qr_signature' => 'QR Signature Pembahas / Evaluator',
                'pmb_signature' => 'Image Signature Pembahas / Evaluator',

                'pa_nama' => 'Nama Pembimbing Akademik (PA)',
                'pa_nip' => 'NIP Pembimbing Akademik (PA)',
                'pa_email' => 'Email Pembimbing Akademik (PA)',
                'pa_qr_signature' => 'QR Signature Pembimbing Akademik (PA)',
                'pa_signature' => 'Image Signature Pembimbing Akademik (PA)',
            ],
            'Nilai' => [
                'nilai_akhir' => 'Nilai Akhir (Angka)',
                'nilai_huruf' => 'Nilai Akhir (Huruf: A, B+, dsb)',
                'nilai_terbilang' => 'Nilai Akhir (Terbilang)',
                'nilai_rata' => 'Nilai Rata-rata Evaluator',
                'nilai_catatan' => 'Catatan/Saran Penguji',
                'p1_bobot' => 'Bobot Nilai P1 (%)',
                'p1_nilai' => 'Nilai Asli P1',
                'p1_nilai_bobot' => 'Nilai Terbobot P1',
                'p2_bobot' => 'Bobot Nilai P2 (%)',
                'p2_nilai' => 'Nilai Asli P2',
                'p2_nilai_bobot' => 'Nilai Terbobot P2',
                'pmb_bobot' => 'Bobot Nilai Pembahas (%)',
                'pmb_nilai' => 'Nilai Asli Pembahas',
                'pmb_nilai_bobot' => 'Nilai Terbobot Pembahas',
                'HM' => 'Huruf Mutu (A/B/C/D/E)',
                'NxB' => 'Nilai x Bobot (Total)',
                'p1_nxb' => 'NxB P1 (Nilai x Bobot)',
                'p2_nxb' => 'NxB P2 (Nilai x Bobot)',
                'pmb_nxb' => 'NxB Pembahas (Nilai x Bobot)',
                'terbilang' => 'Nilai Terbilang (Kata-kata)',
                'dinyatakan' => 'Hasil (LULUS/TIDAK LULUS)',
                'diperkenankan' => 'Status Diperkenankan/Belum (Lanjutan)',
            ],
        ];

        // Tambahkan Role Persetujuan secara dinamis
        try {
            $roles = \App\Models\SuratRole::where('is_active', true)->get();
            if ($roles->count() > 0) {
                $approvalFields = [];
                foreach ($roles as $role) {
                    $prefix = strtolower($role->kode);
                    $approvalFields[$prefix . '_nama'] = 'Nama ' . $role->nama;
                    $approvalFields[$prefix . '_nip'] = 'NIP ' . $role->nama;
                    $approvalFields[$prefix . '_jabatan'] = 'Jabatan ' . $role->nama;
                    $approvalFields[$prefix . '_qr_signature'] = 'QR Signature ' . $role->nama;
                    $approvalFields[$prefix . '_signature'] = 'Image Signature ' . $role->nama;
                }
                $fields['Role Persetujuan (Approval)'] = $approvalFields;
            }
        } catch (\Exception $e) {
            // Abaikan jika tabel belum ada atau error database lainnya
        }

        // Add Assessment Aspects
        if ($seminarJenis) {
            $aspects = $seminarJenis->assessmentAspects()->orderBy('evaluator_type')->orderBy('urutan')->get();
            if ($aspects->count() > 0) {
                $aspectFields = [];
                $grouped = $aspects->groupBy('evaluator_type');
                
                foreach ($grouped as $type => $typeAspects) {
                    $prefix = ($type === 'pembahas' ? 'pmb' : $type);
                    $i = 1;
                    foreach ($typeAspects as $aspect) {
                        $key = $prefix . '_' . $i;
                        $aspectFields[$key] = '[' . strtoupper($type) . '] ' . $aspect->nama_aspek;
                        $i++;
                    }
                }
                $fields['Aspek Penilaian (Scores)'] = $aspectFields;
            }
        }

        // Add Dynamic Fields from SeminarJenis
        if ($seminarJenis && !empty($seminarJenis->berkas_syarat_items)) {
            $dynamicFields = [];
            foreach ($seminarJenis->berkas_syarat_items as $item) {
                if (isset($item['key'])) {
                    $dynamicFields[$item['key']] = ($item['label'] ?? $item['key']);
                }
            }
            if (!empty($dynamicFields)) {
                $fields['Data Form Seminar (Dynamic)'] = $dynamicFields;
            }
        }

        return $fields;
    }
}
