<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'kode',
        'deskripsi',
        'dosen_id',
        'urutan',
        'warna',
        'is_active',
    ];

    protected $casts = [
        'urutan' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the delegated dosen for this role
     */
    public function delegatedDosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }

    /**
     * Get all assignments for this role
     */
    // Relationship removed because surat_workflow_steps no longer stores role_id
    /*
    public function assignments()
    {
        return $this->hasMany(SuratRoleAssignment::class, 'role_id');
    }
    */

    /**
     * Get all approvals for this role
     */
    public function approvals()
    {
        return $this->hasMany(SuratApproval::class, 'surat_role_id');
    }

    /**
     * Get templates that use this role
     */
    public function templates()
    {
        // This relationship is likely broken now as well, but we'll update the key just in case
        return $this->hasManyThrough(
            SuratTemplate::class,
            SuratRoleAssignment::class,
            'surat_role_id', // Changed from role_id
            'id',
            'id',
            'template_id'
        );
    }

    /**
     * Scope for active roles only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordering by sequence
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan');
    }
}
