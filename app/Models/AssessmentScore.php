<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentScore extends Model
{
    protected $fillable = [
        'seminar_nilai_id',
        'assessment_aspect_id',
        'nilai',
    ];

    protected $casts = [
        'nilai' => 'decimal:2',
    ];

    public function seminarNilai()
    {
        return $this->belongsTo(SeminarNilai::class, 'seminar_nilai_id');
    }

    public function assessmentAspect()
    {
        return $this->belongsTo(AssessmentAspect::class, 'assessment_aspect_id');
    }
}
