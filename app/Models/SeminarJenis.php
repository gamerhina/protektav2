<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeminarJenis extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nama',
        'kode',
        'keterangan',
        'syarat_seminar',
        'berkas_syarat_extensions',
        'berkas_syarat_max_size_kb',
        'berkas_syarat_items',
        'p1_weight',
        'p2_weight',
        'pembahas_weight',
        'p1_required',
        'p2_required',
        'pembahas_required',
        'grading_scheme',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'p1_weight' => 'decimal:2',
        'p2_weight' => 'decimal:2',
        'pembahas_weight' => 'decimal:2',
        'p1_required' => 'boolean',
        'p2_required' => 'boolean',
        'pembahas_required' => 'boolean',
        'grading_scheme' => 'array',
        'berkas_syarat_extensions' => 'array',
        'berkas_syarat_max_size_kb' => 'integer',
        'berkas_syarat_items' => 'array',
    ];

    /**
     * Relationship with seminars
     */
    public function seminars()
    {
        return $this->hasMany(Seminar::class, 'seminar_jenis_id');
    }

    /**
     * Relationship with assessment aspects
     */
    public function assessmentAspects()
    {
        return $this->hasMany(AssessmentAspect::class, 'seminar_jenis_id');
    }

    /**
     * Relationship with document templates
     */
    public function documentTemplates()
    {
        return $this->hasMany(DocumentTemplate::class, 'seminar_jenis_id');
    }
}
