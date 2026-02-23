<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\AssessmentScore;

class SeminarNilai extends Model
{
    use HasFactory;

    protected $table = 'seminar_nilai';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'seminar_id',
        'dosen_id',
        'jenis_penilai',
        'nilai_angka',
        'komponen_nilai',
        'catatan',
    ];

    /**
     * Cast attributes to proper types
     */
    protected $casts = [
        'komponen_nilai' => 'array',
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

    /**
     * Relationship with assessment scores
     */
    public function assessmentScores()
    {
        return $this->hasMany(AssessmentScore::class, 'seminar_nilai_id');
    }

    /**
     * Calculate final score based on assessment aspects
     * Returns the average of all aspect scores (not weighted by aspect percentages)
     */
    public function calculateFinalScore()
    {
        $scores = $this->assessmentScores()->with('assessmentAspect')->get();
        if ($scores->isEmpty()) return 0;

        // Group scores by category for potential dynamic calculations
        $scoresByAspectId = $scores->keyBy('assessment_aspect_id');
        $aspects = $this->seminar->seminarJenis->assessmentAspects()
            ->where('evaluator_type', $this->jenis_penilai)
            ->orderBy('urutan')
            ->get();

        $calculatedScores = [];
        $hasWeights = $aspects->sum('persentase') > 0;

        // Phase 1: Identify and compute calculation aspects
        foreach ($aspects as $aspect) {
            if ($aspect->type === 'input') {
                $score = $scoresByAspectId->get($aspect->id);
                $calculatedScores[$aspect->id] = $score ? (float)$score->nilai : 0;
            } else {
                // Calculation Type: sum or prev_avg
                // Priority 1: Use related_aspects (explicitly selected aspect IDs)
                // Priority 2: Use category (implicit grouping)
                
                $targetScores = collect();
                
                if (!empty($aspect->related_aspects)) {
                    foreach ($aspect->related_aspects as $relatedId) {
                        if (isset($calculatedScores[$relatedId])) {
                            $targetScores->push($calculatedScores[$relatedId]);
                        }
                    }
                } else if ($aspect->category) {
                    $relatedAspects = $aspects->where('type', 'input')->where('category', $aspect->category);
                    foreach ($relatedAspects as $rel) {
                        $targetScores->push($calculatedScores[$rel->id] ?? 0);
                    }
                }

                if ($targetScores->isEmpty()) {
                    $calculatedScores[$aspect->id] = 0;
                    continue;
                }

                $sum = $targetScores->sum();

                if ($aspect->type === 'prev_avg') {
                    $calculatedScores[$aspect->id] = $sum / $targetScores->count();
                } else {
                    $calculatedScores[$aspect->id] = $sum;
                }
                
                // Update the derived score in DB so it matches (important for PDF recap)
                AssessmentScore::updateOrCreate(
                    ['seminar_nilai_id' => $this->id, 'assessment_aspect_id' => $aspect->id],
                    ['nilai' => $calculatedScores[$aspect->id]]
                );
            }
        }

        // Phase 2: Compute Final Score
        if ($hasWeights) {
            $finalScore = 0;
            foreach ($aspects as $aspect) {
                if ($aspect->persentase > 0) {
                    $finalScore += ($calculatedScores[$aspect->id] ?? 0) * ($aspect->persentase / 100);
                }
            }
            return round($finalScore, 2);
        }

        // Check for "calculated" aspects (sum/avg) to determine final score
        // Only if weights are not valid (persentase sum = 0)
        $calculatedAspects = $aspects->whereIn('type', ['sum', 'prev_avg']);
        
        if ($calculatedAspects->isNotEmpty()) {
            // Assume the last calculated aspect (by order) is the Final Score
            $finalAspect = $calculatedAspects->last();
            return round($calculatedScores[$finalAspect->id] ?? 0, 2);
        }

        // Fallback: Simple Average of all 'input' aspects
        $inputAspects = $aspects->where('type', 'input');
        if ($inputAspects->isEmpty()) return 0;

        $total = 0;
        foreach ($inputAspects as $aspect) {
            $total += ($calculatedScores[$aspect->id] ?? 0);
        }
        
        return round($total / $inputAspects->count(), 2);
    }
}
