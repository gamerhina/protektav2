<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property \Illuminate\Support\Carbon|null $tanggal
 */
class Seminar extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mahasiswa_id',
        'seminar_jenis_id',
        'no_surat',
        'judul',
        'tanggal',
        'waktu_mulai',
        'lokasi',
        'p1_dosen_id',
        'p1_nama',
        'p1_nip',
        'p2_dosen_id',
        'p2_nama',
        'p2_nip',
        'pembahas_dosen_id',
        'pembahas_nama',
        'pembahas_nip',
        'berkas_syarat',
        'status',
        'tanggal_nilai',
        'folder_gdrive',
        'undangan_sent_at',
        'undangan_recipients',
        'nilai_sent_at',
        'nilai_recipients',
        'borang_sent_at',
        'borang_recipients',
        'nilai_catatan',
        'custom_html',
    ];

    /**
     * Cast attributes to proper types
     */
    protected $casts = [
        'berkas_syarat' => 'array',
        'tanggal' => 'date',
        'tanggal_nilai' => 'datetime',
        'undangan_sent_at' => 'datetime',
        'undangan_recipients' => 'array',
        'nilai_sent_at' => 'datetime',
        'nilai_recipients' => 'array',
        'borang_sent_at' => 'datetime',
        'borang_recipients' => 'array',
    ];

    /**
     * Relationship with mahasiswa
     */
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    /**
     * Relationship with seminar jenis
     */
    public function seminarJenis()
    {
        return $this->belongsTo(SeminarJenis::class, 'seminar_jenis_id');
    }

    /**
     * Relationship with P1 dosen
     */
    public function p1Dosen()
    {
        return $this->belongsTo(Dosen::class, 'p1_dosen_id');
    }

    /**
     * Relationship with P2 dosen
     */
    public function p2Dosen()
    {
        return $this->belongsTo(Dosen::class, 'p2_dosen_id');
    }

    /**
     * Relationship with pembahas dosen
     */
    public function pembahasDosen()
    {
        return $this->belongsTo(Dosen::class, 'pembahas_dosen_id');
    }

    /**
     * Relationship with nilai
     */
    public function nilai()
    {
        return $this->hasMany(SeminarNilai::class, 'seminar_id');
    }

    /**
     * Relationship with signatures
     */
    public function signatures()
    {
        return $this->hasMany(SeminarSignature::class, 'seminar_id');
    }

    /**
     * Relationship with comments
     */
    public function comments()
    {
        return $this->hasMany(SeminarComment::class)->latest();
    }

    public function calculateWeightedScore(): float
    {
        $jenis = $this->seminarJenis;
        if (!$jenis) {
            return 0;
        }

        $submittedNilai = $this->nilai;
        if ($submittedNilai->isEmpty()) {
            return 0;
        }

        $scores = [
            'p1' => $submittedNilai->where('jenis_penilai', 'p1')->first()?->nilai_angka ?? 0,
            'p2' => $submittedNilai->where('jenis_penilai', 'p2')->first()?->nilai_angka ?? 0,
            'pembahas' => $submittedNilai->where('jenis_penilai', 'pembahas')->first()?->nilai_angka ?? 0,
        ];

        $p1Weight = (float) $jenis->p1_weight;
        $p2Weight = (float) $jenis->p2_weight;
        $pembahasWeight = (float) $jenis->pembahas_weight;

        // If weights are 0, might need to fallback to simple average or handle accordingly.
        // But usually they specify percentages (e.g. 40, 30, 30).
        $totalWeight = $p1Weight + $p2Weight + $pembahasWeight;

        if ($totalWeight <= 0) {
            return (float) $submittedNilai->avg('nilai_angka');
        }

        $weightedSum = ($scores['p1'] * $p1Weight) + ($scores['p2'] * $p2Weight) + ($scores['pembahas'] * $pembahasWeight);
        
        return round($weightedSum / $totalWeight, 2);
    }

    /**
     * Recalculate and update seminar status based on evaluator nilai and signatures.
     */
    public function refreshCompletionStatus(): void
    {
        $jenis = $this->seminarJenis;
        $p1Required = (bool) ($jenis?->p1_required ?? true);
        $p2Required = (bool) ($jenis?->p2_required ?? true);
        $pembahasRequired = (bool) ($jenis?->pembahas_required ?? true);

        $evaluatorIds = [];
        if ($p1Required && ($this->p1_dosen_id || $this->p1_nama)) {
            $evaluatorIds[] = ['dosen_id' => $this->p1_dosen_id, 'jenis_penilai' => 'p1'];
        }
        if ($p2Required && ($this->p2_dosen_id || $this->p2_nama)) {
            $evaluatorIds[] = ['dosen_id' => $this->p2_dosen_id, 'jenis_penilai' => 'p2'];
        }
        if ($pembahasRequired && ($this->pembahas_dosen_id || $this->pembahas_nama)) {
            $evaluatorIds[] = ['dosen_id' => $this->pembahas_dosen_id, 'jenis_penilai' => 'pembahas'];
        }

        if (empty($evaluatorIds)) {
            return;
        }

        $submittedNilai = $this->nilai()
            ->where(function ($query) use ($evaluatorIds) {
                foreach ($evaluatorIds as $evaluator) {
                    $query->orWhere(function ($subQuery) use ($evaluator) {
                        $subQuery->where('dosen_id', $evaluator['dosen_id'])
                            ->where('jenis_penilai', $evaluator['jenis_penilai']);
                    });
                }
            })
            ->get();

        foreach ($submittedNilai as $nilai) {
            $nilai->nilai_angka = $nilai->calculateFinalScore();
            $nilai->save();
        }

        $evaluatorsComplete = 0;
        foreach ($evaluatorIds as $evaluator) {
            $nilaiRecord = $this->nilai()
                ->where('jenis_penilai', $evaluator['jenis_penilai'])
                ->where('dosen_id', $evaluator['dosen_id'])
                ->first();

            $hasSignature = $this->signatures()
                ->where('dosen_id', $evaluator['dosen_id'])
                ->where('jenis_penilai', $evaluator['jenis_penilai'])
                ->exists();

            $isNilaiComplete = false;
            if ($nilaiRecord) {
                // Check if all aspects for this role are filled
                $aspectCount = $this->seminarJenis->assessmentAspects()
                    ->where('evaluator_type', $evaluator['jenis_penilai'])
                    ->count();
                
                if ($aspectCount > 0) {
                    $scoreCount = $nilaiRecord->assessmentScores()->count();
                    // Nilai is complete ONLY if score count matches or exceeds aspect count
                    $isNilaiComplete = ($scoreCount >= $aspectCount);
                } else {
                    // Fallback to basic check if no aspects defined
                    $isNilaiComplete = $nilaiRecord->nilai_angka > 0;
                }
            }

            if ($isNilaiComplete && $hasSignature) {
                $evaluatorsComplete++;
            }
        }

        $allComplete = $evaluatorsComplete >= count($evaluatorIds);
        $someComplete = $submittedNilai->count() > 0 || $this->signatures()->count() > 0;

        $seminarDate = $this->tanggal ? (clone $this->tanggal)->startOfDay() : null;
        $today = now()->startOfDay();
        $isDueOrPast = $seminarDate && $seminarDate->lte($today);

        // Determine status transitions
        $previousStatus = $this->status;
        
        if ($allComplete) {
            if ($this->status !== 'selesai') {
                $this->update([
                    'status' => 'selesai',
                    'tanggal_nilai' => now(),
                ]);
            }
        } else {
            // Not complete
            if ($this->status === 'selesai') {
                // If it was finished but now incomplete, revert to belum_lengkap
                $this->update(['status' => 'belum_lengkap']);
            } elseif ($this->status === 'disetujui' && $isDueOrPast) {
                // If approved but date reached and not complete, mark as belum_lengkap
                $this->update(['status' => 'belum_lengkap']);
            }
        }

        // Notify if status was changed to selesai
        if ($previousStatus !== $this->status && $this->status === 'selesai') {
            if ($this->mahasiswa) {
                $this->mahasiswa->notify(new \App\Notifications\SeminarStatusUpdatedNotification($this, $previousStatus));
            }

            $admins = \App\Models\Admin::all();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\SeminarStatusUpdatedNotification($this, $previousStatus));
            }
        }
    }
}
