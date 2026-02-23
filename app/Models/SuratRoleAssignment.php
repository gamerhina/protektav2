<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratRoleAssignment extends Model
{
    use HasFactory;

    protected $table = 'surat_workflow_steps';

    protected $fillable = [
        'surat_jenis_id',
        'role_nama',
        'dosen_id',
        'urutan',
        'is_required',
    ];

    protected $casts = [
        'urutan' => 'integer',
        'is_required' => 'boolean',
    ];

    /**
     * Get the surat jenis for this assignment
     */
    public function suratJenis()
    {
        return $this->belongsTo(SuratJenis::class, 'surat_jenis_id');
    }

    /**
     * Get the assigned dosen/user
     */
    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }

    /**
     * Scope for ordering by sequence
     */
    public function scopeOrdered($query)
    {
        return $this->orderBy('urutan');
    }

    /**
     * Scope for required assignments only
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }
}
