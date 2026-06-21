<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BeceMockScore extends Model
{
    protected $fillable = [
        'student_id', 'subject_id', 'class_academic_year_id',
        'mock_exam_label', 'raw_score', 'bece_grade', 'recorded_by',
    ];

    protected $casts = [
        'raw_score' => 'decimal:2',
        'bece_grade' => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function classAcademicYear(): BelongsTo
    {
        return $this->belongsTo(ClassAcademicYear::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Convert a raw percentage score to a WAEC BECE 1-9 grade.
     *
     * WAEC BECE Grading Scale:
     * 1 = 80-100 (Highest)
     * 2 = 70-79
     * 3 = 65-69
     * 4 = 60-64
     * 5 = 55-59
     * 6 = 50-54
     * 7 = 40-49
     * 8 = 30-39
     * 9 = 0-29 (Lowest/Fail)
     */
    public static function rawScoreToBECEGrade(float $score): int
    {
        return match (true) {
            $score >= 80 => 1,
            $score >= 70 => 2,
            $score >= 65 => 3,
            $score >= 60 => 4,
            $score >= 55 => 5,
            $score >= 50 => 6,
            $score >= 40 => 7,
            $score >= 30 => 8,
            default => 9,
        };
    }

    /**
     * Get the interpretation label for a BECE grade.
     */
    public static function gradeInterpretation(int $grade): string
    {
        return match ($grade) {
            1 => 'Excellent',
            2 => 'Very Good',
            3 => 'Good',
            4 => 'Credit',
            5 => 'Credit',
            6 => 'Credit',
            7 => 'Pass',
            8 => 'Pass',
            9 => 'Fail',
            default => 'N/A',
        };
    }

    /**
     * Calculate BECE aggregate for a student (best 6 subjects, lower = better).
     * Aggregate of 6 = best possible, 54 = worst possible.
     * At-risk threshold: aggregate > 36.
     */
    public static function calculateAggregate(int $studentId, int $classAcademicYearId, string $mockLabel = 'Mock 1'): ?array
    {
        $scores = static::where('student_id', $studentId)
            ->where('class_academic_year_id', $classAcademicYearId)
            ->where('mock_exam_label', $mockLabel)
            ->orderBy('bece_grade', 'asc')
            ->get();

        if ($scores->isEmpty()) {
            return null;
        }

        // Best 6 subjects (lowest grade numbers)
        $best6 = $scores->take(6);
        $aggregate = $best6->sum('bece_grade');

        return [
            'aggregate' => $aggregate,
            'best6' => $best6,
            'all_scores' => $scores,
            'is_at_risk' => $aggregate > 36,
            'total_subjects' => $scores->count(),
        ];
    }
}
