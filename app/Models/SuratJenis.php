<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratJenis extends Model
{
    use HasFactory;

    protected $table = 'surat_jenis';

    protected $fillable = [
        'nama',
        'kode',
        'keterangan',
        'informasi',
        'form_fields',
        'aktif',
        'allow_download',
        'is_uploaded',
        'upload_max_kb',
        'target_pemohon',
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'allow_download' => 'boolean',
        'is_uploaded' => 'boolean',
        'upload_max_kb' => 'integer',
        'form_fields' => 'array',
        'target_pemohon' => 'array',
    ];

    public function template()
    {
        return $this->hasOne(SuratTemplate::class, 'surat_jenis_id')->where('aktif', true);
    }

    public function templates()
    {
        return $this->hasMany(SuratTemplate::class, 'surat_jenis_id');
    }

    public function surats()
    {
        return $this->hasMany(Surat::class, 'surat_jenis_id');
    }

    public function workflowSteps()
    {
        return $this->hasMany(SuratRoleAssignment::class, 'surat_jenis_id')->orderBy('urutan');
    }
}
