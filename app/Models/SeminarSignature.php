<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeminarSignature extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'seminar_id',
        'dosen_id',
        'jenis_penilai',
        'tanda_tangan',
        'tanggal_ttd',
        'verification_token',
        'qr_code_path',
        'signature_type',
    ];

    /**
     * Cast attributes to proper types
     */
    protected $casts = [
        'tanggal_ttd' => 'datetime',
    ];

    /**
     * Relationship with seminar
     */
    public function seminar()
    {
        return $this->belongsTo(Seminar::class, 'seminar_id');
    }

    /**
     * Relationship with dosen
     */
    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }
}
