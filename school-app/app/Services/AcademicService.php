<?php

namespace App\Services;

use App\Models\AssessmentScore;
use App\Models\ClassAcademicYear;
use App\Models\GradeScale;
use App\Models\Student;
use App\Models\TimetableSlot;
use Illuminate\Support\Collection;

class AcademicService
{
    /**
     * Calculate the final weighted score for a student in a specific subject.
     * Respects the CA/Exam weighting defined in AssessmentComponents.
     */
    public function calculateSubjectTotal(int $studentId, int $subjectId, int $classYearId): float
    {
        $scores = AssessmentScore::with('component')
            ->where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->where('class_academic_year_id', $classYearId)
            ->get();

        $total = 0;
        foreach ($scores as $score) {
            // weight is a percentage (e.g. 30.00 or 70.00)
            $weighted = ($score->score / $score->component->max_score) * ($score->component->weight);
            $total += $weighted;
        }

        return round($total, 2);
    }

    /**
     * Map a numeric score to a Ghana-standard grade based on level.
     */
    public function getGrade(float $score, string $level): string
    {
        $scale = GradeScale::where('level', $level)
            ->where('min_score', '<=', $score)
            ->where('max_score', '>=', $score)
            ->first();

        return $scale ? $scale->grade : 'N/A';
    }

    /**
     * Calculate JHS BECE Aggregate (Best 6 Subjects).
     */
    public function calculateJHSAggregate(int $studentId, int $classYearId): int
    {
        $classYear = ClassAcademicYear::findOrFail($classYearId);
        $subjectScores = AssessmentScore::where('student_id', $studentId)
            ->where('class_academic_year_id', $classYearId)
            ->get()
            ->groupBy('subject_id');

        $grades = collect();

        foreach ($subjectScores as $subjectId => $scores) {
            $total = $this->calculateSubjectTotal($studentId, $subjectId, $classYearId);
            $gradeStr = $this->getGrade($total, 'jhs');

            if (is_numeric($gradeStr)) {
                $grades->push((int) $gradeStr);
            }
        }

        // BECE logic: Aggregate is the sum of the best 6 subjects.
        // A typical "at-risk" threshold is aggregate 36.
        return $grades->sort()->take(6)->sum();
    }

    /**
     * Rank students in a class based on their total average.
     */
    public function rankClass(int $classYearId): Collection
    {
        $classYear = ClassAcademicYear::with('students')->findOrFail($classYearId);
        $rankings = collect();

        foreach ($classYear->students as $student) {
            $subjects = AssessmentScore::where('student_id', $student->id)
                ->where('class_academic_year_id', $classYearId)
                ->distinct()
                ->pluck('subject_id');

            if ($subjects->isEmpty()) {
                continue;
            }

            $totalPoints = 0;
            foreach ($subjects as $subjectId) {
                $totalPoints += $this->calculateSubjectTotal($student->id, $subjectId, $classYearId);
            }

            $average = $totalPoints / $subjects->count();

            $rankings->push([
                'student_id' => $student->id,
                'name' => $student->full_name,
                'total_average' => round($average, 2),
            ]);
        }

        return $rankings->sortByDesc('total_average')->values()->map(function ($item, $key) {
            $item['position'] = $key + 1;
            return $item;
        });
    }

    /**
     * Check for Timetable Clashes.
     * Proves: Teacher cannot be in two classes at once, and a class cannot have two subjects at once.
     */
    public function hasClash(int $classYearId, int $staffId, string $day, string $start, string $end, ?int $ignoreId = null): bool
    {
        $query = TimetableSlot::where('day_of_week', $day)
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        // Check if teacher is busy elsewhere
        $teacherBusy = (clone $query)->where('staff_id', $staffId)->exists();

        // Check if class already has a subject at this time
        $classBusy = (clone $query)->where('class_academic_year_id', $classYearId)->exists();

        return $teacherBusy || $classBusy;
    }
}