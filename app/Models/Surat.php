<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Surat extends Model
{
    use HasFactory;

    protected $fillable = [
        'surat_jenis_id',
        'pemohon_type',
        'pemohon_dosen_id',
        'pemohon_mahasiswa_id',
        'pemohon_admin_id',
        'mahasiswa_id',
        'untuk_type',
        'no_surat',
        'tanggal_surat',
        'tujuan',
        'perihal',
        'isi',
        'data',
        'penerima_email',
        'status',
        'generated_file_path',
        'sent_at',
        'verification_token',
        'qr_code_path',
        'signature_type',
        'html_content',
        'uploaded_pdf_path',
        'approval_status',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'sent_at' => 'datetime',
        'data' => 'array',
    ];

    public function jenis()
    {
        return $this->belongsTo(SuratJenis::class, 'surat_jenis_id');
    }

    public function pemohonDosen()
    {
        return $this->belongsTo(Dosen::class, 'pemohon_dosen_id');
    }

    public function pemohonMahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'pemohon_mahasiswa_id');
    }

    public function pemohonAdmin()
    {
        return $this->belongsTo(\App\Models\Admin::class, 'pemohon_admin_id');
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    /**
     * Relationship with signatures (Optional, if we keep the separate table logic)
     */
    public function signatures()
    {
        return $this->hasMany(SuratSignature::class, 'surat_id');
    }

    /**
     * Get all approvals for this surat
     */
    public function approvals()
    {
        return $this->hasMany(SuratApproval::class)->ordered();
    }

    /**
     * Get pending approvals
     */
    public function pendingApprovals()
    {
        return $this->hasMany(SuratApproval::class)->pending();
    }

    /**
     * Get the current pending approval (next in sequence)
     */
    public function currentPendingApproval()
    {
        return $this->pendingApprovals()->ordered()->first();
    }

    /**
     * Check if surat needs approval
     */
    public function needsApproval()
    {
        return $this->approval_status === 'pending' && $this->approvals()->pending()->exists();
    }

    /**
     * Check if surat is fully approved
     */
    public function isFullyApproved()
    {
        if ($this->approval_status === 'approved') {
            return true;
        }

        // If it has approvals but none are pending, it's done
        return $this->approvals()->exists() && !$this->approvals()->pending()->exists();
    }

    /**
     * Check if surat was rejected
     */
    public function isRejected()
    {
        return $this->approval_status === 'rejected';
    }

    /**
     * Get the verification URL for this surat
     */
    public function getVerificationUrlAttribute(): string
    {
        if (!$this->verification_token) {
            return '';
        }
        // Using the public verification route with a cryptographic signature
        $longUrl = \Illuminate\Support\Facades\URL::signedRoute('verify.surat', ['suratId' => $this->id]);
        return app(\App\Services\UrlShortenerService::class)->shorten($longUrl);
    }

    public function getPemohonAttribute()
    {
        if ($this->pemohon_type === 'dosen') {
            return $this->pemohonDosen;
        }
        if ($this->pemohon_type === 'mahasiswa') {
            return $this->pemohonMahasiswa;
        }
        if ($this->pemohon_type === 'admin') {
            return $this->pemohonAdmin;
        }
        return null;
    }

    public function getWorkflowStatusTextAttribute(): string
    {
        if ($this->isRejected()) {
            return 'Ditolak';
        }

        if ($this->isFullyApproved()) {
            return 'Selesai Disetujui';
        }

        if (!$this->approvals()->exists()) {
            return 'Menunggu Antrean';
        }

        $lowestPendingUrutan = $this->approvals()->pending()->min('urutan');
        if ($lowestPendingUrutan === null) {
            return 'Selesai Disetujui';
        }

        $pendingApprovers = $this->approvals()
            ->pending()
            ->where('urutan', $lowestPendingUrutan)
            ->with('dosen')
            ->get();

        $names = $pendingApprovers->map(function ($app) {
            return $app->role_nama ?: ($app->dosen->nama ?? 'Pejabat');
        })->unique()->join(', ');

        return "Menunggu " . ($this->jenis?->is_uploaded ? "TTD" : "Persetujuan") . " $lowestPendingUrutan ($names)";
    }
    public function comments()
    {
        return $this->hasMany(SuratComment::class)->latest();
    }
}
