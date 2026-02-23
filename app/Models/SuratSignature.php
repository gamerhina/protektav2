<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratSignature extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'surat_id',
        'signer_type',
        'signer_name',
        'signer_nip',
        'qr_code',
        'signed_at',
    ];

    /**
     * Cast attributes to proper types
     */
    protected $casts = [
        'signed_at' => 'datetime',
    ];

    /**
     * Signer type labels
     */
    public const SIGNER_TYPES = [
        'admin' => 'Administrator',
        'ketua_jurusan' => 'Ketua Jurusan',
        'kaprodi' => 'Kepala Program Studi',
        'dekan' => 'Dekan',
        'other' => 'Lainnya',
    ];

    /**
     * Relationship with surat
     */
    public function surat()
    {
        return $this->belongsTo(Surat::class, 'surat_id');
    }

    /**
     * Get the signer type label
     */
    public function getSignerTypeLabelAttribute(): string
    {
        return self::SIGNER_TYPES[$this->signer_type] ?? $this->signer_type;
    }

    /**
     * Generate the verification URL for this signature
     */
    public function getVerificationUrlAttribute(): string
    {
        return url('/verify/signature/' . $this->qr_code);
    }
}
